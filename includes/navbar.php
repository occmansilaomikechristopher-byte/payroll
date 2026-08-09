<?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
<?php $login_role = intval($_SESSION['login_role'] ?? 0); ?>
<?php $is_cashier = ($login_role === 9); ?>

<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
       
        <a href="home" class="logo logo-light">
            <span class="logo-sm">Gv</span>
            <span class="logo-lg"><div class="logo"> Payroll</div></span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>

            <ul class="navbar-nav" id="navbar-nav">

                <?php if ($login_role === 10): ?>

                <!-- Reports Only for Owner -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0)">
                        <i class="ri-bar-chart-box-line"></i> <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item ps-3">
                    <a href="sales-report" class="nav-link <?= $page === 'sales-report' ? 'active' : '' ?>">
                        <i class="ri-money-dollar-circle-line me-1"></i>Sales
                    </a>
                </li>
                <li class="nav-item ps-3">
                    <a href="attendance" class="nav-link <?= $page === 'attendance' ? 'active' : '' ?>">
                        <i class="ri-calendar-check-line me-1"></i>Attendance
                    </a>
                </li>
                <li class="nav-item ps-3">
                    <a href="inventory-report" class="nav-link <?= $page === 'inventory-report' ? 'active' : '' ?>">
                        <i class="ri-store-3-line me-1"></i>Inventory
                    </a>
                </li>
                <li class="nav-item ps-3">
                    <a href="payroll-report" class="nav-link <?= $page === 'payroll-report' ? 'active' : '' ?>">
                        <i class="ri-file-list-3-line me-1"></i>Payroll
                    </a>
                </li>

                <?php elseif ($login_role !== 6 && $login_role !== 7): ?>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $page === 'home' ? 'active' : '' ?>" href="home">
                        <i class="ri-dashboard-fill"></i> <span>Dashboard</span>
                    </a>
                </li>

                <!-- Workforce Information -->
                <?php if (!$is_cashier): ?>
                <li class="nav-item">
                    <a href="employee" class="nav-link <?= in_array($page, ['employee','employee-details']) ? 'active' : '' ?>">
                        <i class="ri-group-line"></i> <span>Employees</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="position" class="nav-link <?= $page === 'position' ? 'active' : '' ?>">
                        <i class="ri-briefcase-4-line"></i> <span>Position</span>
                    </a>
                </li>

                <!-- Payroll -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= (in_array($page, ['payroll','payroll_items','payroll_calculations']) && (!isset($_GET['p2']) || $_GET['p2'] === 'false')) ? 'active' : '' ?>"
                        href="payroll?p2=false">
                        <i class="ri-calculator-line"></i> <span>Payroll</span>
                    </a>
                </li>
                <!-- Benefits & Compensation -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarBenefits" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= in_array($page, ['deductions','contributions','refunds']) ? 'true' : 'false' ?>">
                        <i class="ri-gift-line"></i> <span>Benefits & Compensation</span>
                    </a>
                    <div class="menu-dropdown collapse <?= in_array($page, ['deductions','contributions']) ? 'show' : '' ?>" id="sidebarBenefits">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="contributions" class="nav-link <?= $page === 'contributions' ? 'active' : '' ?>">
                                    <i class="ri-hand-coin-line me-1"></i>Contributions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="deductions" class="nav-link <?= $page === 'deductions' ? 'active' : '' ?>">
                                    <i class="ri-subtract-line me-1"></i>Deductions
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Time & Attendance -->
                <?php if (!$is_cashier): ?>
                <li class="nav-item">
                    <a href="dtr" class="nav-link <?= in_array($page, ['dtr','dtr-details','attendance']) ? 'active' : '' ?>">
                        <i class="ri-calendar-line"></i> <span>Time & Attendance</span>
                    </a>
                </li>
                <?php endif; ?>
                <!-- POS Systems -->
                <li class="nav-item">
                    <a class="nav-link menu-link js-sidebar-collapse-toggle" href="#sidebarPOS" role="button"
                        aria-controls="sidebarPOS"
                        aria-expanded="<?= in_array($page, ['branches','categories','products','inventory-report']) ? 'true' : 'false' ?>">
                        <i class="ri-shopping-cart-2-line"></i> <span>POS Systems</span> <i class="ri-arrow-right-s-line float-end"></i>
                    </a>
                    <div class="menu-dropdown collapse <?= in_array($page, ['branches','categories','products','inventory-report']) ? 'show' : '' ?>" id="sidebarPOS">
                        <ul class="nav nav-sm flex-column">
                            <?php if (!$is_cashier): ?>
                            <li class="nav-item">
                                <a href="branches" class="nav-link <?= $page === 'branches' ? 'active' : '' ?>">
                                    <i class="ri-git-branch-line me-1"></i>Branches
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="categories" class="nav-link <?= $page === 'categories' ? 'active' : '' ?>">
                                    <i class="ri-layout-grid-line me-1"></i>Categories
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="products" class="nav-link <?= $page === 'products' ? 'active' : '' ?>">
                                    <i class="ri-shopping-bag-2-line me-1"></i>Products
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="inventory-report" class="nav-link <?= $page === 'inventory-report' ? 'active' : '' ?>">
                                    <i class="ri-store-3-line me-1"></i>Inventory
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link menu-link js-sidebar-collapse-toggle" href="#sidebarReports" role="button"
                        aria-controls="sidebarReports"
                        aria-expanded="<?= $page === 'sales-report' ? 'true' : 'false' ?>">
                        <i class="ri-bar-chart-box-line"></i> <span>Reports</span> <i class="ri-arrow-right-s-line float-end"></i>
                    </a>
                    <div class="menu-dropdown collapse <?= $page === 'sales-report' ? 'show' : '' ?>" id="sidebarReports">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="sales-report" class="nav-link <?= $page === 'sales-report' ? 'active' : '' ?>">
                                    <i class="ri-money-dollar-circle-line me-1"></i>Sales
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Users — Admin only -->
                <?php if ($login_role === 1): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $page === 'users' ? 'active' : '' ?>" href="users">
                        <i class="ri-shield-user-line"></i> <span>User Management</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php endif; ?>

                <!-- Role 9 (Cashier) -->
                <?php if ($login_role === 9): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link js-sidebar-collapse-toggle" href="#sidebarCashierTools" role="button"
                            aria-controls="sidebarCashierTools"
                                aria-expanded="<?= in_array($page, ['damage-items','damage-items-details']) ? 'true' : 'false' ?>">
                        <i class="ri-file-damage-line"></i> <span>Damage Items</span> <i class="ri-arrow-right-s-line float-end"></i>
                    </a>
                    <div class="menu-dropdown collapse <?= in_array($page, ['damage-items','damage-items-details']) ? 'show' : '' ?>" id="sidebarCashierTools">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="damage-items" class="nav-link <?= $page === 'damage-items' ? 'active' : '' ?>">
                                    <i class="ri-tools-line me-1"></i>Manage Damage Items
                                </a>
                            </li>
                            <li class="nav-item">
                                <!-- <a href="damage-items-details" class="nav-link <?= $page === 'damage-items-details' ? 'active' : '' ?>">
                                    <i class="ri-file-list-3-line me-1"></i>Damage Items Details
                                </a> -->
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="owner-requisition" class="nav-link <?= $page === 'owner-requisition' ? 'active' : '' ?>">
                        <i class="ri-file-list-3-line me-1"></i>Owner Requisition
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $page === 'sales-transaction' ? 'active' : '' ?>" href="sales-transaction">
                        <i class="ri-shopping-cart-2-line"></i> <span>Sales Transaction</span>
                    </a>
                </li>
                <?php endif; ?>


                <!-- Role 6 (PIC) -->
                <?php if ($login_role === 6): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAttendance6" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= in_array($page, ['attendance','dtr','dtr-details']) ? 'true' : 'false' ?>">
                        <i class="ri-calendar-line"></i> <span>Time & Attendance</span>
                    </a>
                    <div class="menu-dropdown collapse <?= in_array($page, ['attendance','dtr','dtr-details']) ? 'show' : '' ?>" id="sidebarAttendance6">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="dtr" class="nav-link <?= in_array($page, ['dtr','dtr-details']) ? 'active' : '' ?>">
                                    <i class="ri-time-line me-1"></i>Daily Time Record
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Role 7 (Auditor) -->
                <?php if ($login_role === 7): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $page === 'home' ? 'active' : '' ?>" href="home">
                        <i class="ri-dashboard-fill"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAttendance7" data-bs-toggle="collapse" role="button"
                        aria-expanded="<?= in_array($page, ['attendance','dtr','dtr-details']) ? 'true' : 'false' ?>">
                        <i class="ri-calendar-line"></i> <span>Time & Attendance</span>
                    </a>
                    <div class="menu-dropdown collapse <?= in_array($page, ['attendance','dtr','dtr-details']) ? 'show' : '' ?>" id="sidebarAttendance7">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="dtr" class="nav-link <?= in_array($page, ['dtr','dtr-details']) ? 'active' : '' ?>">
                                    <i class="ri-time-line me-1"></i>Daily Time Record
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="attendance" class="nav-link <?= $page === 'attendance' ? 'active' : '' ?>">
                                    <i class="ri-calendar-check-line me-1"></i>Attendance Record
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

<script>
(function () {
function initSidebarDropdowns() {
    document.querySelectorAll('.js-sidebar-collapse-toggle').forEach(function (toggle) {
        var targetSelector = toggle.getAttribute('href');
        var target = targetSelector ? document.querySelector(targetSelector) : null;

        if (!target) {
            return;
        }

        target.addEventListener('shown.bs.collapse', function () {
            toggle.setAttribute('aria-expanded', 'true');
        });

        target.addEventListener('hidden.bs.collapse', function () {
            toggle.setAttribute('aria-expanded', 'false');
        });

        toggle.addEventListener('click', function (event) {
            event.preventDefault();

            if (window.bootstrap && bootstrap.Collapse) {
                var collapse = bootstrap.Collapse.getInstance(target) || new bootstrap.Collapse(target, { toggle: false });
                collapse.toggle();
                return;
            }

            var willOpen = !target.classList.contains('show');
            target.classList.toggle('show', willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarDropdowns);
} else {
    initSidebarDropdowns();
}
})();
</script>
