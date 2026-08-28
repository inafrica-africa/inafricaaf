<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
include('config.php');

if (!isset($con)) {
    die("Database connection error: " . mysqli_connect_error());
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// CSRF Token Management
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Step 1: Get slug exactly as it is
$PostUrl = $_GET['PostUrl'] ?? '';

// Step 2: Stop if empty
if ($PostUrl === '') {
    echo "<div class='alert alert-danger'>Missing or invalid article URL.</div>";
    ob_end_flush();
    exit;
}

// Step 3: Try with and without trailing period
$potentialUrls = [$PostUrl, $PostUrl . '.', rtrim($PostUrl, '.')];
mysqli_set_charset($con, "utf8mb4");

// Try each potential URL format
$found = false;
$row = null;
$matchedUrl = ''; // Store which URL format matched

foreach ($potentialUrls as $urlToTry) {
    $stmt = $con->prepare("
        SELECT
            p.PostTitle AS posttitle,
            p.PostImage,
            p.PostDetails AS postdetails,
            p.PostingDate AS postingdate,
            p.PostUrl AS url,
            p.viewCounter,
            p.postedBy AS postedby,
            c.CategoryName AS category,
            c.id AS catid,
            s.Subcategory AS subcategory
        FROM tblposts p
        LEFT JOIN tblcategory c ON c.id = p.CategoryId
        LEFT JOIN tblsubcategory s ON s.SubCategoryId = p.SubCategoryId
        WHERE p.PostUrl = ? AND p.Is_Active = 1
    ");
    
    if (!$stmt) {
        continue; // Skip to next URL format if query fails
    }
    
    $stmt->bind_param("s", $urlToTry);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row) {
        $found = true;
        $matchedUrl = $urlToTry; // Save which URL variation worked
        break; // Exit the loop if we found the article
    }
}

// Step 4: If still not found, provide useful error
if (!$found) {
    // Let's check for any similar URLs for debugging
    $likeStmt = $con->prepare("SELECT PostUrl FROM tblposts WHERE PostUrl LIKE ? AND Is_Active = 1 LIMIT 5");
    $likeParam = '%' . substr($PostUrl, 0, 30) . '%'; // Use first 30 chars to find similar
    $likeStmt->bind_param("s", $likeParam);
    $likeStmt->execute();
    $likeResult = $likeStmt->get_result();
    
    if ($likeResult->num_rows > 0) {
        echo "<div class='alert alert-warning'>Article not found. Similar articles:<ul>";
        while ($likeRow = $likeResult->fetch_assoc()) {
            echo "<li>" . htmlentities($likeRow['PostUrl']) . "</li>";
        }
        echo "</ul></div>";
    } else {
        echo "<div class='alert alert-danger'>The requested article could not be found.</div>";
    }
    
    $likeStmt->close();
    ob_end_flush();
    exit;
}

// Make sure viewCounter exists in the table and is an integer
$checkStmt = $con->prepare("SHOW COLUMNS FROM tblposts LIKE 'viewCounter'");
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$checkStmt->close();

if ($checkResult->num_rows === 0) {
} else {
    // Try to update the counter without COLLATE as it might be causing issues
    $updateStmt = $con->prepare("UPDATE tblposts SET viewCounter = IFNULL(viewCounter, 0) + 1 WHERE PostUrl = ?");
    $updateStmt->bind_param("s", $matchedUrl);
    $success = $updateStmt->execute();
    $affected = $updateStmt->affected_rows;
    $updateStmt->close();
    
    // If that didn't work, try with ID if available
    if ($affected === 0 && isset($row['id'])) {
        $updateStmt = $con->prepare("UPDATE tblposts SET viewCounter = IFNULL(viewCounter, 0) + 1 WHERE id = ?");
        $updateStmt->bind_param("i", $row['id']);
        $success = $updateStmt->execute();
        $affected = $updateStmt->affected_rows;
        $updateStmt->close();
        
    }
    
    // Now fetch the updated value to ensure we display the correct count
    $refreshStmt = $con->prepare("SELECT viewCounter FROM tblposts WHERE PostUrl = ?");
    $refreshStmt->bind_param("s", $matchedUrl);
    $refreshStmt->execute();
    $refreshResult = $refreshStmt->get_result();
    $refreshRow = $refreshResult->fetch_assoc();
    $refreshStmt->close();
    
    if ($refreshRow) {
        $row['viewCounter'] = $refreshRow['viewCounter'];
        
    }
}

// Step 6: Generate full URL and get post title
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$fullPostUrl = $scheme . $_SERVER['HTTP_HOST'] . '/news-details?PostUrl=' . urlencode($row['url']);

// Deliberately NOT htmlentities()'d here: every place these are displayed
// already calls htmlspecialchars() itself (title tag, h1, img alt, share
// links). Pre-encoding here too meant "&" became "&amp;" a second time,
// e.g. a real "“" (curly quote) went in as htmlentities' own "&ldquo;" and
// came out the far end as literal, visible text "&ldquo;" instead of a
// quote mark.
$postTitle   = $row['posttitle'] ?? '';
$postImage   = $row['PostImage'] ?? '';
// Not htmlentities()'d: this is trusted admin-authored HTML (from the admin panel's
// rich-text editor) and needs to render as real markup, not escaped text.
$postDetails = $row['postdetails'] ?? '';
$postDate    = htmlentities($row['postingdate'] ?? '', ENT_QUOTES, 'UTF-8');
$category    = htmlentities($row['category']    ?? '', ENT_QUOTES, 'UTF-8');
$subCategory = htmlentities($row['subcategory'] ?? '', ENT_QUOTES, 'UTF-8');
$postedBy    = htmlentities($row['postedby']    ?? '', ENT_QUOTES, 'UTF-8');
$viewCounter = isset($row['viewCounter']) ? intval($row['viewCounter']) : 0;


// Step 8: Handle Comment or Reply Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrftoken'] ?? '')) {
        die("Security violation: Invalid CSRF token");
    }
    
    // === Handle Comment Submission ===
    if (isset($_POST['submit'])) {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $comment = trim($_POST['comment'] ?? '');
        
        // Validate inputs
        if (empty($name) || !$email || empty($comment)) {
            die("All fields are required and must contain valid values");
        }
        
        try {
            // Use the matched URL format that worked with the database
            $stmt = $con->prepare("INSERT INTO tblcomments (PostUrl, name, email, comment, status) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $matchedUrl, $name, $email, $comment);
            
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Redirect to avoid resubmission - use the original URL parameter for consistency
            header("Location: news-details?PostUrl=" . urlencode($PostUrl));
            exit();
        } catch (Exception $e) {
            error_log($e->getMessage());
           
        }
    }
    
    // === Handle Reply Submission ===
    if (isset($_POST['reply_submit'])) {
        $reply = trim($_POST['reply'] ?? '');
        $parent_id = intval($_POST['parent_id'] ?? 0);
        
        if (!empty($reply) && $parent_id > 0) {
            $name = 'Anonymous'; // Change if user is logged in
            
            // Use the matched URL format that worked with the database
            $stmt = $con->prepare("INSERT INTO tblcomments (Name, Comment, ParentId, PostUrl, Status) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssis", $name, $reply, $parent_id, $matchedUrl);
            
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: news-details?PostUrl=" . urlencode($PostUrl));
                exit();
            } else {
                $stmt->close();
                echo "Error posting reply.";
            }
        } else {
            echo "Invalid reply content or missing parent ID.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="INAfrica Youth Initiative: Connecting more than 1.54 Billion African Citizens.">
    <meta name="author" content="INAfrica">
  <title><?= htmlspecialchars($postTitle) ?> | INAfrica</title>

    <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">

<link rel="shortcut icon" href="images/logo.png" type="image/x-icon">

<?php
$ogImage = !empty($row['PostImage']) ? 'admin/postimages/' . $row['PostImage'] : 'images/logo.png';
renderMetaTags(
    $row['posttitle'],
    mb_substr(strip_tags($row['postdetails'] ?? ''), 0, 297),
    $ogImage,
    '/news-details?PostUrl=' . urlencode($row['url']),
    'article'
);
?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

</head>
<body>
    <?php include('header.php');?>

  <!-- Advert Container -->
  <?php $adverts = fetchAdverts($con); ?>
  <div class="advert-container below-fixed-header d-flex justify-content-between align-items-center pb-2">
    <div class="advert-container-left">
      <?php if (!empty($adverts['landscape'][0])): ?>
        <?php renderAdvertCard($adverts['landscape'][0], 'width:100%; max-width:350px; height:auto; max-height:100px;'); ?>
      <?php endif; ?>
    </div>

    <div class="advert-container-middle text-center">
      <!-- Display current date and time -->
      <div id="currentDate"></div>
      <div id="currentTime"></div>
    </div>

    <div class="advert-container-right">
      <?php if (!empty($adverts['landscape'][1])): ?>
        <?php renderAdvertCard($adverts['landscape'][1], 'width:100%; max-width:350px; height:auto; max-height:100px;'); ?>
      <?php endif; ?>
    </div>
  </div>
  <div id="google_translate_element" class="ms-4 mb-2"></div>

<section class="single-post">
    <div class="container-fluid">
        <div class="row">
            <!-- Left: narrow sidebar -->
            <div class="col-lg-3 col-md-12 order-2 order-lg-1">
                <div class="sidebar">
                    <?php if (!empty($adverts['portrait'][0])): ?>
                    <div class="widget mb-4" style="margin:0;">
                        <?php renderAdvertCard($adverts['portrait'][0], 'width:100%; max-width:220px; height:auto; border-radius:4px; display:block;', 'Sponsored', false); ?>
                    </div>
                    <?php endif; ?>
                    <div class="widget">
                        <h5 class="widget-title">Recent Posts</h5>
                        <ul class="list-unstyled">
                            <?php
                            $recent_query = $con->query("SELECT PostUrl, PostTitle, PostImage, PostingDate FROM tblposts WHERE Is_Active = 1 ORDER BY PostingDate DESC LIMIT 5");
                            while ($recent_post = $recent_query->fetch_assoc()):
                                $recentThumb = $recent_post['PostImage'] ? 'admin/postimages/' . htmlspecialchars($recent_post['PostImage']) : 'images/blog/post-1.jpg';
                            ?>
                                <li class="d-flex align-items-start mb-3">
                                    <a href="news-details?PostUrl=<?= urlencode($recent_post['PostUrl']) ?>" class="flex-shrink-0 mr-3">
                                        <img src="<?= $recentThumb ?>" alt="<?= htmlspecialchars($recent_post['PostTitle']) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                                    </a>
                                    <div>
                                        <a href="news-details?PostUrl=<?= urlencode($recent_post['PostUrl']) ?>" class="text-dark d-block"><?= htmlspecialchars($recent_post['PostTitle']) ?></a>
                                        <small class="text-muted"><?= date("F j, Y", strtotime($recent_post['PostingDate'])) ?></small>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <div class="widget mt-4">
                        <h5 class="widget-title">Follow Us</h5>
                        <div class="social-icons">
                            <a href="#" class="btn btn-outline-primary"><i class="ti-facebook"></i></a>
                            <a href="#" class="btn btn-outline-info"><i class="ti-twitter"></i></a>
                            <a href="#" class="btn btn-outline-danger"><i class="ti-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle: post details -->
            <div class="col-lg-6 col-md-12 order-1 order-lg-2">
                <div class="post-details">
                    <h1 class="post-title"><?= htmlspecialchars($postTitle) ?></h1>
                    
                    <ul class="post-meta list-inline">
                        <li class="list-inline-item">
                            <i class="ti-user mr-2"></i>Posted by <a href="#" class="text-primary"><?= htmlspecialchars($postedBy) ?></a>
                        </li>
                        <li class="list-inline-item">
                            <i class="ti-calendar mr-2"></i><?= date("F j, Y", strtotime($postDate)) ?>
                        </li>
                        <li class="list-inline-item">
                            <i class="ti-tag mr-2"></i><a href="#" class="text-primary"><?= htmlspecialchars($category) ?></a>
                        </li>
                        <li class="list-inline-item">
                            <i class="ti-eye mr-2"></i><?= number_format($viewCounter) ?> views
                        </li>
                        <li class="list-inline-item">
                            <a href="#" onclick="window.print()"><i class="ti-printer"></i> Print</a>
                        </li>
                    </ul>

                    <?php
                    // Same fallback used everywhere else a post's cover shows up
                    // (homepage Recent Updates/blog cards, this page's own Recent
                    // Posts and Related Articles below) so whatever image a reader
                    // already saw in a preview is the one they see here too.
                    $mainPostImageSrc = !empty($postImage) ? 'admin/postimages/' . htmlspecialchars($postImage) : 'images/blog/post-1.jpg';
                    ?>
                    <div class="post-image mb-4">
                        <img src="<?= $mainPostImageSrc ?>" alt="<?= htmlspecialchars($postTitle) ?>" class="img-fluid rounded">
                    </div>
                    
                    <div class="content">
                        <?= $postDetails ?>
                    </div>
                    
                    <div class="social-share-buttons text-center my-4">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($fullPostUrl) ?>" target="_blank" rel="noopener" class="btn btn-primary"><i class="ti-facebook"></i> Share</a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($fullPostUrl) ?>&text=<?= urlencode($postTitle) ?>" target="_blank" rel="noopener" class="btn btn-info"><i class="ti-twitter-alt"></i> Share on X</a>
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($postTitle . ' ' . $fullPostUrl) ?>" target="_blank" rel="noopener" class="btn btn-success"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:-2px"><path d="M17.5 14.4c-.3-.1-1.7-.9-2-1s-.5-.1-.7.1-.8 1-.9 1.2-.3.2-.6.1a7.9 7.9 0 0 1-2.3-1.4 8.6 8.6 0 0 1-1.6-2c-.2-.3 0-.5.1-.6l.4-.5.2-.3a.6.6 0 0 0 0-.5c-.1-.1-.7-1.6-.9-2.2s-.5-.5-.7-.5h-.6a1.1 1.1 0 0 0-.8.4 3.4 3.4 0 0 0-1 2.5 5.9 5.9 0 0 0 1.3 3.1c.1.2 2.2 3.4 5.4 4.7a18.2 18.2 0 0 0 1.8.7 4.3 4.3 0 0 0 2 .1 3.3 3.3 0 0 0 2.1-1.5 2.6 2.6 0 0 0 .2-1.5c-.1-.1-.3-.2-.6-.3zM12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.3A10 10 0 1 0 12 2z"/></svg> Share</a>
                    </div>
                </div>

                <div class="comments-section mt-5">
                    <h4 class="mb-4">Comments</h4>
                    
                    <div class="comment-form-container mb-5">
                        <h5 class="mb-3">Leave a comment</h5>
                        <form method="post" action="news-details?PostUrl=<?= urlencode($PostUrl) ?>">
                            <input type="hidden" name="csrftoken" value="<?= htmlspecialchars($csrfToken) ?>">
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" rows="5" placeholder="Your Comment" required></textarea>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Submit Comment</button>
                        </form>
                    </div>

                    <?php
                    // Fetch comments and replies
                    $comments_query = $con->prepare("SELECT id, name, comment, postingDate FROM tblcomments WHERE PostUrl = ? AND status = 1 ORDER BY postingDate DESC");
                    $comments_query->bind_param("s", $matchedUrl);
                    $comments_query->execute();
                    $comments_result = $comments_query->get_result();

                    if ($comments_result->num_rows > 0) {
                        while ($comment = $comments_result->fetch_assoc()):
                    ?>
                        <div class="comment-item mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <img src="images/comment-user.png" alt="User" class="rounded-circle me-3" style="width: 50px; height: 50px;">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($comment['name']) ?></h6>
                                    <small class="text-muted"><?= date("F j, Y, g:i a", strtotime($comment['postingDate'])) ?></small>
                                </div>
                            </div>
                            <p class="ms-5"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                            
                            <div class="reply-form-container ms-5 mt-2">
                                <form method="post" action="news-details?PostUrl=<?= urlencode($PostUrl) ?>">
                                    <input type="hidden" name="csrftoken" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="reply_submit" value="1">
                                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                                    <textarea name="reply" class="form-control mb-2" rows="2" placeholder="Write a reply..."></textarea>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Reply</button>
                                </form>
                            </div>
                        </div>
                    <?php
                        endwhile;
                    } else {
                        echo "<p>No comments yet. Be the first to comment!</p>";
                    }
                    ?>
                </div>

                <div class="related-posts mt-5">
                    <h4 class="mb-4">Related Articles</h4>
                    <div class="row">
                        <?php
                        $related_query = $con->prepare("SELECT PostUrl, PostTitle, PostImage, PostingDate FROM tblposts WHERE CategoryId = ? AND PostUrl != ? AND Is_Active = 1 ORDER BY RAND() LIMIT 3");
                        $related_query->bind_param("is", $row['catid'], $matchedUrl);
                        $related_query->execute();
                        $related_result = $related_query->get_result();

                        while ($related_post = $related_result->fetch_assoc()):
                            $relatedThumb = $related_post['PostImage'] ? 'admin/postimages/' . htmlspecialchars($related_post['PostImage']) : 'images/blog/post-1.jpg';
                        ?>
                            <div class="col-md-4 mb-4">
                                <a href="news-details?PostUrl=<?= urlencode($related_post['PostUrl']) ?>">
                                    <img src="<?= $relatedThumb ?>" alt="<?= htmlspecialchars($related_post['PostTitle']) ?>" class="img-fluid rounded mb-2">
                                    <h6 class="text-dark"><?= htmlspecialchars($related_post['PostTitle']) ?></h6>
                                </a>
                                <small class="text-muted"><?= date("F j, Y", strtotime($related_post['PostingDate'])) ?></small>
                            </div>
                        <?php
                        endwhile;
                        ?>
                    </div>
                </div>
            </div>

            <!-- Right: advert rail, up to 4 stacked -->
            <div class="col-lg-3 col-md-12 order-3">
                <div class="advert-rail">
                    <?php
                    $railAds = array_slice($adverts['portrait'], 0, 4);
                    foreach ($railAds as $railAd):
                    ?>
                        <div class="widget mb-4">
                            <?php renderAdvertCard($railAd, 'width:100%; max-width:220px; height:auto; border-radius:4px; display:block;', 'Sponsored', false); ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($railAds)): ?>
                        <div class="widget text-center text-muted small">Ad space available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

    <div class="back-to-top-container">
        <a href="#" class="back-to-top"><i class="fa fa-angle-up"></i></a>
    </div>
    <?php include('footer.php'); ?>
    <!-- jQuery -->
    <script src="plugins/jQuery/jquery.min.js"></script>
    <!-- Bootstrap core JavaScript -->
    <script src="plugins/bootstrap/bootstrap.min.js"></script>
    <!-- Custom Script -->
    <script src="js/script.js?v=<?= @filemtime(__DIR__ . '/js/script.js') ?>"></script>
    <!-- Google Translate -->
    <script type="text/javascript">
    function googleTranslateElementInit() {
      new google.translate.TranslateElement({ pageLanguage: 'en' }, 'google_translate_element');
    }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<script>
function toggleReplyBox(commentId) {
    const replyBox = document.getElementById('reply-box-' + commentId);
    replyBox.style.display = (replyBox.style.display === 'none' || replyBox.style.display === '') ? 'block' : 'none';
}
</script>
</body>
</html>
<?php
// End output buffering
ob_end_flush();
?>

