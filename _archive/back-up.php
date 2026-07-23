<?php
session_start();
include('includes/config.php');
mysqli_set_charset($con, 'utf8mb4');
error_reporting(E_ALL);

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        // Get & sanitize inputs
        $posttitle   = trim($_POST['posttitle'] ?? '');
        $catid       = intval($_POST['category'] ?? 0);
        $subcatid    = intval($_POST['subcategory'] ?? 0);
        $postdetails = trim($_POST['postdescription'] ?? '');

        if ($posttitle === '' || $catid <= 0 || $subcatid <= 0 || $postdetails === '') {
            $error = 'Please fill in all required fields.';
        }

        // Handle image upload
        if (!$error && isset($_FILES['postimage']) && $_FILES['postimage']['error'] === UPLOAD_ERR_OK) {
            $fileTmp   = $_FILES['postimage']['tmp_name'];
            $fileName  = basename($_FILES['postimage']['name']);
            $ext       = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed   = ['jpg','jpeg','png','gif'];

            if (!in_array($ext, $allowed, true)) {
                $error = 'Invalid image format. Only JPG, JPEG, PNG & GIF allowed.';
            } else {
                $newName = bin2hex(random_bytes(16)) . '.' . $ext;
                $dest    = __DIR__ . '/postimages/' . $newName;
                if (!move_uploaded_file($fileTmp, $dest)) {
                    $error = 'Failed to move uploaded image.';
                }
            }
        } else {
            $error = 'Please upload a feature image.';
        }

        // Insert into database
        if (!$error) {
            $url = preg_replace('/\s+/', '-', strtolower(trim(preg_replace('/[^A-Za-z0-9 ]/', '', $posttitle))));
            $stmt = $con->prepare("
                INSERT INTO tblposts
                  (PostTitle, CategoryId, SubCategoryId, PostDetails, PostUrl, Is_Active, PostImage, postedBy)
                VALUES (?, ?, ?, ?, ?, 0, ?, ?)
            ");
            $stmt->bind_param(
                'sisssss',
                $posttitle,
                $catid,
                $subcatid,
                $postdetails,
                $url,
                $newName,
                $_SESSION['login']
            );

            if ($stmt->execute()) {
                $msg = 'Post successfully added and awaiting approval.';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $error = 'Database error: ' . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Advanced Newsletter Editor (Updated)</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="Construction Html5 Template">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="author" content="Themefisher">
<meta name="generator" content="Themefisher Educenter HTML Template v1.0">

<!-- theme meta -->
<meta name="theme-name" content="educenter" />

<!-- ** Plugins Needed for the Project ** -->
<!-- Bootstrap -->
<link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
<!-- slick slider -->
<link rel="stylesheet" href="plugins/slick/slick.css">
<!-- themefy-icon -->
<link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
<!-- animation css -->
<link rel="stylesheet" href="plugins/animate/animate.css">
<!-- aos -->
<link rel="stylesheet" href="plugins/aos/aos.css">
<!-- venobox popup -->
<link rel="stylesheet" href="plugins/venobox/venobox.css">

<!-- Main Stylesheet -->
<link href="style.css" rel="stylesheet">
<style>
.picker-panel { display:none; position:absolute; background:#fff; border:1px solid #ccc; padding:5px; z-index:1000; }
</style>
</head>
<body>

<?php include('includes/topheader.php'); ?>

<div class="content-page">
<div class="content">
<div class="container">

<!-- Page Title -->
<div class="row">
  <div class="col-xs-12">
    <div class="page-title-box">
      <h4 class="page-title">Add Post</h4>
      <ol class="breadcrumb p-0 m-0">
        <li><a href="#">Post</a></li>
        <li class="active">Add Post</li>
      </ol>
      <div class="clearfix"></div>
    </div>
  </div>
</div>

<!-- Messages -->
<div class="row">
  <div class="col-sm-6">
    <?php if ($msg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </div>
</div>

<!-- Form -->
<form name="addpost" method="post" enctype="multipart/form-data" class="row">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div class="form-group col-md-6">
  <label for="posttitle">Post Title</label>
  <input type="text" id="posttitle" name="posttitle" class="form-control" required>
</div>

<div class="form-group col-md-6">
  <label for="category">Category</label>
  <select id="category" name="category" class="form-control" onchange="getSubCat(this.value)" required>
    <option value="">Select Category</option>
    <?php
      $cats = $con->query("SELECT id, CategoryName FROM tblcategory WHERE Is_Active=1");
      while ($c = $cats->fetch_assoc()):
    ?>
      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['CategoryName']) ?></option>
    <?php endwhile; ?>
  </select>
</div>

<div class="form-group col-md-6">
  <label for="subcategory">Sub Category</label>
  <select id="subcategory" name="subcategory" class="form-control" required></select>
</div>

<div class="form-group col-md-12">
  <label for="postdescription">Post Details</label>
  <div class="card-box">

<!-- Toolbar -->
<button type="button" onclick="format('bold')" title="Bold"><b>B</b></button>
<button type="button" onclick="format('italic')" title="Italic"><i>I</i></button>
<button type="button" onclick="format('underline')" title="Underline"><u>U</u></button>
<button type="button" onclick="format('strikeThrough')" title="Strikethrough"><s>S</s></button>
<button type="button" onclick="format('superscript')" title="Superscript">x²</button>
<button type="button" onclick="format('subscript')" title="Subscript">x₂</button>
<button type="button" onclick="setAlignment('left')" title="Align Left">⬅️</button>
<button type="button" onclick="setAlignment('center')" title="Align Center">↔️</button>
<button type="button" onclick="setAlignment('right')" title="Align Right">➡️</button>
<button type="button" onclick="setAlignment('justify')" title="Justify">☰</button>

<select onchange="setLineHeight(this.value)" title="Line Spacing">
<option value="">↕️</option><option value="1">1</option><option value="1.5">1.5</option><option value="2">2</option><option value="2.5">2.5</option><option value="3">3</option>
</select>

<button type="button" onclick="format('insertOrderedList')" title="Ordered List">🔢</button>
<button type="button" onclick="format('insertUnorderedList')" title="Bullet List">•●•</button>
<button type="button" onclick="insertLink()" title="Insert Link">🔗</button>
<button type="button" onclick="uploadImage()" title="Insert Image">🖼️</button>
<button type="button" onclick="uploadVideo()" title="Insert Video">🎥</button>
<button type="button" onclick="insertHorizontalLine()" title="Horizontal Line">―</button>
<button type="button" onclick="insertBlockquote()" title="Blockquote">❝</button>
<button type="button" onclick="insertCodeBlock()" title="Code Block">&lt;/&gt;</button>
<button type="button" onclick="insertTable()" title="Insert Table">🗒️</button>

<select id="fontFamily" onchange="applyFontFamily()" title="Font">
<option value="Arial">Arial</option><option value="Courier New">Courier New</option><option value="Georgia">Georgia</option><option value="Times New Roman">Times New Roman</option><option value="Verdana">Verdana</option>
</select>

<select id="fontSize" onchange="applyFontSize()" title="Font Size">
<option value="12px">12px</option><option value="14px">14px</option><option value="16px" selected>16px</option><option value="18px">18px</option><option value="20px">20px</option><option value="24px">24px</option>
</select>

<select onchange="applyHeading(this.value)" title="Heading Level">
<option value="">Heading</option><option value="H1">H1</option><option value="H2">H2</option><option value="H3">H3</option><option value="H4">H4</option><option value="H5">H5</option><option value="H6">H6</option>
</select>

<button type="button" onclick="insertDateTime()" title="Insert Date/Time">🕒</button>
<button type="button" onclick="togglePicker('colorPicker')" title="Text Color">🎨</button>
<button type="button" onclick="togglePicker('bgColorPicker')" title="Background Color">🖌️</button>
<button type="button" onclick="togglePicker('emojiPicker')" title="Emoji">😊</button>
<button type="button" onclick="format('undo')" title="Undo">↩️</button>
<button type="button" onclick="format('redo')" title="Redo">↪️</button>
<button type="button" onclick="clearContent()" title="Clear">🗑️</button>

<div id="pickers">
<div id="colorPicker" class="picker-panel"><input type="color" onchange="applyTextColor(this.value)"></div>
<div id="bgColorPicker" class="picker-panel"><input type="color" onchange="applyBgColor(this.value)"></div>
<div id="emojiPicker" class="picker-panel">😀 😃 😄 😁 😆 😅 😂 🤣 😊 😇 😉 😍 🥰 😘 😗 😙 😚</div>
</div>

<textarea name="postdescription" id="postdescription" class="form-control" rows="10" required></textarea>

</div></div>

<div class="form-group col-md-6">
<label for="postimage">Feature Image</label>
<input type="file" name="postimage" id="postimage" class="form-control" accept=".jpg,.jpeg,.png,.gif" required>
</div>

<div class="form-group col-md-12">
<button type="submit" class="btn btn-primary">Submit</button>
</div>

</form>

</div></div></div>

<script src="plugins/jQuery/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="plugins/bootstrap/bootstrap.min.js"></script>
<!-- slick slider -->
<script src="plugins/slick/slick.min.js"></script>
<!-- aos -->
<script src="plugins/aos/aos.js"></script>
<!-- venobox popup -->
<script src="plugins/venobox/venobox.min.js"></script>
<!-- filter -->
<script src="plugins/filterizr/jquery.filterizr.min.js"></script>
<!-- google map -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcABaamniA6OL5YvYSpB3pFMNrXwXnLwU"></script>
<script src="plugins/google-map/gmap.js"></script>

<!-- Main Script -->
<script src="script.js"></script>

</body></html>
