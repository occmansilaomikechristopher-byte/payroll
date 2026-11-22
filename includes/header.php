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
    <link href="assets/css/custom.min.css?v=2" rel="stylesheet" type="text/css" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="assets2/vendor/toastr/toastr.min.css">
    <style>
        /* GV Brand Theme - Aligned with Login Page */
        .navbar-menu {
            background: linear-gradient(180deg, #01544c 0%, #01433d 100%) !important;
            border-right: 1px solid rgba(244, 180, 46, 0.35) !important;
            box-shadow: 2px 0 16px rgba(1, 67, 61, 0.2) !important;
        }

        .navbar-brand-box {
            border-bottom: 2px solid #f4b42e !important;
        }

        .navbar-brand-box .logo-light .logo,
        .navbar-brand-box .logo {
            color: #ffffff !important;
            font-weight: 700;
        }

        .navbar-menu .navbar-nav .nav-link,
        .navbar-menu .navbar-nav .nav-link i,
        .navbar-menu .menu-title span {
            color: rgba(255, 255, 255, 0.85);
        }

        .navbar-menu .navbar-nav .nav-link:hover,
        .navbar-menu .navbar-nav .nav-link:hover i {
            color: #f4b42e !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
        }

        .navbar-menu .navbar-nav .nav-link.active,
        .navbar-menu .navbar-nav .nav-link.active i,
        .navbar-menu .navbar-nav .nav-link[aria-expanded="true"],
        .navbar-menu .navbar-nav .nav-link[aria-expanded="true"] i {
            color: #ffffff !important;
        }

        .navbar-menu .navbar-nav .nav-link.active {
            background: rgba(244, 180, 46, 0.18) !important;
            border-left: 3px solid #f4b42e !important;
            box-shadow: inset 0 0 0 1px rgba(244, 180, 46, 0.25) !important;
        }

        /* Submenu / dropdown items */
        .navbar-menu .menu-dropdown .nav-link,
        .navbar-menu .nav-sub .nav-link {
            color: rgba(255, 255, 255, 0.75) !important;
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
            color: #f4b42e !important;
            background: rgba(244, 180, 46, 0.12) !important;
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

        .header-profile-user-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
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
            color: #01544c;
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

        /* ── GLOBAL THEME OVERRIDES (Force Dark Teal & Gold Palette) ── */
        #page-topbar {
            border-bottom: 2px solid #f4b42e !important;
        }

        /* Force ALL Table Headers to Brand Dark Teal across all pages */
        html body table thead th,
        html body .table thead th,
        html body table#table thead th,
        html body #table thead th,
        html body table#table thead.table-dark th,
        html body thead.table-dark th,
        html body .table-dark th,
        html body #collections-table thead th,
        html body #sales-table thead th,
        html body #inventory-table thead th,
        html body #data-table thead th,
        html body #data-table1 thead th,
        html body #table-1 thead th,
        html body #table-employee thead th,
        html body #table-loan thead th,
        html body #table-contributions thead th,
        html body #table-deductions thead th,
        html body #damage-table thead th,
        html body #owner-table thead th,
        html body #dtr-recent-table thead th,
        html body .attendance-log-table th {
            background-color: #01544c !important;
            background: #01544c !important;
            border-color: #01433d !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        /* Force Filter Bars / Report Headers across all pages */
        html body div.cr-filter,
        html body form.cr-filter,
        html body div.sr-filter,
        html body form.sr-filter,
        html body div.ir-filter,
        html body form.ir-filter {
            background: linear-gradient(135deg, #01544c 0%, #01433d 100%) !important;
            border-bottom: 2px solid #f4b42e !important;
            color: #ffffff !important;
        }

        /* Card Top Borders and Brand Stat Numbers */
        html body div.cr-card,
        html body div.sr-card,
        html body div.ir-card,
        html body div.dd-card,
        html body div.pos-card {
            border-top: 3px solid #01544c !important;
        }

        html body .cr-stat .v,
        html body .sr-stat .v,
        html body .ir-stat .v,
        html body .pos-stat-val,
        html body .stat-card__value {
            color: #01544c !important;
        }

        /* Modal Headers */
        html body .modal-header {
            background: linear-gradient(135deg, #01544c 0%, #01433d 100%) !important;
            border-bottom: 2px solid #f4b42e !important;
            color: #ffffff !important;
        }

        html body .modal-header .modal-title,
        html body .modal-header h4,
        html body .modal-header h5,
        html body .modal-header h6 {
            color: #ffffff !important;
        }

        /* Buttons & Badges */
        html body .btn-primary,
        html body .btn-info,
        html body .btn-success {
            background-color: #01544c !important;
            border-color: #01544c !important;
            color: #ffffff !important;
        }

        html body .btn-primary:hover,
        html body .btn-info:hover,
        html body .btn-success:hover,
        html body .btn-primary:focus,
        html body .btn-info:focus,
        html body .btn-success:focus {
            background-color: #01433d !important;
            border-color: #01433d !important;
            color: #ffffff !important;
        }

        html body .badge.bg-success {
            background-color: #00695c !important;
            color: #ffffff !important;
        }

        html body .badge.bg-warning {
            background-color: #f4b42e !important;
            color: #1f2937 !important;
        }

        /* .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            transition: all 0.2s ease;
        } */

    </style>

</head>
