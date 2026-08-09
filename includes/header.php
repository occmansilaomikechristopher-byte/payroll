<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none" data-theme="default" data-theme-colors="default">

<head>

    <meta charset="utf-8" />
    <title>Payroll</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content=" Payroll" name="description" />
    <meta content="" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Layout config Js -->
    <!-- <script src="assets/js/layout.js"></script> -->
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="assets2/vendor/toastr/toastr.min.css">
    <style>
        /* Dark blue sidebar theme */
        .navbar-menu {
            background: linear-gradient(180deg, #0d493a 0%, #017065 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.71);
        }

        .navbar-brand-box .logo-light .logo {
            color: #F8FAFC;
        }

        .navbar-menu .navbar-nav .nav-link,
        .navbar-menu .navbar-nav .nav-link i,
        .navbar-menu .menu-title span {
            color: rgba(248, 250, 252, 0.82);
        }

        .navbar-menu .navbar-nav .nav-link:hover,
        .navbar-menu .navbar-nav .nav-link:hover i {
            color: #000000 !important;
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        .navbar-menu .navbar-nav .nav-link.active,
        .navbar-menu .navbar-nav .nav-link.active i,
        .navbar-menu .navbar-nav .nav-link[aria-expanded="true"],
        .navbar-menu .navbar-nav .nav-link[aria-expanded="true"] i {
            color: #ffffff !important;
        }

        .navbar-menu .navbar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.14);
            box-shadow: inset 0 0 0 1px rgba(230, 234, 241, 0.24);
        }

        /* Submenu / dropdown items */
        .navbar-menu .menu-dropdown .nav-link,
        .navbar-menu .nav-sub .nav-link {
            color: rgba(248, 250, 252, 0.74) !important;
        }

        .navbar-menu .navbar-nav .nav-link.menu-link {
            position: relative;
        }
        .navbar-menu .navbar-nav .nav-link.menu-link .ri-arrow-right-s-line {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.2s ease;
        }
        .navbar-menu .navbar-nav .nav-link.menu-link[aria-expanded="true"] .ri-arrow-right-s-line {
            transform: translateY(-50%) rotate(90deg);
        }

        .navbar-menu .menu-dropdown .nav-link:hover,
        .navbar-menu .menu-dropdown .nav-link.active,
        .navbar-menu .nav-sub .nav-link:hover,
        .navbar-menu .nav-sub .nav-link.active {
            color: #FFFFFF !important;
            background: rgba(37, 99, 235, 0.08);
        }

        .parsley-errors-list {
            color: #f36363;
            margin-top: 0px !important;
            list-style: none !important;
            padding: 0 !important;
        }



        .modal form .form-group {
            padding-bottom: 10px;

        }

        .pull-right {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .site-wapper i {
            background: #E91E63;
            color: #fff;
            font-size: 14px;
            border-radius: 4px;
            padding: 2px;
        }

        .table-responsive {
            overflow: unset !important;
        }

        .card {
            padding: 0px;
        }

        .modal-title {
            font-size: 17px;
        }

        .logo {
            color: #219688;
            font-size: 20px;
            font-weight: 600;
        }

        .text-right {
            text-align: right;
        }

        .action-buttons {
            text-align: center;
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .logo-lg {
            color: #000;
        }

        .logo-sm {
            /* height: 30px  !important;
            width: 30px  !important; */
            /* background: #ffffff ; */
            border-radius: 50% !important;
            display: flex;
            align-items: center !important;
            justify-content: center !important;
            /* color: red  !important; */
            /* margin-top: 14px; */
            font-size: 15px;
        }

        /* .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            transition: all 0.2s ease;
        } */

    </style>

</head>
