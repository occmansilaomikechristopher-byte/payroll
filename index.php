<?php
// error_reporting(0);
$allowed_values = array(1, 2, 3, 4);
$allowed_values_2 = array(1, 2, 3);
?>
<?php
$clasification_array = ['bg-primary', 'bg-secondary', 'bg-warning', 'bg-danger', 'bg-dark', 'bg-info', 'bg-primary']
?>
<?php include 'db_connect.php'; ?>
<?php
session_start();
if (!isset($_SESSION['is_login']))
    header('location:login.php');

// Owner role (10) can now access the web app as well.

?>

<?php include 'includes/header.php' ?>

<?php
$login_role = $_SESSION['login_role'];

function getRole($login_role)
{
    switch ($login_role) {
        case 1:
            return "Administrator";
            break;
        case 2:
            return "Staff";
            break;
        case 3:
            return "Auditor";
            break;
        case 4:
            return "Payroll Clerk";
            break;
        case 5:
            return "Timekeeper";
            break;
        case 6:
            return "PIC";
            break;
        case 7:
            return "Auditor";
            break;
        case 8:
            return "Secretary";
            break;
        case 9:
            return "Cashier";
            break;
        case 10:
            return "Owner";
            break;
        default:
            return "Access denied. Unknown role.";
            break;
    }
}
?>


