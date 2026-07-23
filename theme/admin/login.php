<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $con->prepare("SELECT id, AdminUserName, AdminPassword, userType FROM tbladmin WHERE AdminUserName = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $valid = false;
        if ($admin) {
            if (password_verify($password, $admin['AdminPassword'])) {
                $valid = true;
            } elseif (hash_equals($admin['AdminPassword'], $password)) {
                // Legacy plain-text password: migrate to a proper hash transparently.
                $valid = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $con->prepare("UPDATE tbladmin SET AdminPassword = ? WHERE id = ?");
                $update->bind_param("si", $newHash, $admin['id']);
                $update->execute();
                $update->close();
            }
        }

        if ($valid) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['AdminUserName'];
            $_SESSION['utype'] = (string) $admin['userType'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login | INAfrica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/core.css" rel="stylesheet">
    <link href="../assets/css/components.css" rel="stylesheet">
    <link href="../assets/css/icons.css" rel="stylesheet">
    <link href="../assets/css/pages.css" rel="stylesheet">
    <link href="brand.css" rel="stylesheet">
</head>
<body class="account-pages-bg">
    <div class="account-pages">
        <div class="wrapper-page">
            <div class="card-box">
                <div class="account-content text-center">
                    <img src="../images/logo.png" alt="INAfrica" height="60" class="mb-3">
                    <h4 class="text-uppercase mt-0">INAfrica Admin</h4>
                    <p class="text-muted m-b-30">Sign in to manage the site.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="form-group text-left">
                            <label>Username</label>
                            <input class="form-control" type="text" name="username" required autofocus>
                        </div>
                        <div class="form-group text-left">
                            <label>Password</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div class="form-group text-center m-t-20">
                            <button class="btn btn-primary btn-block" type="submit">Log In</button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        <a href="../index.php"><i class="mdi mdi-arrow-left mr-1"></i>Back to Website</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>
