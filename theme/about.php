<?php
include('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>About Us | INAfrica</title>
  <meta name="description" content="INAfrica: A Pan-African, youth-led framework for sustainable development and continental transformation.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="plugins/themify-icons/themify-icons.css">
  <link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
  <?php renderMetaTags(
    'About Us | INAfrica',
    'INAfrica: A Pan-African, youth-led framework for sustainable development and continental transformation.',
    'images/logo.png',
    '/about'
  ); ?>
</head>
<body class="about-page">
  <?php include('header.php'); ?>

  <section class="page-title-section bg-cover overlay" style="background-image: url('images/banner/banner-1.jpg');">
    <div class="container">
      <h1 class="text-white">About INAfrica</h1>
      <p class="text-white mb-0">A Pan-African, Youth-Led Framework for Sustainable Development and Continental Transformation</p>
    </div>
  </section>

  <!-- Who We Are -->
  <section class="section">
    <div class="container">
      <div class="row">
        <div class="col-lg-9 mx-auto text-center mb-4">
          <h3 class="section-title">Who We Are</h3>
          <p>
            INAfrica is a Pan-African, youth-led catalyst for sustainable development, socio-economic
            autonomy, and the restoration of Africa's global standing. We empower young Africans to
            become active contributors to the continent's transformation through responsible
            leadership, strategic dialogue, innovation, research, and practical implementation &mdash;
            not as a conventional advocacy body, but as a catalytic platform that convenes ideas,
            connects generations of leadership, and translates strategic thinking into measurable
            continental impact.
          </p>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="card h-100 p-4 shadow-sm">
            <i class="ti-target" style="font-size: 34px; color: #2fb44b;"></i>
            <h4 class="mt-3">Our Vision</h4>
            <p class="mb-0">
              To empower the next generation of African leaders to design, champion, and implement
              indigenous solutions for Africa's shared future &mdash; because durable development
              outcomes are best achieved when conceived, owned, and driven by Africans themselves.
            </p>
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="card h-100 p-4 shadow-sm">
            <i class="ti-book" style="font-size: 34px; color: #2fb44b;"></i>
            <h4 class="mt-3">Our Mission</h4>
            <p class="mb-0">
              Educating Africans about Africa while strengthening leadership, collaboration, and
              sustainable socio-economic transformation. An informed citizenry is better equipped to
              hold institutions accountable, participate meaningfully in policy dialogue, and drive
              inclusive growth.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Strategic Priorities -->
  <section class="section bg-light">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center mb-5">
          <h3 class="section-title">Strategic Priorities</h3>
          <p>
            INAfrica organizes its work around four interdependent pillars &mdash; each one stage in
            a continuous, self-reinforcing development cycle.
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3 col-sm-6 mb-4 text-center">
          <i class="ti-book" style="font-size: 36px; color: #2fb44b;"></i>
          <h5 class="mt-3">Education</h5>
          <p class="small">
            Quality education, research, leadership development, innovation, and practical skills
            &mdash; the entry point of the development cycle.
          </p>
        </div>
        <div class="col-md-3 col-sm-6 mb-4 text-center">
          <i class="ti-heart" style="font-size: 36px; color: #2fb44b;"></i>
          <h5 class="mt-3">Health</h5>
          <p class="small">
            Accessible, modern, and sustainable healthcare systems &mdash; the foundation of a
            productive, resilient workforce.
          </p>
        </div>
        <div class="col-md-3 col-sm-6 mb-4 text-center">
          <i class="ti-bolt" style="font-size: 36px; color: #2fb44b;"></i>
          <h5 class="mt-3">Technology</h5>
          <p class="small">
            Digital transformation, innovation, and African technological capacity &mdash; the
            multiplier that accelerates education and health gains.
          </p>
        </div>
        <div class="col-md-3 col-sm-6 mb-4 text-center">
          <i class="ti-settings" style="font-size: 36px; color: #2fb44b;"></i>
          <h5 class="mt-3">Industry</h5>
          <p class="small">
            Value addition, manufacturing, industrialization, and economic self-reliance &mdash;
            where investment converts into jobs and output.
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-9 mx-auto text-center">
          <p class="mb-0">
            <strong>Education &rarr; Health &rarr; Technology &rarr; Industry</strong> forms a
            continuous, self-reinforcing cycle: industrial growth funds further investment in
            education and health, which produces the skilled talent that drives the next wave of
            technological and industrial advancement.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Operational Framework -->
  <section class="section">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center mb-5">
          <h3 class="section-title">How We Work</h3>
          <p>
            Three interconnected mechanisms move ideas from open conversation, through formal
            position-taking, to concrete, monitored implementation.
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 mb-4 text-center">
          <i class="ti-comments" style="font-size: 36px; color: #2fb44b;"></i>
          <h5 class="mt-3">Open Dialogue</h5>
          <p class="small">
            Structured, inclusive conversations bringing together youth, policymakers, civil
            society, academia, and the private sector to surface issues and build shared
            understanding before formal positions are taken.
          </p>
        </div>
        <div class="col-md-4 mb-4 text-center">
          <i class="ti-files" style="font-size: 36px; color: #2fb44b;"></i>
          <h5 class="mt-3">Policy Statements &amp; Position Papers</h5>
          <p class="small">
            Insights from Open Dialogue are consolidated into evidence-based positions across our
            four strategic pillars &mdash; a citable reference point for partners and governments.
          </p>
        </div>
        <div class="col-md-4 mb-4 text-center">
          <i class="ti-rocket" style="font-size: 36px; color: #2fb44b;"></i>
          <h5 class="mt-3">Strategic Initiatives &amp; Implementation</h5>
          <p class="small">
            Policy positions are carried forward into the operational programs, partnerships, and
            projects through which our priorities are put into practice on the ground.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Flagship Platforms -->
  <section class="section bg-light">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center mb-5">
          <h3 class="section-title">Continental Flagship Platforms</h3>
          <p>
            We deliver our Open Dialogue and Strategic Initiatives through flagship convening
            platforms, each targeting a distinct constituency across the Pan-African ecosystem.
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="card h-100 p-4 shadow-sm">
            <i class="ti-medall" style="font-size: 30px; color: #2fb44b;"></i>
            <h5 class="mt-3">Africa Young Leaders Summit</h5>
            <p class="small mb-0">
              A flagship convening for leadership development and continental collaboration,
              bringing together emerging African leaders to build networks, sharpen strategic
              thinking, and coordinate cross-border initiatives.
            </p>
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="card h-100 p-4 shadow-sm">
            <i class="ti-microphone" style="font-size: 30px; color: #2fb44b;"></i>
            <h5 class="mt-3">Africa Open Discussion Forum</h5>
            <p class="small mb-0">
              A platform for constructive dialogue among policymakers, civil society, academia, the
              private sector, and citizens &mdash; operationalizing Open Dialogue at scale to feed
              directly into our policy development process.
            </p>
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="card h-100 p-4 shadow-sm">
            <i class="ti-money" style="font-size: 30px; color: #2fb44b;"></i>
            <h5 class="mt-3">AfricaFreeK Summit</h5>
            <p class="small">
              Connecting governments, investors, entrepreneurs, and development partners within
              Africa to mobilize capital, partnerships, and technical expertise behind the
              continent's industrialization and self-reliance agenda.
            </p>
            <p class="small mb-0">
              The <strong>&lsquo;K&rsquo;</strong> draws on three concepts central to our
              philosophy of self-determination: <em>Karahiya</em> &mdash; independent thinking;
              <em>Kankira</em> &mdash; rejecting humiliation and dependency; and
              <em>Khanda</em> &mdash; resilience against structural and historical barriers.
            </p>
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="card h-100 p-4 shadow-sm">
            <i class="ti-share" style="font-size: 30px; color: #2fb44b;"></i>
            <h5 class="mt-3">Africa Youth Lead Dialogue Program</h5>
            <p class="small mb-0">
              Our primary vehicle for structured, ongoing youth engagement, built on six pillars:
              accountable and inclusive governance; African heritage and cultural capital;
              youth-led peacebuilding and regional security; digital transformation aligned with
              AU Agenda 2063; translating Pan-Africanism into practical solutions; and inclusive
              economic growth through intra-African trade.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Tagline -->
  <section class="section">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h3 class="section-title">9.3 cm to 40.5 cm</h3>
          <p>
            The symbolic distance from a person's heart to the reach of their own two hands &mdash;
            an individual's immediate sphere of influence. Lasting continental transformation begins
            with personal responsibility, ethical leadership, and meaningful action within one's own
            community, rather than waiting on distant institutions to act first.
          </p>
          <blockquote class="mt-4 mb-4" style="border-left: 3px solid #2fb44b; padding-left: 20px; font-style: italic;">
            &ldquo;If you seek to transform Africa, your responsibility begins within the space
            immediately around you.&rdquo;
          </blockquote>
          <p class="font-weight-bold">Change the space within your reach. Together, we transform Africa.</p>

          <div class="mt-4">
            <a href="contact" class="btn btn-primary mr-2">Contact Us</a>
            <a href="donate" class="btn btn-outline-primary">Support Our Work</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include('footer.php'); ?>
  <script src="plugins/jQuery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
  <script src="js/script.js?v=<?= @filemtime(__DIR__ . '/js/script.js') ?>"></script>
</body>
</html>
