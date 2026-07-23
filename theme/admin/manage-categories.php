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

        if ($action === 'toggle' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblcategory SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Category status updated.';
        } elseif ($action === 'edit' && $id > 0) {
            $name = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if ($name === '') {
                $error = 'Category name is required.';
            } else {
                $stmt = $con->prepare("UPDATE tblcategory SET CategoryName = ?, Description = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $description, $id);
                $stmt->execute();
                $stmt->close();
                $success = 'Category updated.';
            }
        }
    }
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, CategoryName, Description FROM tblcategory WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$categories = [];
$result = mysqli_query($con, "SELECT id, CategoryName, Description, Is_Active FROM tblcategory ORDER BY CategoryName ASC");
if ($result) {
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Categories</h4>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($editRow): ?>
            <div class="row">
                <div class="col-md-9">
                    <div class="card-box">
                        <h5>Edit Category</h5>
                        <form method="post" action="manage-categories.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($editRow['CategoryName']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($editRow['Description'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-categories.php" class="btn btn-light">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No categories yet.</td></tr>
                                    <?php else: foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cat['CategoryName']) ?></td>
                                            <td><?= htmlspecialchars($cat['Description'] ?? '') ?></td>
                                            <td>
                                                <?php if ($cat['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="manage-categories.php?edit=<?= (int) $cat['id'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-categories.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning">
                                                        <?= $cat['Is_Active'] ? 'Deactivate' : 'Activate' ?>
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
