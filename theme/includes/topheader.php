<!DOCTYPE html>
<html lang="en">
   <head>
      <title>INAfrica Admin</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
      <link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
      <link href="../assets/css/core.css" rel="stylesheet" type="text/css" />
      <link href="../assets/css/components.css" rel="stylesheet" type="text/css" />
      <link href="../assets/css/icons.css" rel="stylesheet" type="text/css" />
      <link href="../assets/css/pages.css" rel="stylesheet" type="text/css" />
      <link href="../assets/css/menu.css?v=<?= @filemtime(__DIR__ . '/../assets/css/menu.css') ?>" rel="stylesheet" type="text/css" />
      <link href="../assets/css/responsive.css?v=<?= @filemtime(__DIR__ . '/../assets/css/responsive.css') ?>" rel="stylesheet" type="text/css" />
      <link href="brand.css" rel="stylesheet" type="text/css" />
      <script src="../assets/js/modernizr.min.js"></script>
      <script src="../assets/js/jquery.min.js"></script>
   </head>

   <body class="fixed-left">
      <!-- Begin page -->
      <div id="wrapper">
        <div class="topbar">
                <!-- LOGO -->
                <div class="topbar-left">
                   <a href="dashboard.php" class="logo">
                       <span>
                           <img src="../images/logo.png" alt="INAfrica" height="40">
                       </span>
                   </a>
                </div>

                <!-- Button mobile view to collapse sidebar menu -->
                <div class="navbar navbar-default" role="navigation">
                    <div class="container">

                        <!-- Navbar-left -->
                        <ul class="nav navbar-nav navbar-left">
                            <li>
                                <button class="button-menu-mobile open-left waves-effect">
                                    <i class="mdi mdi-menu"></i>
                                </button>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav topbar-title-wrap" style="margin-top: 15px;">
                            <li><b class="topbar-title">INAfrica Youth Initiative &mdash; Admin Panel</b></li>
                        </ul>

                        <!-- Right(Notification) -->
                        <ul class="nav navbar-nav navbar-right">

                            <li class="dropdown user-box">
                                <a href="javascript:void(0);" class="dropdown-toggle waves-effect user-link" data-toggle="dropdown" aria-expanded="true">
                                    <i class="mdi mdi-account-circle m-r-5"></i>
                                    <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
                                </a>

                                <ul class="dropdown-menu dropdown-menu-right arrow-dropdown-menu arrow-menu-right user-list notify-list">
                                    <li><a href="change-password.php"><i class="ti-settings m-r-5"></i> Change Password</a></li>
                                    <li><a href="logout.php"><i class="ti-power-off m-r-5"></i> Logout</a></li>
                                </ul>
                            </li>

                        </ul> <!-- end navbar-right -->

                    </div><!-- end container -->
                </div><!-- end navbar -->
            </div>
