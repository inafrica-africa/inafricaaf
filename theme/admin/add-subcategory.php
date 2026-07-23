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
        $categoryId = intval($_POST['category_id'] ?? 0);
        $name = trim($_POST['subcategory_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($categoryId <= 0 || $name === '') {
            $error = 'Category and sub-category name are required.';
        } else {
            // SubCategoryId has no AUTO_INCREMENT in this schema, so assign the next id manually.
            $nextId = countRows($con, "SELECT COALESCE(MAX(SubCategoryId), 0) + 1 FROM tblsubcategory");
            $stmt = $con->prepare("INSERT INTO tblsubcategory (SubCategoryId, CategoryId, Subcategory, SubCatDescription, Is_Active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("iiss", $nextId, $categoryId, $name, $description);
            $stmt->execute();
            $stmt->close();
            $success = 'Sub-category added successfully.';
        }
    }
}

$categories = [];
$result = mysqli_query($con, "SELECT id, CategoryName FROM tblcategory WHERE Is_Active = 1 ORDER BY CategoryName ASC");
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
                    <h4 class="page-title">Add Sub Category</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <div class="card-box">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <form method="post" action="add-subcategory.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['CategoryName']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sub Category Name</label>
                                <input type="text" name="subcategory_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Sub Category</button>
                            <a href="manage-subcategories.php" class="btn btn-light">View All Sub Categories</a>
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
