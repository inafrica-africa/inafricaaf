<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_GALLERY_IMG_BYTES = 5 * 1024 * 1024;
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
        $title = trim($_POST['title'] ?? '');
        $mediaType = $_POST['media_type'] ?? 'Image';
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || !in_array($mediaType, ['Image', 'Video'], true)) {
            $error = 'Title and media type are required.';
        } elseif ($mediaType === 'Video') {
            $youtubeUrl = trim($_POST['youtube_url'] ?? '');
            $videoId = extractYoutubeId($youtubeUrl);
            if (!$videoId) {
                $error = 'Please enter a valid YouTube video URL.';
            } else {
                $stmt = $con->prepare("INSERT INTO tblgallery (Title, MediaType, YoutubeUrl, Description, Is_Active) VALUES (?, 'Video', ?, ?, 1)");
                $stmt->bind_param("sss", $title, $youtubeUrl, $description);
                $stmt->execute();
                $stmt->close();
                $success = 'Video added to gallery.';
            }
        } else {
            $file = $_FILES['gallery_image'] ?? null;
            if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
                $error = 'Please choose an image to upload.';
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload failed (error code ' . $file['error'] . ').';
            } elseif ($file['size'] > MAX_GALLERY_IMG_BYTES) {
                $error = 'Image is too large (max 5MB).';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                if (!isset($ALLOWED_MIME[$mime])) {
                    $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                } else {
                    $imageName = bin2hex(random_bytes(16)) . '.' . $ALLOWED_MIME[$mime];
                    if (!move_uploaded_file($file['tmp_name'], $IMG_DIR . $imageName)) {
                        $error = 'Could not save the uploaded image.';
                    } else {
                        $stmt = $con->prepare("INSERT INTO tblgallery (Title, MediaType, ImagePath, Description, Is_Active) VALUES (?, 'Image', ?, ?, 1)");
                        $stmt->bind_param("sss", $title, $imageName, $description);
                        $stmt->execute();
                        $stmt->close();
                        $success = 'Image added to gallery.';
                    }
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
                    <h4 class="page-title">Add to Gallery</h4>
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
                        <form method="post" action="add-gallery.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="media_type" id="media_type" class="form-control">
                                    <option value="Image">Image</option>
                                    <option value="Video">YouTube Video</option>
                                </select>
                            </div>
                            <div class="form-group" id="image_field">
                                <label>Image</label>
                                <input type="file" name="gallery_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div class="form-group" id="video_field" style="display:none;">
                                <label>YouTube URL</label>
                                <input type="text" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                            <div class="form-group">
                                <label>Description (optional)</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add to Gallery</button>
                            <a href="manage-gallery.php" class="btn btn-light">View Gallery</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        function toggleMediaFields() {
            var type = $('#media_type').val();
            $('#image_field').toggle(type === 'Image');
            $('#video_field').toggle(type === 'Video');
        }
        $('#media_type').on('change', toggleMediaFields);
        toggleMediaFields();
    });
</script>
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
