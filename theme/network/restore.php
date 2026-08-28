<?php
session_start();
include(__DIR__ . '/../config.php');
require_once __DIR__ . '/includes/identity.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$me = networkResolveIdentity($con);
if ($me) {
    header('Location: /network/chat');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrftoken'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $contact = trim($_POST['contact'] ?? '');
        if ($contact === '') {
            $error = 'Enter the email or WhatsApp number you registered with.';
        } else {
            // No password/OTP check here — a deliberate, stated tradeoff
            // (see plan): this module has no login at all, so "restore
            // access" is intentionally just "prove you know a contact
            // detail already on file," not a real identity verification.
            $stmt = $con->prepare("SELECT id FROM tblnetworkusers WHERE Is_Active = 1 AND (Email = ? OR WhatsApp = ?)");
            $stmt->bind_param("ss", $contact, $contact);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $error = "We couldn't find a registration matching that email or WhatsApp number.";
            } else {
                networkIssueDevice($con, $user['id']);
                header('Location: /network/chat');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <base href="/">
  <title>Restore Access | Let Africa Connects</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>" rel="stylesheet">
  <link href="network/css/network.css?v=<?= @filemtime(__DIR__ . '/css/network.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
</head>
<body>
  <?php include(__DIR__ . '/../header.php'); ?>
  <?php networkFlagBanner(); ?>

  <section class="section network-register-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-5">
          <h1 class="text-center mb-2">Restore Access</h1>
          <p class="text-center text-muted mb-4">On a new browser or device? Re-enter the email or WhatsApp number you registered with.</p>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="post" class="card p-4 shadow-sm">
            <input type="hidden" name="csrftoken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="form-group">
              <label>Email or WhatsApp Number</label>
              <input type="text" name="contact" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Restore Access</button>
            <p class="text-center text-muted small mt-3 mb-0">
              Not registered yet? <a href="network/">Register</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </section>

  <?php include(__DIR__ . '/../footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
</body>
</html>
