<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'unapprove' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblcomments SET status = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Comment moved back to pending.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("DELETE FROM tblcomments WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Comment deleted.';
        }
    }
}

$comments = [];
$result = mysqli_query($con, "SELECT id, PostUrl, name, email, comment, postingDate FROM tblcomments WHERE status = 1 ORDER BY postingDate DESC");
if ($result) {
    $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Approved Comments</h4>
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
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Post</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Comment</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($comments)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No approved comments yet.</td></tr>
                                    <?php else: foreach ($comments as $comment): ?>
                                        <tr>
                                            <td><a href="../news-details.php?PostUrl=<?= urlencode($comment['PostUrl']) ?>" target="_blank"><?= htmlspecialchars($comment['PostUrl']) ?></a></td>
                                            <td><?= htmlspecialchars($comment['name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($comment['email'] ?? '') ?></td>
                                            <td><?= nl2br(htmlspecialchars($comment['comment'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($comment['postingDate'])) ?></td>
                                            <td>
                                                <form method="post" action="manage-comments.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="unapprove">
                                                    <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning">Unapprove</button>
                                                </form>
                                                <form method="post" action="manage-comments.php" style="display:inline;" onsubmit="return confirm('Delete this comment?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
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
