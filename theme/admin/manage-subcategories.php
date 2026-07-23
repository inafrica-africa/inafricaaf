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
            $stmt = $con->prepare("UPDATE tblsubcategory SET Is_Active = 1 - Is_Active WHERE SubCategoryId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Sub-category status updated.';
        } elseif ($action === 'edit' && $id > 0) {
            $name = trim($_POST['subcategory_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categoryId = intval($_POST['category_id'] ?? 0);
            if ($name === '' || $categoryId <= 0) {
                $error = 'Category and sub-category name are required.';
            } else {
                $stmt = $con->prepare("UPDATE tblsubcategory SET Subcategory = ?, SubCatDescription = ?, CategoryId = ? WHERE SubCategoryId = ?");
                $stmt->bind_param("ssii", $name, $description, $categoryId, $id);
                $stmt->execute();
                $stmt->close();
                $success = 'Sub-category updated.';
            }
        }
    }
}

$categories = [];
$result = mysqli_query($con, "SELECT id, CategoryName FROM tblcategory WHERE Is_Active = 1 ORDER BY CategoryName ASC");
if ($result) {
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT SubCategoryId, CategoryId, Subcategory, SubCatDescription FROM tblsubcategory WHERE SubCategoryId = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$subcategories = [];
$result = mysqli_query($con, "
    SELECT s.SubCategoryId, s.Subcategory, s.SubCatDescription, s.Is_Active, c.CategoryName
    FROM tblsubcategory s
    LEFT JOIN tblcategory c ON c.id = s.CategoryId
    ORDER BY c.CategoryName ASC, s.Subcategory ASC
");
if ($result) {
    $subcategories = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Sub Categories</h4>
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
                        <h5>Edit Sub Category</h5>
                        <form method="post" action="manage-subcategories.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['SubCategoryId'] ?>">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int) $cat['id'] ?>" <?= $cat['id'] == $editRow['CategoryId'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['CategoryName']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sub Category Name</label>
                                <input type="text" name="subcategory_name" class="form-control" value="<?= htmlspecialchars($editRow['Subcategory']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($editRow['SubCatDescription'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-subcategories.php" class="btn btn-light">Cancel</a>
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
                                        <th>Category</th>
                                        <th>Sub Category</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($subcategories)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">No sub-categories yet.</td></tr>
                                    <?php else: foreach ($subcategories as $sub): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sub['CategoryName'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($sub['Subcategory']) ?></td>
                                            <td><?= htmlspecialchars($sub['SubCatDescription'] ?? '') ?></td>
                                            <td>
                                                <?php if ($sub['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="manage-subcategories.php?edit=<?= (int) $sub['SubCategoryId'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-subcategories.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $sub['SubCategoryId'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning">
                                                        <?= $sub['Is_Active'] ? 'Deactivate' : 'Activate' ?>
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
