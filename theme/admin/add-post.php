<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_UPLOAD_BYTES = 2048 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$UPLOAD_DIR = __DIR__ . '/postimages/';
$LANGUAGES = ['English', 'Swahili', 'French'];

function saveUploadedImage($file, $allowedMime, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload failed (error code ' . $file['error'] . ').'];
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
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

function slugify($text) {
    $text = preg_replace('/[^a-zA-Z0-9]+/', '-', trim($text));
    $text = trim($text, '-');
    return strtolower($text) ?: 'post';
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $title = trim($_POST['post_title'] ?? '');
        $regionId = intval($_POST['region_id'] ?? 0) ?: null;
        $countryId = intval($_POST['country_id'] ?? 0) ?: null;
        $details = $_POST['post_details'] ?? '';
        $language = in_array($_POST['language'] ?? '', $LANGUAGES, true) ? $_POST['language'] : 'English';

        if ($title === '' || !$regionId || trim(strip_tags($details)) === '') {
            $error = 'Title, category, and post content are required.';
        } else {
            [$coverImage, $uploadError] = saveUploadedImage($_FILES['cover_image'] ?? null, $ALLOWED_MIME, $UPLOAD_DIR);
            if ($uploadError) {
                $error = $uploadError;
            } else {
                $baseSlug = slugify($title);
                $slug = $baseSlug;
                $suffix = 2;
                while (countRows($con, "SELECT COUNT(*) FROM tblposts WHERE PostUrl = '" . $con->real_escape_string($slug) . "'") > 0) {
                    $slug = $baseSlug . '-' . $suffix;
                    $suffix++;
                }

                $postedBy = $_SESSION['admin_name'] ?? 'Admin';
                $stmt = $con->prepare("
                    INSERT INTO tblposts (PostTitle, Language, RegionId, CountryId, PostDetails, PostUrl, PostImage, Is_Active, postedBy, viewCounter, share_count, Status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, 0, 0, 'Approved')
                ");
                $stmt->bind_param(
                    "ssiissss",
                    $title,
                    $language,
                    $regionId,
                    $countryId,
                    $details,
                    $slug,
                    $coverImage,
                    $postedBy
                );
                $stmt->execute();
                $postId = $stmt->insert_id;
                $stmt->close();

                // Optional gallery images
                if (!empty($_FILES['gallery_images']['name'][0])) {
                    $order = 0;
                    foreach ($_FILES['gallery_images']['name'] as $i => $name) {
                        if ($name === '') {
                            continue;
                        }
                        $galleryFile = [
                            'name' => $_FILES['gallery_images']['name'][$i],
                            'type' => $_FILES['gallery_images']['type'][$i],
                            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                            'error' => $_FILES['gallery_images']['error'][$i],
                            'size' => $_FILES['gallery_images']['size'][$i],
                        ];
                        [$galleryImage, ] = saveUploadedImage($galleryFile, $ALLOWED_MIME, $UPLOAD_DIR);
                        if ($galleryImage) {
                            $imgStmt = $con->prepare("INSERT INTO tblpostimages (post_id, image_path, image_order) VALUES (?, ?, ?)");
                            $imgStmt->bind_param("isi", $postId, $galleryImage, $order);
                            $imgStmt->execute();
                            $imgStmt->close();
                            $order++;
                        }
                    }
                }

                $success = 'Post published successfully.';
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

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Add Post</h4>
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
                        <form method="post" action="add-post.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                            <div class="form-group">
                                <label>Post Title</label>
                                <input type="text" name="post_title" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Language</label>
                                <select name="language" class="form-control">
                                    <?php foreach ($LANGUAGES as $lang): ?>
                                        <option value="<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($lang) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Category (Regional Bloc)</label>
                                    <select name="region_id" id="region_id" class="form-control" required>
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($regions as $region): ?>
                                            <option value="<?= (int) $region['id'] ?>"><?= htmlspecialchars($region['RegionName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sub Category (Country)</label>
                                    <select name="country_id" id="country_id" class="form-control">
                                        <option value="">-- Select Sub Category --</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= (int) $country['id'] ?>" data-region="<?= (int) $country['RegionId'] ?>"><?= htmlspecialchars($country['CountryName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Cover Image</label>
                                <input type="file" name="cover_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>

                            <div class="form-group">
                                <label>Gallery Images (optional, multiple)</label>
                                <input type="file" name="gallery_images[]" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                            </div>

                            <div class="form-group">
                                <label>Post Content</label>
                                <textarea name="post_details" id="post_details" class="summernote" rows="10"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Publish Post</button>
                            <a href="manage-posts.php" class="btn btn-light">View All Posts</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
            $('#country_id').val('');
        }
        $('#region_id').on('change', filterCountries);
        filterCountries();
    });
</script>
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
