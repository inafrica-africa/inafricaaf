<?php
// Fire-and-forget endpoint the share buttons on document-details.php hit
// (via fetch) right as they open a share link, so "shares" reflects share
// intent — clicking Facebook/X/WhatsApp/LinkedIn or Copy Link — the same way
// most share-count widgets count a click rather than confirming the post
// actually went out (which the destination site never tells us anyway).
include('config.php');

header('Content-Type: application/json');

$type = $_POST['type'] ?? '';
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($type !== 'document' || $id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit();
}

$stmt = $con->prepare("UPDATE tbldocuments SET share_count = share_count + 1 WHERE id = ? AND Is_Active = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$updated = $stmt->affected_rows > 0;
$stmt->close();

echo json_encode(['ok' => $updated]);
