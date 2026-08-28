<?php
// POST multipart/form-data: csrftoken, file. Returns {ok, mediaType,
// mediaPath, durationSeconds} or {ok:false, error}. A separate step from
// send-message.php so the UI can show upload progress before the message
// actually posts.
session_start();
header('Content-Type: application/json');
include(__DIR__ . '/../../config.php');
require_once __DIR__ . '/../includes/identity.php';

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

const MAX_IMAGE_BYTES = 10 * 1024 * 1024;
const MAX_VIDEO_BYTES = 50 * 1024 * 1024;
$ALLOWED_IMAGE_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$ALLOWED_VIDEO_MIME = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov'];
$MEDIA_DIR = __DIR__ . '/../media/';

$file = $_FILES['file'] ?? null;
if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
    respond(['ok' => false, 'error' => 'No file was uploaded.']);
}
if ($file['error'] !== UPLOAD_ERR_OK) {
    respond(['ok' => false, 'error' => 'Upload failed (error code ' . $file['error'] . ').']);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

if (isset($ALLOWED_IMAGE_MIME[$mime])) {
    if ($file['size'] > MAX_IMAGE_BYTES) {
        respond(['ok' => false, 'error' => 'Image is too large (max 10MB).']);
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $ALLOWED_IMAGE_MIME[$mime];
    if (!move_uploaded_file($file['tmp_name'], $MEDIA_DIR . $filename)) {
        respond(['ok' => false, 'error' => 'Could not save the uploaded image.'], 500);
    }
    respond(['ok' => true, 'mediaType' => 'image', 'mediaPath' => $filename, 'durationSeconds' => null]);
} elseif (isset($ALLOWED_VIDEO_MIME[$mime])) {
    if ($file['size'] > MAX_VIDEO_BYTES) {
        respond(['ok' => false, 'error' => 'Video is too large (max 50MB).']);
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $ALLOWED_VIDEO_MIME[$mime];
    if (!move_uploaded_file($file['tmp_name'], $MEDIA_DIR . $filename)) {
        respond(['ok' => false, 'error' => 'Could not save the uploaded video.'], 500);
    }

    $check = validateNetworkVideo($MEDIA_DIR . $filename);
    if (!$check['ok']) {
        @unlink($MEDIA_DIR . $filename);
        respond(['ok' => false, 'error' => $check['reason']]);
    }

    respond(['ok' => true, 'mediaType' => 'video', 'mediaPath' => $filename, 'durationSeconds' => $check['durationSeconds']]);
} else {
    respond(['ok' => false, 'error' => 'Only images (JPG/PNG/GIF/WEBP) and video (MP4/WEBM/MOV) are allowed — no audio/voice notes.']);
}
