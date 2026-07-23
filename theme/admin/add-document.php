<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_DOC_BYTES = 10 * 1024 * 1024;
$ALLOWED_DOC_MIME = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
];
$DOC_DIR = __DIR__ . '/documents/';
$DOC_TYPES = ['Statement', 'Letter', 'Report'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $docType = $_POST['doc_type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $file = $_FILES['document_file'] ?? null;

        if ($title === '' || !in_array($docType, $DOC_TYPES, true)) {
            $error = 'Title and document type are required.';
        } elseif (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $error = 'Please choose a file to upload.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload failed (error code ' . $file['error'] . ').';
        } elseif ($file['size'] > MAX_DOC_BYTES) {
            $error = 'File is too large (max 10MB).';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!isset($ALLOWED_DOC_MIME[$mime])) {
                $error = 'Only PDF, DOC, or DOCX files are allowed.';
            } else {
                $filename = bin2hex(random_bytes(16)) . '.' . $ALLOWED_DOC_MIME[$mime];
                if (!move_uploaded_file($file['tmp_name'], $DOC_DIR . $filename)) {
                    $error = 'Could not save the uploaded file.';
                } else {
                    $uploadedBy = $_SESSION['admin_name'] ?? 'Admin';
                    $stmt = $con->prepare("INSERT INTO tbldocuments (Title, DocType, Description, FilePath, UploadedBy, Is_Active) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt->bind_param("sssss", $title, $docType, $description, $filename, $uploadedBy);
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Document uploaded successfully.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Add Document</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <div class="card-box">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <form method="post" action="add-document.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="doc_type" class="form-control" required>
                                    <option value="">-- Select Type --</option>
                                    <?php foreach ($DOC_TYPES as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>File (PDF, DOC, or DOCX)</label>
                                <input type="file" name="document_file" class="form-control-file" accept=".pdf,.doc,.docx" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload Document</button>
                            <a href="manage-documents.php" class="btn btn-light">View All Documents</a>
                        </form>
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
