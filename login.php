<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>

    <meta charset="utf-8" />
    <title>Jejors Payroll - Login Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="JEJORS Payroll" name="description" />
    <meta content="Niel Daculan" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Layout config Js -->
    <script src="assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css" />
    <style>
        .parsley-errors-list {
            color: #f36363;
            margin-top: 0px !important;
            list-style: none !important;
            padding: 0 !important;
        }

        .logo {
            height: 100px;
            width: 100px;
            border-radius: 50%;
            background: #fff;
            text-align: center;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo img {
            height: 56px;
        }

        /* .auth-one-bg {
            background-image: url(login-bg.jpg) !important;
            background-position: center;
            background-size: cover;
        } */

        .auth-one-bg .bg-overlay {
            background: -webkit-gradient(linear, left top, right top, from(#f87e7e), to(#ff0506cc));
            background: linear-gradient(to right, #dc3c3c82, #ff0506ed);
            opacity: .9;
        }
    </style>

</head>

<body>

    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div class="logo">
                                <img src="assets2/images/logo.jpeg" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4 card-bg-fill">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Welcome Back !</h5>
                                    <p class="text-muted">Sign in to continue to Jejors.</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <form novalidate id="form-login" method="post">
                                        <div id="message-show"></div>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username" id="username" placeholder="Enter username" data-parsley-required-message="Username is required." required>
                                        </div>

                                        <div class="mb-3">
                                            <!-- <div class="float-end">
                                                <a href="auth-pass-reset-basic.html" class="text-muted">Forgot password?</a>
                                            </div> -->
                                            <label class="form-label" for="password-input">Password</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input"  name="password" placeholder="Enter password" id="password-input" data-parsley-required-message="Password is required." required>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="auth-remember-check">
                                            <label class="form-check-label" for="auth-remember-check">Remember me</label>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Sign In</button>
                                        </div>

                                        
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                        

                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy;
                                <script>
                                    document.write(new Date().getFullYear())
                                </script> Jejors. Crafted with <i class="mdi mdi-heart text-danger"></i> by Niel M. Daculan
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js" integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JAVASCRIPT -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/feather-icons/feather.min.js"></script>
    <script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="assets/js/plugins.js"></script>

    <!-- particles js -->
    <script src="assets/libs/particles.js/particles.js"></script>
    <!-- particles app js -->
    <script src="assets/js/pages/particles.app.js"></script>
    <!-- password-addon init -->
    <!-- Parsley.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <!-- password-addon init -->
    <script src="assets/js/pages/password-addon.init.js"></script>
    <script>
        $(document).ready(function() {
            $("#form-login").on('submit', async function(e) {
                e.preventDefault();
                var form = $(this);

                form.parsley().validate();
                Swal.fire({
                    title: ` Authenticating , please wait...`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
                await new Promise((resolve) => setTimeout(resolve, 1000));
                if (form.parsley().isValid()) {
                    $.ajax({
                        url: 'ajax.php?action=login',
                        method: 'POST',
                        dataType: "JSON",
                        data: $(this).serialize(),
                        error: (xhr, status, error) => {
                            Swal.close();
                            $('#message-show').html(`<div class="alert alert-danger">${xhr.responseText}</div>`)
                        },
                        success: function(res) {
                            Swal.close();
                            if (res?.result) {
                                location.href = 'index.php?page=home';
                            } else {
                                $('#message-show').html(`<div class="alert alert-danger">${res.message}</div>`)
                            }
                        }
                    })
                }
            });
        });
    </script>
</body>

</html>