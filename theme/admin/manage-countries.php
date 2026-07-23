<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblcountries SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Country status updated.';
        } elseif ($action === 'edit' && $id > 0) {
            $hook = trim($_POST['hook'] ?? '');
            $sizeArea = trim($_POST['size_area'] ?? '');
            $latitude = trim($_POST['latitude'] ?? '');
            $longitude = trim($_POST['longitude'] ?? '');
            $locationDesc = trim($_POST['location_desc'] ?? '');
            $languages = trim($_POST['languages'] ?? '');
            $adminType = trim($_POST['administrative_type'] ?? '');
            $culture = trim($_POST['culture'] ?? '');
            $economicBasis = trim($_POST['economic_basis'] ?? '');

            $lat = $latitude === '' ? null : (float) $latitude;
            $lng = $longitude === '' ? null : (float) $longitude;

            $stmt = $con->prepare("
                UPDATE tblcountries
                SET Hook = ?, SizeArea = ?, Latitude = ?, Longitude = ?, LocationDesc = ?,
                    Languages = ?, AdministrativeType = ?, Culture = ?, EconomicBasis = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssddssssi", $hook, $sizeArea, $lat, $lng, $locationDesc, $languages, $adminType, $culture, $economicBasis, $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Country profile updated.';
        }
    }
}

$regions = [];
$result = mysqli_query($con, "SELECT id, RegionName FROM tblregions ORDER BY RegionName ASC");
if ($result) {
    $regions = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$regionFilter = isset($_GET['region']) ? intval($_GET['region']) : 0;

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT * FROM tblcountries WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$pageno = isset($_GET['pageno']) ? max(1, intval($_GET['pageno'])) : 1;
$perPage = 20;
$offset = ($pageno - 1) * $perPage;

$whereRegion = $regionFilter > 0 ? " WHERE c.RegionId = " . $regionFilter : "";

$totalRows = countRows($con, "SELECT COUNT(*) FROM tblcountries c" . $whereRegion);
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$countries = [];
$result = mysqli_query($con, "
    SELECT c.id, c.CountryName, c.CountryCode, c.Hook, c.Is_Active, r.RegionName
    FROM tblcountries c
    LEFT JOIN tblregions r ON r.id = c.RegionId
    $whereRegion
    ORDER BY r.RegionName ASC, c.CountryName ASC
    LIMIT $offset, $perPage
");
if ($result) {
    $countries = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function countriesPagerUrl($page, $regionFilter) {
    $params = ['pageno' => $page];
    if ($regionFilter > 0) {
        $params['region'] = $regionFilter;
    }
    return 'manage-countries.php?' . http_build_query($params);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Countries</h4>
                    <p class="text-muted">Countries are pre-seeded by region. Edit a country to add its public profile: hook text, size, location &amp; coordinates, languages, administrative type, culture, and economic basis.</p>
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
                        <h5>Edit Country Profile &mdash; <?= htmlspecialchars($editRow['CountryName']) ?></h5>
                        <form method="post" action="manage-countries.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">

                            <div class="form-group">
                                <label>Hook (short teaser shown on the country card, ~1-2 sentences)</label>
                                <textarea name="hook" class="form-control" rows="2" maxlength="300"><?= htmlspecialchars($editRow['Hook'] ?? '') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Size (area)</label>
                                        <input type="text" name="size_area" class="form-control" placeholder="e.g. 1,246,700 km&sup2;" value="<?= htmlspecialchars($editRow['SizeArea'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Latitude</label>
                                        <input type="number" step="any" name="latitude" class="form-control" value="<?= htmlspecialchars($editRow['Latitude'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Longitude</label>
                                        <input type="number" step="any" name="longitude" class="form-control" value="<?= htmlspecialchars($editRow['Longitude'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Location description</label>
                                <input type="text" name="location_desc" class="form-control" placeholder="e.g. Southern Africa, bordering Namibia and Botswana" value="<?= htmlspecialchars($editRow['LocationDesc'] ?? '') ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Languages</label>
                                        <input type="text" name="languages" class="form-control" placeholder="e.g. English, Swahili" value="<?= htmlspecialchars($editRow['Languages'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Administrative type</label>
                                        <input type="text" name="administrative_type" class="form-control" placeholder="e.g. Federal Parliamentary Republic" value="<?= htmlspecialchars($editRow['AdministrativeType'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Culture</label>
                                <textarea name="culture" class="form-control" rows="4"><?= htmlspecialchars($editRow['Culture'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Economic basis</label>
                                <textarea name="economic_basis" class="form-control" rows="4"><?= htmlspecialchars($editRow['EconomicBasis'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-countries.php?pageno=<?= $pageno ?><?= $regionFilter ? '&region=' . $regionFilter : '' ?>" class="btn btn-light">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-12">
                    <form method="get" action="manage-countries.php" class="form-inline">
                        <label class="mr-2">Filter by region</label>
                        <select name="region" class="form-control mr-2" onchange="this.form.submit()">
                            <option value="0">All Regions</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= (int) $region['id'] ?>" <?= $regionFilter == $region['id'] ? 'selected' : '' ?>><?= htmlspecialchars($region['RegionName']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit" class="btn btn-sm btn-secondary">Filter</button></noscript>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Region</th>
                                        <th>Country</th>
                                        <th>Hook</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($countries)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">No countries found.</td></tr>
                                    <?php else: foreach ($countries as $country): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($country['RegionName'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($country['CountryName']) ?></td>
                                            <td><?= htmlspecialchars($country['Hook'] ? (mb_strlen($country['Hook']) > 60 ? mb_substr($country['Hook'], 0, 60) . '…' : $country['Hook']) : '—') ?></td>
                                            <td>
                                                <?php if ($country['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="manage-countries.php?edit=<?= (int) $country['id'] ?>&pageno=<?= $pageno ?><?= $regionFilter ? '&region=' . $regionFilter : '' ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-countries.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $country['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning">
                                                        <?= $country['Is_Active'] ? 'Deactivate' : 'Activate' ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalPages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination mb-0">
                                <li class="page-item <?= $pageno <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= countriesPagerUrl(max(1, $pageno - 1), $regionFilter) ?>">&laquo; Prev</a>
                                </li>
                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                    <li class="page-item <?= $p == $pageno ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= countriesPagerUrl($p, $regionFilter) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $pageno >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= countriesPagerUrl(min($totalPages, $pageno + 1), $regionFilter) ?>">Next &raquo;</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
