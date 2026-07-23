<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_QUOTE_IMG_BYTES = 5 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$IMG_DIR = __DIR__ . '/quoteimages/';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblquotes SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Quote status updated.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("SELECT QuoteImage FROM tblquotes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row && $row['QuoteImage']) {
                @unlink(__DIR__ . '/quoteimages/' . basename($row['QuoteImage']));
            }

            $delStmt = $con->prepare("DELETE FROM tblquotes WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
            $delStmt->close();
            $success = 'Quote deleted.';
        } elseif ($action === 'edit' && $id > 0) {
            $name = trim($_POST['name'] ?? '');
            $quote = trim($_POST['quote'] ?? '');
            $year = trim($_POST['year_id'] ?? '');

            if ($name === '' || $quote === '') {
                $error = 'Name and quote text are required.';
            } else {
                $newImage = null;
                $file = $_FILES['quote_image'] ?? null;
                if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Upload failed (error code ' . $file['error'] . ').';
                    } elseif ($file['size'] > MAX_QUOTE_IMG_BYTES) {
                        $error = 'Image is too large (max 5MB).';
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
                        $stmt = $con->prepare("SELECT QuoteImage FROM tblquotes WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $old = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        if ($old && $old['QuoteImage']) {
                            @unlink($IMG_DIR . basename($old['QuoteImage']));
                        }

                        $stmt = $con->prepare("UPDATE tblquotes SET Name = ?, Quote = ?, QuoteImage = ?, year_id = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $name, $quote, $newImage, $year, $id);
                    } else {
                        $stmt = $con->prepare("UPDATE tblquotes SET Name = ?, Quote = ?, year_id = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $name, $quote, $year, $id);
                    }
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Quote updated.';
                }
            }
        }
    }
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, Name, Quote, QuoteImage, year_id FROM tblquotes WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$quotes = [];
$result = mysqli_query($con, "SELECT id, Name, Quote, QuoteImage, year_id, Is_Active FROM tblquotes ORDER BY updated_at DESC");
if ($result) {
    $quotes = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Quotes</h4>
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
                        <h5>Edit Quote</h5>
                        <form method="post" action="manage-quotes.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                            <div class="form-group">
                                <label>Person / Source</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editRow['Name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Year (optional)</label>
                                <input type="text" name="year_id" class="form-control" maxlength="10" value="<?= htmlspecialchars($editRow['year_id'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <?php if ($editRow['QuoteImage']): ?>
                                    <img src="quoteimages/<?= htmlspecialchars($editRow['QuoteImage']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:50%;" class="mb-2 d-block">
                                <?php endif; ?>
                                <label>Replace Photo (optional)</label>
                                <input type="file" name="quote_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div class="form-group">
                                <label>Quote</label>
                                <textarea name="quote" class="form-control" rows="4" required><?= htmlspecialchars($editRow['Quote']) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-quotes.php" class="btn btn-light">Cancel</a>
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
                                        <th>Photo</th>
                                        <th>Quote</th>
                                        <th>Person</th>
                                        <th>Year</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($quotes)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No quotes yet.</td></tr>
                                    <?php else: foreach ($quotes as $q): ?>
                                        <tr>
                                            <td>
                                                <?php if ($q['QuoteImage']): ?>
                                                    <img src="quoteimages/<?= htmlspecialchars($q['QuoteImage']) ?>" alt="<?= htmlspecialchars($q['Name']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars(mb_strimwidth($q['Quote'], 0, 80, '...')) ?></td>
                                            <td><?= htmlspecialchars($q['Name']) ?></td>
                                            <td><?= htmlspecialchars($q['year_id']) ?></td>
                                            <td>
                                                <?php if ($q['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="manage-quotes.php?edit=<?= (int) $q['id'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-quotes.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning"><?= $q['Is_Active'] ? 'Deactivate' : 'Activate' ?></button>
                                                </form>
                                                <form method="post" action="manage-quotes.php" style="display:inline;" onsubmit="return confirm('Delete this quote?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $q['id'] ?>">
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
