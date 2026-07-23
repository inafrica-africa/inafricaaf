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

        if ($action === 'restore' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblposts SET Is_Active = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Post restored.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("SELECT PostImage FROM tblposts WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $post = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $imgStmt = $con->prepare("SELECT image_path FROM tblpostimages WHERE post_id = ?");
            $imgStmt->bind_param("i", $id);
            $imgStmt->execute();
            $galleryImages = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $imgStmt->close();

            $uploadDir = __DIR__ . '/postimages/';
            if ($post && $post['PostImage']) {
                @unlink($uploadDir . basename($post['PostImage']));
            }
            foreach ($galleryImages as $img) {
                @unlink($uploadDir . basename($img['image_path']));
            }

            $delImgStmt = $con->prepare("DELETE FROM tblpostimages WHERE post_id = ?");
            $delImgStmt->bind_param("i", $id);
            $delImgStmt->execute();
            $delImgStmt->close();

            $delStmt = $con->prepare("DELETE FROM tblposts WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
            $delStmt->close();

            $success = 'Post permanently deleted.';
        }
    }
}

$posts = [];
$result = mysqli_query($con, "
    SELECT p.id, p.PostTitle, p.PostingDate, c.CategoryName
    FROM tblposts p
    LEFT JOIN tblcategory c ON c.id = p.CategoryId
    WHERE p.Is_Active = 0
    ORDER BY p.PostingDate DESC
");
if ($result) {
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Trash Posts</h4>
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
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Trash is empty.</td></tr>
                                    <?php else: foreach ($posts as $post): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($post['PostTitle']) ?></td>
                                            <td><?= htmlspecialchars($post['CategoryName'] ?? '—') ?></td>
                                            <td><?= date('M j, Y', strtotime($post['PostingDate'])) ?></td>
                                            <td>
                                                <form method="post" action="trash-posts.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-success">Restore</button>
                                                </form>
                                                <form method="post" action="trash-posts.php" style="display:inline;" onsubmit="return confirm('Permanently delete this post? This cannot be undone.');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Delete Permanently</button>
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
