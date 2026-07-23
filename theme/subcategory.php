<?php
session_start();
include('config.php');   ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php $siteUrl = 'https://inafricaac.org/'; ?>
    <meta charset="utf-8">
  <title>INAfrica Youth Initiative.</title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Construction Html5 Template">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="author" content="Themefisher">
  <meta name="generator" content="Themefisher Educenter HTML Template v1.0">
  
  <!-- theme meta -->
  <meta name="theme-name" content="INAfrica" />

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
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">

  <!--Favicon-->
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
  <link rel="icon" href="images/logo.png" type="image/x-icon">


<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($siteUrl . 'subcategory.php' . (isset($_GET['subcatid']) ? '?subcatid=' . (int) $_GET['subcatid'] : '')) ?>">
<meta property="og:title" content="INAfrica Youth Initiative">
<meta property="og:image" content="<?= $siteUrl ?>images/logo.png">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

 <!-- Third-party scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://apis.google.com/js/api.js"></script>
    <script src="https://www.googletagservices.com/tag/js/gpt.js" async></script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7608871255429945"
     crossorigin="anonymous"></script>

</head>
<body>
    <?php include('header.php');?>

<!-- Main Content and Sidebar -->
<div class="container">
    <!-- Main Content -->
    <div class="col-md-9">
      <?php 
if (isset($_GET['subcatid'])) {
    $_SESSION['subcatid'] = intval($_GET['subcatid']);
}

$pageno = isset($_GET['pageno']) ? intval($_GET['pageno']) : 1;
$no_of_records_per_page = 8;
$offset = ($pageno - 1) * $no_of_records_per_page;

// Count total posts in the subcategory
$total_pages_sql = "SELECT COUNT(*) FROM tblposts WHERE Is_Active='1' AND SubCategoryId='" . $_SESSION['subcatid'] . "'";
$result = mysqli_query($con, $total_pages_sql);
$total_rows = mysqli_fetch_array($result)[0];
$total_pages = ceil($total_rows / $no_of_records_per_page);

// Fetch posts for subcategory
$query = mysqli_query($con, "
    SELECT 
        tblposts.PostUrl,
        tblposts.PostTitle as posttitle,
        tblposts.PostImage,
        tblcategory.CategoryName as category,
        tblsubcategory.Subcategory as subcategory,
        tblposts.PostDetails as postdetails,
        tblposts.PostingDate as postingdate
    FROM tblposts 
    LEFT JOIN tblcategory ON tblcategory.id = tblposts.CategoryId 
    LEFT JOIN tblsubcategory ON tblsubcategory.SubCategoryId = tblposts.SubCategoryId 
    WHERE tblposts.SubCategoryId='" . $_SESSION['subcatid'] . "' 
      AND tblposts.Status='Approved' 
    ORDER BY tblposts.id DESC 
    LIMIT $offset, $no_of_records_per_page
");

$rowcount = mysqli_num_rows($query);

// Fetch subcategory name for the heading
$subcatName = "Subcategory";
if (!empty($_SESSION['subcatid'])) {
    $subcatRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT Subcategory FROM tblsubcategory WHERE SubCategoryId=" . $_SESSION['subcatid']));
    $subcatName = $subcatRow['Subcategory'] ?? 'Subcategory';
}

if ($rowcount == 0) {
    echo "<p>No records found under <strong>" . htmlentities($subcatName) . "</strong>.</p>";
} else {
    echo "<h1>" . htmlentities($subcatName) . " Related News</h1>";
    echo '<div class="row">';
    while ($row = mysqli_fetch_array($query)) {
?>
        <div class="col-lg-4 col-sm-6 mb-4">
        <div class="card h-100">
            <?php if (!empty($row['PostImage'])): ?>
                <img class="card-img-top" src="admin/postimages/<?php echo htmlentities($row['PostImage']); ?>" alt="<?php echo htmlentities($row['posttitle']); ?>">
            <?php else: ?>
                <div class="card-img-top post-image-placeholder d-flex align-items-center justify-content-center" style="height:160px;">
                    <i class="ti-image"></i>
                </div>
            <?php endif; ?>
            <div class="card-body">
                <p class="mb-1"><small>Posted on <?php echo htmlentities($row['postingdate']); ?></small></p>
                <a href="news-details.php?PostUrl=<?php echo urlencode($row['PostUrl']); ?>" class="text-decoration-none text-dark">
                    <h2 class="h5"><?php echo htmlentities($row['posttitle']); ?></h2>
                </a>
                <a href="news-details.php?PostUrl=<?php echo urlencode($row['PostUrl']); ?>">Read More →</a>
            </div>
        </div>
        </div>
<?php
    }
    echo '</div>';
}
?>

    </div>
</div>
<!-- jQuery -->
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
<script src="js/script.js?v=<?= @filemtime(__DIR__ . '/js/script.js') ?>"></script>
</body>
</html>
