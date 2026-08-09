<?php
if ($login_role === 6) {
    echo "<script>location.href='index.php?page=dtr';</script>";
    exit;
}

// ── Stat counters ──────────────────────────────────────────────
$total_employees   = (int) $conn->query("SELECT COUNT(*) AS c FROM employee WHERE status=1")->fetch_assoc()['c'];
$total_inactive    = (int) $conn->query("SELECT COUNT(*) AS c FROM employee WHERE status=0")->fetch_assoc()['c'];
$total_sites       = (int) $conn->query("SELECT COUNT(*) AS c FROM sites WHERE status=1")->fetch_assoc()['c'];
$total_clusters    = (int) $conn->query("SELECT COUNT(*) AS c FROM clusters")->fetch_assoc()['c'];
$total_positions   = (int) $conn->query("SELECT COUNT(*) AS c FROM position")->fetch_assoc()['c'];
$total_users       = (int) $conn->query("SELECT COUNT(*) AS c FROM users WHERE role!=1 AND status=1")->fetch_assoc()['c'];
$total_payrolls    = (int) $conn->query("SELECT COUNT(*) AS c FROM payroll")->fetch_assoc()['c'];
$pending_dtr       = (int) $conn->query("SELECT COUNT(*) AS c FROM DTR WHERE status=1")->fetch_assoc()['c'];
$approved_dtr      = (int) $conn->query("SELECT COUNT(*) AS c FROM DTR WHERE status=2")->fetch_assoc()['c'];
$visitors_today    = (int) $conn->query("SELECT COUNT(*) AS c FROM visitors_logs WHERE DATE(date_visited)=CURDATE()")->fetch_assoc()['c'];

// ── Payroll status breakdown ────────────────────────────────────
$pay_new        = (int) $conn->query("SELECT COUNT(*) AS c FROM payroll WHERE status=0")->fetch_assoc()['c'];
$pay_calculated = (int) $conn->query("SELECT COUNT(*) AS c FROM payroll WHERE status=1")->fetch_assoc()['c'];
$pay_locked     = (int) $conn->query("SELECT COUNT(*) AS c FROM payroll WHERE status=2")->fetch_assoc()['c'];

// ── Employee by payroll type ────────────────────────────────────
$emp_weekly  = (int) $conn->query("SELECT COUNT(*) AS c FROM employee WHERE weekly_payroll=1 AND status=1")->fetch_assoc()['c'];
$emp_monthly = (int) $conn->query("SELECT COUNT(*) AS c FROM employee WHERE weekly_payroll=0 AND status=1")->fetch_assoc()['c'];

