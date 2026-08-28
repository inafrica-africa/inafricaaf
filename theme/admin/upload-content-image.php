<?php
// AJAX target for Summernote's post-body image button/paste/drag-drop
// (js/editor-init.js). Saves the image to a folder and hands back its URL,
// so PostDetails ends up with an <img src="..."> reference instead of a
// multi-megabyte base64 data: URI — that base64-in-the-database pattern is
// exactly what was blowing past nginx's upload size limit (413) on posts
// with inline images, on top of bloating every row that used it.
require_once __DIR__ . '/includes/auth-check.php';

header('Content-Type: application/json');

function respondError($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondError('Method not allowed.', 405);
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    respondError('Security check failed. Please refresh the page and try again.', 403);
}

const MAX_CONTENT_IMG_BYTES = 5 * 1024 * 1024;
$ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$UPLOAD_DIR = __DIR__ . '/postimages/content/';

$file = $_FILES['file'] ?? null;
if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
    respondError('No file was uploaded.');
}
if ($file['error'] !== UPLOAD_ERR_OK) {
    respondError('Upload failed (error code ' . $file['error'] . ').');
}
if ($file['size'] > MAX_CONTENT_IMG_BYTES) {
    respondError('Image is too large (max 5MB).');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!isset($ALLOWED_MIME[$mime])) {
    respondError('Only JPG, PNG, GIF, or WEBP images are allowed.');
}

$filename = bin2hex(random_bytes(16)) . '.' . $ALLOWED_MIME[$mime];
if (!move_uploaded_file($file['tmp_name'], $UPLOAD_DIR . $filename)) {
    respondError('Could not save the uploaded image.', 500);
}

// Domain-root-absolute, not relative: this src gets embedded verbatim into
// PostDetails and rendered both from here (/admin/add-post) and later from
// the public /news-details page — a relative path would only resolve
// correctly from one of those two locations.
echo json_encode(['url' => '/admin/postimages/content/' . $filename]);
