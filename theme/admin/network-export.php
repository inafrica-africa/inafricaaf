<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config.php';

$result = mysqli_query($con, "
    SELECT u.Name, u.WhatsApp, u.Email, u.Status, c.CountryName, u.CreatedDate, u.Is_Active
    FROM tblnetworkusers u
    JOIN tblcountries c ON c.id = u.CountryId
    ORDER BY u.CreatedDate ASC
");

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="networking-users-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
// PHP 8.5 deprecates the implicit default for the 5th ($escape) parameter —
// pass the historical defaults (",", '"', "\\") explicitly, or the
// deprecation notice prints straight into the CSV body ahead of the headers.
fputcsv($out, ['Name', 'WhatsApp', 'Email', 'Status', 'Country', 'Registered', 'Active'], ',', '"', '\\');
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($out, [
        $row['Name'],
        $row['WhatsApp'],
        $row['Email'],
        $row['Status'],
        $row['CountryName'],
        $row['CreatedDate'],
        $row['Is_Active'] ? 'Yes' : 'No',
    ], ',', '"', '\\');
}
fclose($out);
