<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

$totalUsers = countRows($con, "SELECT COUNT(*) FROM tblnetworkusers WHERE Is_Active = 1");
$totalMessages = countRows($con, "SELECT COUNT(*) FROM tblnetworkmessages WHERE Is_Active = 1");
$messagesToday = countRows($con, "SELECT COUNT(*) FROM tblnetworkmessages WHERE Is_Active = 1 AND DATE(CreatedDate) = CURDATE()");
$activeLast7Days = countRows($con, "SELECT COUNT(*) FROM tblnetworkusers WHERE Is_Active = 1 AND LastSeenDate >= NOW() - INTERVAL 7 DAY");
$totalReads = countRows($con, "SELECT COUNT(*) FROM tblnetworkmessagereads");
// Read rate: of messages that had at least one other person around to read
// them (i.e. not the very last message with nobody after it yet), what
// fraction actually got read by someone. A rough engagement signal, not a
// per-message guarantee.
$messagesWithReads = countRows($con, "SELECT COUNT(DISTINCT MessageId) FROM tblnetworkmessagereads");

$byStatus = mysqli_fetch_all(mysqli_query($con, "
    SELECT Status, COUNT(*) AS Total FROM tblnetworkusers WHERE Is_Active = 1 GROUP BY Status ORDER BY Total DESC
"), MYSQLI_ASSOC);

$byCountry = mysqli_fetch_all(mysqli_query($con, "
    SELECT c.CountryName, COUNT(*) AS Total
    FROM tblnetworkusers u JOIN tblcountries c ON c.id = u.CountryId
    WHERE u.Is_Active = 1
    GROUP BY c.CountryName
    ORDER BY Total DESC
"), MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Networking — Statistics</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card-box text-center">
                        <h2 class="mb-0"><?= number_format($totalUsers) ?></h2>
                        <p class="text-muted mb-0">Registered Users</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-box text-center">
                        <h2 class="mb-0"><?= number_format($totalMessages) ?></h2>
                        <p class="text-muted mb-0">Total Messages</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-box text-center">
                        <h2 class="mb-0"><?= number_format($messagesToday) ?></h2>
                        <p class="text-muted mb-0">Messages Today</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-box text-center">
                        <h2 class="mb-0"><?= number_format($activeLast7Days) ?></h2>
                        <p class="text-muted mb-0">Active in Last 7 Days</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-box text-center">
                        <h2 class="mb-0"><?= number_format($totalReads) ?></h2>
                        <p class="text-muted mb-0">Total Read Receipts</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card-box text-center">
                        <h2 class="mb-0"><?= number_format($messagesWithReads) ?> / <?= number_format($totalMessages) ?></h2>
                        <p class="text-muted mb-0">Messages Read By Someone</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card-box">
                        <h5>Registrations by Status</h5>
                        <table class="table table-sm mb-0">
                            <?php if (empty($byStatus)): ?>
                                <tr><td class="text-muted">No data yet.</td></tr>
                            <?php else: foreach ($byStatus as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Status']) ?></td>
                                    <td class="text-right"><?= number_format($row['Total']) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-box">
                        <h5>Registrations by Country</h5>
                        <table class="table table-sm mb-0">
                            <?php if (empty($byCountry)): ?>
                                <tr><td class="text-muted">No data yet.</td></tr>
                            <?php else: foreach ($byCountry as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['CountryName']) ?></td>
                                    <td class="text-right"><?= number_format($row['Total']) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </table>
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
