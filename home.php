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
$total_positions   = db_count($conn, "SELECT COUNT(*) AS c FROM position");
$total_users       = db_count($conn, "SELECT COUNT(*) AS c FROM users WHERE role!=1 AND status=1");
$total_payrolls    = db_count($conn, "SELECT COUNT(*) AS c FROM payroll");
$pending_dtr       = db_count($conn, "SELECT COUNT(*) AS c FROM dtr WHERE status=1");
$approved_dtr      = db_count($conn, "SELECT COUNT(*) AS c FROM dtr WHERE status=2");

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
    FROM dtr d
    LEFT JOIN branches s ON d.branch_id = s.id
    LEFT JOIN users u ON d.uploaded_by = u.id
    ORDER BY d.id DESC LIMIT 6
");
?>
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
        align-items: stretch;
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid #e8eff4;
        border-radius: 18px;
        padding: 24px;
        min-height: 170px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .stat-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.09);
    }
    .stat-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }
    .stat-card__icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .stat-card__content {
        display: grid;
        gap: 8px;
        min-width: 0;
    }
    .stat-card__value {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.03em;
        margin: 0;
        color: #0f766e;
        line-height: 1;
    }
    .stat-card__label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .16em;
        color: #64748b;
        margin: 0;
    }
    .stat-card__meta {
        font-size: 0.84rem;
        color: #94a3b8;
        margin: 0;
    }
    .stat-card--green { border-top: 4px solid #0f766e; }
    .stat-card--purple { border-top: 4px solid #6d28d9; }
    .stat-card--gold { border-top: 4px solid #f59e0b; }
    .stat-card--teal { border-top: 4px solid #0ea5e9; }
    .stat-card--red { border-top: 4px solid #dc2626; }
    .stat-card--blue { border-top: 4px solid #2563eb; }
    .stat-card__icon--green { background: #ecfdf5; color: #0f766e; }
    .stat-card__icon--purple { background: #f5f3ff; color: #6d28d9; }
    .stat-card__icon--gold { background: #ffedd5; color: #b45309; }
    .stat-card__icon--teal { background: #eff6ff; color: #0ea5e9; }
    .stat-card__icon--red { background: #fee2e2; color: #dc2626; }
    .stat-card__icon--blue { background: #e0f2fe; color: #2563eb; }
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
            <div class="dashboard-grid">
                <div class="stat-card stat-card--green">
                    <div class="stat-card__top">
                        <div class="stat-card__content">
                            <p class="stat-card__value"><?= $total_employees ?></p>
                            <p class="stat-card__label">Employees</p>
                        </div>
                        <div class="stat-card__icon stat-card__icon--green"><i class="ri-group-line"></i></div>
                    </div>
                    <p class="stat-card__meta"><?= $total_inactive ?> inactive</p>
                </div>
                <div class="stat-card stat-card--blue">
                    <div class="stat-card__top">
                        <div class="stat-card__content">
                            <p class="stat-card__value"><?= $total_sites ?></p>
                            <p class="stat-card__label">Active Branches</p>
                        </div>
                        <div class="stat-card__icon stat-card__icon--blue"><i class="ri-map-pin-2-line"></i></div>
                    </div>
                    <p class="stat-card__meta">Available branches</p>
                </div>
                <div class="stat-card stat-card--purple">
                    <div class="stat-card__top">
                        <div class="stat-card__content">
                            <p class="stat-card__value"><?= $total_payrolls ?></p>
                            <p class="stat-card__label">Payrolls</p>
                        </div>
                        <div class="stat-card__icon stat-card__icon--purple"><i class="ri-money-dollar-circle-line"></i></div>
                    </div>
                    <p class="stat-card__meta"><?= $pay_locked ?> locked</p>
                </div>
                <div class="stat-card stat-card--gold">
                    <div class="stat-card__top">
                        <div class="stat-card__content">
                            <p class="stat-card__value"><?= $pending_dtr ?></p>
                            <p class="stat-card__label">Pending DTR</p>
                        </div>
                        <div class="stat-card__icon stat-card__icon--gold"><i class="ri-time-line"></i></div>
                    </div>
                    <p class="stat-card__meta"><?= $approved_dtr ?> approved</p>
                </div>
                <div class="stat-card stat-card--teal">
                    <div class="stat-card__top">
                        <div class="stat-card__content">
                            <p class="stat-card__value"><?= $total_users ?></p>
                            <p class="stat-card__label">Active Users</p>
                        </div>
                        <div class="stat-card__icon stat-card__icon--teal"><i class="ri-shield-user-line"></i></div>
                    </div>
                    <p class="stat-card__meta"><?= $total_positions ?> positions</p>
                </div>
            </div>

          
        </div>
    </div>
</div>

