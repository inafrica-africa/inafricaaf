<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (($_SESSION['utype'] ?? null) !== '1') {
    require_once __DIR__ . '/../includes/topheader.php';
    require_once __DIR__ . '/../includes/leftsidebar.php';
    echo '<div class="content-page"><div class="content"><div class="container"><div class="alert alert-danger mt-3">You do not have permission to view this page.</div></div></div></div>';
    echo '</div></div><script src="../assets/js/bootstrap.min.js"></script><script src="../assets/js/detect.js"></script><script src="../assets/js/fastclick.js"></script><script src="../assets/js/jquery.blockUI.js"></script><script src="../assets/js/waves.js"></script><script src="../assets/js/jquery.slimscroll.js"></script><script src="../assets/js/jquery.scrollTo.min.js"></script><script src="../assets/js/jquery.core.js"></script><script src="../assets/js/jquery.app.js"></script></body></html>';
    exit;
}

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

        if ($action === 'delete' && $id > 0 && $id !== (int) $_SESSION['admin_id']) {
            $stmt = $con->prepare("DELETE FROM tbladmin WHERE id = ? AND userType = 0");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Sub-admin removed.';
        } elseif ($action === 'edit' && $id > 0) {
            $username = trim($_POST['username'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($username === '' || !$email) {
                $error = 'A valid username and email are required.';
            } elseif ($password !== '' && strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Password and confirmation do not match.';
            } else {
                $checkStmt = $con->prepare("SELECT id FROM tbladmin WHERE AdminUserName = ? AND id != ?");
                $checkStmt->bind_param("si", $username, $id);
                $checkStmt->execute();
                $exists = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if ($exists) {
                    $error = 'That username is already taken.';
                } else {
                    if ($password !== '') {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $con->prepare("UPDATE tbladmin SET AdminUserName = ?, AdminEmailId = ?, AdminPassword = ? WHERE id = ? AND userType = 0");
                        $stmt->bind_param("sssi", $username, $email, $hash, $id);
                    } else {
                        $stmt = $con->prepare("UPDATE tbladmin SET AdminUserName = ?, AdminEmailId = ? WHERE id = ? AND userType = 0");
                        $stmt->bind_param("ssi", $username, $email, $id);
                    }
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Sub-admin updated.';
                }
            }
        }
    }
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, AdminUserName, AdminEmailId FROM tbladmin WHERE id = ? AND userType = 0");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$subadmins = [];
$result = mysqli_query($con, "SELECT id, AdminUserName, AdminEmailId, CreationDate FROM tbladmin WHERE userType = 0 ORDER BY AdminUserName ASC");
if ($result) {
    $subadmins = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Sub-admins</h4>
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
                        <h5>Edit Sub-admin</h5>
                        <form method="post" action="manage-subadmins.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editRow['AdminUserName']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editRow['AdminEmailId'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>New Password (leave blank to keep current password)</label>
                                <input type="password" name="password" class="form-control" minlength="8">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="8">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-subadmins.php" class="btn btn-light">Cancel</a>
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
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($subadmins)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No sub-admins yet.</td></tr>
                                    <?php else: foreach ($subadmins as $admin): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($admin['AdminUserName']) ?></td>
                                            <td><?= htmlspecialchars($admin['AdminEmailId'] ?? '') ?></td>
                                            <td><?= date('M j, Y', strtotime($admin['CreationDate'])) ?></td>
                                            <td>
                                                <a href="manage-subadmins.php?edit=<?= (int) $admin['id'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-subadmins.php" style="display:inline;" onsubmit="return confirm('Remove this sub-admin?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Remove</button>
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
