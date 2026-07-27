<?php
session_start();
include('config.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrftoken'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $name = trim($_POST['donor_name'] ?? '');
        $contact = trim($_POST['donor_contact'] ?? '');
        $amount = trim($_POST['amount'] ?? '');
        $method = $_POST['payment_method'] ?? 'MTN MoMo';
        $message = trim($_POST['message'] ?? '');
        $validMethods = ['MTN MoMo', 'Airtel Money', 'Bank Transfer', 'Other'];

        if ($name === '' || $contact === '' || !is_numeric($amount) || (float) $amount <= 0 || !in_array($method, $validMethods, true)) {
            $error = 'Please fill in your name, contact, and a valid amount.';
        } else {
            $stmt = $con->prepare("INSERT INTO tbldonations (DonorName, DonorContact, Amount, PaymentMethod, Message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdss", $name, $contact, $amount, $method, $message);
            $stmt->execute();
            $stmt->close();
            $success = 'Thank you! Your pledge has been recorded. Our team will reach out to confirm your contribution.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Donate | INAfrica</title>
  <meta name="description" content="INAfrica Youth Initiative: Connecting more than 1.54 Billion African Citizens.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
</head>
<body>
  <?php include('header.php'); ?>

  <section class="page-title-section bg-cover overlay" style="background-image: url('images/banner/banner-1.jpg');">
    <div class="container">
      <h1 class="text-white">Donate to INAfrica</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 mb-4">
          <h3 class="section-title">Ways to Give</h3>
          <span class="badge badge-warning mb-3">Payment details coming soon &mdash; not yet active</span>

          <div class="card mb-3">
            <div class="card-body">
              <h5 class="card-title"><i class="ti-mobile mr-2"></i>MTN Mobile Money</h5>
              <p class="card-text mb-1">Number: <strong>[ADD MTN MOMO NUMBER]</strong></p>
              <p class="card-text">Account Name: <strong>[ADD ACCOUNT NAME]</strong></p>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-body">
              <h5 class="card-title"><i class="ti-mobile mr-2"></i>Airtel Money</h5>
              <p class="card-text mb-1">Number: <strong>[ADD AIRTEL MONEY NUMBER]</strong></p>
              <p class="card-text">Account Name: <strong>[ADD ACCOUNT NAME]</strong></p>
            </div>
          </div>

          <p class="text-muted small">Once you've sent your contribution via one of the methods above, please fill in the form to let us know &mdash; our team will confirm and follow up with you.</p>
        </div>

        <div class="col-lg-6">
          <h3 class="section-title">Let Us Know</h3>
          <div class="card">
            <div class="card-body">
              <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>
              <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
              <?php endif; ?>
              <form method="post" action="donate.php">
                <input type="hidden" name="csrftoken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="form-group">
                  <label>Your Name</label>
                  <input type="text" name="donor_name" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Phone or Email</label>
                  <input type="text" name="donor_contact" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Amount (RWF)</label>
                  <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                </div>
                <div class="form-group">
                  <label>Payment Method Used</label>
                  <select name="payment_method" class="form-control">
                    <option value="MTN MoMo">MTN Mobile Money</option>
                    <option value="Airtel Money">Airtel Money</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Message (optional)</label>
                  <textarea name="message" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Pledge</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include('footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
  <script src="js/script.js?v=<?= @filemtime(__DIR__ . '/js/script.js') ?>"></script>
</body>
</html>
