            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">
                        <ul>
                            <li class="menu-title">Navigation</li>

                            <li class="has_sub">
                                <a href="dashboard.php" class="waves-effect"><i class="mdi mdi-view-dashboard"></i> <span> Dashboard </span> </a>
                            </li>

<?php if (($_SESSION['utype'] ?? null) == '1'): ?>
                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="ti ti-user"></i> <span> Sub-admins </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-subadmins.php">Add Sub-admin</a></li>
                                    <li><a href="manage-subadmins.php">Manage Sub-admin</a></li>
                                </ul>
                            </li>
<?php endif; ?>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-format-list-bulleted"></i> <span> Category </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-category.php">Add Category</a></li>
                                    <li><a href="manage-categories.php">Manage Category</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="manage-regions.php" class="waves-effect"><i class="mdi mdi-earth"></i> <span> Regional Bloc Logos </span></a>
                            </li>

                            <li class="has_sub">
                                <a href="manage-countries.php" class="waves-effect"><i class="mdi mdi-earth-box"></i> <span> Manage Countries </span></a>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="ti ti-layout-list-thumb"></i> <span>Sub Category </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-subcategory.php">Add Sub Category</a></li>
                                    <li><a href="manage-subcategories.php">Manage Sub Category</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-newspaper"></i> <span> Posts (News) </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-post.php">Add Post</a></li>
                                    <li><a href="manage-posts.php">Manage Posts</a></li>
                                    <li><a href="trash-posts.php">Trash Posts</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="ti ti-files"></i> <span> Documents </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-document.php">Add Document</a></li>
                                    <li><a href="manage-documents.php">Manage Documents</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="manage-donations.php" class="waves-effect"><i class="mdi mdi-hand-heart"></i> <span> Donations </span></a>
                            </li>

                            <li class="has_sub">
                                <a href="manage-contact-messages.php" class="waves-effect"><i class="mdi mdi-email-outline"></i> <span> Contact Messages </span></a>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-image-multiple"></i> <span> Gallery </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-gallery.php">Add to Gallery</a></li>
                                    <li><a href="manage-gallery.php">Manage Gallery</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-format-quote-close"></i> <span> Quotes </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-quote.php">Add Quote</a></li>
                                    <li><a href="manage-quotes.php">Manage Quotes</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-calendar"></i> <span> Events & Summits </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-event.php">Add Event / Summit</a></li>
                                    <li><a href="manage-events.php">Manage Events</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-comment-account-outline"></i> <span> Comments </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="unapprove-comment.php">Waiting for Approval</a></li>
                                    <li><a href="manage-comments.php">Approved Comments</a></li>
                                </ul>
                            </li>

                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect"><i class="mdi mdi-bullhorn-outline"></i> <span> Adverts </span> <span class="menu-arrow"></span></a>
                                <ul class="list-unstyled">
                                    <li><a href="add-advert.php">Add Advert</a></li>
                                    <li><a href="manage-adverts.php">Manage Adverts</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <!-- Sidebar -->
                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>
