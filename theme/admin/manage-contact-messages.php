<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'read' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblcontactmessages SET IsRead = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Marked as read.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("DELETE FROM tblcontactmessages WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Message deleted.';
        }
    }
}

$messages = [];
$result = mysqli_query($con, "SELECT id, Name, Email, Subject, Message, MailSent, IsRead, CreatedDate FROM tblcontactmessages ORDER BY CreatedDate DESC");
if ($result) {
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Contact Messages</h4>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <?php if (empty($messages)): ?>
                        <div class="card-box text-center text-muted">No messages yet.</div>
                    <?php else: foreach ($messages as $m): ?>
                        <div class="card-box <?= $m['IsRead'] ? '' : 'stat-card-yellow' ?>">
                            <div class="d-flex justify-content-between flex-wrap">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($m['Subject'] ?: '(No subject)') ?></h5>
                                    <p class="mb-1"><strong><?= htmlspecialchars($m['Name']) ?></strong> &lt;<?= htmlspecialchars($m['Email']) ?>&gt;</p>
                                    <small class="text-muted"><?= date('M j, Y g:i a', strtotime($m['CreatedDate'])) ?>
                                        &middot; <?= $m['MailSent'] ? '<span class="text-success">Email sent</span>' : '<span class="text-danger">Email delivery failed — saved here instead</span>' ?>
                                    </small>
                                </div>
                                <div>
                                    <?php if (!$m['IsRead']): ?>
                                    <form method="post" action="manage-contact-messages.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="read">
                                        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-info">Mark Read</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="post" action="manage-contact-messages.php" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($m['Message'])) ?></p>
                        </div>
                    <?php endforeach; endif; ?>
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
