<?php
// POST: csrftoken, message_id, mode=me|everyone.
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

$messageId = intval($_POST['message_id'] ?? 0);
$mode = $_POST['mode'] ?? '';
if (!$messageId || !in_array($mode, ['me', 'everyone'], true)) {
    respond(['ok' => false, 'error' => 'Invalid request.']);
}

if ($mode === 'me') {
    $stmt = $con->prepare("INSERT IGNORE INTO tblnetworkmessagehidden (MessageId, UserId) VALUES (?, ?)");
    $stmt->bind_param("ii", $messageId, $me['id']);
    $stmt->execute();
    $stmt->close();
    respond(['ok' => true]);
}

// mode === 'everyone': sender-only (admin moderation removal is a separate
// Is_Active flag, handled from admin/network-messages.php, not here).
$stmt = $con->prepare("UPDATE tblnetworkmessages SET IsDeletedForEveryone = 1 WHERE id = ? AND UserId = ?");
$stmt->bind_param("ii", $messageId, $me['id']);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    respond(['ok' => false, 'error' => 'You can only delete your own messages for everyone.'], 403);
}
respond(['ok' => true]);
