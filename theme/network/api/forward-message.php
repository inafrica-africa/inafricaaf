<?php
// POST: csrftoken, message_id -> inserts a new, independent message row
// copying the source's content, so it can itself be replied to/forwarded/
// deleted separately from the original.
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

$sourceId = intval($_POST['message_id'] ?? 0);
if (!$sourceId) {
    respond(['ok' => false, 'error' => 'Invalid request.']);
}

$stmt = $con->prepare("SELECT MessageType, MessageText, MediaPath, MediaDurationSeconds FROM tblnetworkmessages WHERE id = ? AND Is_Active = 1 AND IsDeletedForEveryone = 0");
$stmt->bind_param("i", $sourceId);
$stmt->execute();
$source = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$source) {
    respond(['ok' => false, 'error' => 'That message is no longer available to forward.']);
}

$insert = $con->prepare("
    INSERT INTO tblnetworkmessages (UserId, MessageType, MessageText, MediaPath, MediaDurationSeconds, ForwardedFromMessageId)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insert->bind_param(
    "isssdi",
    $me['id'],
    $source['MessageType'],
    $source['MessageText'],
    $source['MediaPath'],
    $source['MediaDurationSeconds'],
    $sourceId
);
$insert->execute();
$newId = $insert->insert_id;
$insert->close();

respond(['ok' => true, 'message' => networkFetchOneMessage($con, $me['id'], $newId)]);
