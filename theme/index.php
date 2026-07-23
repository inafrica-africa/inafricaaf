<?php
include('config.php');   ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>INAfrica Youth Initiative.</title>

  <!-- Mobile Specific Metas
	================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="INAfrica Youth Initiative: Connecting more than 1.54 Billion African Citizens.">
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


  <!-- Main Stylesheet -->
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">

  <!--Favicon-->
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
  <link rel="icon" href="images/logo.png" type="image/x-icon">

</head>

<body>
  <?php include('header.php');?>


<section class="hero-section overlay">
  <div class="container">
<div class="row align-items-center">
  <!-- Left: Quotes -->
  <div class="col-md-3">
    <h1>Quotes of The Day:</h1>
    <div class="hero-slider">
      <?php
      $query = false;
      try {
          $query = mysqli_query($con, "SELECT Name, Quote, QuoteImage, year_id FROM tblquotes WHERE Is_Active = 1 ORDER BY updated_at DESC LIMIT 10");
      } catch (mysqli_sql_exception $e) {
          $query = false;
      }
      if ($query && mysqli_num_rows($query) > 0) {
          while ($row = mysqli_fetch_assoc($query)) {
              $name = htmlspecialchars($row['Name']);
              $quote = nl2br(htmlspecialchars($row['Quote']));
              $year = htmlspecialchars($row['year_id']);
              $quoteImg = !empty($row['QuoteImage']) ? 'admin/quoteimages/' . htmlspecialchars($row['QuoteImage']) : null;
      ?>
      <div class="hero-slider-item">
        <div class="row">
          <div class="col-md-12">
            <?php if ($quoteImg): ?>
            <img src="<?= $quoteImg ?>" alt="<?= $name ?>" class="rounded-circle mb-3"
                 style="width:80px; height:80px; object-fit:cover; border:3px solid #fff;"
                 data-animation-out="fadeOutRight"
                 data-delay-out="5"
                 data-duration-in=".3"
                 data-animation-in="fadeInLeft"
                 data-delay-in="0">
            <?php endif; ?>
            <h1 class="text-white"
                data-animation-out="fadeOutRight"
                data-delay-out="5"
                data-duration-in=".3"
                data-animation-in="fadeInLeft"
                data-delay-in=".1">
              <?= $year ?>
            </h1>
            <p class="text-muted mb-4"
               data-animation-out="fadeOutRight"
               data-delay-out="5"
               data-duration-in=".3"
               data-animation-in="fadeInLeft"
               data-delay-in=".4">
              <?= $quote ?>
            </p>
            <a class="btn btn-primary"
               style="background-color: #2fb44b; border-color: #ede32b;"
               data-animation-out="fadeOutRight"
               data-delay-out="5"
               data-duration-in=".3"
               data-animation-in="fadeInLeft"
               data-delay-in=".7">
              <?= $name ?>
            </a>
          </div>
        </div>
      </div>
      <?php
          }
      } else {
          echo '<p>No quotes found at the moment.</p>';
      }
      ?>
    </div>
  </div>

  <!-- Middle: Africa map -->
  <div class="col-md-6 text-center">
    <img src="images/banner/banner-1.jpg" alt="Map of Africa with all 54 countries" class="hero-map-img">
  </div>

  <!-- Right: Recent Updates (cycling cover + title) -->
  <div class="col-md-3 text-white">
    <h1>Recent Updates:</h1>
    <div class="hero-slider recent-updates-slider">
    <?php
    $recentQuery = mysqli_query($con, "
        SELECT PostTitle, PostUrl, PostImage, PostingDate
        FROM tblposts
        WHERE Status = 'Approved' AND Is_Active = 1
        ORDER BY PostingDate DESC
        LIMIT 6
    ");
    if ($recentQuery && mysqli_num_rows($recentQuery) > 0) {
        while ($recent = mysqli_fetch_assoc($recentQuery)) {
            $recentTitle = htmlspecialchars($recent['PostTitle']);
            $recentLink = 'news-details.php?PostUrl=' . urlencode($recent['PostUrl']);
            $recentImg = $recent['PostImage'] ? 'admin/postimages/' . htmlspecialchars($recent['PostImage']) : 'images/blog/post-1.jpg';
    ?>
    <div class="hero-slider-item">
      <a href="<?= $recentLink ?>" class="d-block text-white">
        <img src="<?= $recentImg ?>" alt="<?= $recentTitle ?>" class="recent-update-cover rounded mb-3">
        <h5 class="text-white"><?= $recentTitle ?></h5>
        <small><?= date('F j, Y', strtotime($recent['PostingDate'])) ?></small>
      </a>
    </div>
    <?php
        }
    } else {
        echo '<p class="text-white mb-4">No recent updates yet.</p>';
    }
    ?>
    </div>
  </div>
</div>

  </div>
</section>

<!-- adverts -->
<?php $homeAdverts = fetchAdverts($con); ?>
<?php if (!empty($homeAdverts['landscape'])): ?>
<section class="py-4 bg-light">
  <div class="container">
    <div class="row align-items-center justify-content-center">
      <?php if (!empty($homeAdverts['landscape'][0])): ?>
        <div class="col-md-6 mb-3 mb-md-0 text-center">
          <?php renderAdvertCard($homeAdverts['landscape'][0], 'width:100%; max-width:500px; height:auto; border-radius:4px; display:block;', 'Sponsored', false); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($homeAdverts['landscape'][1])): ?>
        <div class="col-md-6 text-center">
          <?php renderAdvertCard($homeAdverts['landscape'][1], 'width:100%; max-width:500px; height:auto; border-radius:4px; display:block;', 'Sponsored', false); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- /adverts -->

