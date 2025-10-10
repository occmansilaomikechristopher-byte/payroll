<?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>

<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index.php" class="logo logo-dark">
            <span class="logo-sm">
                JP
            </span>
            <span class="logo-lg">
                <img src="assets/images/logo-dark.png" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index.php" class="logo logo-light">
            <span class="logo-sm">
                JP
            </span>
            <span class="logo-lg">
                <div class="logo">JEJORS Payroll</div>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>


    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <?php if ($login_role  !== 6) { ?>
                <ul class="navbar-nav" id="navbar-nav">
                    <li class="menu-title"><span data-key="t-menu">Links</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php if ($page == 'home') {
                                                            echo 'active';
                                                        } ?>" href="index.php">
                            <i class=" ri-dashboard-fill"></i> <span data-key="t-widgets">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarLanding" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                            <i class="ri-shield-user-line"></i> <span data-key="t-landing">Workforce Information</span>
                        </a>
                        <div class="menu-dropdown collapse <?php if ($page == 'employee' || $page == 'employee-details' || $page == 'position') {
                                                                echo 'show';
                                                            } ?>" id="sidebarLanding" style="">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="index.php?page=employee" class="nav-link <?php if ($page == 'employee' || $page == 'employee-details') {
                                                                                            echo 'active';
                                                                                        } ?>" data-key="t-nft-landing">Employees</a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?page=position" class="nav-link <?php if ($page == 'position') {
                                                                                            echo 'active';
                                                                                        } ?>" data-key=" t-job">Position</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php
                                                        if (($page == 'payroll' || $page == 'payroll_items' || $page == 'payroll_calculations') && (!isset($_GET['p2']) || $_GET['p2'] == 'false')) {
                                                            echo 'active';
                                                        }
                                                        ?>" href="index.php?page=payroll&p2=false">
                            <i class="ri-calculator-line"></i><span>Payroll</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link <?php
                                                        if (($page == 'payroll' || $page == 'payroll_items' || $page == 'payroll_calculations') && isset($_GET['p2']) && $_GET['p2'] == 'true') {
                                                            echo 'active';
                                                        }
                                                        ?>" href="index.php?page=payroll&p2=true">
                            <i class="ri-calculator-line"></i><span>P2</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarLanding2" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                            <i class="ri-gift-line"></i> <span data-key="t-landing">Benefits & Compensation</span>
                        </a>
                        <div class="menu-dropdown collapse <?php if ($page == 'deductions' || $page == 'contributions' || $page == 'refunds') {
                                                                echo 'show';
                                                            } ?>" id="sidebarLanding2" style="">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="index.php?page=contributions" class="nav-link <?php if ($page == 'contributions') {
                                                                                                echo 'active';
                                                                                            } ?>" data-key=" t-job">Contributions</a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?page=deductions" class="nav-link <?php if ($page == 'deductions') {
                                                                                            echo 'active';
                                                                                        } ?>" data-key="t-nft-landing">Deductions</a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?page=refunds" class="nav-link <?php if ($page == 'refunds') {
                                                                                            echo 'active';
                                                                                        } ?>" data-key="t-nft-landing">Refunds</a>
                                </li>

                                <!-- <li class="nav-item"> <a href="#" class="nav-link">Holidays</a></li>
                            <li class="nav-item"> <a href="#" class="nav-link">Leave</a></li>
                            <li class="nav-item"> <a href="#" class="nav-link">Bunos & Allowances</a></li> -->
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarLanding3" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                            <i class="ri-calendar-line"></i> <span data-key="t-landing">Time & Attendance</span>
                        </a>
                        <div class="menu-dropdown collapse <?php if ($page == 'attendance' || $page == 'dtr'  ||  $page == 'dtr-details') {
                                                                echo 'show';
                                                            } ?>" id="sidebarLanding3" style="">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="index.php?page=dtr" class="nav-link <?php if ($page == 'dtr'  ||  $page == 'dtr-details') {
                                                                                        echo 'active';
                                                                                    } ?>" data-key=" t-job">Daily Time Record</a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?page=attendance" class="nav-link <?php if ($page == 'attendance') {
                                                                                            echo 'active';
                                                                                        } ?>" data-key="t-nft-landing">Attendance Record</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarLanding4" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                            <i class=" ri-map-pin-2-line"></i> <span data-key="t-landing">Projects</span>
                        </a>
                        <div class="menu-dropdown collapse <?php if ($page == 'clusters' || $page == 'sites') {
                                                                echo 'show';
                                                            } ?>" id="sidebarLanding4" style="">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="index.php?page=clusters" class="nav-link <?php if ($page == 'clusters') {
                                                                                            echo 'active';
                                                                                        } ?>" data-key=" t-job">Clusters</a>
                                </li>
                                <li class="nav-item">
                                    <a href="index.php?page=sites" class="nav-link <?php if ($page == 'sites') {
                                                                                        echo 'active';
                                                                                    } ?>" data-key="t-nft-landing">Sites</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    <!-- <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarLanding5" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                        <i class=" ri-bar-chart-line"></i> <span data-key="t-landing">Performance Mgmt</span>
                    </a>
                    <div class="menu-dropdown collapse" id="sidebarLanding5" style="">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"> <a href="#" class="nav-link">Employee Evaluation</a></li>
                            <li class="nav-item"> <a href="#" class="nav-link">Performance</a></li>
                            <li class="nav-item"> <a href="#" class="nav-link">Attendance Analytics</a></li>
                        </ul>
                    </div>
                </li> -->
                    <!-- <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarLanding6" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                        <i class="ri-slideshow-3-line"></i> <span data-key="t-landing">Training & Development</span>
                    </a>
                    <div class="menu-dropdown collapse " id="sidebarLanding6">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"> <a href="#" class="nav-link">Traing</a></li>
                            <li class="nav-item"> <a href="#" class="nav-link">Seminar</a></li>
                        </ul>
                    </div>
                </li> -->
                    <!-- <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarLanding7" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                        <i class=" ri-file-edit-line"></i> <span data-key="t-landing">Compliance & Legal</span>
                    </a>
                    <div class="menu-dropdown collapse " id="sidebarLanding7">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"> <a href="#" class="nav-link">Company Policy</a></li>
                            <li class="nav-item"> <a href="#" class="nav-link">Memorandum & Announcement</a></li>
                        </ul>
                    </div>
                </li> -->
                    <!-- <li class="nav-item ">
                    <a class="nav-link menu-link" href="#sidebarLanding8" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                        <i class=" ri-bar-chart-box-line"></i> <span data-key="t-landing">Reports</span>
                    </a>
                    <div class="menu-dropdown collapse <?php if ($page == 'payroll-report') {
                                                            echo 'show';
                                                        } ?>" id="sidebarLanding8">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"> <a href="#" class="nav-link">Employee Masterlist</a></li>
                            <li class="nav-item"> <a href="index.php?page=payroll-report" class="nav-link <?php if ($page == 'payroll-report') {
                                                                                                                echo 'active';
                                                                                                            } ?>">Payroll</a></li>
                        </ul>
                    </div>
                </li> -->
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php if ($page == 'visitors-logs') {
                                                            echo 'active';
                                                        } ?>" href="index.php?page=visitors-logs">
                            <i class="ri-timer-line"></i> <span data-key="t-widgets">Visitors Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php if ($page == 'users') {
                                                            echo 'active';
                                                        } ?>" href="index.php?page=users">
                            <i class=" ri-team-line"></i> <span data-key="t-widgets">User Profile</span>
                        </a>
                    </li>
                </ul>
            <?php } ?>
            <?php if ($login_role  === 6) { ?>
                <ul class="navbar-nav" id="navbar-nav">
                    <li class="menu-title"><span data-key="t-menu">Links</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarLanding3" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarLanding">
                            <i class="ri-calendar-line"></i> <span data-key="t-landing">Time & Attendance</span>
                        </a>
                        <div class="menu-dropdown collapse <?php if ($page == 'attendance' || $page == 'dtr'  ||  $page == 'dtr-details') {
                                                                echo 'show';
                                                            } ?>" id="sidebarLanding3" style="">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="index.php?page=dtr" class="nav-link <?php if ($page == 'dtr'  ||  $page == 'dtr-details') {
                                                                                        echo 'active';
                                                                                    } ?>" data-key=" t-job">Daily Time Record</a>
                                </li>
                                <!-- <li class="nav-item">
                                <a href="index.php?page=attendance" class="nav-link <?php if ($page == 'attendance') {
                                                                                        echo 'active';
                                                                                    } ?>" data-key="t-nft-landing">Attendance Record</a>
                            </li> -->

                            </ul>
                        </div>
                    </li>

                </ul>
            <?php } ?>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>