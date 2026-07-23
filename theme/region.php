<?php
include('config.php');

$regionId = isset($_GET['region']) ? intval($_GET['region']) : 0;
$countryId = isset($_GET['country']) ? intval($_GET['country']) : 0;

$regions = [];
$result = mysqli_query($con, "SELECT id, RegionName FROM tblregions WHERE Is_Active = 1 ORDER BY RegionName ASC");
if ($result) {
    $regions = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$activeRegion = null;
$countries = [];
$posts = [];

if ($regionId > 0) {
    $stmt = $con->prepare("SELECT id, RegionName, RegionLogo FROM tblregions WHERE id = ? AND Is_Active = 1");
    $stmt->bind_param("i", $regionId);
    $stmt->execute();
    $activeRegion = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$activeCountry = null;

$countriesPerPage = 24; // 4 columns x 6 rows
$countryPage = isset($_GET['cpage']) ? max(1, intval($_GET['cpage'])) : 1;
$totalCountryPages = 1;

if ($activeRegion) {
    $totalCountries = countRows($con, "SELECT COUNT(*) FROM tblcountries WHERE RegionId = " . (int) $regionId . " AND Is_Active = 1");
    $totalCountryPages = max(1, (int) ceil($totalCountries / $countriesPerPage));
    $countryOffset = ($countryPage - 1) * $countriesPerPage;

    $stmt = $con->prepare("SELECT id, CountryName, CountryCode, Hook FROM tblcountries WHERE RegionId = ? AND Is_Active = 1 ORDER BY CountryName ASC LIMIT ?, ?");
    $stmt->bind_param("iii", $regionId, $countryOffset, $countriesPerPage);
    $stmt->execute();
    $countries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if ($countryId > 0) {
        $stmt = $con->prepare("SELECT * FROM tblcountries WHERE id = ? AND RegionId = ? AND Is_Active = 1");
        $stmt->bind_param("ii", $countryId, $regionId);
        $stmt->execute();
        $activeCountry = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if ($countryId > 0) {
        $stmt = $con->prepare("
            SELECT p.PostTitle, p.PostUrl, p.PostImage, p.PostingDate
            FROM tblposts p
            WHERE p.RegionId = ? AND p.CountryId = ? AND p.Is_Active = 1 AND p.Status = 'Approved'
            ORDER BY p.PostingDate DESC
        ");
        $stmt->bind_param("ii", $regionId, $countryId);
    } else {
        $stmt = $con->prepare("
            SELECT p.PostTitle, p.PostUrl, p.PostImage, p.PostingDate
            FROM tblposts p
            WHERE p.RegionId = ? AND p.Is_Active = 1 AND p.Status = 'Approved'
            ORDER BY p.PostingDate DESC
        ");
        $stmt->bind_param("i", $regionId);
    }
    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $activeRegion ? htmlspecialchars($activeRegion['RegionName']) : 'Africa Regions' ?> | INAfrica</title>
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
      <h1 class="text-white"><?= $activeRegion ? htmlspecialchars($activeRegion['RegionName']) : 'Africa Regions' ?></h1>
    </div>
  </section>

  <section class="section">
    <div class="container">

      <div class="row mb-4">
        <div class="col-12 text-center">
          <ul class="list-inline">
            <?php foreach ($regions as $region): ?>
              <li class="list-inline-item mx-2 mb-2">
                <a href="region.php?region=<?= (int) $region['id'] ?>" class="btn btn-sm <?= $activeRegion && $activeRegion['id'] == $region['id'] ? 'btn-primary' : 'btn-outline-primary' ?>"><?= htmlspecialchars($region['RegionName']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <?php if (!$activeRegion): ?>
        <div class="col-12 text-center text-muted">Select a region above to browse its countries and news.</div>
      <?php else: ?>

        <div class="row mb-4">
          <div class="col-12 text-center">
            <?php if ($activeRegion['RegionLogo']): ?>
              <img src="admin/regionlogos/<?= htmlspecialchars($activeRegion['RegionLogo']) ?>" alt="<?= htmlspecialchars($activeRegion['RegionName']) ?> logo" style="max-width:100px; max-height:100px; object-fit:contain;" class="mb-3">
            <?php endif; ?>
            <?php if ($activeCountry && $activeCountry['CountryCode']): ?>
              <div class="mb-2">
                <img src="https://flagcdn.com/w80/<?= htmlspecialchars($activeCountry['CountryCode']) ?>.png" alt="Flag of <?= htmlspecialchars($activeCountry['CountryName']) ?>" style="height:50px; box-shadow:0 2px 8px rgba(0,0,0,0.2); border-radius:3px;">
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($countryId === 0): ?>

        <?php if (empty($countries)): ?>
          <div class="col-12 text-center text-muted mb-4">No countries listed for this region yet.</div>
        <?php else: ?>
        <div class="row">
          <?php foreach ($countries as $country): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
              <a href="region.php?region=<?= $regionId ?>&country=<?= (int) $country['id'] ?>" class="card h-100 text-decoration-none text-dark country-card">
                <div class="card-body text-center">
                  <?php if ($country['CountryCode']): ?>
                    <img src="https://flagcdn.com/w80/<?= htmlspecialchars($country['CountryCode']) ?>.png" alt="Flag of <?= htmlspecialchars($country['CountryName']) ?>" style="height:40px; box-shadow:0 1px 4px rgba(0,0,0,0.2); border-radius:2px; margin-bottom:10px;">
                  <?php endif; ?>
                  <h6 class="card-title mb-2"><?= htmlspecialchars($country['CountryName']) ?></h6>
                  <p class="card-text small text-muted mb-0">
                    <?php if (!empty($country['Hook'])): ?>
                      <?= htmlspecialchars(mb_strlen($country['Hook']) > 110 ? mb_substr($country['Hook'], 0, 110) . '…' : $country['Hook']) ?>
                    <?php else: ?>
                      Details coming soon.
                    <?php endif; ?>
                  </p>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($totalCountryPages > 1): ?>
        <nav class="mb-4">
          <ul class="pagination justify-content-center">
            <li class="page-item <?= $countryPage <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="region.php?region=<?= $regionId ?>&cpage=<?= max(1, $countryPage - 1) ?>">&laquo; Prev</a>
            </li>
            <?php for ($p = 1; $p <= $totalCountryPages; $p++): ?>
              <li class="page-item <?= $p == $countryPage ? 'active' : '' ?>">
                <a class="page-link" href="region.php?region=<?= $regionId ?>&cpage=<?= $p ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $countryPage >= $totalCountryPages ? 'disabled' : '' ?>">
              <a class="page-link" href="region.php?region=<?= $regionId ?>&cpage=<?= min($totalCountryPages, $countryPage + 1) ?>">Next &raquo;</a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>

        <?php endif; ?>

        <?php else: ?>
        <div class="row mb-4">
          <div class="col-12 text-center">
            <a href="region.php?region=<?= $regionId ?>" class="btn btn-xs btn-outline-secondary mb-1">&larr; Back to all <?= htmlspecialchars($activeRegion['RegionName']) ?> countries</a>
          </div>
        </div>

        <?php if ($activeCountry):
          $hasOverview = !empty($activeCountry['SizeArea']) || !empty($activeCountry['LocationDesc']) || ($activeCountry['Latitude'] !== null && $activeCountry['Longitude'] !== null);
          $hasGovernance = !empty($activeCountry['AdministrativeType']) || !empty($activeCountry['Languages']);
          $hasCulture = !empty($activeCountry['Culture']);
          $hasEconomy = !empty($activeCountry['EconomicBasis']);
          $hasAnyProfile = !empty($activeCountry['Hook']) || $hasOverview || $hasGovernance || $hasCulture || $hasEconomy;
        ?>

        <?php if (!empty($activeCountry['Hook'])): ?>
        <div class="row mb-3">
          <div class="col-lg-8 offset-lg-2 text-center">
            <p class="lead"><?= htmlspecialchars($activeCountry['Hook']) ?></p>
          </div>
        </div>
        <?php endif; ?>

        <?php if ($hasAnyProfile): ?>
        <div class="row mb-4">
          <?php if ($hasOverview): ?>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
              <div class="card-body">
                <i class="ti ti-world text-primary" style="font-size:28px;"></i>
                <h6 class="card-title mt-2 mb-3">Overview</h6>
                <ul class="list-unstyled text-left small mb-0">
                  <?php if (!empty($activeCountry['SizeArea'])): ?>
                    <li class="mb-2"><strong>Size:</strong> <?= htmlspecialchars($activeCountry['SizeArea']) ?></li>
                  <?php endif; ?>
                  <?php if (!empty($activeCountry['LocationDesc'])): ?>
                    <li class="mb-2"><strong>Location:</strong> <?= htmlspecialchars($activeCountry['LocationDesc']) ?></li>
                  <?php endif; ?>
                  <?php if ($activeCountry['Latitude'] !== null && $activeCountry['Longitude'] !== null): ?>
                    <li class="mb-0"><strong>Coordinates:</strong> <?= htmlspecialchars($activeCountry['Latitude']) ?>&deg;, <?= htmlspecialchars($activeCountry['Longitude']) ?>&deg;</li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($hasGovernance): ?>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
              <div class="card-body">
                <i class="ti ti-shield text-primary" style="font-size:28px;"></i>
                <h6 class="card-title mt-2 mb-3">Governance</h6>
                <ul class="list-unstyled text-left small mb-0">
                  <?php if (!empty($activeCountry['AdministrativeType'])): ?>
                    <li class="mb-2"><strong>Administrative type:</strong> <?= htmlspecialchars($activeCountry['AdministrativeType']) ?></li>
                  <?php endif; ?>
                  <?php if (!empty($activeCountry['Languages'])): ?>
                    <li class="mb-0"><strong>Languages:</strong> <?= htmlspecialchars($activeCountry['Languages']) ?></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($hasCulture): ?>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
              <div class="card-body">
                <i class="ti ti-heart text-primary" style="font-size:28px;"></i>
                <h6 class="card-title mt-2 mb-3">Culture</h6>
                <p class="text-left small mb-0"><?= nl2br(htmlspecialchars($activeCountry['Culture'])) ?></p>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($hasEconomy): ?>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 text-center">
              <div class="card-body">
                <i class="ti ti-bar-chart text-primary" style="font-size:28px;"></i>
                <h6 class="card-title mt-2 mb-3">Economy</h6>
                <p class="text-left small mb-0"><?= nl2br(htmlspecialchars($activeCountry['EconomicBasis'])) ?></p>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="row mb-4">
          <div class="col-12 text-center text-muted">No profile details have been added for this country yet.</div>
        </div>
        <?php endif; ?>

        <?php endif; ?>

        <?php endif; ?>

        <?php if ($countryId): ?>
        <div class="row">
          <div class="col-12">
            <h5 class="mb-3">Latest Updates</h5>
          </div>
        </div>
        <?php endif; ?>

        <div class="row">
          <?php if (empty($posts)): ?>
            <div class="col-12 text-center text-muted">No posts tagged for this <?= $countryId ? 'country' : 'region' ?> yet.</div>
          <?php else: foreach ($posts as $post): ?>
            <div class="col-lg-4 col-sm-6 mb-4">
              <div class="card h-100">
                <?php if (!empty($post['PostImage'])): ?>
                  <img class="card-img-top" src="admin/postimages/<?= htmlspecialchars($post['PostImage']) ?>" alt="<?= htmlspecialchars($post['PostTitle']) ?>">
                <?php else: ?>
                  <div class="card-img-top post-image-placeholder d-flex align-items-center justify-content-center" style="height:160px;">
                    <i class="ti-image"></i>
                  </div>
                <?php endif; ?>
                <div class="card-body">
                  <p class="mb-1"><small><?= date('F j, Y', strtotime($post['PostingDate'])) ?></small></p>
                  <a href="news-details.php?PostUrl=<?= urlencode($post['PostUrl']) ?>" class="text-decoration-none text-dark">
                    <h5 class="card-title"><?= htmlspecialchars($post['PostTitle']) ?></h5>
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>

      <?php endif; ?>
    </div>
  </section>

  <?php include('footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
</body>
</html>
