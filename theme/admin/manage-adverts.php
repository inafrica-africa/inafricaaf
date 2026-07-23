<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle' && $id > 0) {
            $stmt = $con->prepare("UPDATE gifadvert SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Advert status updated.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("DELETE FROM gifadvert WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Advert deleted.';
        } elseif ($action === 'edit' && $id > 0) {
            $title = trim($_POST['title'] ?? '');
            $targetUrl = trim($_POST['url'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($title === '') {
                $error = 'Title is required.';
            } else {
                $newImage = null;
                $file = $_FILES['advert_image'] ?? null;
                if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Upload failed (error code ' . $file['error'] . ').';
                    } elseif ($file['size'] > 5 * 1024 * 1024) {
                        $error = 'Image is too large (max 5MB).';
                    } else {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($file['tmp_name']);
                        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
                            $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                        } else {
                            $newImage = file_get_contents($file['tmp_name']);
                        }
                    }
                }

                if (!$error) {
                    if ($newImage !== null) {
                        $stmt = $con->prepare("UPDATE gifadvert SET title = ?, file = ?, url = ?, description = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $title, $newImage, $targetUrl, $description, $id);
                    } else {
                        $stmt = $con->prepare("UPDATE gifadvert SET title = ?, url = ?, description = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $title, $targetUrl, $description, $id);
                    }
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Advert updated.';
                }
            }
        }
    }
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, title, url, description FROM gifadvert WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$items = [];
$result = mysqli_query($con, "SELECT id, title, file, url, Is_Active FROM gifadvert ORDER BY id DESC");
if ($result) {
    $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Adverts</h4>
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
                        <h5>Edit Advert</h5>
                        <form method="post" action="manage-adverts.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editRow['title']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Replace Image (optional, JPG/PNG/GIF/WEBP)</label>
                                <input type="file" name="advert_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div class="form-group">
                                <label>Target URL</label>
                                <input type="text" name="url" class="form-control" value="<?= htmlspecialchars($editRow['url'] ?? '') ?>" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>Description (optional)</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editRow['description'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-adverts.php" class="btn btn-light">Cancel</a>
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
                                        <th>Preview</th>
                                        <th>Title</th>
                                        <th>Orientation</th>
                                        <th>Target URL</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($items)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No adverts yet. <a href="add-advert.php">Add one</a>.</td></tr>
                                    <?php else: foreach ($items as $item):
                                        $info = @getimagesizefromstring($item['file'] ?? '');
                                        $orientation = 'Unknown';
                                        $thumb = null;
                                        if ($info) {
                                            $orientation = $info[0] >= $info[1] ? 'Landscape' : 'Portrait';
                                            $thumb = 'data:' . ($info['mime'] ?? 'image/png') . ';base64,' . base64_encode($item['file']);
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <?php if ($thumb): ?>
                                                    <img src="<?= $thumb ?>" alt="" style="max-width:70px;max-height:70px;object-fit:contain;border-radius:4px;">
                                                <?php else: ?>
                                                    <span class="text-muted">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($item['title']) ?></td>
                                            <td><?= $orientation ?></td>
                                            <td>
                                                <?php if (!empty($item['url'])): ?>
                                                    <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank"><?= htmlspecialchars($item['url']) ?></a>
                                                <?php else: ?>
                                                    <span class="text-muted">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="manage-adverts.php?edit=<?= (int) $item['id'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-adverts.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning"><?= $item['Is_Active'] ? 'Deactivate' : 'Activate' ?></button>
                                                </form>
                                                <form method="post" action="manage-adverts.php" style="display:inline;" onsubmit="return confirm('Delete this advert?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
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
