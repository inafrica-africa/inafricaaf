<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

$totalPosts = countRows($con, "SELECT COUNT(*) FROM tblposts WHERE Is_Active = 1");
$pendingComments = countRows($con, "SELECT COUNT(*) FROM tblcomments WHERE status = 0");
$totalRegions = countRows($con, "SELECT COUNT(*) FROM tblregions WHERE Is_Active = 1");
$totalCountries = countRows($con, "SELECT COUNT(*) FROM tblcountries WHERE Is_Active = 1");
$totalDocuments = countRows($con, "SELECT COUNT(*) FROM tbldocuments WHERE Is_Active = 1");
$totalEvents = countRows($con, "SELECT COUNT(*) FROM tblevents WHERE Is_Active = 1 AND EventDate >= CURDATE()");
$totalGallery = countRows($con, "SELECT COUNT(*) FROM tblgallery WHERE Is_Active = 1");
$totalQuotes = countRows($con, "SELECT COUNT(*) FROM tblquotes WHERE Is_Active = 1");
$totalAdmins = countRows($con, "SELECT COUNT(*) FROM tbladmin");

$recentPosts = [];
$result = mysqli_query($con, "
    SELECT p.PostTitle, p.PostUrl, p.PostingDate, p.viewCounter, r.RegionName
    FROM tblposts p
    LEFT JOIN tblregions r ON r.id = p.RegionId
    ORDER BY p.PostingDate DESC
    LIMIT 8
");
if ($result) {
    $recentPosts = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$stats = [
    ['label' => 'Published Posts',           'value' => $totalPosts,        'class' => 'stat-card-green',  'icon' => 'mdi-newspaper',              'link' => 'manage-posts.php'],
    ['label' => 'Pending Comments',          'value' => $pendingComments,   'class' => 'stat-card-yellow',  'icon' => 'mdi-comment-alert-outline',  'link' => 'unapprove-comment.php'],
    ['label' => 'Categories (Regional Blocs)', 'value' => $totalRegions,    'class' => 'stat-card-blue',    'icon' => 'mdi-earth',                  'link' => null],
    ['label' => 'Sub Categories (Countries)', 'value' => $totalCountries,  'class' => 'stat-card-blue',    'icon' => 'mdi-map-marker',              'link' => null],
    ['label' => 'Documents',                 'value' => $totalDocuments,    'class' => 'stat-card-green',   'icon' => 'mdi-file-document-outline',  'link' => 'manage-documents.php'],
    ['label' => 'Upcoming Events',           'value' => $totalEvents,       'class' => 'stat-card-yellow',  'icon' => 'mdi-calendar',               'link' => 'manage-events.php'],
    ['label' => 'Gallery Items',             'value' => $totalGallery,      'class' => 'stat-card-blue',    'icon' => 'mdi-image-multiple',         'link' => 'manage-gallery.php'],
    ['label' => 'Quotes',                    'value' => $totalQuotes,       'class' => 'stat-card-green',   'icon' => 'mdi-format-quote-close',     'link' => 'manage-quotes.php'],
];

$quickActions = [
    ['label' => 'Add Post',     'link' => 'add-post.php',     'icon' => 'mdi-plus-box'],
    ['label' => 'Add Event',    'link' => 'add-event.php',    'icon' => 'mdi-calendar-plus'],
    ['label' => 'Add Document', 'link' => 'add-document.php', 'icon' => 'mdi-file-plus'],
    ['label' => 'Add to Gallery', 'link' => 'add-gallery.php', 'icon' => 'mdi-image-plus'],
    ['label' => 'Add Quote',    'link' => 'add-quote.php',    'icon' => 'mdi-comment-plus-outline'],
];

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">

            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Dashboard</h4>
                    <p class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>. Here's what's happening across INAfrica.</p>
                </div>
            </div>

            <div class="row">
                <?php foreach ($stats as $stat): ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <?php if ($stat['link']): ?><a href="<?= $stat['link'] ?>" class="text-decoration-none"><?php endif; ?>
                        <div class="card-box <?= $stat['class'] ?> text-center h-100">
                            <i class="mdi <?= $stat['icon'] ?>" style="font-size: 28px; color: #8585a4;"></i>
                            <h2 class="m-0"><?= number_format((int) $stat['value']) ?></h2>
                            <p class="text-muted mb-0"><?= htmlspecialchars($stat['label']) ?></p>
                        </div>
                        <?php if ($stat['link']): ?></a><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <h4 class="mt-0 header-title mb-3">Quick Actions</h4>
                        <div class="d-flex flex-wrap" style="gap: 10px;">
                            <?php foreach ($quickActions as $action): ?>
                                <a href="<?= $action['link'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi <?= $action['icon'] ?> mr-1"></i><?= htmlspecialchars($action['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <h4 class="mt-0 header-title">Recent Posts</h4>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentPosts)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No posts yet.</td></tr>
                                    <?php else: foreach ($recentPosts as $post): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($post['PostTitle']) ?></td>
                                            <td><?= htmlspecialchars($post['RegionName'] ?? '—') ?></td>
                                            <td><?= date('M j, Y', strtotime($post['PostingDate'])) ?></td>
                                            <td><?= number_format((int) $post['viewCounter']) ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
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
