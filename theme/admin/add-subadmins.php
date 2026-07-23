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
        $username = trim($_POST['username'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($username === '' || !$email) {
            $error = 'A valid username and email are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Password and confirmation do not match.';
        } else {
            $checkStmt = $con->prepare("SELECT id FROM tbladmin WHERE AdminUserName = ?");
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ($exists) {
                $error = 'That username is already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $con->prepare("INSERT INTO tbladmin (AdminUserName, AdminPassword, AdminEmailId, userType) VALUES (?, ?, ?, 0)");
                $stmt->bind_param("sss", $username, $hash, $email);
                $stmt->execute();
                $stmt->close();
                $success = 'Sub-admin added successfully.';
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
                    <h4 class="page-title">Add Sub-admin</h4>
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
                        <form method="post" action="add-subadmins.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" minlength="8" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Sub-admin</button>
                            <a href="manage-subadmins.php" class="btn btn-light">View All Sub-admins</a>
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