// ── Monthly payroll count – last 7 months ──────────────────────
$monthly_labels = [];
$monthly_data   = [];
$monthly_res = $conn->query("
    SELECT DATE_FORMAT(date_from,'%b %Y') AS lbl, COUNT(*) AS cnt
    FROM payroll
    WHERE date_from >= DATE_SUB(NOW(), INTERVAL 7 MONTH)
    GROUP BY DATE_FORMAT(date_from,'%Y-%m')
    ORDER BY MIN(date_from) ASC
");
while ($r = $monthly_res->fetch_assoc()) {
    $monthly_labels[] = $r['lbl'];
    $monthly_data[]   = (int) $r['cnt'];
}

// ── Top 5 positions by employee count ──────────────────────────
$pos_labels = [];
$pos_data   = [];
$pos_res = $conn->query("
    SELECT p.name, COUNT(e.id) AS cnt
    FROM employee e
    LEFT JOIN position p ON e.position_id = p.id
    WHERE e.status = 1
    GROUP BY p.id
    ORDER BY cnt DESC
    LIMIT 6
");
while ($r = $pos_res->fetch_assoc()) {
    $pos_labels[] = $r['name'] ?: 'Unassigned';
    $pos_data[]   = (int) $r['cnt'];
}

// ── Recent payrolls ─────────────────────────────────────────────
$recent_payrolls = $conn->query("
    SELECT p.*, e.employer_name
    FROM payroll p
    LEFT JOIN employers e ON p.employer_id = e.id
    ORDER BY p.id DESC LIMIT 6
");

// ── Recent DTR uploads ──────────────────────────────────────────
$recent_dtr = $conn->query("
    SELECT d.*, s.site_name, s.site_code, u.name AS uploader
    FROM DTR d
    LEFT JOIN sites s ON d.site_id = s.id
    LEFT JOIN users u ON d.uploaded_by = u.id
    ORDER BY d.id DESC LIMIT 6
");
?>
<style>
    .dash-stat { border-top:3px solid #394b7c; border-radius:6px; background:#fff; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 4px rgba(57,75,124,.07); transition:box-shadow .2s; }
    .dash-stat:hover { box-shadow:0 4px 16px rgba(57,75,124,.13); }
    .dash-stat .ds-icon { width:46px; height:46px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
    .dash-stat .ds-val { font-size:24px; font-weight:800; color:#394b7c; line-height:1; }
    .dash-stat .ds-lbl { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.4px; margin-top:3px; }
    .dash-stat .ds-sub { font-size:11px; color:#aaa; margin-top:2px; }
    .dash-section-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#394b7c; margin-bottom:10px; display:flex; align-items:center; gap:6px; }
    .pay-status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }
    #data-table thead th,
    #dtr-recent-table thead th { background-color:#394b7c !important; border-color:#2d3d66 !important; color:#fff !important; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-dashboard-line me-2" style="color:#394b7c;"></i>Dashboard</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ROW 1: Stat cards ── -->
            <div class="row g-3 mb-3">
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="dash-stat">
                        <div class="ds-icon" style="background:#eef0f8;"><i class="ri-group-line" style="color:#394b7c;"></i></div>
                        <div>
                            <div class="ds-val"><?= $total_employees ?></div>
                            <div class="ds-lbl">Employees</div>
                            <div class="ds-sub"><?= $total_inactive ?> inactive</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="dash-stat" style="border-top-color:#28a745;">
                        <div class="ds-icon" style="background:#e8f8ee;"><i class="ri-map-pin-2-line" style="color:#28a745;"></i></div>
                        <div>
                            <div class="ds-val" style="color:#28a745;"><?= $total_sites ?></div>
                            <div class="ds-lbl">Active Sites</div>
                            <div class="ds-sub"><?= $total_clusters ?> clusters</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="dash-stat" style="border-top-color:#6f42c1;">
                        <div class="ds-icon" style="background:#f2eefb;"><i class="ri-money-dollar-circle-line" style="color:#6f42c1;"></i></div>
                        <div>
                            <div class="ds-val" style="color:#6f42c1;"><?= $total_payrolls ?></div>
                            <div class="ds-lbl">Payrolls</div>
                            <div class="ds-sub"><?= $pay_locked ?> locked</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="dash-stat" style="border-top-color:#fd7e14;">
                        <div class="ds-icon" style="background:#fff4ec;"><i class="ri-time-line" style="color:#fd7e14;"></i></div>
                        <div>
                            <div class="ds-val" style="color:#fd7e14;"><?= $pending_dtr ?></div>
                            <div class="ds-lbl">Pending DTR</div>
                            <div class="ds-sub"><?= $approved_dtr ?> approved</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="dash-stat" style="border-top-color:#17a2b8;">
                        <div class="ds-icon" style="background:#e8f7fa;"><i class="ri-shield-user-line" style="color:#17a2b8;"></i></div>
                        <div>
                            <div class="ds-val" style="color:#17a2b8;"><?= $total_users ?></div>
                            <div class="ds-lbl">Active Users</div>
                            <div class="ds-sub"><?= $total_positions ?> positions</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="dash-stat" style="border-top-color:#dc3545;">
                        <div class="ds-icon" style="background:#fdf0f1;"><i class="ri-user-search-line" style="color:#dc3545;"></i></div>
                        <div>
                            <div class="ds-val" style="color:#dc3545;"><?= $visitors_today ?></div>
                            <div class="ds-lbl">Visitors Today</div>
                            <div class="ds-sub">logged in</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ROW 2: Charts ── -->
            <div class="row g-3 mb-3">

                <!-- Monthly Payroll Bar Chart -->
                <div class="col-xl-8">
                    <div class="card h-100" style="border-top:3px solid #394b7c;">
                        <div class="card-header d-flex align-items-center py-2">
                            <h6 class="card-title mb-0 flex-grow-1">
                                <i class="ri-bar-chart-2-line me-2" style="color:#394b7c;"></i>Payroll Activity (Last 7 Months)
                            </h6>
                        </div>
                        <div class="card-body pb-2">
                            <div id="chart-payroll-monthly" style="min-height:240px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Status + Employee Type -->
                <div class="col-xl-4">
                    <div class="card mb-3" style="border-top:3px solid #394b7c;">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0">
                                <i class="ri-pie-chart-line me-2" style="color:#394b7c;"></i>Payroll Status
                            </h6>
                        </div>
                        <div class="card-body py-2">
                            <div id="chart-payroll-status" style="min-height:160px;"></div>
                            <div class="d-flex justify-content-center gap-3 mt-1" style="font-size:12px;">
                                <span><span class="pay-status-dot" style="background:#394b7c;"></span>New (<?= $pay_new ?>)</span>
                                <span><span class="pay-status-dot" style="background:#28a745;"></span>Calculated (<?= $pay_calculated ?>)</span>
                                <span><span class="pay-status-dot" style="background:#dc3545;"></span>Locked (<?= $pay_locked ?>)</span>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="border-top:3px solid #394b7c;">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0">
                                <i class="ri-donut-chart-line me-2" style="color:#394b7c;"></i>Employee Payroll Type
                            </h6>
                        </div>
                        <div class="card-body py-2">
                            <div id="chart-emp-type" style="min-height:130px;"></div>
                            <div class="d-flex justify-content-center gap-3 mt-1" style="font-size:12px;">
                                <span><span class="pay-status-dot" style="background:#394b7c;"></span>Monthly (<?= $emp_monthly ?>)</span>
                                <span><span class="pay-status-dot" style="background:#17a2b8;"></span>Weekly (<?= $emp_weekly ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ROW 3: Recent records ── -->
            <div class="row g-3 mb-3">

                <!-- Recent Payrolls -->
                <div class="col-xl-7">
                    <div class="card h-100" style="border-top:3px solid #394b7c;">
                        <div class="card-header d-flex align-items-center py-2">
                            <h6 class="card-title mb-0 flex-grow-1">
                                <i class="ri-money-dollar-circle-line me-2" style="color:#394b7c;"></i>Recent Payrolls
                            </h6>
                            <a href="index.php?page=payroll" class="btn btn-sm btn-outline-secondary" style="font-size:11px;">
                                View All <i class="ri-arrow-right-line ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="background:#394b7c;color:#fff;padding:8px 12px;font-size:11px;border:none;">Ref No.</th>
                                            <th style="background:#394b7c;color:#fff;padding:8px 12px;font-size:11px;border:none;">Employer</th>
                                            <th style="background:#394b7c;color:#fff;padding:8px 12px;font-size:11px;border:none;">Period</th>
                                            <th style="background:#394b7c;color:#fff;padding:8px 12px;font-size:11px;border:none;text-align:center;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($r = $recent_payrolls->fetch_assoc()): ?>
                                        <tr>
                                            <td style="padding:7px 12px;font-size:12px;">
                                                <span style="font-family:monospace;font-weight:700;color:#394b7c;"><?= htmlspecialchars($r['ref_no']) ?></span>
                                            </td>
                                            <td style="padding:7px 12px;font-size:12px;font-weight:600;"><?= htmlspecialchars($r['employer_name']) ?></td>
                                            <td style="padding:7px 12px;font-size:11px;color:#555;">
                                                <?= date('M d', strtotime($r['date_from'])) ?> &ndash; <?= date('M d, Y', strtotime($r['date_to'])) ?>
                                            </td>
                                            <td style="padding:7px 12px;text-align:center;">
                                                <?php if ($r['status'] == 0): ?>
                                                    <span class="badge bg-primary" style="font-size:10px;">New</span>
                                                <?php elseif ($r['status'] == 1): ?>
                                                    <span class="badge bg-success" style="font-size:10px;">Calculated</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger" style="font-size:10px;"><i class="ri-lock-fill me-1"></i>Locked</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent DTR -->
                <div class="col-xl-5">
                    <div class="card h-100" style="border-top:3px solid #394b7c;">
                        <div class="card-header d-flex align-items-center py-2">
                            <h6 class="card-title mb-0 flex-grow-1">
                                <i class="ri-time-line me-2" style="color:#394b7c;"></i>Recent DTR Uploads
                            </h6>
                            <a href="index.php?page=dtr" class="btn btn-sm btn-outline-secondary" style="font-size:11px;">
                                View All <i class="ri-arrow-right-line ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="background:#394b7c;color:#fff;padding:8px 12px;font-size:11px;border:none;">Site</th>
                                            <th style="background:#394b7c;color:#fff;padding:8px 12px;font-size:11px;border:none;">Uploaded By</th>
                                            <th style="background:#394b7c;color:#fff;padding:8px 12px;font-size:11px;border:none;text-align:center;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($r = $recent_dtr->fetch_assoc()): ?>
                                        <tr>
                                            <td style="padding:7px 12px;">
                                                <span style="background:#394b7c;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:700;font-family:monospace;"><?= htmlspecialchars($r['site_code']) ?></span>
                                                <div style="font-size:12px;font-weight:600;margin-top:2px;"><?= htmlspecialchars($r['site_name']) ?></div>
                                            </td>
                                            <td style="padding:7px 12px;font-size:12px;"><?= htmlspecialchars($r['uploader'] ?? '—') ?></td>
                                            <td style="padding:7px 12px;text-align:center;">
                                                <?php if ($r['status'] == 2): ?>
                                                    <span class="badge bg-success" style="font-size:10px;"><i class="ri-check-line me-1"></i>Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark" style="font-size:10px;">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ROW 4: Quick Links ── -->
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="card" style="border-top:3px solid #394b7c;">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0">
                                <i class="ri-apps-line me-2" style="color:#394b7c;"></i>Quick Access
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="index.php?page=employee" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-group-line me-1"></i>Employees
                                </a>
                                <a href="index.php?page=payroll" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-money-dollar-circle-line me-1"></i>Payroll
                                </a>
                                <a href="index.php?page=dtr" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-time-line me-1"></i>DTR
                                </a>
                                <a href="index.php?page=attendance" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-calendar-check-line me-1"></i>Attendance
                                </a>
                                <a href="index.php?page=sites" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-map-pin-2-line me-1"></i>Sites
                                </a>
                                <a href="index.php?page=clusters" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-global-line me-1"></i>Clusters
                                </a>
                                <a href="index.php?page=visitors-logs" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-user-search-line me-1"></i>Visitor Logs
                                </a>
                                <a href="index.php?page=users" class="btn btn-sm" style="background:#eef0f8;color:#394b7c;border:1px solid #d0d7ee;font-weight:600;">
                                    <i class="ri-shield-user-line me-1"></i>Users
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<script>
(function () {
    var primary = '#394b7c';

    // ── Monthly Payroll Bar Chart ───────────────────────────────
    new ApexCharts(document.getElementById('chart-payroll-monthly'), {
        chart: { type: 'bar', height: 240, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: [primary],
        series: [{ name: 'Payrolls', data: <?= json_encode($monthly_data) ?> }],
        xaxis: { categories: <?= json_encode($monthly_labels) ?>, labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { style: { fontSize: '11px' } }, min: 0, tickAmount: 4, forceNiceScale: true },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
        dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
        grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
        tooltip: { y: { formatter: function(v){ return v + ' payroll(s)'; } } },
    }).render();

    // ── Payroll Status Donut ────────────────────────────────────
    new ApexCharts(document.getElementById('chart-payroll-status'), {
        chart: { type: 'donut', height: 160, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: [primary, '#28a745', '#dc3545'],
        series: [<?= $pay_new ?>, <?= $pay_calculated ?>, <?= $pay_locked ?>],
        labels: ['New', 'Calculated', 'Locked'],
        legend: { show: false },
        dataLabels: { enabled: true, style: { fontSize: '11px' } },
        plotOptions: { pie: { donut: { size: '60%' } } },
        tooltip: { y: { formatter: function(v){ return v + ' payroll(s)'; } } },
    }).render();

    // ── Employee Type Donut ─────────────────────────────────────
    new ApexCharts(document.getElementById('chart-emp-type'), {
        chart: { type: 'donut', height: 130, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: [primary, '#17a2b8'],
        series: [<?= $emp_monthly ?>, <?= $emp_weekly ?>],
        labels: ['Monthly', 'Weekly'],
        legend: { show: false },
        dataLabels: { enabled: true, style: { fontSize: '11px' } },
        plotOptions: { pie: { donut: { size: '55%' } } },
        tooltip: { y: { formatter: function(v){ return v + ' employee(s)'; } } },
    }).render();
})();
</script>
