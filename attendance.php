<?php
// Backward-compat: read URL params for initial filter state
$init_from         = isset($_GET['from'])        ? htmlspecialchars($_GET['from'])  : '';
$init_to           = isset($_GET['to'])          ? htmlspecialchars($_GET['to'])    : '';
$init_employee_ids = isset($_GET['employee_id']) ? implode(',', array_map('intval', (array)$_GET['employee_id'])) : '';
$init_site_id      = isset($_GET['site_id'])     ? intval($_GET['site_id'])         : '';
?>
<style>
    .att-stat-box { flex:1; min-width:120px; border:1px solid #d0d7ee; border-radius:4px; background:#eef0f8; padding:9px 12px; display:flex; align-items:center; gap:10px; }
    .att-stat-box .ico { font-size:22px; color:#394b7c; flex-shrink:0; }
    .att-stat-box .val { font-size:17px; font-weight:700; color:#394b7c; font-family:'Segoe UI',monospace; line-height:1.1; }
    .att-stat-box .lbl { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:.3px; margin-top:1px; }
    .att-stats-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
    .att-filter-bar { background:#394b7c; color:#fff; border-radius:4px; padding:10px 16px; margin-bottom:10px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .att-filter-bar .lbl { font-size:12px; opacity:.8; }
    .att-filter-bar .val { font-weight:700; font-size:13px; }
    .att-site-badge { background:#394b7c; color:#fff; padding:2px 7px; border-radius:3px; font-size:11px; font-weight:700; display:inline-block; margin-bottom:2px; }
    .att-site-name { font-size:12px; font-weight:600; }
    .att-date-main { font-weight:700; color:#394b7c; font-size:13px; }
    .att-emp-id { font-family:monospace; color:#394b7c; font-size:11px; }
    .att-pill { padding:3px 9px; border-radius:5px; font-size:12px; font-weight:600; display:inline-block; }
    .att-pill-work { background:#d4edda; color:#155724; }
    .att-pill-ot   { background:#fff3cd; color:#856404; }
    .att-pill-ut   { background:#d1ecf1; color:#0c5460; }
    .att-pill-late { background:#f8d7da; color:#721c24; }
    .att-log-bio   { background:#394b7c; color:#fff; font-size:10px; }
    .att-log-manual{ background:#dc3545; color:#fff; font-size:10px; }
    #attendance-table thead th { background-color:#394b7c !important; border-color:#2d3d66 !important; color:#fff !important; }
    .att-empty { text-align:center; padding:3.5rem 1rem 3rem; background:#f7f8fc; border-radius:8px; border:2px dashed #c5cde8; }
    .att-empty .att-empty-icon { width:64px; height:64px; border-radius:50%; background:#eef0f8; border:2px solid #d0d7ee; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
    .att-empty .att-empty-icon i { font-size:28px; color:#394b7c; opacity:.6; }
    .att-empty h6 { color:#394b7c; font-weight:700; margin-bottom:6px; font-size:15px; }
    .att-empty p { color:#888; font-size:12px; margin-bottom:14px; }
    .att-empty .att-empty-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 16px; border-radius:4px; background:#394b7c; color:#fff; font-size:12px; font-weight:600; text-decoration:none; border:none; cursor:pointer; }
    .att-empty .att-empty-btn:hover { background:#2d3d66; color:#fff; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-calendar-check-line me-2 text-primary"></i>Attendance Record</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Attendance Record</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #394b7c;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-calendar-check-line me-2" style="color:#394b7c;"></i>Attendance Records
                        </h4>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-filter" style="display:none!important;">
                                <i class="ri-close-line me-1"></i>Clear Filter
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-filter" style="background:#394b7c;border-color:#394b7c;">
                                <i class="ri-filter-3-line me-1"></i>Filter Records
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filter Summary Bar (hidden until filter applied) -->
                        <div class="att-filter-bar" id="att-filter-bar" style="display:none;">
                            <i class="ri-filter-3-line" style="font-size:16px;opacity:.8;"></i>
                            <div>
                                <div class="lbl">Date Range</div>
                                <div class="val" id="filter-date-label">—</div>
                            </div>
                            <div style="height:30px;width:1px;background:rgba(255,255,255,.3);"></div>
                            <div>
                                <div class="lbl">Employees</div>
                                <div class="val" id="filter-emp-label">—</div>
                            </div>
                            <div style="height:30px;width:1px;background:rgba(255,255,255,.3);"></div>
                            <div>
                                <div class="lbl">Site</div>
                                <div class="val" id="filter-site-label">All Sites</div>
                            </div>
                            <button type="button" class="btn btn-sm ms-auto" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);"
                                data-bs-toggle="modal" data-bs-target="#modal-filter">
                                <i class="ri-edit-2-line me-1"></i>Modify
                            </button>
                        </div>

                        <!-- Stats Row -->
                        <div class="att-stats-row" id="att-stats-row" style="display:none;">
                            <div class="att-stat-box">
                                <div class="ico"><i class="ri-file-list-line"></i></div>
                                <div><div class="val" id="stat-total">0</div><div class="lbl">Total Records</div></div>
                            </div>
                            <div class="att-stat-box">
                                <div class="ico"><i class="ri-time-line"></i></div>
                                <div><div class="val" id="stat-hours">0</div><div class="lbl">Total Hours</div></div>
                            </div>
                            <div class="att-stat-box" style="border-color:#ffc10740;background:#fffdf0;">
                                <div class="ico" style="color:#856404;"><i class="ri-flashlight-line"></i></div>
                                <div><div class="val" id="stat-ot" style="color:#856404;">0</div><div class="lbl">Overtime Hrs</div></div>
                            </div>
                            <div class="att-stat-box" style="border-color:#17a2b840;background:#f0fbfc;">
                                <div class="ico" style="color:#0c5460;"><i class="ri-timer-line"></i></div>
                                <div><div class="val" id="stat-ut" style="color:#0c5460;">0m</div><div class="lbl">Avg Undertime</div></div>
                            </div>
                            <div class="att-stat-box" style="border-color:#dc354540;background:#fdf0f1;">
                                <div class="ico" style="color:#721c24;"><i class="ri-alarm-warning-line"></i></div>
                                <div><div class="val" id="stat-late" style="color:#721c24;">0m</div><div class="lbl">Avg Late</div></div>
                            </div>
                            <div class="att-stat-box" style="border-color:#6c757d40;background:#f8f9fa;">
                                <div class="ico" style="color:#6c757d;"><i class="ri-group-line"></i></div>
                                <div><div class="val" id="stat-emps" style="color:#6c757d;">0</div><div class="lbl">Employees</div></div>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="attendance-table" class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:110px;"><i class="ri-calendar-line me-1"></i>Date</th>
                                        <th style="width:190px;"><i class="ri-user-line me-1"></i>Employee</th>
                                        <th><i class="ri-building-line me-1"></i>Site</th>
                                        <th class="text-center" style="width:95px;"><i class="ri-time-line me-1"></i>Work Hrs</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-flashlight-line me-1"></i>OT</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-timer-line me-1"></i>Undertime</th>
                                        <th class="text-center" style="width:80px;"><i class="ri-alarm-warning-line me-1"></i>Late</th>
                                        <th style="width:130px;"><i class="ri-history-line me-1"></i>Time Logs</th>
                                        <th class="text-center" style="width:95px;"><i class="ri-checkbox-circle-line me-1"></i>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'component/add_attendance.php'; ?>

<script>
// Plain JS only — jQuery not yet available at this point
var attFilter = {
    from:         '<?= $init_from ?>',
    to:           '<?= $init_to ?>',
    employee_ids: '<?= $init_employee_ids ?>',
    site_id:      '<?= $init_site_id ?>',
    emp_label:    '',
    site_label:   '',
};
</script>
