<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Flash messages via query string after the redirect below — see
// network-users.php for why (avoids the browser's "Confirm Form
// Resubmission" warning on refresh/back after a POST here).
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'toggle' && $id > 0) {
            // Admin removal (Is_Active) is deliberately separate from the
            // user-facing IsDeletedForEveryone flag — this is moderation,
            // not the sender's own delete action.
            $stmt = $con->prepare("UPDATE tblnetworkmessages SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Message status updated.';
        }
    }

    $redirectParams = ['q' => $_GET['q'] ?? ''];
    if ($error) {
        $redirectParams['error'] = $error;
    } elseif ($success) {
        $redirectParams['success'] = $success;
    }
    header('Location: network-messages.php?' . http_build_query(array_filter($redirectParams)));
    exit;
}

$search = trim($_GET['q'] ?? '');
$where = '';
$params = [];
$types = '';
if ($search !== '') {
    $where = 'WHERE m.MessageText LIKE ? OR u.Name LIKE ?';
    $like = '%' . $search . '%';
    $params = [$like, $like];
    $types = 'ss';
}

$stmt = $con->prepare("
    SELECT m.id, m.MessageType, m.MessageText, m.MediaPath, m.IsDeletedForEveryone, m.Is_Active, m.CreatedDate, u.Name AS SenderName
    FROM tblnetworkmessages m
    JOIN tblnetworkusers u ON u.id = m.UserId
    $where
    ORDER BY m.id DESC
    LIMIT 300
");
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Networking — Moderate Messages</h4>
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
                            <input type="text" name="q" class="form-control mr-2 mb-2" placeholder="Search text or sender name" value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-secondary mb-2">Search</button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Sender</th>
                                        <th>Type</th>
                                        <th>Content</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($messages)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No messages found.</td></tr>
                                    <?php else: foreach ($messages as $m): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($m['SenderName']) ?></td>
                                            <td><?= htmlspecialchars($m['MessageType']) ?></td>
                                            <td>
                                                <?php if ($m['IsDeletedForEveryone']): ?>
                                                    <em class="text-muted">Deleted by sender</em>
                                                <?php elseif ($m['MessageType'] === 'text'): ?>
                                                    <?= htmlspecialchars(mb_substr((string) $m['MessageText'], 0, 120)) ?>
                                                <?php elseif ($m['MediaPath']): ?>
                                                    <a href="../network/media/<?= htmlspecialchars($m['MediaPath']) ?>" target="_blank">View <?= htmlspecialchars($m['MessageType']) ?></a>
                                                <?php else: ?>
                                                    <em class="text-muted">(no content)</em>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('M j, Y g:ia', strtotime($m['CreatedDate'])) ?></td>
                                            <td>
                                                <?php if (!$m['Is_Active']): ?>
                                                    <span class="badge badge-danger">Removed by admin</span>
                                                <?php elseif ($m['IsDeletedForEveryone']): ?>
                                                    <span class="badge badge-secondary">Deleted by sender</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Visible</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('<?= $m['Is_Active'] ? 'Remove' : 'Restore' ?> this message?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                                    <button type="submit" class="btn btn-sm <?= $m['Is_Active'] ? 'btn-danger' : 'btn-success' ?>">
                                                        <?= $m['Is_Active'] ? 'Remove' : 'Restore' ?>
                                                    </button>
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
