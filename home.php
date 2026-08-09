<?php
$home_login_role = (int)($_SESSION['login_role'] ?? 0);
if ($home_login_role === 6) {
    echo "<script>location.href='dtr';</script>";
    exit;
}

// ── Stat counters ──────────────────────────────────────────────
function db_count($conn, $sql) {
    try {
        $r = $conn->query($sql);
        return $r ? (int)$r->fetch_assoc()['c'] : 0;
    } catch (Throwable $e) {
        return 0;
    }
}
$total_employees   = db_count($conn, "SELECT COUNT(*) AS c FROM employee WHERE status=1");
$total_inactive    = db_count($conn, "SELECT COUNT(*) AS c FROM employee WHERE status=0");
$total_sites       = db_count($conn, "SELECT COUNT(*) AS c FROM branches WHERE status=1");
$total_clusters    = 0;
$total_positions   = db_count($conn, "SELECT COUNT(*) AS c FROM position");
$total_users       = db_count($conn, "SELECT COUNT(*) AS c FROM users WHERE role!=1 AND status=1");
$total_payrolls    = db_count($conn, "SELECT COUNT(*) AS c FROM payroll");
$pending_dtr       = db_count($conn, "SELECT COUNT(*) AS c FROM DTR WHERE status=1");
$approved_dtr      = db_count($conn, "SELECT COUNT(*) AS c FROM DTR WHERE status=2");
$visitors_today    = db_count($conn, "SELECT COUNT(*) AS c FROM visitors_logs WHERE DATE(date_visited)=CURDATE()");

// ── Payroll status breakdown ────────────────────────────────────
$pay_new        = db_count($conn, "SELECT COUNT(*) AS c FROM payroll WHERE status=0");
$pay_calculated = db_count($conn, "SELECT COUNT(*) AS c FROM payroll WHERE status=1");
$pay_locked     = db_count($conn, "SELECT COUNT(*) AS c FROM payroll WHERE status=2");

// ── Employee by payroll type ────────────────────────────────────
$emp_weekly  = db_count($conn, "SELECT COUNT(*) AS c FROM employee WHERE weekly_payroll=1 AND status=1");
$emp_monthly = db_count($conn, "SELECT COUNT(*) AS c FROM employee WHERE weekly_payroll=0 AND status=1");

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
    SELECT p.*
    FROM payroll p
    ORDER BY p.id DESC LIMIT 6
");

// ── Recent DTR uploads ──────────────────────────────────────────
$recent_dtr = $conn->query("
    SELECT d.*, s.branch_name, s.branch_code, u.name AS uploader
    FROM DTR d
    LEFT JOIN branches s ON d.branch_id = s.id
    LEFT JOIN users u ON d.uploaded_by = u.id
    ORDER BY d.id DESC LIMIT 6
");
?>
<style>
    .dash-stat { border-top:3px solid #009688; border-radius:6px; background:#fff; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 4px rgba(57,75,124,.07); transition:box-shadow .2s; }
    .dash-stat:hover { box-shadow:0 4px 16px rgba(57,75,124,.13); }
    .dash-stat .ds-icon { width:46px; height:46px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
    .dash-stat .ds-val { font-size:24px; font-weight:800; color:#009688; line-height:1; }
    .dash-stat .ds-lbl { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.4px; margin-top:3px; }
    .dash-stat .ds-sub { font-size:11px; color:#aaa; margin-top:2px; }
    .dash-section-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#009688; margin-bottom:10px; display:flex; align-items:center; gap:6px; }
    .pay-status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }
    #data-table thead th,
    #dtr-recent-table thead th { background-color:#009688 !important; border-color:#2d3d66 !important; color:#fff !important; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-dashboard-line me-2" style="color:#009688;"></i>Dashboard</h4>
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
                        <div class="ds-icon" style="background:#eef0f8;"><i class="ri-group-line" style="color:#009688;"></i></div>
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

          
        </div>
    </div>
</div>

<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<script>
(function () {
    var primary = '#009688';

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
