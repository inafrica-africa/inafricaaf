<?php
include('config.php');

$validTypes = ['Event', 'Summit'];
$type = $_GET['type'] ?? 'Event';
if (!in_array($type, $validTypes, true)) {
    $type = 'Event';
}
$typeLabels = ['Event' => 'Events', 'Summit' => 'Summits'];
$pageTitle = $typeLabels[$type];

$stmt = $con->prepare("
    SELECT Title, Description, EventDate, Location, EventImage, IsInAfricaEvent, InAfricaAttending
    FROM tblevents
    WHERE EventType = ? AND Is_Active = 1
    ORDER BY EventDate ASC
");
$stmt->bind_param("s", $type);
$stmt->execute();
$allEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$today = strtotime('today');
$upcoming = [];
$finished = [];
foreach ($allEvents as $event) {
    if (strtotime($event['EventDate']) >= $today) {
        $upcoming[] = $event;
    } else {
        $finished[] = $event;
    }
}
// Finished events look best most-recent-first.
usort($finished, function ($a, $b) {
    return strtotime($b['EventDate']) <=> strtotime($a['EventDate']);
});

function renderEventCard($event) {
    $img = $event['EventImage'] ? 'admin/events/' . htmlspecialchars($event['EventImage']) : 'images/blog/post-1.jpg';
    echo '<div class="mb-4">';
    echo '<div class="card h-100">';
    echo '<img class="card-img-top" src="' . $img . '" alt="' . htmlspecialchars($event['Title']) . '">';
    echo '<div class="card-body">';
    if ($event['IsInAfricaEvent']) {
        echo '<span class="badge badge-success mr-1">INAfrica Event</span>';
    }
    if ($event['InAfricaAttending']) {
        echo '<span class="badge badge-warning">INAfrica Attending</span>';
    }
    echo '<h5 class="card-title mt-2">' . htmlspecialchars($event['Title']) . '</h5>';
    echo '<p class="card-text small text-muted">' . date('F j, Y', strtotime($event['EventDate']));
    if (!empty($event['Location'])) {
        echo ' &middot; ' . htmlspecialchars($event['Location']);
    }
    echo '</p>';
    if (!empty($event['Description'])) {
        echo '<p class="card-text">' . htmlspecialchars($event['Description']) . '</p>';
    }
    echo '</div></div></div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle) ?> | INAfrica</title>
  <meta name="description" content="INAfrica Youth Initiative: Connecting more than 1.54 Billion African Citizens.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
</head>
<body>
  <?php include('header.php'); ?>

  <section class="page-title-section bg-cover overlay" style="background-image: url('images/banner/banner-1.jpg');">
    <div class="container">
      <h1 class="text-white"><?= htmlspecialchars($pageTitle) ?></h1>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="row">
        <!-- Left: Finished -->
        <div class="col-md-6 border-right">
          <h2 class="section-title">Finished <?= htmlspecialchars($pageTitle) ?></h2>
          <?php if (empty($finished)): ?>
            <div class="text-center text-muted mb-4">No finished <?= htmlspecialchars(strtolower($pageTitle)) ?> yet.</div>
          <?php else: foreach ($finished as $event) { renderEventCard($event); } endif; ?>
        </div>

        <!-- Right: Upcoming -->
        <div class="col-md-6">
          <h2 class="section-title">Upcoming <?= htmlspecialchars($pageTitle) ?></h2>
          <?php if (empty($upcoming)): ?>
            <div class="text-center text-muted mb-4">No upcoming <?= htmlspecialchars(strtolower($pageTitle)) ?>.</div>
          <?php else: foreach ($upcoming as $event) { renderEventCard($event); } endif; ?>
        </div>
      </div>
    </div>
  </section>

  <?php include('footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
</body>
</html>
