<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_EVENT_IMG_BYTES = 5 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$IMG_DIR = __DIR__ . '/events/';
$EVENT_TYPES = ['Event', 'Summit'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $eventType = $_POST['event_type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $isInAfricaEvent = isset($_POST['is_inafrica_event']) ? 1 : 0;
        $inAfricaAttending = isset($_POST['inafrica_attending']) ? 1 : 0;

        $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);

        if ($title === '' || !in_array($eventType, $EVENT_TYPES, true) || !$dateObj) {
            $error = 'Title, type, and a valid date are required.';
        } else {
            $imageName = null;
            $file = $_FILES['event_image'] ?? null;
            if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Upload failed (error code ' . $file['error'] . ').';
                } elseif ($file['size'] > MAX_EVENT_IMG_BYTES) {
                    $error = 'Image is too large (max 5MB).';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    if (!isset($ALLOWED_MIME[$mime])) {
                        $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                    } else {
                        $imageName = bin2hex(random_bytes(16)) . '.' . $ALLOWED_MIME[$mime];
                        if (!move_uploaded_file($file['tmp_name'], $IMG_DIR . $imageName)) {
                            $error = 'Could not save the uploaded image.';
                            $imageName = null;
                        }
                    }
                }
            }

            if (!$error) {
                $stmt = $con->prepare("
                    INSERT INTO tblevents (Title, EventType, Description, EventDate, Location, EventImage, IsInAfricaEvent, InAfricaAttending, Is_Active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->bind_param("ssssssii", $title, $eventType, $description, $eventDate, $location, $imageName, $isInAfricaEvent, $inAfricaAttending);
                $stmt->execute();
                $stmt->close();
                $success = 'Event saved successfully.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Add Event / Summit</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <div class="card-box">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <form method="post" action="add-event.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="event_type" class="form-control" required>
                                    <?php foreach ($EVENT_TYPES as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Image</label>
                                <input type="file" name="event_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="is_inafrica_event" id="is_inafrica_event" class="form-check-input" value="1">
                                <label class="form-check-label" for="is_inafrica_event">This is an INAfrica event</label>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="inafrica_attending" id="inafrica_attending" class="form-check-input" value="1">
                                <label class="form-check-label" for="inafrica_attending">INAfrica will attend this event</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Event</button>
                            <a href="manage-events.php" class="btn btn-light">View All Events</a>
                        </form>
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