<body>

    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="index.php" class="logo logo-dark">
                                <span class="logo-sm">
                                    JP
                                </span>
                                <span class="logo-lg">
                                     Payroll
                                </span>
                            </a>

                            <a href="index.php" class="logo logo-light">
                                <span class="logo-sm">
                                    JP
                                </span>
                                <span class="logo-lg">
                                     Payroll
                                </span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>

                        <!-- App Search-->

                    </div>

                    <div class="d-flex align-items-center">





                        <!-- <div class="dropdown topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class='bx bx-category-alt fs-22'></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg p-0 dropdown-menu-end">
                                <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fw-semibold fs-15"> Web Apps </h6>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#!" class="btn btn-sm btn-soft-info"> View All Apps
                                                <i class="ri-arrow-right-s-line align-middle"></i></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-2">
                                    <div class="row g-0">
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="assets/images/brands/github.png" alt="Github">
                                                <span>GitHub</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="assets/images/brands/bitbucket.png" alt="bitbucket">
                                                <span>Bitbucket</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="assets/images/brands/dribbble.png" alt="dribbble">
                                                <span>Dribbble</span>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="row g-0">
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="assets/images/brands/dropbox.png" alt="dropbox">
                                                <span>Dropbox</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="assets/images/brands/mail_chimp.png" alt="mail_chimp">
                                                <span>Mail Chimp</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="assets/images/brands/slack.png" alt="slack">
                                                <span>Slack</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <!-- 
                        <div class="dropdown topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" id="page-header-cart-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                                <i class='bx bx-shopping-bag fs-22'></i>
                                <span class="position-absolute topbar-badge cartitem-badge fs-10 translate-middle badge rounded-pill bg-info">5</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end p-0 dropdown-menu-cart" aria-labelledby="page-header-cart-dropdown">
                                <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fs-16 fw-semibold"> My Cart</h6>
                                        </div>
                                        <div class="col-auto">
                                            <span class="badge bg-warning-subtle text-warning fs-13"><span class="cartitem-badge">7</span>
                                                items</span>
                                        </div>
                                    </div>
                                </div>
                                <div data-simplebar style="max-height: 300px;">
                                    <div class="p-2">
                                        <div class="text-center empty-cart" id="empty-cart">
                                            <div class="avatar-md mx-auto my-3">
                                                <div class="avatar-title bg-info-subtle text-info fs-36 rounded-circle">
                                                    <i class='bx bx-cart'></i>
                                                </div>
                                            </div>
                                            <h5 class="mb-3">Your Cart is Empty!</h5>
                                            <a href="apps-ecommerce-products.html" class="btn btn-success w-md mb-3">Shop Now</a>
                                        </div>
                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/products/img-1.png" class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html" class="text-reset">Branded
                                                            T-Shirts</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>10 x $32</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span class="cart-item-price">320</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/products/img-2.png" class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html" class="text-reset">Bentwood Chair</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>5 x $18</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span class="cart-item-price">89</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/products/img-3.png" class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html" class="text-reset">
                                                            Borosil Paper Cup</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>3 x $250</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span class="cart-item-price">750</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/products/img-6.png" class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html" class="text-reset">Gray
                                                            Styled T-Shirt</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>1 x $1250</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$ <span class="cart-item-price">1250</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/products/img-5.png" class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html" class="text-reset">Stillbird Helmet</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>2 x $495</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span class="cart-item-price">990</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button" class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-bottom-0 border-start-0 border-end-0 border-dashed border" id="checkout-elem">
                                    <div class="d-flex justify-content-between align-items-center pb-3">
                                        <h5 class="m-0 text-muted">Total:</h5>
                                        <div class="px-2">
                                            <h5 class="m-0" id="cart-item-total">$1258.58</h5>
                                        </div>
                                    </div>

                                    <a href="apps-ecommerce-checkout.html" class="btn btn-success text-center w-100">
                                        Checkout
                                    </a>
                                </div>
                            </div>
                        </div> -->

                        <div class="ms-1 header-item d-none d-sm-flex">
                            <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                                <i class='bx bx-fullscreen fs-22'></i>
                            </button>
                        </div>


                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <span class="avatar-xs">
                                        <span class="avatar-title bg-light text-secondary rounded-circle header-profile-user-icon">
                                            <i class="ri-user-line fs-16"></i>
                                        </span>
                                    </span>
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= $_SESSION['login_name'] ?></span>
                                        <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text"><?= getRole($_SESSION['login_role']) ?></span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <h6 class="dropdown-header">Welcome <?= $_SESSION['login_name'] ?>!</h6>
                                <a class="dropdown-item" href="index.php?page=profile"><i class="ri ri-user-line text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
                                <!-- <a class="dropdown-item" href="apps-chat.html"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>
                                <a class="dropdown-item" href="apps-tasks-kanban.html"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a>
                                <a class="dropdown-item" href="pages-faqs.html"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Help</span></a>
                                <div class="dropdown-divider"></div> -->
                                <!-- <a class="dropdown-item" href="pages-profile.html"><i class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance : <b>$5971.67</b></span></a>
                                <a class="dropdown-item" href="pages-profile-settings.html"><span class="badge bg-success-subtle text-success mt-1 float-end">New</span><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
                                <a class="dropdown-item" href="auth-lockscreen-basic.html"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a> -->
                                <a class="dropdown-item" href="ajax.php?action=logout"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- removeNotificationModal -->
        <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mt-2 text-center">
                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                            <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                <h4>Are you sure ?</h4>
                                <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                        </div>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <!-- ========== App Menu ========== -->
        <?php include 'includes/navbar.php' ?>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div id="main-content">
            <?php
            $routes = [
                'home'                 => 'home',
                'employee'             => 'employee',
                'employee-details'     => 'employee-details',
                'payroll'              => 'payroll',
                'payroll_calculations' => 'payroll_calculations',
                'dtr'                  => 'dtr',
                'dtr-details'          => 'dtr-details',
                'attendance'           => 'manage_attendance',
                'sites'                => 'sites',
                'position'             => 'position',
                'users'                => 'users',
                'visitors-logs'        => 'visitors-logs',
                'profile'              => 'profile',
                'allowances'           => 'allowances',
                'contributions'        => 'contributions',
                'deductions'           => 'deductions',
                'refunds'              => 'refunds',
                'payroll-report'       => 'payroll-report',
                'payroll_items'        => 'payroll_items',
                'time-logs'            => 'time-logs',
                'site_settings'        => 'site_settings',
                'department'           => 'department',
                'compare-dtr'          => 'compare-dtr',
                'pos'                  => 'pos',
                'branches'             => 'branches',
                'categories'           => 'categories',
                'products'             => 'products',
                'damage-items'         => 'damage-items',
                'damage-items-details'  => 'damage-items-details',
                'owner-requisition'    => 'owner-requisition',
                'sales-report'         => 'sales-report',
                'inventory-report'     => 'inventory-report',
                'sales-transaction'    => 'sales-transaction',
            ];

            $page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

            if (!array_key_exists($page, $routes)) {
                $page = 'home';
            }

            if (isset($_SESSION['login_role']) && $_SESSION['login_role'] === 10) {
                if (!in_array($page, ['sales-report', 'attendance', 'inventory-report', 'payroll-report'])) {
                    $page = 'sales-report';
                }
            }

            // Cashiers must not access biometric attendance logs directly by URL.
            if ((int)($_SESSION['login_role'] ?? 0) === 9 && $page === 'attendance') {
                $page = 'home';
            }

            include $routes[$page] . '.php';
            ?>
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear());
                        </script>
                        © Payroll.
                    </div>

                </div>
            </div>
        </footer>

        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!--start back-to-top-->
    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <!--end back-to-top-->

    <!-- Theme Settings -->
    


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js" integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script>
    <!-- JAVASCRIPT -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/feather-icons/feather.min.js"></script>
    <script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="assets/js/plugins.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <script src="assets2/vendor/toastr/toastr.js"></script>
    <!-- Parsley.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>

    <!-- <script src="assets2/bundles/datatablescripts.bundle.js"></script> -->
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.3.0/typeahead.bundle.min.js"></script> -->

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

    <script type="text/javascript">
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })



        toastr.options.timeOut = "false";
        toastr.options.closeButton = true;
        toastr.options.positionClass = 'toast-top-right';


        function handleError(e) {
            $('.submitbutton').removeAttr('disabled');
            $(".fa-spinner-button").hide();
            toastr['error'](e ? e : 'Someting went wrong. Please contact administrator.', "Error Notification");
        }

        $('.filterme').keypress(function(eve) {
            if ((eve.which != 46 || $(this).val().indexOf('.') != -1) && (eve.which < 48 || eve.which > 57) || (eve.which == 46 && $(this).caret().start == 0)) {
                eve.preventDefault();
            }
            $('.filterme').keyup(function(eve) {
                if ($(this).val().indexOf('.') == 0) {
                    $(this).val($(this).val().substring(1));
                }
            });
        });

        function _conf($msg = '', $func = '', $params = []) {
            Swal.fire({
                title: 'Confirmation',
                text: $msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonColor: '#2196F3',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window[$func]($params.join(','));
                }
            })
        }
    </script>

    <!-- <script src="assets2/vendor/select2/select2.min.js"></script> -->

    <?php if ($page == 'home') { ?>
        <script src="assets2/js/home.js"></script>
    <?php } ?>
    <?php if (!empty($include_products_js)) { ?>
        <script src="assets2/js/products.js"></script>
    <?php } ?>

    <?php if ($page == 'employee' || $page == 'employee-details') { ?>
        <script src="assets2/js/employee.js?v=<?= filemtime(__DIR__ . '/assets2/js/employee.js') ?>"></script>
    <?php } ?>
    <?php if ($page == 'department') { ?>
        <script src="assets2/js/department.js"></script>
    <?php } ?>
    <?php if ($page == 'position') { ?>
        <script src="assets2/js/position.js"></script>
    <?php } ?>
    <?php if ($page == 'deductions') { ?>
        <script src="assets2/js/deductions.js"></script>
    <?php } ?>
    <?php if ($page == 'payroll'  || $page == 'payroll_items') { ?>
        <script src="assets2/js/payroll.js?v=2"></script>
    <?php } ?>
    <?php if ($page == 'attendance') { ?>
        <script src="assets2/js/attendance.js"></script>
        <script src="assets2/js/dtr.js?v=<?= filemtime(__DIR__ . '/assets2/js/dtr.js') ?>"></script>
    <?php } ?>
    <?php if ($page == 'time-logs') { ?>
        <script src="assets2/js/time_logs.js"></script>
    <?php } ?>
    <?php if ($page == 'allowances') { ?>
        <script src="assets2/js/allowances.js"></script>
    <?php } ?>

    <?php if ($page == 'sites') { ?>
        <script src="assets2/js/sites.js"></script>
    <?php } ?>

    <?php if ($page == 'users') { ?>
        <script src="assets2/js/user.js?v=<?= filemtime(__DIR__ . '/assets2/js/user.js') ?>"></script>
    <?php } ?>

    <?php if ($page == 'dtr') { ?>
        <script src="assets2/js/dtr.js?v=<?= filemtime(__DIR__ . '/assets2/js/dtr.js') ?>"></script>
    <?php } ?>
    <?php if ($page == 'dtr-details') { ?>
        <script src="assets2/js/dtr-details.js"></script>
    <?php } ?>
    <?php if ($page == 'visitors-logs') { ?>
        <script src="assets2/js/logs.js"></script>
    <?php } ?>
    <?php if ($page == 'visitors-logs') { ?>
        <script src="assets2/js/visitors-logs.js"></script>
    <?php } ?>
    <?php if ($page == 'payroll_calculations') { ?>
        <script src="assets2/js/payroll_calculations.js"></script>
    <?php } ?>
    <?php if ($page == 'refunds') { ?>
        <script src="assets2/js/refunds.js"></script>
    <?php } ?>
    <?php if ($page == 'branches') { ?>
        <script src="assets2/js/branches.js"></script>
    <?php } ?>
    <?php if ($page == 'categories') { ?>
        <script src="assets2/js/categories.js"></script>
    <?php } ?>
    <?php if ($page == 'products') { ?>
        <script src="assets2/js/products.js"></script>
    <?php } ?>

    <style>
        .table-dark  th{
            background-color: #009688 !important;
            border-color: #24a192 !important;
        }
    </style>
</body>

</html>
