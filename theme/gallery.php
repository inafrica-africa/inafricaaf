<?php
include('config.php');

$items = [];
$result = mysqli_query($con, "SELECT id, Title, MediaType, ImagePath, YoutubeUrl, Description FROM tblgallery WHERE Is_Active = 1 ORDER BY CreatedDate DESC");
if ($result) {
    $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Gallery | INAfrica</title>
  <meta name="description" content="INAfrica Youth Initiative: Connecting more than 1.54 Billion African Citizens.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link rel="stylesheet" href="plugins/venobox/venobox.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
</head>
<body>
  <?php include('header.php'); ?>

  <section class="page-title-section bg-cover overlay" style="background-image: url('images/banner/banner-1.jpg');">
    <div class="container">
      <h1 class="text-white">Gallery</h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="row">
        <?php if (empty($items)): ?>
          <div class="col-12 text-center text-muted">No gallery items yet.</div>
        <?php else: foreach ($items as $item): ?>
          <div class="col-lg-4 col-sm-6 mb-4">
            <div class="card h-100">
              <?php if ($item['MediaType'] === 'Image' && $item['ImagePath']): ?>
                <a href="admin/gallery/<?= htmlspecialchars($item['ImagePath']) ?>" class="venobox" data-gall="inafrica-gallery" title="<?= htmlspecialchars($item['Title']) ?>">
                  <img class="card-img-top" src="admin/gallery/<?= htmlspecialchars($item['ImagePath']) ?>" alt="<?= htmlspecialchars($item['Title']) ?>" style="height:220px; object-fit:cover;">
                </a>
              <?php elseif ($item['MediaType'] === 'Video' && $item['YoutubeUrl']): ?>
                <a href="<?= htmlspecialchars($item['YoutubeUrl']) ?>" class="venobox" data-vbtype="video" data-gall="inafrica-gallery-videos" title="<?= htmlspecialchars($item['Title']) ?>">
                  <div class="d-flex align-items-center justify-content-center bg-dark" style="height:220px;">
                    <i class="ti-youtube" style="font-size:48px; color:#fff;"></i>
                  </div>
                </a>
              <?php endif; ?>
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($item['Title']) ?></h5>
                <?php if (!empty($item['Description'])): ?>
                  <p class="card-text small text-muted"><?= htmlspecialchars($item['Description']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </section>

  <?php include('footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
  <script src="plugins/venobox/venobox.min.js"></script>
  <script>
    $(document).ready(function () {
      $('.venobox').venobox();
    });
  </script>
</body>
</html>
