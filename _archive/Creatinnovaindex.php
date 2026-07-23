<?php
include('config.php');   ?>
<?php
$mainDomain = 'https://hafinabuzz.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Hafinabuzz: Numbers Money Business Business develepment | Think About Your Future!">
    <meta name="author" content="Hafinabuzz Team">
  <title>CREATINNOVA | HAFINABUZZ.COM</title>
  
    <!-- Bootstrap core CSS -->
    <link href="<?= $mainDomain ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="<?= $mainDomain ?>/css/icons.css">
    <link rel="stylesheet" href="<?= $mainDomain ?>/css/owl.carousel.min.css">
    <link rel="stylesheet" href="<?= $mainDomain ?>/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/style.css"> 
    
 <link rel="shortcut icon" href="/Adel_Ade.png">

    <!-- Third-party scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://apis.google.com/js/api.js"></script>
    <script src="https://www.googletagservices.com/tag/js/gpt.js" async></script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7608871255429945"
     crossorigin="anonymous"></script>
     
    
</head>

<body>
    <div class="container-fluid custom-header header-top py-2">
  <div class="d-flex justify-content-between align-items-center w-100 flex-wrap">

    <!-- Left: Call/WhatsApp -->
    <a href="tel:+250729356481" class="text-decoration-none header-link d-flex align-items-center flex-shrink-1">
      <i class="fas fa-comments me-1"></i> 
      <strong>+(250) 729 356 481</strong>
    </a>

    <!-- Right group: About Us + Social Icons -->
    <div class="d-flex align-items-center about-social-group flex-shrink-1">

      <a href="<?= $mainDomain ?>/about" class="text-decoration-none header-link d-flex align-items-center flex-shrink-1">
        <strong>About Us</strong>
      </a>

      <div class="social-icons d-flex align-items-center flex-shrink-1">
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://tiktok.com" target="_blank"><i class="fab fa-tiktok"></i></a>
      </div>

    </div>

  </div>
</div>




  <!-- Advert Container (Combined with Header) -->
  <div class="advert-container d-flex justify-content-between align-items-center py-2">
    <div class="advert-container-left">
      <!-- Display image adverts -->
      <div class="gallery-item">
        <?php
$gifQuery = mysqli_query($con, "SELECT * FROM gifadvert WHERE Is_Active = 1 ORDER BY id DESC");
if ($gifQuery && mysqli_num_rows($gifQuery) > 0) {
    while ($row = mysqli_fetch_assoc($gifQuery)) {
        $fileData = $row['file'];
        $mimeType = getMimeType($fileData);

        // Check if it's really a GIF
        if (strpos($mimeType, 'gif') !== false) {
            $imageData = base64_decode($fileData);
            $img = imagecreatefromstring($imageData);
            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                imagedestroy($img);

                // Only show if it's landscape
                if ($width > $height) {
                    echo '<a href="advert.php?id=' . $row["id"] . '">
                        <img src="data:' . $mimeType . ';base64,' . base64_encode($fileData) . '" 
                        style="width: 350px; height: auto; max-height: 100px;" />
                    </a>';
                    break; // Show only one gif
                }
            }
        }
    }
}
?>

      </div>
    </div>

    <div class="advert-container-middle text-center">
      <!-- Display current date and time -->
      <div id="currentDate"></div>
      <div id="currentTime"></div>
    </div>

    <div class="advert-container-right">
      <!-- Display image adverts -->
      <div class="gallery-item">
        <?php
