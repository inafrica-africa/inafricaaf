<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_LOGO_BYTES = 3 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
$LOGO_DIR = __DIR__ . '/regionlogos/';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $regionId = intval($_POST['region_id'] ?? 0);
        $file = $_FILES['logo'] ?? null;

        if ($regionId <= 0 || !$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $error = 'Please choose a logo file to upload.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload failed (error code ' . $file['error'] . ').';
        } elseif ($file['size'] > MAX_LOGO_BYTES) {
            $error = 'Logo is too large (max 3MB).';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!isset($ALLOWED_MIME[$mime])) {
                $error = 'Only JPG, PNG, WEBP, or SVG images are allowed.';
            } else {
                $logoName = bin2hex(random_bytes(16)) . '.' . $ALLOWED_MIME[$mime];
                if (!move_uploaded_file($file['tmp_name'], $LOGO_DIR . $logoName)) {
                    $error = 'Could not save the uploaded logo.';
                } else {
                    $stmt = $con->prepare("SELECT RegionLogo FROM tblregions WHERE id = ?");
                    $stmt->bind_param("i", $regionId);
                    $stmt->execute();
                    $old = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    if ($old && $old['RegionLogo']) {
                        @unlink($LOGO_DIR . basename($old['RegionLogo']));
                    }

                    $updateStmt = $con->prepare("UPDATE tblregions SET RegionLogo = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $logoName, $regionId);
                    $updateStmt->execute();
                    $updateStmt->close();
                    $success = 'Logo updated.';
                }
            }
        }
    }
}

$regions = [];
$result = mysqli_query($con, "SELECT id, RegionName, RegionLogo FROM tblregions ORDER BY RegionName ASC");
if ($result) {
    $regions = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Regional Bloc Logos</h4>
                    <p class="text-muted">The 6 regional blocs (EAC, SADC, ECOWAS, IGAD, ECCAS, Maghreb) are fixed &mdash; upload each one's official logo here. It's shown when a visitor clicks that region on the public site.</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($regions as $region): ?>
                <div class="col-md-4 mb-4">
                    <div class="card-box text-center">
                        <h5><?= htmlspecialchars($region['RegionName']) ?></h5>
                        <?php if ($region['RegionLogo']): ?>
                            <img src="regionlogos/<?= htmlspecialchars($region['RegionLogo']) ?>" alt="<?= htmlspecialchars($region['RegionName']) ?>" style="max-width:120px; max-height:120px; object-fit:contain;" class="mb-2">
                        <?php else: ?>
                            <p class="text-muted small">No logo uploaded yet.</p>
                        <?php endif; ?>
                        <form method="post" action="manage-regions.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="region_id" value="<?= (int) $region['id'] ?>">
                            <input type="file" name="logo" class="form-control-file mb-2" accept="image/jpeg,image/png,image/webp,image/svg+xml" required>
                            <button type="submit" class="btn btn-sm btn-primary">Upload Logo</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
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
