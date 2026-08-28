<?php
// GET ?since=<lastSeenMessageId> -> {ok, messages: [...]}. Returns only
// messages newer than `since`, not a full reload — id is monotonic and
// indexed, so this stays cheap regardless of how long the chat history gets.
header('Content-Type: application/json');
include(__DIR__ . '/../../config.php');
require_once __DIR__ . '/../includes/identity.php';
require_once __DIR__ . '/../includes/messages.php';

$me = networkResolveIdentity($con);
if (!$me) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Not registered.']);
    exit;
}

$since = intval($_GET['since'] ?? 0);
echo json_encode(['ok' => true, 'messages' => networkFetchMessages($con, $me['id'], $since)]);
