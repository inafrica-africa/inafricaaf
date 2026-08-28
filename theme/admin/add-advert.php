<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_ADVERT_IMG_BYTES = 2048 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $targetUrl = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $file = $_FILES['advert_image'] ?? null;

        if ($title === '') {
            $error = 'Title is required.';
        } elseif (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $error = 'Please choose an image to upload.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload failed (error code ' . $file['error'] . ').';
        } elseif ($file['size'] > MAX_ADVERT_IMG_BYTES) {
            $error = 'Image is too large (max 2GB).';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!in_array($mime, $ALLOWED_MIME, true)) {
                $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
            } else {
                $binary = file_get_contents($file['tmp_name']);
                $stmt = $con->prepare("INSERT INTO gifadvert (title, file, url, description, Is_Active) VALUES (?, ?, ?, ?, 1)");
                $stmt->bind_param("ssss", $title, $binary, $targetUrl, $description);
                $stmt->execute();
                $stmt->close();
                $success = 'Advert uploaded.';
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
                    <h4 class="page-title">Add Advert</h4>
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
                        <p class="text-muted">
                            The image's own dimensions decide where it's shown: wider-than-tall images are
                            used as landscape banners, taller-than-wide images are used as portrait posters.
                        </p>
                        <form method="post" action="add-advert.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Image (JPG, PNG, GIF, or WEBP &mdash; landscape or portrait)</label>
                                <input type="file" name="advert_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp" required>
                            </div>
                            <div class="form-group">
                                <label>Target URL (where the advert links to)</label>
                                <input type="text" name="url" class="form-control" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>Description (optional)</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload Advert</button>
                            <a href="manage-adverts.php" class="btn btn-light">Manage Adverts</a>
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
