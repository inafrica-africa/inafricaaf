<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Flash messages passed via query string after the redirect below, rather
// than rendered directly from the POST handler — a plain "process POST,
// then render the same response" leaves the delete/toggle action baked
// into the browser history, so refreshing this page (or navigating back
// to it) throws up the browser's native "Confirm Form Resubmission"
// warning, and clicking through it silently re-runs the action. Redirecting
// to a GET after every successful POST means there's never a POST response
// sitting in history to resubmit.
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'toggle' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblnetworkusers SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'User status updated.';
        } elseif ($action === 'delete' && $id > 0) {
            // "Remove" (toggle, above) only deactivates — this is the actual
            // permanent delete: the user, every device that ever logged in
            // as them, every message they sent (and its media file on
            // disk), and every "delete for me" they'd set on others'
            // messages. Irreversible, unlike toggle.
            $mediaDir = __DIR__ . '/../network/media/';
            $stmt = $con->prepare("SELECT MediaPath FROM tblnetworkmessages WHERE UserId = ? AND MediaPath IS NOT NULL");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $mediaFiles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            foreach ($mediaFiles as $m) {
                @unlink($mediaDir . basename($m['MediaPath']));
            }

            $stmt = $con->prepare("
                DELETE FROM tblnetworkmessagehidden
                WHERE UserId = ? OR MessageId IN (SELECT id FROM tblnetworkmessages WHERE UserId = ?)
            ");
            $stmt->bind_param("ii", $id, $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $con->prepare("DELETE FROM tblnetworkmessages WHERE UserId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $con->prepare("DELETE FROM tblnetworkdevices WHERE UserId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $con->prepare("DELETE FROM tblnetworkusers WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $success = 'User permanently deleted, along with their messages and devices.';
        }
    }

    // Preserve whatever status/country filter was active (browsers send the
    // current URL's query string along with a form that has no explicit
    // action) so the admin lands back on the same filtered view.
    $redirectParams = [
        'status' => $_GET['status'] ?? '',
        'country' => $_GET['country'] ?? '',
    ];
    if ($error) {
        $redirectParams['error'] = $error;
    } elseif ($success) {
        $redirectParams['success'] = $success;
    }
    header('Location: network-users.php?' . http_build_query(array_filter($redirectParams)));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$countryFilter = intval($_GET['country'] ?? 0);

$where = [];
$params = [];
$types = '';
if (in_array($statusFilter, ['NGO', 'Individual', 'Initiative/Movement'], true)) {
    $where[] = 'u.Status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}
if ($countryFilter) {
    $where[] = 'u.CountryId = ?';
    $params[] = $countryFilter;
    $types .= 'i';
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $con->prepare("
    SELECT u.id, u.Name, u.WhatsApp, u.Email, u.Status, u.Is_Active, u.CreatedDate, u.LastSeenDate, c.CountryName
    FROM tblnetworkusers u
    JOIN tblcountries c ON c.id = u.CountryId
    $whereSql
    ORDER BY u.CreatedDate DESC
");
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$countries = mysqli_fetch_all(mysqli_query($con, "SELECT id, CountryName FROM tblcountries ORDER BY CountryName ASC"), MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Networking — Registered Users</h4>
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
                        <form method="get" class="form-inline mb-3">
                            <select name="status" class="form-control mr-2 mb-2">
                                <option value="">All statuses</option>
                                <?php foreach (['NGO', 'Individual', 'Initiative/Movement'] as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>" <?= $s === $statusFilter ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="country" class="form-control mr-2 mb-2">
                                <option value="">All countries</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>" <?= $c['id'] == $countryFilter ? 'selected' : '' ?>><?= htmlspecialchars($c['CountryName']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-secondary mb-2">Filter</button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>WhatsApp</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Country</th>
                                        <th>Registered</th>
                                        <th>Last Active</th>
                                        <th>Account Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr><td colspan="9" class="text-center text-muted">No registered users yet.</td></tr>
                                    <?php else: foreach ($users as $u): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($u['Name']) ?></td>
                                            <td><?= htmlspecialchars($u['WhatsApp']) ?></td>
                                            <td><?= htmlspecialchars($u['Email']) ?></td>
                                            <td><?= htmlspecialchars($u['Status']) ?></td>
                                            <td><?= htmlspecialchars($u['CountryName']) ?></td>
                                            <td><?= date('M j, Y', strtotime($u['CreatedDate'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($u['LastSeenDate'])) ?></td>
                                            <td>
                                                <?php if ($u['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Removed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('<?= $u['Is_Active'] ? 'Remove' : 'Restore' ?> this user?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                                    <button type="submit" class="btn btn-sm <?= $u['Is_Active'] ? 'btn-danger' : 'btn-success' ?>">
                                                        <?= $u['Is_Active'] ? 'Remove' : 'Restore' ?>
                                                    </button>
                                                </form>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Permanently delete &quot;<?= htmlspecialchars(addslashes($u['Name'])) ?>&quot;? This removes the user, all their messages, and their devices for good — it cannot be undone.');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete Permanently</button>
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
