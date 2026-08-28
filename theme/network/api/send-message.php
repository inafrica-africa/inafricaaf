<?php
// POST: csrftoken, text (optional), media_type + media_path (+ duration_seconds,
// for video) from a prior upload-media.php call (optional), reply_to (optional
// message id). At least one of text/media is required.
session_start();
header('Content-Type: application/json');
include(__DIR__ . '/../../config.php');
require_once __DIR__ . '/../includes/identity.php';
require_once __DIR__ . '/../includes/messages.php';

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$me = networkResolveIdentity($con);
if (!$me) {
    respond(['ok' => false, 'error' => 'Not registered.'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['ok' => false, 'error' => 'Method not allowed.'], 405);
}
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrftoken'] ?? '')) {
    respond(['ok' => false, 'error' => 'Security check failed. Reload the page and try again.'], 403);
}

$text = trim($_POST['text'] ?? '');
$mediaType = $_POST['media_type'] ?? '';
$mediaPath = $_POST['media_path'] ?? '';
$duration = isset($_POST['duration_seconds']) && $_POST['duration_seconds'] !== '' ? (float) $_POST['duration_seconds'] : null;
$replyTo = intval($_POST['reply_to'] ?? 0) ?: null;

$hasMedia = in_array($mediaType, ['image', 'video'], true) && $mediaPath !== '';
if ($text === '' && !$hasMedia) {
    respond(['ok' => false, 'error' => 'Write something or attach an image/video.']);
}

// The uploaded file must actually exist and have come through
// upload-media.php (which only ever writes safely-named files into this
// folder) — reject any path with a directory separator etc. outright.
if ($hasMedia && (strpos($mediaPath, '/') !== false || !is_file(__DIR__ . '/../media/' . $mediaPath))) {
    respond(['ok' => false, 'error' => 'The attached media could not be found. Please re-upload.']);
}

if ($replyTo) {
    $check = $con->prepare("SELECT id FROM tblnetworkmessages WHERE id = ? AND Is_Active = 1");
    $check->bind_param("i", $replyTo);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        $replyTo = null;
    }
    $check->close();
}

$type = $hasMedia ? $mediaType : 'text';
$storedMediaPath = $hasMedia ? $mediaPath : null;
$storedDuration = ($hasMedia && $mediaType === 'video') ? $duration : null;
$storedText = $text !== '' ? $text : null;

$stmt = $con->prepare("
    INSERT INTO tblnetworkmessages (UserId, MessageType, MessageText, MediaPath, MediaDurationSeconds, ReplyToMessageId)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("isssdi", $me['id'], $type, $storedText, $storedMediaPath, $storedDuration, $replyTo);
$stmt->execute();
$newId = $stmt->insert_id;
$stmt->close();

respond(['ok' => true, 'message' => networkFetchOneMessage($con, $me['id'], $newId)]);
