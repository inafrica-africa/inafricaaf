<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_GALLERY_IMG_BYTES = 2048 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$IMG_DIR = __DIR__ . '/gallery/';

function extractYoutubeId($url) {
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~', $url, $m)) {
        return $m[1];
    }
    return null;
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
            $stmt = $con->prepare("UPDATE tblgallery SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Gallery item status updated.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("SELECT ImagePath FROM tblgallery WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($item && $item['ImagePath']) {
                @unlink(__DIR__ . '/gallery/' . basename($item['ImagePath']));
            }

            $delStmt = $con->prepare("DELETE FROM tblgallery WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
            $delStmt->close();
            $success = 'Gallery item deleted.';
        } elseif ($action === 'edit' && $id > 0) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($title === '') {
                $error = 'Title is required.';
            } else {
                $stmt = $con->prepare("SELECT MediaType, ImagePath FROM tblgallery WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $current = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$current) {
                    $error = 'Gallery item not found.';
                } elseif ($current['MediaType'] === 'Video') {
                    $youtubeUrl = trim($_POST['youtube_url'] ?? '');
                    $videoId = extractYoutubeId($youtubeUrl);
                    if (!$videoId) {
                        $error = 'Please enter a valid YouTube video URL.';
                    } else {
                        $stmt = $con->prepare("UPDATE tblgallery SET Title = ?, YoutubeUrl = ?, Description = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $title, $youtubeUrl, $description, $id);
                        $stmt->execute();
                        $stmt->close();
                        $success = 'Gallery item updated.';
                    }
                } else {
                    $newImage = null;
                    $file = $_FILES['gallery_image'] ?? null;
                    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            $error = 'Upload failed (error code ' . $file['error'] . ').';
                        } elseif ($file['size'] > MAX_GALLERY_IMG_BYTES) {
                            $error = 'Image is too large (max 2GB).';
                        } else {
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mime = $finfo->file($file['tmp_name']);
                            if (!isset($ALLOWED_MIME[$mime])) {
                                $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                            } else {
                                $newImage = bin2hex(random_bytes(16)) . '.' . $ALLOWED_MIME[$mime];
                                if (!move_uploaded_file($file['tmp_name'], $IMG_DIR . $newImage)) {
                                    $error = 'Could not save the uploaded image.';
                                    $newImage = null;
                                }
                            }
                        }
                    }

                    if (!$error) {
                        if ($newImage) {
                            if ($current['ImagePath']) {
                                @unlink($IMG_DIR . basename($current['ImagePath']));
                            }
                            $stmt = $con->prepare("UPDATE tblgallery SET Title = ?, ImagePath = ?, Description = ? WHERE id = ?");
                            $stmt->bind_param("sssi", $title, $newImage, $description, $id);
                        } else {
                            $stmt = $con->prepare("UPDATE tblgallery SET Title = ?, Description = ? WHERE id = ?");
                            $stmt->bind_param("ssi", $title, $description, $id);
                        }
                        $stmt->execute();
                        $stmt->close();
                        $success = 'Gallery item updated.';
                    }
                }
            }
        }
    }
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, Title, MediaType, ImagePath, YoutubeUrl, Description FROM tblgallery WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$items = [];
$result = mysqli_query($con, "SELECT id, Title, MediaType, ImagePath, YoutubeUrl, Is_Active FROM tblgallery ORDER BY CreatedDate DESC");
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
                    <h4 class="page-title">Manage Gallery</h4>
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
                        <h5>Edit Gallery Item</h5>
                        <form method="post" action="manage-gallery.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editRow['Title']) ?>" required>
                            </div>
                            <?php if ($editRow['MediaType'] === 'Video'): ?>
                                <div class="form-group">
                                    <label>YouTube URL</label>
                                    <input type="text" name="youtube_url" class="form-control" value="<?= htmlspecialchars($editRow['YoutubeUrl'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=..." required>
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <?php if ($editRow['ImagePath']): ?>
                                        <img src="gallery/<?= htmlspecialchars($editRow['ImagePath']) ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:4px;" class="mb-2 d-block">
                                    <?php endif; ?>
                                    <label>Replace Image (optional)</label>
                                    <input type="file" name="gallery_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Description (optional)</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editRow['Description'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-gallery.php" class="btn btn-light">Cancel</a>
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
                                        <th>Type</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($items)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">No gallery items yet.</td></tr>
                                    <?php else: foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if ($item['MediaType'] === 'Image' && $item['ImagePath']): ?>
                                                    <img src="gallery/<?= htmlspecialchars($item['ImagePath']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">
                                                <?php else: ?>
                                                    <span class="badge badge-danger"><i class="mdi mdi-youtube"></i> YouTube</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($item['Title']) ?></td>
                                            <td><?= htmlspecialchars($item['MediaType']) ?></td>
                                            <td>
                                                <?php if ($item['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="manage-gallery.php?edit=<?= (int) $item['id'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-gallery.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning"><?= $item['Is_Active'] ? 'Deactivate' : 'Activate' ?></button>
                                                </form>
                                                <form method="post" action="manage-gallery.php" style="display:inline;" onsubmit="return confirm('Delete this gallery item?');">
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
