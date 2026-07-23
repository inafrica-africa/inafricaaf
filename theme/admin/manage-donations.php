<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $con->prepare("UPDATE tbldonations SET Status = 'Confirmed' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Pledge marked as confirmed.';
        }
    }
}

$donations = [];
$result = mysqli_query($con, "SELECT id, DonorName, DonorContact, Amount, PaymentMethod, Message, Status, CreatedDate FROM tbldonations ORDER BY CreatedDate DESC");
if ($result) {
    $donations = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Donation Pledges</h4>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Donor</th>
                                        <th>Contact</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($donations)): ?>
                                        <tr><td colspan="8" class="text-center text-muted">No pledges yet.</td></tr>
                                    <?php else: foreach ($donations as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($d['DonorName']) ?></td>
                                            <td><?= htmlspecialchars($d['DonorContact']) ?></td>
                                            <td><?= number_format((float) $d['Amount'], 2) ?> RWF</td>
                                            <td><?= htmlspecialchars($d['PaymentMethod']) ?></td>
                                            <td><?= htmlspecialchars(mb_strimwidth($d['Message'] ?? '', 0, 40, '...')) ?></td>
                                            <td>
                                                <?php if ($d['Status'] === 'Confirmed'): ?>
                                                    <span class="badge badge-success">Confirmed</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($d['CreatedDate'])) ?></td>
                                            <td>
                                                <?php if ($d['Status'] !== 'Confirmed'): ?>
                                                <form method="post" action="manage-donations.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-success">Mark Confirmed</button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
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