$gifQuery = mysqli_query($con, "SELECT * FROM gifadvert WHERE Is_Active = 1 ORDER BY id DESC");
if ($gifQuery && mysqli_num_rows($gifQuery) > 0) {
    while ($row = mysqli_fetch_assoc($gifQuery)) {
        $fileData = $row['file'];
        $mimeType = getMimeType($fileData);

        // Check if it's really a GIF
        if (strpos($mimeType, 'gif') !== false) {
            $imageData = base64_decode($fileData);
            $img = imagecreatefromstring($imageData);
            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                imagedestroy($img);

                // Only show if it's landscape
                if ($width > $height) {
                    echo '<a href="advert.php?id=' . $row["id"] . '">
                        <img src="data:' . $mimeType . ';base64,' . base64_encode($fileData) . '" 
                        style="width: 350px; height: auto; max-height: 100px;" />
                    </a>';
                    break; // Show only one gif
                }
            }
        }
    }
}
?>

      </div>
    </div>
  </div>
   <div id="google_translate_element" class="ms-4" style="margin-top: 10px;"></div>
</div>
        <h1 class="m-15 display-6 text-uppercase">
            <a class="m-2 avbar-brand text" href="<?= $mainDomain ?>">HAFinabuzz.com</a>
        </h1>
    <?php include('header.php');?>
    
<!-- blog -->
<section class="section pt-0">
  <div class="container">
    <div class="row">
      <div class="col-md-9">
        <div class="col-12">
          <h2 class="section-title">Pitch to the World of Investors:</h2>
        </div>

        <div class="row">
          <?php
          // Define the limit_words function once
          function limit_words($text, $limit) {
              $words = explode(' ', strip_tags($text));
              if (count($words) > $limit) {
                  return implode(' ', array_slice($words, 0, $limit)) . '...';
              }
              return $text;
          }

          // Fetch posts from tblcreatinnova
          $query = mysqli_query($con, "SELECT id, PostTitle, PostDetails, PostingDate, postedBy FROM tblcreatinnova ORDER BY PostingDate DESC LIMIT 12");

          while ($row = mysqli_fetch_assoc($query)) {
              $postId = $row['id'];
              $postTitle = $row['PostTitle'] ?? '';
              $postDetails = $row['PostDetails'] ?? '';
              $postDate = $row['PostingDate'] ?? '';
              $postAuthor = $row['postedBy'] ?? 'Unknown';
          ?>
          <!-- blog post -->
          <article class="col-lg-4 col-sm-6 mb-5">
            <div class="card rounded-0 border-bottom border-primary hover-shadow">
              <img class="card-img-top rounded-0" src="images/blog/post-3.jpg" alt="Post thumb">

              <div class="card-body">
                <!-- post meta -->
                <ul class="list-inline mb-3">
                  <li class="list-inline-item mr-3 ml-0">
                    <?= $postDate ? date("F j, Y", strtotime($postDate)) : 'No date' ?>
                  </li>
                  <li class="list-inline-item mr-3 ml-0">
                    By <?= htmlspecialchars($postAuthor) ?>
                  </li>
                </ul>

                <h4 class="card-title"><?= htmlspecialchars($postTitle) ?></h4>

                <!-- Preview -->
                <p class="card-text preview-text" id="preview-<?= $postId ?>">
                  <?= nl2br(htmlspecialchars(limit_words($postDetails, 150))) ?>
                </p>

                <!-- Full content (hidden) -->
                <p class="card-text full-text" id="full-<?= $postId ?>" style="display:none;">
                  <?= nl2br(htmlspecialchars(limit_words($postDetails, 1500))) ?>
                </p>

                <button class="btn btn-primary btn-sm" onclick="toggleMessage('<?= $postId ?>')" type="button">
                  Open
                </button>
              </div>
            </div>
          </article>
          <?php } ?>
        </div>
      </div>
        <?php include('sidebar.php'); ?>
    </div>
  </div>
</section>
<!-- /blog -->
 <div class="back-to-top-container">
        <a href="#" class="back-to-top"><i class="fa fa-angle-up"></i></a>
    </div>
<?php include('footer.php'); ?>
    <!-- Bootstrap core JavaScript -->
    <script src="<?= $mainDomain ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Owl Carousel -->
    <script src="<?= $mainDomain ?>/js/owl.carousel.min.js"></script>
    <!-- Custom Script -->
    <script src="<?= $mainDomain ?>/script.js"></script>
    <!-- Google Translate -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
