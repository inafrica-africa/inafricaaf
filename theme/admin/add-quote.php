<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_QUOTE_IMG_BYTES = 2048 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$IMG_DIR = __DIR__ . '/quoteimages/';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $quote = trim($_POST['quote'] ?? '');
        $year = trim($_POST['year_id'] ?? '');

        if ($name === '' || $quote === '') {
            $error = 'Name and quote text are required.';
        } else {
            $imageName = null;
            $file = $_FILES['quote_image'] ?? null;
            if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Upload failed (error code ' . $file['error'] . ').';
                } elseif ($file['size'] > MAX_QUOTE_IMG_BYTES) {
                    $error = 'Image is too large (max 2GB).';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    if (!isset($ALLOWED_MIME[$mime])) {
                        $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                    } else {
                        $imageName = bin2hex(random_bytes(16)) . '.' . $ALLOWED_MIME[$mime];
                        if (!move_uploaded_file($file['tmp_name'], $IMG_DIR . $imageName)) {
                            $error = 'Could not save the uploaded image.';
                            $imageName = null;
                        }
                    }
                }
            }

            if (!$error) {
                $stmt = $con->prepare("INSERT INTO tblquotes (Name, Quote, QuoteImage, year_id, Is_Active) VALUES (?, ?, ?, ?, 1)");
                $stmt->bind_param("ssss", $name, $quote, $imageName, $year);
                $stmt->execute();
                $stmt->close();
                $success = 'Quote added successfully.';
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
                    <h4 class="page-title">Add Quote</h4>
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
                        <form method="post" action="add-quote.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label>Person / Source</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Year (optional)</label>
                                <input type="text" name="year_id" class="form-control" maxlength="10">
                            </div>
                            <div class="form-group">
                                <label>Photo (optional, shown in the homepage cycle)</label>
                                <input type="file" name="quote_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div class="form-group">
                                <label>Quote</label>
                                <textarea name="quote" class="form-control" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Quote</button>
                            <a href="manage-quotes.php" class="btn btn-light">View All Quotes</a>
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