<!-- blog -->
<section class="section pt-0">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h2 class="section-title">What is happening in Africa:</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <?php
      $homeBlogQuery = mysqli_query($con, "
          SELECT p.PostTitle, p.PostUrl, p.PostImage, p.PostingDate, p.postedBy, c.CategoryName
          FROM tblposts p
          LEFT JOIN tblcategory c ON c.id = p.CategoryId
          WHERE p.Status = 'Approved' AND p.Is_Active = 1
          ORDER BY p.PostingDate DESC
          LIMIT 3
      ");
      if ($homeBlogQuery && mysqli_num_rows($homeBlogQuery) > 0) {
          while ($post = mysqli_fetch_assoc($homeBlogQuery)) {
              $postLink = 'news-details.php?PostUrl=' . urlencode($post['PostUrl']);
              $postImg = $post['PostImage'] ? 'admin/postimages/' . htmlspecialchars($post['PostImage']) : 'images/blog/post-1.jpg';
      ?>
      <article class="col-lg-4 col-sm-6 mb-5 mb-lg-0">
        <div class="card rounded-0 border-bottom border-primary border-top-0 border-left-0 border-right-0 hover-shadow">
          <img class="card-img-top rounded-0" src="<?= $postImg ?>" alt="Post thumb">
          <div class="card-body">
            <ul class="list-inline mb-3">
              <li class="list-inline-item mr-3 ml-0"><?= date('F j, Y', strtotime($post['PostingDate'])) ?></li>
              <?php if (!empty($post['CategoryName'])): ?>
                <li class="list-inline-item mr-3 ml-0"><?= htmlspecialchars($post['CategoryName']) ?></li>
              <?php endif; ?>
            </ul>
            <a href="<?= $postLink ?>">
              <h4 class="card-title"><?= htmlspecialchars($post['PostTitle']) ?></h4>
            </a>
            <a href="<?= $postLink ?>" class="btn btn-primary btn-sm">read more</a>
          </div>
        </div>
      </article>
      <?php
          }
      } else {
          echo '<div class="col-12 text-center text-muted">No posts yet.</div>';
      }
      ?>
    </div>
  </div>
</section>
<!-- /blog -->

<?php include('footer.php');?>

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
