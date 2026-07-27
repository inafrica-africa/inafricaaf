<?php
session_start();
include('config.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const CONTACT_TO_EMAIL = 'info@inafricaac.org';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrftoken'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '' || !$email || $message === '') {
            $error = 'Please fill in your name, a valid email, and a message.';
        } else {
            $mailSubject = $subject !== '' ? $subject : 'New message from INAfrica website';
            $mailBody = "From: $name <$email>\n\n$message";
            $headers = "From: no-reply@inafricaac.org\r\nReply-To: " . $email . "\r\n";
            $mailSent = @mail(CONTACT_TO_EMAIL, $mailSubject, $mailBody, $headers) ? 1 : 0;

            $stmt = $con->prepare("INSERT INTO tblcontactmessages (Name, Email, Subject, Message, MailSent) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $email, $subject, $message, $mailSent);
            $stmt->execute();
            $stmt->close();

            $success = 'Thank you, ' . htmlspecialchars($name) . '! Your message has been received. Our team will get back to you soon.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Contact Us | INAfrica</title>
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
      <h1 class="text-white">Contact Us</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="row">
        <div class="col-lg-5 mb-4">
          <h3 class="section-title">Get in Touch</h3>
          <ul class="list-unstyled footer-contact">
            <li class="mb-3"><i class="ti-location-pin mr-2"></i> Kigali, Rwanda</li>
            <li class="mb-3"><i class="ti-mobile mr-2"></i> <a href="tel:+250793903919">+250 793 903 919</a></li>
            <li class="mb-3"><i class="ti-email mr-2"></i> <a href="mailto:info@inafricaac.org">info@inafricaac.org</a></li>
            <li class="mb-3"><i class="ti-email mr-2"></i> <a href="mailto:contact@inafricaac.org">contact@inafricaac.org</a></li>
          </ul>
        </div>
        <div class="col-lg-7">
          <h3 class="section-title">Send Us a Message</h3>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
          <?php endif; ?>
          <form method="post" action="contact.php">
            <input type="hidden" name="csrftoken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Your Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="form-group col-md-6">
                <label>Your Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Subject</label>
              <input type="text" name="subject" class="form-control">
            </div>
            <div class="form-group">
              <label>Message</label>
              <textarea name="message" class="form-control" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
          </form>
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
