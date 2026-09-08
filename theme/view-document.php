<?php
// Every "View" / "Open Document" link on the site routes through here instead
// of straight to the file, so a view is counted no matter which page the
// click came from (the documents listing, a shared document-details link,
// admin previews go straight to the file and are intentionally not counted
// here). After incrementing, it 302s on to the same URL the button used to
// link to directly.
include('config.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$doc = null;
if ($id > 0) {
    $stmt = $con->prepare("SELECT id, FilePath FROM tbldocuments WHERE id = ? AND Is_Active = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$doc) {
    http_response_code(404);
    exit('Document not found.');
}

$updateStmt = $con->prepare("UPDATE tbldocuments SET viewCounter = viewCounter + 1 WHERE id = ?");
$updateStmt->bind_param("i", $id);
$updateStmt->execute();
$updateStmt->close();

// Same open-vs-download logic used on the listing and details pages: PDFs
// open directly, .doc/.docx go through Google's viewer so they render
// instead of just downloading.
$fileUrl = 'admin/documents/' . rawurlencode($doc['FilePath']);
$ext = strtolower(pathinfo($doc['FilePath'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    $absoluteFileUrl = SITE_URL . '/' . $fileUrl;
    $target = 'https://docs.google.com/viewer?url=' . urlencode($absoluteFileUrl) . '&embedded=true';
} else {
    $target = SITE_URL . '/' . $fileUrl;
}

header('Location: ' . $target, true, 302);
exit();
