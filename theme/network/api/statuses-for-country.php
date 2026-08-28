<?php
// GET-only, no identity required (used during registration, before anyone
// has an identity) — just reflects tblnetworkstatuscountry for a country.
header('Content-Type: application/json');
include(__DIR__ . '/../../config.php');

$countryId = intval($_GET['country_id'] ?? 0);
if (!$countryId) {
    echo json_encode(['statuses' => []]);
    exit;
}

$stmt = $con->prepare("SELECT Status FROM tblnetworkstatuscountry WHERE CountryId = ? AND Is_Active = 1 ORDER BY Status ASC");
$stmt->bind_param("i", $countryId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['statuses' => array_column($rows, 'Status')]);
