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

$countries = [];
$result = mysqli_query($con, "SELECT id, CountryName, RegionId FROM tblcountries WHERE Is_Active = 1 ORDER BY CountryName ASC");
if ($result) {
    $countries = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

const STATUSES = ['NGO', 'Individual', 'Initiative/Movement'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrftoken'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $status = $_POST['status'] ?? '';
        $countryId = intval($_POST['country_id'] ?? 0);

        if ($name === '' || $whatsapp === '' || !$email || !$countryId) {
            $error = 'Please fill in your name, WhatsApp number, a valid email, and your country.';
        } elseif (!in_array($status, STATUSES, true)) {
            $error = 'Please choose a valid status.';
        } else {
            // Re-check the status is actually enabled for this country
            // server-side — the client-side filtering is just UX, this is
            // what actually enforces the per-country restriction.
            $check = $con->prepare("SELECT Is_Active FROM tblnetworkstatuscountry WHERE CountryId = ? AND Status = ?");
            $check->bind_param("is", $countryId, $status);
            $check->execute();
            $row = $check->get_result()->fetch_assoc();
            $check->close();

            if (!$row || !$row['Is_Active']) {
                $error = 'That status is not available for the selected country.';
            } else {
                $stmt = $con->prepare("INSERT INTO tblnetworkusers (Name, WhatsApp, Email, Status, CountryId) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $name, $whatsapp, $email, $status, $countryId);
                $stmt->execute();
                $userId = $stmt->insert_id;
                $stmt->close();

                networkIssueDevice($con, $userId);
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
  <!-- Every relative URL on this page (including the ones baked into
       header.php/footer.php, which assume they're being rendered from a
       page at the site root) resolves against this instead of the page's
       actual nested /network URL. Lets header.php/footer.php be included
       here completely unmodified, and lets this page's own asset links use
       the exact same root-relative style as every top-level page (about.php
       etc.) instead of hand-counting "../"s. -->
  <base href="/">
  <title>Let Africa Connects</title>
  <meta name="description" content="Register to join INAfrica's Networking space — connect and discuss with participants across Africa's Events and Summits.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?>" rel="stylesheet">
  <link href="network/css/network.css?v=<?= @filemtime(__DIR__ . '/css/network.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
  <?php renderMetaTags(
    'Let Africa Connects',
    "Register to join INAfrica's Networking space — connect and discuss with participants across Africa's Events and Summits.",
    'images/logo.png',
    '/network/'
  ); ?>
</head>
<body>
  <?php include(__DIR__ . '/../header.php'); ?>
  <?php networkFlagBanner(); ?>

  <section class="section network-register-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <h1 class="text-center mb-2">Let Africa Connects</h1>
          <p class="text-center text-muted mb-4">Register once — no password, no sign-in screen — and join the conversation.</p>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="post" class="card p-4 shadow-sm">
            <input type="hidden" name="csrftoken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
              <label>Name (yours, or your organization's)</label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
              <label>WhatsApp Number</label>
              <input type="text" name="whatsapp" class="form-control" placeholder="+250..." required>
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Country</label>
              <select name="country_id" id="country_id" class="form-control" required>
                <option value="">-- Select your country --</option>
                <?php foreach ($countries as $country): ?>
                  <option value="<?= (int) $country['id'] ?>"><?= htmlspecialchars($country['CountryName']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Status</label>
              <select name="status" id="status" class="form-control" required>
                <option value="">-- Select a country first --</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Join the Conversation</button>
            <p class="text-center text-muted small mt-3 mb-0">
              Already registered on another device? <a href="/network/restore">Restore access</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </section>

  <?php include(__DIR__ . '/../footer.php'); ?>

  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
  <script>
  (function () {
    var countrySelect = document.getElementById('country_id');
    var statusSelect = document.getElementById('status');
    var selectedStatus = <?= json_encode($_POST['status'] ?? '') ?>;

    function loadStatuses() {
      var countryId = countrySelect.value;
      statusSelect.innerHTML = '<option value="">Loading...</option>';
      if (!countryId) {
        statusSelect.innerHTML = '<option value="">-- Select a country first --</option>';
        return;
      }
      fetch('/network/api/statuses-for-country?country_id=' + encodeURIComponent(countryId))
        .then(function (res) { return res.json(); })
        .then(function (data) {
          statusSelect.innerHTML = '<option value="">-- Select a status --</option>';
          (data.statuses || []).forEach(function (s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            if (s === selectedStatus) {
              opt.selected = true;
            }
            statusSelect.appendChild(opt);
          });
        })
        .catch(function () {
          statusSelect.innerHTML = '<option value="">Could not load statuses</option>';
        });
    }

    countrySelect.addEventListener('change', loadStatuses);
    if (countrySelect.value) {
      loadStatuses();
    }
  })();
  </script>
</body>
</html>
