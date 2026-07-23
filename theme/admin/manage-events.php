<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const MAX_EVENT_IMG_BYTES_MP = 5 * 1024 * 1024;
$ALLOWED_MIME_MP = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$EVENT_TYPES_MP = ['Event', 'Summit'];
$IMG_DIR_MP = __DIR__ . '/events/';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle' && $id > 0) {
            $stmt = $con->prepare("UPDATE tblevents SET Is_Active = 1 - Is_Active WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Event status updated.';
        } elseif ($action === 'delete' && $id > 0) {
            $stmt = $con->prepare("SELECT EventImage FROM tblevents WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($event && $event['EventImage']) {
                @unlink(__DIR__ . '/events/' . basename($event['EventImage']));
            }

            $delStmt = $con->prepare("DELETE FROM tblevents WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
            $delStmt->close();
            $success = 'Event deleted.';
        } elseif ($action === 'edit' && $id > 0) {
            $title = trim($_POST['title'] ?? '');
            $eventType = $_POST['event_type'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $eventDate = $_POST['event_date'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $isInAfricaEvent = isset($_POST['is_inafrica_event']) ? 1 : 0;
            $inAfricaAttending = isset($_POST['inafrica_attending']) ? 1 : 0;
            $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);

            if ($title === '' || !in_array($eventType, $EVENT_TYPES_MP, true) || !$dateObj) {
                $error = 'Title, type, and a valid date are required.';
            } else {
                $newImage = null;
                $file = $_FILES['event_image'] ?? null;
                if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Upload failed (error code ' . $file['error'] . ').';
                    } elseif ($file['size'] > MAX_EVENT_IMG_BYTES_MP) {
                        $error = 'Image is too large (max 5MB).';
                    } else {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($file['tmp_name']);
                        if (!isset($ALLOWED_MIME_MP[$mime])) {
                            $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                        } else {
                            $newImage = bin2hex(random_bytes(16)) . '.' . $ALLOWED_MIME_MP[$mime];
                            if (!move_uploaded_file($file['tmp_name'], $IMG_DIR_MP . $newImage)) {
                                $error = 'Could not save the uploaded image.';
                                $newImage = null;
                            }
                        }
                    }
                }

                if (!$error) {
                    if ($newImage) {
                        $stmt = $con->prepare("SELECT EventImage FROM tblevents WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $old = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        if ($old && $old['EventImage']) {
                            @unlink($IMG_DIR_MP . basename($old['EventImage']));
                        }

                        $stmt = $con->prepare("UPDATE tblevents SET Title = ?, EventType = ?, Description = ?, EventDate = ?, Location = ?, EventImage = ?, IsInAfricaEvent = ?, InAfricaAttending = ? WHERE id = ?");
                        $stmt->bind_param("ssssssiii", $title, $eventType, $description, $eventDate, $location, $newImage, $isInAfricaEvent, $inAfricaAttending, $id);
                    } else {
                        $stmt = $con->prepare("UPDATE tblevents SET Title = ?, EventType = ?, Description = ?, EventDate = ?, Location = ?, IsInAfricaEvent = ?, InAfricaAttending = ? WHERE id = ?");
                        $stmt->bind_param("sssssiii", $title, $eventType, $description, $eventDate, $location, $isInAfricaEvent, $inAfricaAttending, $id);
                    }
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Event updated.';
                }
            }
        }
    }
}

$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $con->prepare("SELECT id, Title, EventType, Description, EventDate, Location, EventImage, IsInAfricaEvent, InAfricaAttending FROM tblevents WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$events = [];
$result = mysqli_query($con, "SELECT id, Title, EventType, EventDate, IsInAfricaEvent, InAfricaAttending, Is_Active FROM tblevents ORDER BY EventDate DESC");
if ($result) {
    $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/topheader.php';
require_once __DIR__ . '/../includes/leftsidebar.php';
?>
<div class="content-page">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4 class="page-title">Manage Events / Summits</h4>
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
                        <h5>Edit Event / Summit</h5>
                        <form method="post" action="manage-events.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editRow['Title']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="event_type" class="form-control" required>
                                    <?php foreach ($EVENT_TYPES_MP as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>" <?= $type === $editRow['EventType'] ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars(date('Y-m-d', strtotime($editRow['EventDate']))) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($editRow['Location'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($editRow['Description'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Replace Image (optional)</label>
                                <input type="file" name="event_image" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="is_inafrica_event" id="is_inafrica_event" class="form-check-input" value="1" <?= $editRow['IsInAfricaEvent'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_inafrica_event">This is an INAfrica event</label>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="inafrica_attending" id="inafrica_attending" class="form-check-input" value="1" <?= $editRow['InAfricaAttending'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="inafrica_attending">INAfrica will attend this event</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="manage-events.php" class="btn btn-light">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card-box">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Status Tags</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($events)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No events yet.</td></tr>
                                    <?php else: foreach ($events as $event):
                                        $isPast = strtotime($event['EventDate']) < strtotime('today');
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['Title']) ?></td>
                                            <td><?= htmlspecialchars($event['EventType']) ?></td>
                                            <td><?= date('M j, Y', strtotime($event['EventDate'])) ?></td>
                                            <td>
                                                <span class="badge <?= $isPast ? 'badge-secondary' : 'badge-info' ?>"><?= $isPast ? 'Finished' : 'Upcoming' ?></span>
                                                <?php if ($event['IsInAfricaEvent']): ?><span class="badge badge-success">INAfrica Event</span><?php endif; ?>
                                                <?php if ($event['InAfricaAttending']): ?><span class="badge badge-warning">INAfrica Attending</span><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($event['Is_Active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="manage-events.php?edit=<?= (int) $event['id'] ?>" class="btn btn-xs btn-info">Edit</a>
                                                <form method="post" action="manage-events.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-warning">
                                                        <?= $event['Is_Active'] ? 'Deactivate' : 'Activate' ?>
                                                    </button>
                                                </form>
                                                <form method="post" action="manage-events.php" style="display:inline;" onsubmit="return confirm('Permanently delete this event?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                                </form>
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
