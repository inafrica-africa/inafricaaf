<?php
define('DB_SERVER', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_USER', 'inafrica_app');
define('DB_PASS', 'change_me');
define('DB_NAME', 'inafrica');

$con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

if (!defined('SITE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    define('SITE_URL', $scheme . ($_SERVER['HTTP_HOST'] ?? 'inafricaac.org'));
}

if (!function_exists('renderMetaTags')) {
    // Open Graph / Twitter Card tags, shared by every page, so that pasting any
    // page's link into Facebook, WhatsApp, X, Slack, etc. shows the right title,
    // description and image. $url and $image must be absolute — link-preview
    // scrapers ignore relative URLs — so both are resolved against SITE_URL here
    // rather than left to each page to get right (or wrong) on its own.
    function renderMetaTags($title, $description, $image, $path, $type = 'website') {
        $url = SITE_URL . $path;
        $absImage = preg_match('#^https?://#i', $image) ? $image : SITE_URL . '/' . ltrim($image, '/');

        $title = htmlspecialchars($title);
        $description = htmlspecialchars($description);
        $url = htmlspecialchars($url);
        $absImage = htmlspecialchars($absImage);
        $type = htmlspecialchars($type);
        ?>
<link rel="canonical" href="<?= $url ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="<?= $type ?>">
<meta property="og:url" content="<?= $url ?>">
<meta property="og:title" content="<?= $title ?>">
<meta property="og:description" content="<?= $description ?>">
<meta property="og:image" content="<?= $absImage ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="<?= $url ?>">
<meta name="twitter:title" content="<?= $title ?>">
<meta name="twitter:description" content="<?= $description ?>">
<meta name="twitter:image" content="<?= $absImage ?>">
        <?php
    }
}

if (!function_exists('getMimeType')) {
    function getMimeType($base64Data) {
        $binary = base64_decode($base64Data, true);
        if ($binary === false) {
            return '';
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($binary);
    }
}

if (!function_exists('fetchAdverts')) {
    // Loads active adverts from gifadvert and splits them into landscape vs
    // portrait buckets based on their real image dimensions (not a manually
    // set field), so any image format (GIF, JPG, PNG, WEBP) works as a "poster".
    function fetchAdverts($con) {
        $buckets = ['landscape' => [], 'portrait' => []];
        $result = mysqli_query($con, "SELECT id, title, file, url, description FROM gifadvert WHERE Is_Active = 1 ORDER BY id DESC");
        if (!$result) {
            return $buckets;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $binary = $row['file'];
            if (empty($binary)) {
                continue;
            }
            $info = @getimagesizefromstring($binary);
            if (!$info) {
                continue;
            }
            $width = $info[0];
            $height = $info[1];
            $mime = $info['mime'] ?? 'image/png';
            $ad = [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'url' => $row['url'],
                'description' => $row['description'],
                'mime' => $mime,
                'src' => 'data:' . $mime . ';base64,' . base64_encode($binary),
            ];
            $buckets[$width >= $height ? 'landscape' : 'portrait'][] = $ad;
        }
        return $buckets;
    }
}

if (!function_exists('renderAdvertCard')) {
    // $label/$showTitle let bare banner strips (top of news-details.php) opt out
    // of the bordered card chrome, while sidebar/homepage placements get the
    // full "Sponsored" card with a title caption.
    function renderAdvertCard($ad, $imgStyle, $label = null, $showTitle = false) {
        $link = !empty($ad['url']) ? $ad['url'] : '#';
        $title = htmlspecialchars($ad['title'] ?? 'Advert');

        if ($label === null && !$showTitle) {
            echo '<a href="' . htmlspecialchars($link) . '" target="_blank" rel="noopener sponsored" class="advert-card d-block">';
            echo '<img src="' . $ad['src'] . '" alt="' . $title . '" style="' . $imgStyle . '">';
            echo '</a>';
            return;
        }

        echo '<div class="advert-card-wrap">';
        if ($label) {
            echo '<span class="advert-label">' . htmlspecialchars($label) . '</span>';
        }
        echo '<a href="' . htmlspecialchars($link) . '" target="_blank" rel="noopener sponsored" class="advert-card d-block">';
        echo '<img src="' . $ad['src'] . '" alt="' . $title . '" style="' . $imgStyle . '">';
        echo '</a>';
        if ($showTitle) {
            echo '<p class="advert-title mb-0">' . $title . '</p>';
        }
        echo '</div>';
    }
}

if (!function_exists('countRows')) {
    // Defensive COUNT(*) helper: returns 0 instead of throwing if the query/table
    // isn't available (e.g. a table hasn't been created yet).
    function countRows($con, $sql) {
        try {
            $result = mysqli_query($con, $sql);
            if (!$result) {
                return 0;
            }
            $row = mysqli_fetch_row($result);
            return (int) ($row[0] ?? 0);
        } catch (mysqli_sql_exception $e) {
            return 0;
        }
    }
}
?>