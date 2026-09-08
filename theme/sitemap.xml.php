<?php
// Served at the clean URL /sitemap.xml by the same try_files fallback that
// turns /about into about.php (see /etc/nginx's @php_clean location) — no
// nginx change needed, this file just needs to exist.
//
// Lists the static pages plus every publicly-viewable document and news
// post, so Google (and friends) discover them without having to crawl the
// whole click-path. Regenerated on every request rather than cached to a
// static file, so newly published/unpublished content is reflected
// immediately with no separate "rebuild the sitemap" step to forget.
include(__DIR__ . '/config.php');

header('Content-Type: application/xml; charset=UTF-8');

/**
 * XML-escape a value for use as element text (not attribute) content.
 */
function sitemapEscape($value) {
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function sitemapUrl($loc, $lastmod = null, $changefreq = null, $priority = null) {
    $out = "  <url>\n    <loc>" . sitemapEscape($loc) . "</loc>\n";
    if ($lastmod) {
        $out .= "    <lastmod>" . sitemapEscape($lastmod) . "</lastmod>\n";
    }
    if ($changefreq) {
        $out .= "    <changefreq>" . sitemapEscape($changefreq) . "</changefreq>\n";
    }
    if ($priority !== null) {
        $out .= "    <priority>" . sitemapEscape($priority) . "</priority>\n";
    }
    $out .= "  </url>\n";
    return $out;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static, always-public pages.
$staticPages = [
    ['path' => '/index',                    'changefreq' => 'weekly',  'priority' => '1.0'],
    ['path' => '/about',                    'changefreq' => 'monthly', 'priority' => '0.8'],
    ['path' => '/contact',                  'changefreq' => 'monthly', 'priority' => '0.5'],
    ['path' => '/donate',                   'changefreq' => 'monthly', 'priority' => '0.6'],
    ['path' => '/gallery',                  'changefreq' => 'monthly', 'priority' => '0.5'],
    ['path' => '/documents',                'changefreq' => 'weekly',  'priority' => '0.7'],
    ['path' => '/documents?type=Statement', 'changefreq' => 'weekly',  'priority' => '0.6'],
    ['path' => '/documents?type=Letter',    'changefreq' => 'weekly',  'priority' => '0.6'],
    ['path' => '/documents?type=Report',    'changefreq' => 'weekly',  'priority' => '0.6'],
    ['path' => '/events?type=Event',        'changefreq' => 'weekly',  'priority' => '0.6'],
    ['path' => '/events?type=Summit',       'changefreq' => 'weekly',  'priority' => '0.6'],
];
foreach ($staticPages as $page) {
    echo sitemapUrl(SITE_URL . $page['path'], null, $page['changefreq'], $page['priority']);
}

// One page per active region — each lists that region's countries.
$regionsResult = mysqli_query($con, "SELECT id FROM tblregions WHERE Is_Active = 1 ORDER BY id ASC");
if ($regionsResult) {
    while ($region = mysqli_fetch_assoc($regionsResult)) {
        echo sitemapUrl(SITE_URL . '/region?region=' . (int) $region['id'], null, 'monthly', '0.5');
    }
}

// Published documents.
$docsResult = mysqli_query($con, "SELECT id, UploadDate FROM tbldocuments WHERE Is_Active = 1 ORDER BY id ASC");
if ($docsResult) {
    while ($doc = mysqli_fetch_assoc($docsResult)) {
        $lastmod = $doc['UploadDate'] ? date('Y-m-d', strtotime($doc['UploadDate'])) : null;
        echo sitemapUrl(SITE_URL . '/document-details?id=' . (int) $doc['id'], $lastmod, 'monthly', '0.6');
    }
}

// Approved, active news posts.
$postsResult = mysqli_query($con, "SELECT PostUrl, PostingDate, UpdationDate FROM tblposts WHERE Status = 'Approved' AND Is_Active = 1 ORDER BY id ASC");
if ($postsResult) {
    while ($post = mysqli_fetch_assoc($postsResult)) {
        if (!$post['PostUrl']) {
            continue;
        }
        $lastmodSource = $post['UpdationDate'] ?: $post['PostingDate'];
        $lastmod = $lastmodSource ? date('Y-m-d', strtotime($lastmodSource)) : null;
        echo sitemapUrl(SITE_URL . '/news-details?PostUrl=' . urlencode($post['PostUrl']), $lastmod, 'monthly', '0.6');
    }
}

echo '</urlset>' . "\n";
