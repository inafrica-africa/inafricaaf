<?php
include('config.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$doc = null;
if ($id > 0) {
    $stmt = $con->prepare("SELECT id, Title, DocType, Language, Description, FilePath, ThumbnailPath, UploadDate, viewCounter, share_count FROM tbldocuments WHERE id = ? AND Is_Active = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$doc) {
    http_response_code(404);
}

$typeLabels = ['Statement' => 'Statements & Publications', 'Letter' => 'Letters', 'Report' => 'Reports'];
$typeLabel = $typeLabels[$doc['DocType'] ?? ''] ?? 'Documents';
$listingUrl = 'documents?type=' . urlencode($doc['DocType'] ?? 'Statement');

// The open-vs-download logic (PDFs open directly, .doc/.docx go through
// Google's viewer) now lives in view-document.php, which every "open" link
// routes through so the view gets counted regardless of entry point.
$viewUrl = 'view-document?id=' . $id;

$pageTitle = $doc['Title'] ?? 'Document not found';
$shareDescription = $doc['Description'] ?? '';
if ($shareDescription === '' && $doc) {
    $shareDescription = $typeLabel . ' from INAfrica Youth Initiative.';
}
$ogImage = !empty($doc['ThumbnailPath']) ? 'admin/documents/thumbnails/' . $doc['ThumbnailPath'] : 'images/logo.png';
$sharePath = '/document-details?id=' . $id;
$fullShareUrl = SITE_URL . $sharePath;
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
  <?php if ($doc): renderMetaTags(
    $pageTitle,
    mb_substr($shareDescription, 0, 297),
    $ogImage,
    $sharePath,
    'article'
  ); endif; ?>
</head>
<body>
  <?php include('header.php'); ?>

  <section class="page-title-section bg-cover overlay" style="background-image: url('images/banner/banner-1.jpg');">
    <div class="container">
      <h1 class="text-white"><?= htmlspecialchars($doc ? $typeLabel : 'Document Not Found') ?></h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <?php if (!$doc): ?>
        <div class="text-center text-muted">
          <p>This document doesn't exist or is no longer available.</p>
          <a href="documents" class="btn btn-primary btn-sm">Back to Documents</a>
        </div>
      <?php else: ?>
        <div class="row justify-content-center">
          <div class="col-lg-7">
            <div class="card">
              <?php if (!empty($doc['ThumbnailPath'])): ?>
                <img class="card-img-top" src="admin/documents/thumbnails/<?= htmlspecialchars($doc['ThumbnailPath']) ?>" alt="<?= htmlspecialchars($doc['Title']) ?>" style="max-height:420px; object-fit:cover; object-position:top;">
              <?php else: ?>
                <div class="card-img-top post-image-placeholder d-flex align-items-center justify-content-center" style="height:220px;">
                  <i class="ti-file" style="font-size:48px;"></i>
                </div>
              <?php endif; ?>
              <div class="card-body">
                <h4 class="card-title"><?= htmlspecialchars($doc['Title']) ?></h4>
                <p class="card-text text-muted small">
                  <?= date('F j, Y', strtotime($doc['UploadDate'])) ?>
                  &middot; <span class="badge badge-secondary"><?= htmlspecialchars($doc['Language']) ?></span>
                  &middot; <span class="badge badge-secondary"><?= htmlspecialchars($typeLabel) ?></span>
                </p>
                <?php if (!empty($doc['Description'])): ?>
                  <p class="card-text"><?= htmlspecialchars($doc['Description']) ?></p>
                <?php endif; ?>
                <p class="card-text text-muted small">
                  <i class="ti-eye"></i> <span id="docViewCount"><?= number_format((int) $doc['viewCounter']) ?></span> views
                  &middot; <i class="ti-share"></i> <span id="docShareCount"><?= number_format((int) $doc['share_count']) ?></span> shares
                </p>

                <a href="<?= htmlspecialchars($viewUrl) ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener">Open Document</a>
                <a href="<?= htmlspecialchars($listingUrl) ?>" class="btn btn-outline-secondary btn-sm">Back to <?= htmlspecialchars($typeLabel) ?></a>

                <hr>

                <p class="mb-2"><strong>Share this document</strong></p>
                <div class="social-share-buttons">
                  <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($fullShareUrl) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm js-track-share"><i class="ti-facebook"></i> Facebook</a>
                  <a href="https://twitter.com/intent/tweet?url=<?= urlencode($fullShareUrl) ?>&text=<?= urlencode($pageTitle) ?>" target="_blank" rel="noopener" class="btn btn-info btn-sm js-track-share"><i class="ti-twitter-alt"></i> X</a>
                  <a href="https://api.whatsapp.com/send?text=<?= urlencode($pageTitle . ' ' . $fullShareUrl) ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm js-track-share"><i class="ti-mobile"></i> WhatsApp</a>
                  <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($fullShareUrl) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm js-track-share"><i class="ti-linkedin"></i> LinkedIn</a>
                </div>
                <div class="input-group mt-3">
                  <input type="text" id="shareLinkInput" class="form-control form-control-sm" value="<?= htmlspecialchars($fullShareUrl) ?>" readonly onclick="this.select();">
                  <div class="input-group-append">
                    <button id="copyLinkBtn" class="btn btn-outline-secondary btn-sm js-track-share" type="button" onclick="var i=document.getElementById('shareLinkInput'); i.select(); document.execCommand('copy');">Copy Link</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php include('footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
  <script src="js/script.js?v=<?= @filemtime(__DIR__ . '/js/script.js') ?>"></script>
  <?php if ($doc): ?>
  <script>
    // Fire a one-shot beacon for each share action (social buttons open a new
    // tab so the page never unloads, ruling out sendBeacon-on-unload; a plain
    // fetch works fine here since we don't need to wait for it) and bump the
    // on-page counter optimistically so it doesn't need a reload to show.
    (function () {
      var docId = <?= (int) $id ?>;
      var counter = document.getElementById('docShareCount');
      document.querySelectorAll('.js-track-share').forEach(function (el) {
        el.addEventListener('click', function () {
          if (counter) {
            counter.textContent = (parseInt(counter.textContent.replace(/,/g, ''), 10) || 0) + 1;
          }
          fetch('track-share.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'type=document&id=' + encodeURIComponent(docId)
          });
        });
      });
    })();
  </script>
  <?php endif; ?>
</body>
</html>
