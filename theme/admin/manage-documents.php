<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_DOC_BYTES_MP = 2048 * 1024 * 1024;
$ALLOWED_DOC_MIME_MP = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
];
$DOC_TYPES_MP = ['Statement', 'Letter', 'Report'];
$DOC_DIR_MP = __DIR__ . '/documents/';
$LANGUAGES = ['English', 'Swahili', 'French'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle' && $id > 0) {
            $stmt = $con->prepare("UPDATE tbldocuments SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Document status updated.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("SELECT FilePath FROM tbldocuments WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $doc = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($doc) {
                @unlink(__DIR__ . '/documents/' . basename($doc['FilePath']));
            }

            $delStmt = $con->prepare("DELETE FROM tbldocuments WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
            $delStmt->close();
            $success = 'Document deleted.';
        } elseif ($action === 'edit' && $id > 0) {
            $title = trim($_POST['title'] ?? '');
            $docType = $_POST['doc_type'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $language = in_array($_POST['language'] ?? '', $LANGUAGES, true) ? $_POST['language'] : 'English';

            if ($title === '' || !in_array($docType, $DOC_TYPES_MP, true)) {
                $error = 'Title and document type are required.';
            } else {
                $newFilename = null;
                $file = $_FILES['document_file'] ?? null;
                if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Upload failed (error code ' . $file['error'] . ').';
                    } elseif ($file['size'] > MAX_DOC_BYTES_MP) {
                        $error = 'File is too large (max 2GB).';
                    } else {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($file['tmp_name']);
                        if (!isset($ALLOWED_DOC_MIME_MP[$mime])) {
                            $error = 'Only PDF, DOC, or DOCX files are allowed.';
                        } else {
                            $newFilename = bin2hex(random_bytes(16)) . '.' . $ALLOWED_DOC_MIME_MP[$mime];
                            if (!move_uploaded_file($file['tmp_name'], $DOC_DIR_MP . $newFilename)) {
                                $error = 'Could not save the uploaded file.';
                                $newFilename = null;
                            }
                        }
                    }
                }

                if (!$error) {
                    if ($newFilename) {
                        $stmt = $con->prepare("SELECT FilePath FROM tbldocuments WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $old = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        if ($old && $old['FilePath']) {
                            @unlink($DOC_DIR_MP . basename($old['FilePath']));
                        }

                        $stmt = $con->prepare("UPDATE tbldocuments SET Title = ?, DocType = ?, Language = ?, Description = ?, FilePath = ? WHERE id = ?");
                        $stmt->bind_param("sssssi", $title, $docType, $language, $description, $newFilename, $id);
                    } else {
                        $stmt = $con->prepare("UPDATE tbldocuments SET Title = ?, DocType = ?, Language = ?, Description = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $title, $docType, $language, $description, $id);
                    }
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Document updated.';
                }
            }
        }
    }
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, Title, DocType, Language, Description, FilePath FROM tbldocuments WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$documents = [];
$result = mysqli_query($con, "SELECT id, Title, DocType, Language, FilePath, UploadDate, Is_Active FROM tbldocuments ORDER BY UploadDate DESC");
if ($result) {
    $documents = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Documents</h4>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($editRow): ?>
            <div class="row">
                <div class="col-md-9">
                    <div class="card-box">
                        <h5>Edit Document</h5>
                        <form method="post" action="manage-documents.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editRow['Title']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="doc_type" class="form-control" required>
                                    <?php foreach ($DOC_TYPES_MP as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>" <?= $type === $editRow['DocType'] ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Language</label>
                                <select name="language" class="form-control">
                                    <?php foreach ($LANGUAGES as $lang): ?>
                                        <option value="<?= htmlspecialchars($lang) ?>" <?= $lang === $editRow['Language'] ? 'selected' : '' ?>><?= htmlspecialchars($lang) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editRow['Description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Replace File (optional &mdash; currently: <?= htmlspecialchars($editRow['FilePath']) ?>)</label>
                                <input type="file" name="document_file" class="form-control-file" accept=".pdf,.doc,.docx">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-documents.php" class="btn btn-light">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Language</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($documents)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No documents yet.</td></tr>
                                    <?php else: foreach ($documents as $doc): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($doc['Title']) ?></td>
                                            <td><?= htmlspecialchars($doc['DocType']) ?></td>
                                            <td><?= htmlspecialchars($doc['Language']) ?></td>
                                            <td><?= date('M j, Y', strtotime($doc['UploadDate'])) ?></td>
                                            <td>
                                                <?php if ($doc['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="documents/<?= htmlspecialchars($doc['FilePath']) ?>" target="_blank" class="btn btn-xs btn-info">View</a>
                                                <a href="manage-documents.php?edit=<?= (int) $doc['id'] ?>" class="btn btn-xs btn-secondary">Edit</a>
                                                <form method="post" action="manage-documents.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning">
                                                        <?= $doc['Is_Active'] ? 'Deactivate' : 'Activate' ?>
                                                    </button>
                                                </form>
                                                <form method="post" action="manage-documents.php" style="display:inline;" onsubmit="return confirm('Permanently delete this document?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                </div>
      </div>
      <!-- END wrapper -->
      <script src="../assets/js/bootstrap.min.js"></script>
      <script src="../assets/js/detect.js"></script>
      <script src="../assets/js/fastclick.js"></script>
      <script src="../assets/js/jquery.blockUI.js"></script>
      <script src="../assets/js/waves.js"></script>
      <script src="../assets/js/jquery.slimscroll.js"></script>
      <script src="../assets/js/jquery.scrollTo.min.js"></script>
      <script src="../assets/js/jquery.core.js"></script>
      <script src="../assets/js/jquery.app.js"></script>
   </body>
</html>
