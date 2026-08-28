<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const STATUSES = ['NGO', 'Individual', 'Initiative/Movement'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        // One bulk save: every (country, status) checkbox present in the
        // submitted form is enabled, everything else disabled. 162
        // combinations total (54 countries x 3 statuses) — a plain loop of
        // UPDATEs is more than fast enough for an admin button click.
        $enabled = $_POST['enabled'] ?? []; // array of "countryId:status" keys
        $enabledSet = array_flip($enabled);

        $countries = mysqli_fetch_all(mysqli_query($con, "SELECT id FROM tblcountries"), MYSQLI_ASSOC);
        $stmt = $con->prepare("UPDATE tblnetworkstatuscountry SET Is_Active = ? WHERE CountryId = ? AND Status = ?");
        foreach ($countries as $c) {
            foreach (STATUSES as $s) {
                $isActive = isset($enabledSet[$c['id'] . ':' . $s]) ? 1 : 0;
                $stmt->bind_param("iis", $isActive, $c['id'], $s);
                $stmt->execute();
            }
        }
        $stmt->close();
        $success = 'Country/Status settings saved.';
    }
}

$countries = mysqli_fetch_all(mysqli_query($con, "SELECT id, CountryName FROM tblcountries ORDER BY CountryName ASC"), MYSQLI_ASSOC);
$activeMap = []; // "countryId:status" => bool
$result = mysqli_query($con, "SELECT CountryId, Status, Is_Active FROM tblnetworkstatuscountry");
while ($row = mysqli_fetch_assoc($result)) {
    $activeMap[$row['CountryId'] . ':' . $row['Status']] = (bool) $row['Is_Active'];
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Networking — Country / Status Settings</h4>
                    <p class="text-muted">Which Status options a participant can choose depends on their country. Uncheck a box to disable that status for that country.</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-3">
                                    <thead>
                                        <tr>
                                            <th>Country</th>
                                            <?php foreach (STATUSES as $s): ?>
                                                <th class="text-center"><?= htmlspecialchars($s) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($countries as $c): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($c['CountryName']) ?></td>
                                                <?php foreach (STATUSES as $s):
                                                    $key = $c['id'] . ':' . $s;
                                                    $checked = $activeMap[$key] ?? false;
                                                ?>
                                                    <td class="text-center">
                                                        <input type="checkbox" name="enabled[]" value="<?= htmlspecialchars($key) ?>" <?= $checked ? 'checked' : '' ?>>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
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
