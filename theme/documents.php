<?php
include('config.php');

$validTypes = ['Statement', 'Letter', 'Report'];
$type = $_GET['type'] ?? 'Statement';
if (!in_array($type, $validTypes, true)) {
    $type = 'Statement';
}

$documents = [];
$stmt = $con->prepare("SELECT Title, Description, FilePath, UploadDate FROM tbldocuments WHERE DocType = ? AND Is_Active = 1 ORDER BY UploadDate DESC");
$stmt->bind_param("s", $type);
$stmt->execute();
$documents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$typeLabels = ['Statement' => 'Statements & Publications', 'Letter' => 'Letters', 'Report' => 'Reports'];
$pageTitle = $typeLabels[$type];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle) ?> | INAfrica</title>
  <meta name="description" content="INAfrica Youth Initiative: Connecting more than 1.54 Billion African Citizens.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
</head>
<body>
  <?php include('header.php'); ?>

  <section class="page-title-section bg-cover overlay" style="background-image: url('images/banner/banner-1.jpg');">
    <div class="container">
      <h1 class="text-white"><?= htmlspecialchars($pageTitle) ?></h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="row">
        <?php if (empty($documents)): ?>
          <div class="col-12 text-center text-muted">No <?= htmlspecialchars(strtolower($pageTitle)) ?> published yet.</div>
        <?php else: foreach ($documents as $doc): ?>
          <div class="col-lg-4 col-sm-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($doc['Title']) ?></h5>
                <p class="card-text text-muted small"><?= date('F j, Y', strtotime($doc['UploadDate'])) ?></p>
                <?php if (!empty($doc['Description'])): ?>
                  <p class="card-text"><?= htmlspecialchars($doc['Description']) ?></p>
                <?php endif; ?>
                <a href="admin/documents/<?= htmlspecialchars($doc['FilePath']) ?>" class="btn btn-primary btn-sm" target="_blank">Download</a>
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
</body>
</html>
