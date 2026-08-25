<!-- header -->
<header class="fixed-top header">
  <!-- top header -->
 <div id="top-header" class="top-header py-2 bg-white">
  <div class="container-fluid">
    <div class="row align-items-center">
      
      <!-- Left: WhatsApp -->
      <div class="col-lg-4 text-center text-lg-left">
        <a class="text-color mr-3" href="https://whatsapp.com/dl/">
          <strong>WhatsApp:</strong> +250 793 903 919
        </a>
      </div>

      <!-- Center: Animated Text -->
      <div class="col-lg-4 text-center">
        <p id="animated-text" class="mb-0 font-weight-bold small" style="color: red;"></p>
      </div>

      <!-- Right: Social Icons -->
      <div class="col-lg-4 text-center text-lg-right">
        <ul class="list-inline mb-0 top-header-icons">
          <li class="list-inline-item">
            <a class="d-inline-block text-color" href="https://facebook.com/themefisher/">
              <i class="ti-facebook"></i>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="d-inline-block text-color" href="https://twitter.com/themefisher">
              <i class="ti-twitter-alt"></i>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="d-inline-block text-color" href="https://github.com/themefisher">
              <i class="ti-github"></i>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="d-inline-block text-color" href="https://instagram.com/themefisher/">
              <i class="ti-instagram"></i>
            </a>
          </li>
          <li class="list-inline-item">
        <a class="d-inline-block text-color" href="donate">Donate</a>
      </li>
          <li class="list-inline-item">
        <a class="d-inline-block text-color" href="admin/login.php">Staff</a>
      </li>
        </ul>
      </div>

    </div>
  </div>
</div>

<!-- Main Navigation -->
<div class="navigation w-100">
  <div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-dark p-0 d-flex align-items-center">
      <a class="navbar-brand" href="index">
  <img src="images/logo.png" alt="Logo" style="max-height: 90px; margin-right: 10px;">
  <span id="brandText"></span>
</a>

      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav">
          <li class="nav-item active">
            <a class="nav-link" href="index">Home</a>
          </li>
          <?php
          if (!function_exists('truncateText')) {
              function truncateText($text, $maxLength = 15) {
                  return (strlen($text) > $maxLength) ? substr($text, 0, $maxLength) . '…' : $text;
              }
          }

          // Wrapped in its own function scope so these loop variables (region,
          // regionId, country, countryId, ...) can never leak into / clobber
          // same-named variables in whatever page includes this header
          // (e.g. region.php's own $regionId / $countryId from the URL).
          (function ($con) {
              $regionQuery = mysqli_query($con, "SELECT id, RegionName FROM tblregions WHERE Is_Active = 1 ORDER BY RegionName ASC");
              while ($region = mysqli_fetch_assoc($regionQuery)) {
                  $regionId = $region['id'];
                  $regionName = $region['RegionName'];
                  $truncatedRegionName = truncateText($regionName);

                  // Fetch countries for this region
                  $countryQuery = mysqli_query($con, "SELECT id, CountryName FROM tblcountries WHERE RegionId = $regionId AND Is_Active = 1 ORDER BY CountryName ASC");

                  if (mysqli_num_rows($countryQuery) > 0) {
                      echo '<li class="nav-item dropdown">';
                      echo '<a class="nav-link dropdown-toggle" href="#" id="regionDropdown' . $regionId . '" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="' . htmlspecialchars($regionName) . '">';
                      echo htmlspecialchars($truncatedRegionName);
                      echo '</a>';

                      echo '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="regionDropdown' . $regionId . '">';
                      while ($country = mysqli_fetch_assoc($countryQuery)) {
                          $countryId = $country['id'];
                          $countryName = $country['CountryName'];
                          echo '<li><a class="dropdown-item" href="region?region=' . $regionId . '&country=' . $countryId . '">' . htmlspecialchars($countryName) . '</a></li>';
                      }
                      echo '</ul>';
                      echo '</li>';
                  } else {
                      echo '<li class="nav-item">';
                      echo '<a class="nav-link" href="region?region=' . $regionId . '" title="' . htmlspecialchars($regionName) . '">';
                      echo htmlspecialchars($truncatedRegionName);
                      echo '</a>';
                      echo '</li>';
                  }
              }
          })($con);
          ?>
          <li class="nav-item">
            <a class="nav-link" href="contact">Contact</a>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</div>

<!-- Sub Navigation (directly attached, right aligned) -->
<div id="subNav">
  <ul class="navbar-nav d-flex flex-row">
    <li class="nav-item"><a class="nav-link" href="events?type=Event">Events</a></li>
    <li class="nav-item"><a class="nav-link" href="events?type=Summit">Summits</a></li>
    <li class="nav-item"><a class="nav-link" href="gallery">Gallery</a></li>
    <li class="nav-item"><a class="nav-link" href="documents?type=Statement">Statements & Publications</a></li>
    <li class="nav-item"><a class="nav-link" href="documents?type=Letter">Letters</a></li>
    <li class="nav-item"><a class="nav-link" href="documents?type=Report">Reports</a></li>
    <li class="nav-item"><a class="nav-link" href="about">About INAfrica</a></li>
  </ul>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  // Top header animated text (keeps looping)
  const text = "Connecting more than 1.54 Billion African Citizens .......";
  let index = 0;
  const textSpeed = 100;
  const targetText = document.getElementById("animated-text");

  function typeText() {
    if (index < text.length) {
      targetText.textContent += text.charAt(index);
      index++;
      setTimeout(typeText, textSpeed);
    } else {
      setTimeout(() => {
        targetText.textContent = "";
        index = 0;
        typeText();
      }, 3000);
    }
  }
  typeText();

  // Brand text animation (one-time only)
  const brandText = "INAfrica-Iyi Ni Africa.";
  let brandIndex = 0;
  const brandSpeed = 100;
  const targetBrand = document.getElementById("brandText");

  function typeBrandText() {
    if (brandIndex < brandText.length) {
      targetBrand.textContent += brandText.charAt(brandIndex);
      brandIndex++;
      setTimeout(typeBrandText, brandSpeed);
    }
  }
  typeBrandText(); // runs once
});
</script>


</header>
<!-- /header -->