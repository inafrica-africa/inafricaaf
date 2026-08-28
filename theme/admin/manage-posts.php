<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$UPLOAD_DIR = __DIR__ . '/postimages/';
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
const MAX_UPLOAD_BYTES_MP = 2048 * 1024 * 1024;
$LANGUAGES = ['English', 'Swahili', 'French'];

function saveUploadedImageMP($file, $allowedMime, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload failed (error code ' . $file['error'] . ').'];
    }
    if ($file['size'] > MAX_UPLOAD_BYTES_MP) {
        return [null, 'Image is too large (max 2GB).'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowedMime[$mime])) {
        return [null, 'Only JPG, PNG, GIF, or WEBP images are allowed.'];
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $allowedMime[$mime];
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return [null, 'Could not save the uploaded image.'];
    }
    return [$filename, null];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'trash' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblposts SET Is_Active = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Post moved to trash.';
        } elseif ($action === 'edit' && $id > 0) {
            $title = trim($_POST['post_title'] ?? '');
            $regionId = intval($_POST['region_id'] ?? 0) ?: null;
            $countryId = intval($_POST['country_id'] ?? 0) ?: null;
            $details = $_POST['post_details'] ?? '';
            $language = in_array($_POST['language'] ?? '', $LANGUAGES, true) ? $_POST['language'] : 'English';

            if ($title === '' || !$regionId || trim(strip_tags($details)) === '') {
                $error = 'Title, category, and post content are required.';
            } else {
                [$newImage, $uploadError] = saveUploadedImageMP($_FILES['cover_image'] ?? null, $ALLOWED_MIME, $UPLOAD_DIR);
                if ($uploadError) {
                    $error = $uploadError;
                } else {
                    if ($newImage) {
                        $stmt = $con->prepare("UPDATE tblposts SET PostTitle = ?, Language = ?, RegionId = ?, CountryId = ?, PostDetails = ?, PostImage = ? WHERE id = ?");
                        $stmt->bind_param("ssiissi", $title, $language, $regionId, $countryId, $details, $newImage, $id);
                    } else {
                        $stmt = $con->prepare("UPDATE tblposts SET PostTitle = ?, Language = ?, RegionId = ?, CountryId = ?, PostDetails = ? WHERE id = ?");
                        $stmt->bind_param("ssiisi", $title, $language, $regionId, $countryId, $details, $id);
                    }
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Post updated.';
                }
            }
        }
    }
}

$regions = [];
$result = mysqli_query($con, "SELECT id, RegionName FROM tblregions WHERE Is_Active = 1 ORDER BY RegionName ASC");
if ($result) {
    $regions = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$countries = [];
$result = mysqli_query($con, "SELECT id, RegionId, CountryName FROM tblcountries WHERE Is_Active = 1 ORDER BY CountryName ASC");
if ($result) {
    $countries = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, PostTitle, Language, RegionId, CountryId, PostDetails FROM tblposts WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$posts = [];
$result = mysqli_query($con, "
    SELECT p.id, p.PostTitle, p.Language, p.PostingDate, p.viewCounter, r.RegionName
    FROM tblposts p
    LEFT JOIN tblregions r ON r.id = p.RegionId
    WHERE p.Is_Active = 1
    ORDER BY p.PostingDate DESC
");
if ($result) {
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<?php if ($editRow): ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
<?php endif; ?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Posts</h4>
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
                        <h5>Edit Post</h5>
                        <form method="post" action="manage-posts.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">

                            <div class="form-group">
                                <label>Post Title</label>
                                <input type="text" name="post_title" class="form-control" value="<?= htmlspecialchars($editRow['PostTitle']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Language</label>
                                <select name="language" class="form-control">
                                    <?php foreach ($LANGUAGES as $lang): ?>
                                        <option value="<?= htmlspecialchars($lang) ?>" <?= $lang === $editRow['Language'] ? 'selected' : '' ?>><?= htmlspecialchars($lang) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Category (Regional Bloc)</label>
                                    <select name="region_id" id="region_id" class="form-control" required>
                                        <?php foreach ($regions as $region): ?>
                                            <option value="<?= (int) $region['id'] ?>" <?= $region['id'] == $editRow['RegionId'] ? 'selected' : '' ?>><?= htmlspecialchars($region['RegionName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sub Category (Country)</label>
                                    <select name="country_id" id="country_id" class="form-control">
                                        <option value="">-- Select Sub Category --</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= (int) $country['id'] ?>" data-region="<?= (int) $country['RegionId'] ?>" <?= $country['id'] == $editRow['CountryId'] ? 'selected' : '' ?>><?= htmlspecialchars($country['CountryName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Replace Cover Image (optional)</label>
                                <input type="file" name="cover_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>

                            <div class="form-group">
                                <label>Post Content</label>
                                <textarea name="post_details" class="summernote" rows="10"><?= $editRow['PostDetails'] ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-posts.php" class="btn btn-light">Cancel</a>
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
                                        <th>Title</th>
                                        <th>Language</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Views</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No posts yet.</td></tr>
                                    <?php else: foreach ($posts as $post): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($post['PostTitle']) ?></td>
                                            <td><?= htmlspecialchars($post['Language']) ?></td>
                                            <td><?= htmlspecialchars($post['RegionName'] ?? '—') ?></td>
                                            <td><?= date('M j, Y', strtotime($post['PostingDate'])) ?></td>
                                            <td><?= number_format((int) $post['viewCounter']) ?></td>
                                            <td>
                                                <a href="manage-posts.php?edit=<?= (int) $post['id'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-posts.php" style="display:inline;" onsubmit="return confirm('Move this post to trash?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="trash">
                                                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Trash</button>
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
<?php if ($editRow): ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script src="js/editor-init.js?v=<?= @filemtime(__DIR__ . '/js/editor-init.js') ?>"></script>
<script>
    $(document).ready(function () {
        initPostEditor('.summernote');

        function filterCountries() {
            var regionId = $('#region_id').val();
            $('#country_id option').each(function () {
                var opt = $(this);
                if (opt.val() === '') { return; }
                opt.toggle(opt.data('region') == regionId);
            });
        }
        $('#region_id').on('change', filterCountries);
    });
</script>
<?php endif; ?>
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
