<?php
if (!isset($_GET['id']) || !isset($_GET['device_id']) || !isset($_GET['site_id'])) {
    header("HTTP/1.1 405 Unauthorized");
    echo "Data not available";
    exit;
};

$id =  base64_decode($_GET['id']);
$device_id =  base64_decode($_GET['device_id']);
$site_id =  base64_decode($_GET['site_id']);
$timekeeper_name = base64_decode($_GET['timekeeper_name']);

$query = "SELECT DTR.*,sites.site_code, sites.site_name, employer_name FROM DTR  
        LEFT JOIN sites ON sites.id = DTR.site_id  
        LEFT JOIN employers  ON sites.employer_id = employers.id  
        WHERE DTR.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$dtr = $result->fetch_assoc();

if (!$dtr) {
    header("HTTP/1.1 405 Unauthorized");
    echo "Data not available";
    exit;
}

function stringToArrayBySpaces($string)
{
    $split_array = preg_split('/\s{7}+/', $string, -1, PREG_SPLIT_NO_EMPTY);
    return $split_array;
}

function getDataBySpaces($dataString)
{
    $splitData = [];
    $currentElement = "";

    foreach (str_split($dataString) as $char) {
        if (ctype_space($char)) {
            if ($currentElement) {
                $splitData[] = $currentElement;
                $currentElement = "";
            }
        } else {
            $currentElement .= $char;
        }
    }

    if ($currentElement) {
        $splitData[] = $currentElement;
    }

    return $splitData;
}

$decoded_data = base64_decode(explode(",", $dtr['file'])[1]);
$result_array = stringToArrayBySpaces($decoded_data);
$is_duplicate = false;

// Fetch attendance data grouped by date and employee
$query = $conn->query("SELECT  
                        a.*, 
                        e.employee_no, 
                        e.lastname, 
                        e.firstname, 
                        e.middlename,  
                        d.name as department, 
                        p.name as position,
                        DATE(a.date_time) as attendance_date
                    FROM DTR_details a 
                    INNER JOIN employee e ON a.employee_id = e.id 
                    LEFT JOIN department d ON e.department_id = d.id 
                    LEFT JOIN position p ON e.position_id = p.id  
                    WHERE a.ddtr_id = $id 
                    ORDER BY a.date_time ASC");

$groupedData = [];
$employeeTotals = [];
$dateTotals = [];
$grandTotals = [
    'work_hours' => 0,
    'overtime' => 0,
    'undertime' => 0,
    'late' => 0
];

while ($row = $query->fetch_assoc()) {
    $date = $row['attendance_date'];
    $employeeId = $row['employee_id'];

    // Initialize date group if not exists
    if (!isset($groupedData[$date])) {
        $groupedData[$date] = [];
        $dateTotals[$date] = [
            'work_hours' => 0,
            'overtime' => 0,
            'undertime' => 0,
            'late' => 0
        ];
    }

    // Initialize employee group if not exists
    if (!isset($groupedData[$date][$employeeId])) {
        $groupedData[$date][$employeeId] = [
            'employee_info' => $row,
            'entries' => []
        ];

        // Initialize employee totals if not exists
        if (!isset($employeeTotals[$employeeId])) {
            $employeeTotals[$employeeId] = [
                'employee_info' => $row,
                'work_hours' => 0,
                'overtime' => 0,
                'undertime' => 0,
                'late' => 0
            ];
        }
    }

    // Add entry to the group
    $groupedData[$date][$employeeId]['entries'][] = $row;

    // Update totals
    $workHours = floatval($row['work_hours']);
    $overtime = floatval($row['overtime']);
    $undertime = floatval($row['undertime']);
    $late = floatval($row['late']);

    $employeeTotals[$employeeId]['work_hours'] += $workHours;
    $employeeTotals[$employeeId]['overtime'] += $overtime;
    $employeeTotals[$employeeId]['undertime'] += $undertime;
    $employeeTotals[$employeeId]['late'] += $late;

    $dateTotals[$date]['work_hours'] += $workHours;
    $dateTotals[$date]['overtime'] += $overtime;
    $dateTotals[$date]['undertime'] += $undertime;
    $dateTotals[$date]['late'] += $late;

    $grandTotals['work_hours'] += $workHours;
    $grandTotals['overtime'] += $overtime;
    $grandTotals['undertime'] += $undertime;
    $grandTotals['late'] += $late;
}
?>

<link rel="stylesheet" href="assets2/css/my-style.css">
<style>
/* ── DTR Details — Excel-style overrides ── */
#print-section { display: none; }

/* Stat boxes row */
.dtr-stats-row { display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap; }
.dtr-stat-box {
    flex:1; min-width:130px;
    border:1px solid #c6e0b4; border-radius:3px; background:#fff;
    padding:8px 12px; display:flex; align-items:center; gap:10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.dtr-stat-box .stat-icon {
    width:36px; height:36px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:17px; flex-shrink:0;
}
.dtr-stat-box .stat-val { font-size:18px; font-weight:700; line-height:1.1; font-family:'Segoe UI',Arial,sans-serif; }
.dtr-stat-box .stat-lbl { font-size:10px; color:#666; text-transform:uppercase; letter-spacing:0.5px; }

/* Date group banner */
#table-1 tbody tr.date-separator td {
    background:#217346 !important; color:#fff !important;
    border:1px solid #1a5c38 !important; padding:8px 14px !important;
}
#table-1 tbody tr.date-separator td h6 { color:#fff !important; margin:0; }
#table-1 tbody tr.date-separator td small,
#table-1 tbody tr.date-separator td .stat-label,
#table-1 tbody tr.date-separator td .text-muted { color:rgba(255,255,255,0.8) !important; }
#table-1 tbody tr.date-separator td .text-primary,
#table-1 tbody tr.date-separator td .text-success,
#table-1 tbody tr.date-separator td .stat-value,
#table-1 tbody tr.date-separator td .fw-bold { color:#fff !important; }
#table-1 tbody tr.date-separator td .date-icon { background:rgba(255,255,255,0.15) !important; border-radius:50%; }

/* Employee sub-header */
#table-1 tbody tr.employee-header td {
    background:#e2efda !important; border:1px solid #c6e0b4 !important; padding:6px 10px !important;
}
#table-1 tbody tr.employee-header td h6 { color:#1b5e20 !important; }
#table-1 tbody tr.employee-header td .avatar-md { background:#217346 !important; }
#table-1 tbody tr.employee-header td .text-primary { color:#2e7d32 !important; }
#table-1 tbody tr.employee-header td .total-value { color:#1b5e20 !important; }

/* Duplicate */
#table-1 tbody tr.duplicate-entry td { background:#fff5f5 !important; border-left:3px solid #f06548 !important; }

/* Grand total */
#table-1 tbody tr.grand-total-row td {
    background:#217346 !important; color:#fff !important;
    font-weight:700 !important; font-size:13px !important;
    border:1px solid #1a5c38 !important;
    position:sticky; bottom:0; z-index:8;
    box-shadow:0 -2px 8px rgba(0,0,0,0.14);
}

/* bg-soft utilities */
.bg-soft-primary  { background:rgba(33,115,70,0.08) !important; }
.bg-soft-secondary{ background:rgba(108,117,125,0.08) !important; }
.bg-soft-success  { background:rgba(33,115,70,0.10) !important; }
.bg-soft-danger   { background:rgba(240,101,72,0.10) !important; }
.bg-soft-warning  { background:rgba(247,184,75,0.10) !important; }
.bg-soft-info     { background:rgba(80,165,241,0.10) !important; }
.bg-soft-light    { background:#f8f9fa !important; }

/* Editable fields */
.editable-field { display:flex; align-items:center; justify-content:center; gap:2px; }
.editable-field .form-control { border:1px solid #c6e0b4; border-radius:2px; transition:all 0.2s; }
.editable-field .form-control:focus { border-color:#217346; box-shadow:0 0 0 2px rgba(33,115,70,0.18); }
.logs-container { max-height:120px; overflow-y:auto; }
.log-entry { line-height:1.2; }
.stat-item, .total-item { min-width:60px; }
.stat-value, .total-value { font-size:1.05rem; }
.update-success { border-color:#217346 !important; background:rgba(33,115,70,0.1) !important; animation:pulse-ok 2s; }
@keyframes pulse-ok {
    0%   { box-shadow:0 0 0 0 rgba(33,115,70,0.6); }
    70%  { box-shadow:0 0 0 8px rgba(33,115,70,0); }
    100% { box-shadow:0 0 0 0 rgba(33,115,70,0); }
}

/* Print styles */
@media print {
    body * { visibility:hidden; margin:0; padding:0; }
    #print-section, #print-section * { visibility:visible; }
    #print-section {
        display:block !important; position:absolute; left:0; top:0;
        width:100%; padding:20px; font-family:Arial,sans-serif; font-size:12px; background:#fff;
    }
    .main-content,.page-content,.container-fluid,.card,.btn,.search-box { display:none !important; }
    .print-header { text-align:center; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #333; }
    .print-header h2 { font-size:18px; margin-bottom:10px; color:#333; }
    .print-info { margin-bottom:15px; }
    .print-info p { margin:2px 0; font-size:11px; }
    .print-summary { display:flex; justify-content:center; gap:20px; margin:15px 0; padding:10px; background:#f5f5f5; border-radius:4px; }
    .summary-item { text-align:center; }
    .summary-item .label { display:block; font-size:10px; color:#666; }
    .summary-item .value { display:block; font-size:12px; font-weight:bold; color:#333; }
    .print-table { width:100%; border-collapse:collapse; margin:15px 0; font-size:10px; }
    .print-table th { background:#f8f9fa; border:1px solid #ddd; padding:6px 4px; text-align:center; font-weight:bold; }
    .print-table td { border:1px solid #ddd; padding:5px 3px; text-align:left; }
    .print-table .text-center { text-align:center; }
    .print-table .text-end { text-align:right; }
    .date-separator { background:#e9ecef; page-break-before:auto; page-break-after:avoid; }
    .date-header { padding:8px 5px; font-size:11px; }
    .employee-row { page-break-inside:avoid; }
    .employee-row td { padding:4px 3px; }
    .employee-name { font-weight:500; }
    .grand-total { background:#d1ecf1; font-weight:bold; }
    .grand-total td { padding:8px 3px; }
    .print-footer { margin-top:20px; padding-top:10px; border-top:1px solid #ddd; text-align:center; font-size:10px; color:#666; }
}
</style>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <div class="page-title-enhanced">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">DTR Details</h4>
                                    <?php if ($dtr['status'] === 0): ?>
                                        <span class="payroll-status-badge bg-warning text-dark"><i class="ri-time-line me-1"></i>Open</span>
                                    <?php elseif ($dtr['status'] === 1): ?>
                                        <span class="payroll-status-badge bg-info text-white"><i class="ri-check-double-line me-1"></i>Pending Approval</span>
                                    <?php else: ?>
                                        <span class="payroll-status-badge bg-success text-white"><i class="ri-checkbox-circle-line me-1"></i>Approved</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <i class="ri-calendar-2-line me-1"></i><?= date('M d', strtotime($dtr['date_from'])) ?> &ndash; <?= date('M d, Y', strtotime($dtr['date_to'])) ?>
                                    &nbsp;&bull;&nbsp;<i class="ri-building-line me-1"></i><?= htmlspecialchars($dtr['site_name']) ?> (<?= htmlspecialchars($dtr['site_code']) ?>)
                                    &nbsp;&bull;&nbsp;<i class="ri-user-2-line me-1"></i><?= htmlspecialchars($dtr['employer_name']) ?>
                                    &nbsp;&bull;&nbsp;<i class="ri-shield-user-line me-1"></i><?= htmlspecialchars($timekeeper_name) ?>
                                </small>
                            </div>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">DTR Details</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <!-- print section (hidden on screen, used by printDTRTable()) -->
                <!-- Add this CSS for print styling -->
                <style>
                    @media print {
                        body * {
                            visibility: hidden;
                            margin: 0;
                            padding: 0;
                        }

                        #print-section,
                        #print-section * {
                            visibility: visible;
                        }

                        #print-section {
                            display: block !important;
                            position: absolute;
                            left: 0;
                            top: 0;
                            width: 100%;
                            padding: 20px;
                            font-family: Arial, sans-serif;
                            font-size: 12px;
                            background: white;
                        }

                        /* Hide all other elements */
                        .main-content,
                        .page-content,
                        .container-fluid,
                        .card,
                        .btn,
                        .search-box {
                            display: none !important;
                        }

                        /* Print header styling */
                        .print-header {
                            text-align: center;
                            margin-bottom: 20px;
                            padding-bottom: 15px;
                            border-bottom: 2px solid #333;
                        }

                        .print-header h2 {
                            font-size: 18px;
                            margin-bottom: 10px;
                            color: #333;
                        }

                        .print-info {
                            margin-bottom: 15px;
                        }

                        .print-info p {
                            margin: 2px 0;
                            font-size: 11px;
                        }

                        .print-summary {
                            display: flex;
                            justify-content: center;
                            gap: 20px;
                            margin: 15px 0;
                            padding: 10px;
                            background-color: #f5f5f5;
                            border-radius: 4px;
                        }

                        .summary-item {
                            text-align: center;
                        }

                        .summary-item .label {
                            display: block;
                            font-size: 10px;
                            color: #666;
                        }

                        .summary-item .value {
                            display: block;
                            font-size: 12px;
                            font-weight: bold;
                            color: #333;
                        }

                        /* Print table styling */
                        .print-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 15px 0;
                            font-size: 10px;
                        }

                        .print-table th {
                            background-color: #f8f9fa;
                            border: 1px solid #ddd;
                            padding: 6px 4px;
                            text-align: center;
                            font-weight: bold;
                        }

                        .print-table td {
                            border: 1px solid #ddd;
                            padding: 5px 3px;
                            text-align: left;
                        }

                        .print-table .text-center {
                            text-align: center;
                        }

                        .print-table .text-end {
                            text-align: right;
                        }

                        /* Date separator styling */
                        .date-separator {
                            background-color: #e9ecef;
                        }

                        .date-header {
                            padding: 8px 5px;
                            font-size: 11px;
                        }

                        .employee-count {
                            color: #666;
                            font-weight: normal;
                        }

                        .date-totals {
                            float: right;
                            font-weight: normal;
                            color: #666;
                        }

                        /* Employee row styling */
                        .employee-row td {
                            padding: 4px 3px;
                        }

                        .employee-name {
                            font-weight: 500;
                        }

                        /* Grand total styling */
                        .grand-total {
                            background-color: #d1ecf1;
                            font-weight: bold;
                        }

                        .grand-total td {
                            padding: 8px 3px;
                        }

                        /* Print footer */
                        .print-footer {
                            margin-top: 20px;
                            padding-top: 10px;
                            border-top: 1px solid #ddd;
                            text-align: center;
                            font-size: 10px;
                            color: #666;
                        }

                        /* Page break control */
                        .date-separator {
                            page-break-before: auto;
                            page-break-after: avoid;
                        }

                        .employee-row {
                            page-break-inside: avoid;
                        }
                    }

                    /* Screen styles for print button */
                    .btn-outline-info {
                        border-color: #17a2b8;
                        color: #17a2b8;
                    }

                    .btn-outline-info:hover {
                        background-color: #17a2b8;
                        color: white;
                    }

                    /* Ensure print section is hidden on screen */
                    #print-section {
                        display: none;
                    }
                </style>

                <!-- Add this hidden print table section at the bottom of your page, before the scripts -->
                <div id="print-section" style="display: none;">
                    <div class="print-header">
                        <h2>Daily Time Record (DTR) Details</h2>
                        <div class="print-info">
                            <p><strong>Period:</strong> <?= date('F d', strtotime($dtr['date_from'])) ?> - <?= date('F d, Y', strtotime($dtr['date_to'])) ?></p>
                            <p><strong>Site:</strong> <?= $dtr['site_name'] ?> (<?= $dtr['site_code'] ?>)</p>
                            <p><strong>Employer:</strong> <?= $dtr['employer_name'] ?></p>
                            <p><strong>Timekeeper:</strong> <?= $timekeeper_name ?></p>
                        </div>
                        <div class="print-summary">
                            <div class="summary-item">
                                <span class="label">Total Work Hours:</span>
                                <span class="value"><?= number_format($grandTotals['work_hours'], 2) ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Total Overtime:</span>
                                <span class="value"><?= number_format($grandTotals['overtime'], 2) ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Total Undertime:</span>
                                <span class="value"><?= number_format($grandTotals['undertime'], 2) ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Total Late:</span>
                                <span class="value"><?= number_format($grandTotals['late'], 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee Name</th>
                                <th>Position</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Hours Worked</th>
                                <th>Overtime</th>
                                <th>Undertime</th>
                                <th>Late</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $currentDate = null;
                            $dateEmployeeCount = [];

                            // First pass to count employees per date
                            foreach ($groupedData as $date => $employees) {
                                $dateEmployeeCount[$date] = count($employees);
                            }

                            foreach ($groupedData as $date => $employees):
                                $dateTotal = $dateTotals[$date];
                                $isNewDate = $currentDate !== $date;
                                $currentDate = $date;
                            ?>
                                <?php if ($isNewDate): ?>
                                    <tr class="date-separator">
                                        <td colspan="9" class="date-header">
                                            <strong><?= date("F j, Y", strtotime($date)) ?></strong>
                                            <span class="employee-count">(<?= $dateEmployeeCount[$date] ?> employees)</span>
                                            <span class="date-totals">
                                                Hours: <?= number_format($dateTotal['work_hours'], 2) ?> |
                                                OT: <?= number_format($dateTotal['overtime'], 2) ?> |
                                                UT: <?= number_format($dateTotal['undertime'], 2) ?> |
                                                Late: <?= number_format($dateTotal['late'], 2) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php
                                $employeeCounter = 0;
                                foreach ($employees as $employeeId => $employeeData):
                                    $employeeTotal = $employeeTotals[$employeeId];
                                    $employeeCounter++;
                                    $entries = $employeeData['entries'];
                                    $firstEntry = reset($entries);
                                    $lastEntry = end($entries);

                                    // Get time in and time out from logs
                                    $timeIn = '';
                                    $timeOut = '';
                                    if (!empty($firstEntry['logs'])) {
                                        $logs = json_decode($firstEntry['logs'], true);
                                        if (is_array($logs) && count($logs) > 0) {
                                            $timeIn = date("g:i A", strtotime($logs[0]['dateTime']));
                                            $timeOut = count($logs) > 1 ? date("g:i A", strtotime(end($logs)['dateTime'])) : '';
                                        }
                                    }
                                ?>
                                    <tr class="employee-row">
                                        <td><?= date("m/d/Y", strtotime($date)) ?></td>
                                        <td class="employee-name">
                                            <?= $firstEntry['lastname'] ?>, <?= $firstEntry['firstname'] ?> <?= $firstEntry['middlename'] ?>
                                        </td>
                                        <td><?= $firstEntry['position'] ?></td>
                                        <td class="text-center"><?= $timeIn ?></td>
                                        <td class="text-center"><?= $timeOut ?></td>
                                        <td class="text-center"><?= number_format($employeeTotal['work_hours'], 2) ?></td>
                                        <td class="text-center"><?= number_format($employeeTotal['overtime'], 2) ?></td>
                                        <td class="text-center"><?= number_format($employeeTotal['undertime'], 2) ?></td>
                                        <td class="text-center"><?= number_format($employeeTotal['late'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>

                            <!-- Grand Total Row -->
                            <tr class="grand-total">
                                <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-center"><strong><?= number_format($grandTotals['work_hours'], 2) ?></strong></td>
                                <td class="text-center"><strong><?= number_format($grandTotals['overtime'], 2) ?></strong></td>
                                <td class="text-center"><strong><?= number_format($grandTotals['undertime'], 2) ?></strong></td>
                                <td class="text-center"><strong><?= number_format($grandTotals['late'], 2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="print-footer">
                        <p>Generated on: <?= date('F j, Y g:i A') ?></p>
                    </div>
                </div>
                <!-- Attendance List — Excel panel -->
                <div class="xl-panel" id="dtrDiv">
                    <div class="xl-ribbon">
                        <span class="xl-ribbon-title">
                            <i class="ri-time-line" style="color:#217346;"></i> Attendance List
                        </span>
                        <div class="xl-ribbon-actions">
                            <div class="xl-search-wrap">
                                <i class="ri-search-2-line"></i>
                                <input id="myInput" type="text" placeholder="Search...">
                            </div>
                            <div class="xl-ribbon-sep"></div>
                            <button data-toggle="tooltip" title="Print" onclick="printDTRTable()" class="xl-btn"><i class="ri-printer-line"></i> Print</button>
                            <?php if ($dtr['status'] === 0 && $login_role !== 6): ?>
                                <div class="xl-ribbon-sep"></div>
                                <button data-toggle="tooltip" title="Add Attendance" onclick="addSchedule(<?= $id ?>)" class="xl-btn"><i class="ri-add-line"></i> Add Attendance</button>
                            <?php endif; ?>
                            <?php if ($dtr['status'] === 1): ?>
                                <div class="xl-ribbon-sep"></div>
                                <button data-toggle="tooltip" title="Approve DTR" <?= $is_duplicate ? 'disabled' : '' ?> onclick="approveDtr(<?= $id ?>)" class="xl-btn xl-btn-save"><i class="ri-checkbox-circle-line"></i> Approve</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="xl-panel-body">
                        <!-- Stat boxes -->
                        <div class="dtr-stats-row">
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#e2efda;color:#217346;"><i class="ri-time-line"></i></div>
                                <div class="stat-info">
                                    <div class="stat-val" style="color:#217346;"><?= number_format($grandTotals['work_hours'], 2) ?></div>
                                    <div class="stat-lbl">Work Hours</div>
                                </div>
                            </div>
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#fff8e1;color:#f7b84b;"><i class="ri-sun-line"></i></div>
                                <div class="stat-info">
                                    <div class="stat-val" style="color:#c98a00;"><?= number_format($grandTotals['overtime'], 2) ?></div>
                                    <div class="stat-lbl">Overtime</div>
                                </div>
                            </div>
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#e3f2fd;color:#50a5f1;"><i class="ri-arrow-down-line"></i></div>
                                <div class="stat-info">
                                    <div class="stat-val" style="color:#1565c0;"><?= number_format($grandTotals['undertime'], 2) ?></div>
                                    <div class="stat-lbl">Undertime</div>
                                </div>
                            </div>
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#fce4ec;color:#f06548;"><i class="ri-alarm-warning-line"></i></div>
                                <div class="stat-info">
                                    <div class="stat-val" style="color:#c62828;"><?= number_format($grandTotals['late'], 2) ?></div>
                                    <div class="stat-lbl">Late (min)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Add this modal HTML at the bottom of your page, before the scripts -->
                        <div class="modal fade" id="employeeNotesModal" tabindex="-1" aria-labelledby="employeeNotesModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="employeeNotesModalLabel">
                                            <i class="ri-sticky-note-line me-2"></i>Employee Notes
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="employee-info mb-3">
                                            <h6 id="modalEmployeeName" class="text-primary mb-1"></h6>
                                            <p id="modalEmployeePosition" class="text-muted small mb-0"></p>
                                            <p id="modalEmployeeDate" class="text-muted small mb-0"></p>
                                        </div>
                                        <div class="notes-content">
                                            <label class="form-label fw-semibold">Notes:</label>
                                            <div id="modalNotesContent" class="p-3 bg-light rounded border">
                                                <!-- Notes content will be inserted here -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive2" id="dtr-table-responsive">
                            <table cellspacing="0" id="table-1">
                                <thead>
                                    <tr>
                                        <th class="text-center primary-header">Date</th>
                                        <th class="text-center primary-header">Employee</th>
                                        <th class="text-center primary-header">Position</th>
                                        <th class="text-center success-header">Time In</th>
                                        <th class="text-center danger-header">Time Out</th>
                                        <th class="text-center success-header">Hours Worked</th>
                                        <th class="text-center info-header">Overtime</th>
                                        <th class="text-center info-header">Undertime</th>
                                        <th class="text-center danger-header">Late</th>
                                        <th class="text-center primary-header">Logs</th>
                                        <th class="text-center primary-header">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $dateCounter = 0;
                                    foreach ($groupedData as $date => $employees):
                                        $dateCounter++;
                                        $dateTotal = $dateTotals[$date];
                                    ?>
                                        <!-- Date Separator -->
                                        <tr class="date-separator bg-light">
                                            <td colspan="11" class="p-3 border-bottom">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="date-icon bg-primary rounded-circle p-2 me-3">
                                                            <i class="ri-calendar-2-line text-white fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 text-primary fw-bold"><?= date("l, F j, Y", strtotime($date)) ?></h6>
                                                            <div class="d-flex gap-4">
                                                                <small class="text-muted">
                                                                    <i class="ri-user-line me-1"></i><?= count($employees) ?> employees
                                                                </small>
                                                                <small class="text-success">
                                                                    <i class="ri-time-line me-1"></i><?= number_format($dateTotal['work_hours'], 2) ?> total hours
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="date-stats">
                                                        <div class="d-flex gap-3">
                                                            <div class="stat-item text-center">
                                                                <div class="stat-value text-warning fw-bold"><?= number_format($dateTotal['overtime'], 2) ?></div>
                                                                <div class="stat-label text-muted small">OT Hours</div>
                                                            </div>
                                                            <div class="stat-item text-center">
                                                                <div class="stat-value text-info fw-bold"><?= number_format($dateTotal['undertime'], 2) ?></div>
                                                                <div class="stat-label text-muted small">Undertime</div>
                                                            </div>
                                                            <div class="stat-item text-center">
                                                                <div class="stat-value text-danger fw-bold"><?= number_format($dateTotal['late'], 2) ?></div>
                                                                <div class="stat-label text-muted small">Late (min)</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <?php
                                        $employeeCounter = 0;
                                        foreach ($employees as $employeeId => $employeeData):
                                            $employeeCounter++;
                                            $employeeTotal = $employeeTotals[$employeeId];
                                            $entries = $employeeData['entries'];

                                            // Calculate totals for this employee on this date
                                            $employeeDateWorkHours = 0;
                                            $employeeDateOvertime = 0;
                                            $employeeDateUndertime = 0;
                                            $employeeDateLate = 0;

                                            foreach ($entries as $entry) {
                                                $employeeDateWorkHours += floatval($entry['work_hours']);
                                                $employeeDateOvertime += floatval($entry['overtime']);
                                                $employeeDateUndertime += floatval($entry['undertime']);
                                                $employeeDateLate += floatval($entry['late']);
                                            }
                                        ?>
                                            <!-- Employee Header -->
                                            <tr class="employee-header bg-soft-light">
                                                <td colspan="11" class="p-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="employee-avatar me-3">
                                                                <div class="avatar-md bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                                                    <span class="text-white fw-bold fs-6">
                                                                        <?= strtoupper(substr($employeeData['employee_info']['firstname'], 0, 1)) ?><?= strtoupper(substr($employeeData['employee_info']['lastname'], 0, 1)) ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1 fw-semibold">
                                                                    <?= $employeeData['employee_info']['lastname'] ?>, <?= $employeeData['employee_info']['firstname'] ?> <?= $employeeData['employee_info']['middlename'] ?>
                                                                </h6>
                                                                <div class="d-flex gap-3 align-items-center">
                                                                    <small class="text-muted">
                                                                        <i class="ri-briefcase-line me-1"></i><?= $employeeData['employee_info']['position'] ?>
                                                                    </small>
                                                                    <small class="text-secondary">
                                                                        <i class="ri-file-list-line me-1"></i><?= count($entries) ?> attendance record(s)
                                                                    </small>
                                                                    <?php if (!empty($employeeData['employee_info']['notes'])): ?>
                                                                        <span class="badge bg-warning text-dark cursor-pointer"
                                                                            onclick="showEmployeeNotes(
                                                        '<?= htmlspecialchars($employeeData['employee_info']['lastname'] . ', ' . $employeeData['employee_info']['firstname'] . ' ' . $employeeData['employee_info']['middlename']) ?>',
                                                        '<?= htmlspecialchars($employeeData['employee_info']['position'] ?? 'N/A') ?>',
                                                        '<?= htmlspecialchars($date) ?>',
                                                        `<?= htmlspecialchars($employeeData['employee_info']['notes']) ?>`
                                                    )">
                                                                            <i class="ri-sticky-note-line me-1"></i>Has Notes
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="employee-totals">
                                                            <div class="d-flex gap-4">
                                                                <div class="total-item text-center">
                                                                    <div class="total-value text-success fw-bold"><?= number_format($employeeDateWorkHours, 2) ?></div>
                                                                    <div class="total-label text-muted small">Hours</div>
                                                                </div>
                                                                <div class="total-item text-center">
                                                                    <div class="total-value text-warning fw-bold"><?= number_format($employeeDateOvertime, 2) ?></div>
                                                                    <div class="total-label text-muted small">Overtime</div>
                                                                </div>
                                                                <div class="total-item text-center">
                                                                    <div class="total-value text-info fw-bold"><?= number_format($employeeDateUndertime, 2) ?></div>
                                                                    <div class="total-label text-muted small">Undertime</div>
                                                                </div>
                                                                <div class="total-item text-center">
                                                                    <div class="total-value text-danger fw-bold"><?= number_format($employeeDateLate, 2) ?></div>
                                                                    <div class="total-label text-muted small">Late</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Individual Entries -->
                                            <?php foreach ($entries as $row):
                                                $logs = json_decode($row['logs']);
                                                $logs = isset($logs) ? $logs : [];

                                                $date_check = date("Y-m-d", strtotime($row['date_time']));
                                                $employee_id = $row['employee_id'];

                                                $check_duplicate = $conn->query("SELECT DTR.*, timekeeper.name AS timekeeper_name, uploaded.name AS uploaded_by
                                    FROM DTR_details
                                    LEFT JOIN DTR ON DTR_details.ddtr_id = DTR.id
                                    LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id
                                    LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id 
                                    WHERE date_time = '$date_check'  
                                    AND employee_id = '$employee_id' 
                                    AND ddtr_id != '$id'  
                                    GROUP BY date_time");

                                                $timekeeper_name = '';
                                                $device_id2 = '';
                                                $status = '';
                                                $site_id2 = '';
                                                $id_dtr = '';
                                                $site_name = '';

                                                if ($check_duplicate->num_rows) {
                                                    $is_duplicate = true;
                                                    while ($row_check = $check_duplicate->fetch_assoc()) {
                                                        $timekeeper_name = $row_check['timekeeper_name'];
                                                        $device_id2 = $row_check['device_id'];
                                                        $status = $row_check['status'];
                                                        $site_id2 = $row_check['site_id'];
                                                        $id_dtr = $row_check['id'];
                                                        $site_name = $row_check['site_id'];
                                                    }
                                                } else {
                                                    $is_duplicate = false;
                                                }

                                                // Get time in and time out from logs
                                                $timeIn = '';
                                                $timeOut = '';
                                                if (!empty($logs) && count($logs) > 0) {
                                                    $timeIn = date("g:i A", strtotime($logs[0]->dateTime));
                                                    $timeOut = count($logs) > 1 ? date("g:i A", strtotime(end($logs)->dateTime)) : 'N/A';
                                                }
                                            ?>
                                                <tr class="attendance-entry <?= $is_duplicate ? 'duplicate-entry' : '' ?>">
                                                    <td class="align-middle">
                                                        <div class="text-center">
                                                            <div class="fw-semibold"><?= date("M j", strtotime($row['date_time'])) ?></div>
                                                            <small class="text-muted"><?= date("D", strtotime($row['date_time'])) ?></small>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <div class="avatar-xs bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                                                    <span class="text-primary fw-bold" style="font-size: 10px;">
                                                                        <?= strtoupper(substr($row['firstname'], 0, 1)) ?><?= strtoupper(substr($row['lastname'], 0, 1)) ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="fw-semibold text-truncate" style="max-width: 150px;">
                                                                    <?= $row['lastname'] ?>, <?= $row['firstname'] ?>
                                                                </div>
                                                                <small class="text-muted"><?= $row['employee_no'] ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge bg-soft-secondary text-dark"><?= $row['position'] ?></span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge bg-soft-success text-success"><?= $timeIn ?></span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge bg-soft-danger text-danger"><?= $timeOut ?></span>
                                                    </td>
                                                    <!-- In your PHP file -->
                                                    <!-- Hours Worked -->
                                                    <td width="120" class="text-center">
                                                        <?php if ($login_role !== 6) { ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['work_hours'] ?>"
                                                                    class="form-control form-control-sm text-center"
                                                                    style="width: 80px;">
                                                                <button class="btn btn-sm btn-outline-success ms-1 update-dtr-field"
                                                                    data-id="<?= $row['id'] ?>"
                                                                    data-field="work_hours">
                                                                    <i class="ri-save-line"></i>
                                                                </button>
                                                            </div>
                                                        <?php } else { ?>
                                                            <span class="fw-bold text-primary"><?= $row['work_hours'] ?></span>
                                                        <?php } ?>
                                                    </td>

                                                    <!-- Overtime -->
                                                    <td width="120" class="text-center">
                                                        <?php if ($login_role !== 6) { ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['overtime'] ?>"
                                                                    class="form-control form-control-sm text-center"
                                                                    style="width: 80px;">
                                                                <button class="btn btn-sm btn-outline-warning ms-1 update-dtr-field"
                                                                    data-id="<?= $row['id'] ?>"
                                                                    data-field="overtime">
                                                                    <i class="ri-save-line"></i>
                                                                </button>
                                                            </div>
                                                        <?php } else { ?>
                                                            <span class="fw-bold text-warning"><?= $row['overtime'] ?></span>
                                                        <?php } ?>
                                                    </td>

                                                    <!-- Undertime -->
                                                    <td width="120" class="text-center">
                                                        <?php if ($login_role !== 6) { ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['undertime'] ?>"
                                                                    class="form-control form-control-sm text-center"
                                                                    style="width: 80px;">
                                                                <button class="btn btn-sm btn-outline-info ms-1 update-dtr-field"
                                                                    data-id="<?= $row['id'] ?>"
                                                                    data-field="undertime">
                                                                    <i class="ri-save-line"></i>
                                                                </button>
                                                            </div>
                                                        <?php } else { ?>
                                                            <span class="fw-bold text-info"><?= $row['undertime'] ?></span>
                                                        <?php } ?>
                                                    </td>

                                                    <!-- Late -->
                                                    <td width="120" class="text-center">
                                                        <?php if ($login_role !== 6) { ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['late'] ?>"
                                                                    class="form-control form-control-sm text-center"
                                                                    style="width: 80px;">
                                                                <button class="btn btn-sm btn-outline-danger ms-1 update-dtr-field"
                                                                    data-id="<?= $row['id'] ?>"
                                                                    data-field="late">
                                                                    <i class="ri-save-line"></i>
                                                                </button>
                                                            </div>
                                                        <?php } else { ?>
                                                            <span class="fw-bold text-danger"><?= $row['late'] ?></span>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="logs-container">
                                                            <?php foreach ($logs as $log): ?>
                                                                <div class="log-entry mb-1">
                                                                    <?php if ($log->type === 'bio'): ?>
                                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                                            <i class="ri-fingerprint-line me-1"></i><?= date("g:i A", strtotime($log->dateTime)) ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                                                                            <i class="ri-edit-line me-1"></i><?= date("g:i A", strtotime($log->dateTime)) ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <?php if ($login_role !== 6): ?>
                                                                <button data-toggle="tooltip" title="Delete Attendance"
                                                                    onclick="deleteDTRLogs(<?= $row['id'] ?>)"
                                                                    class="btn btn-outline-danger">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            <?php endif; ?>

                                                            <?php if ($is_duplicate): ?>
                                                                <a data-toggle="tooltip" title="View Duplicate DTR"
                                                                    target="_blank"
                                                                    href="index.php?page=dtr-details&id=<?= base64_encode($id_dtr) ?>&timekeeper_name=<?= base64_encode($timekeeper_name) ?>&device_id=<?= base64_encode($device_id2) ?>&site_id=<?= base64_encode($site_id2) ?>&status=<?= base64_encode($status) ?>"
                                                                    class="btn btn-outline-warning">
                                                                    <i class="ri-alert-line"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>

                                    <!-- Grand Total Row -->
                                    <tr class="grand-total-row bg-primary text-white">
                                        <td colspan="5" class="text-end fw-bold fs-6">GRAND TOTAL</td>
                                        <td class="text-center fw-bold fs-6"><?= number_format($grandTotals['work_hours'], 2) ?></td>
                                        <td class="text-center fw-bold fs-6"><?= number_format($grandTotals['overtime'], 2) ?></td>
                                        <td class="text-center fw-bold fs-6"><?= number_format($grandTotals['undertime'], 2) ?></td>
                                        <td class="text-center fw-bold fs-6"><?= number_format($grandTotals['late'], 2) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div><!-- end xl-panel-body -->
                </div><!-- end xl-panel -->
            </div>
            <!-- end page title -->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
</div>
<?php include 'component/add_attendance.php'; ?>
<script src="assets/js/dtr-details.js"></script>
<script>
    function fitDtrTable() {
        const c = document.getElementById('dtr-table-responsive');
        if (!c) return;
        const top = c.getBoundingClientRect().top + window.scrollY;
        const available = window.innerHeight - (top - window.scrollY) - 24;
        c.style.height = Math.max(available, 200) + 'px';
    }
    document.addEventListener('DOMContentLoaded', fitDtrTable);
    window.addEventListener('resize', fitDtrTable);
</script>


<script>
    // Enhanced search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('myInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#table-1 tbody tr');
                let hasVisibleResults = false;

                rows.forEach(row => {
                    // Skip separator and total rows
                    if (row.classList.contains('date-separator') ||
                        row.classList.contains('employee-header') ||
                        row.classList.contains('grand-total-row')) {
                        return;
                    }

                    const text = row.textContent.toLowerCase();
                    if (filter === '' || text.includes(filter)) {
                        row.style.display = '';
                        hasVisibleResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide date separators and employee headers based on visible content
                updateGroupVisibility();
                showNoResultsMessage(!hasVisibleResults && filter !== '');
            });
        }
    });

    function updateGroupVisibility() {
        const dateSeparators = document.querySelectorAll('.date-separator');

        dateSeparators.forEach(separator => {
            const nextRows = getNextRowsUntil(separator, '.date-separator');
            const hasVisibleRows = Array.from(nextRows).some(row =>
                !row.classList.contains('employee-header') &&
                row.style.display !== 'none'
            );

            separator.style.display = hasVisibleRows ? '' : 'none';
        });
    }

    function getNextRowsUntil(element, selector) {
        const rows = [];
        let next = element.nextElementSibling;

        while (next && !next.matches(selector)) {
            rows.push(next);
            next = next.nextElementSibling;
        }

        return rows;
    }

    function showNoResultsMessage(show) {
        let noResultsRow = document.getElementById('no-results-message');

        if (show && !noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.id = 'no-results-message';
            noResultsRow.innerHTML = `
            <td colspan="11" class="text-center py-5 text-muted">
                <div class="empty-state">
                    <i class="ri-search-line display-4 text-muted mb-3"></i>
                    <h5>No results found</h5>
                    <p class="text-muted">Try adjusting your search terms</p>
                </div>
            </td>
        `;
            document.querySelector('#table-1 tbody').appendChild(noResultsRow);
        } else if (!show && noResultsRow) {
            noResultsRow.remove();
        }
    }
</script>
<script>
    // Add this JavaScript function for printing
    function printDTRTable() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        const printContent = document.getElementById('print-section').innerHTML;

        // Write the print content to the new window
        printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>DTR Details - <?= $dtr['site_name'] ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }
                
                .print-header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #333;
                }
                
                .print-header h2 {
                    font-size: 18px;
                    margin-bottom: 10px;
                    color: #333;
                }
                
                .print-info {
                    margin-bottom: 15px;
                }
                
                .print-info p {
                    margin: 2px 0;
                    font-size: 11px;
                }
                
                .print-summary {
                    display: flex;
                    justify-content: center;
                    gap: 20px;
                    margin: 15px 0;
                    padding: 10px;
                    background-color: #f5f5f5;
                    border-radius: 4px;
                }
                
                .summary-item {
                    text-align: center;
                }
                
                .summary-item .label {
                    display: block;
                    font-size: 10px;
                    color: #666;
                }
                
                .summary-item .value {
                    display: block;
                    font-size: 12px;
                    font-weight: bold;
                    color: #333;
                }
                
                .print-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                    font-size: 10px;
                }
                
                .print-table th {
                    background-color: #f8f9fa;
                    border: 1px solid #ddd;
                    padding: 6px 4px;
                    text-align: center;
                    font-weight: bold;
                }
                
                .print-table td {
                    border: 1px solid #ddd;
                    padding: 5px 3px;
                    text-align: left;
                }
                
                .print-table .text-center {
                    text-align: center;
                }
                
                .print-table .text-end {
                    text-align: right;
                }
                
                .date-separator {
                    background-color: #e9ecef;
                }
                
                .date-header {
                    padding: 8px 5px;
                    font-size: 11px;
                }
                
                .employee-count {
                    color: #666;
                    font-weight: normal;
                }
                
                .date-totals {
                    float: right;
                    font-weight: normal;
                    color: #666;
                }
                
                .employee-row td {
                    padding: 4px 3px;
                }
                
                .employee-name {
                    font-weight: 500;
                }
                
                .grand-total {
                    background-color: #d1ecf1;
                    font-weight: bold;
                }
                
                .grand-total td {
                    padding: 8px 3px;
                }
                
                .print-footer {
                    margin-top: 20px;
                    padding-top: 10px;
                    border-top: 1px solid #ddd;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                }
                
                @media print {
                    body {
                        margin: 0;
                        padding: 15px;
                    }
                    
                    .print-table {
                        font-size: 9px;
                    }
                }
            </style>
        </head>
        <body>
            ${printContent}
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() {
                        window.close();
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `);

        printWindow.document.close();
    }

    // Optional: Add keyboard shortcut for printing (Ctrl+P)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            printDTRTable();
        }
    });

    // Add this JavaScript function to handle the modal
    function showEmployeeNotes(employeeName, position, date, notes) {
        // Set modal content
        document.getElementById('modalEmployeeName').textContent = employeeName;
        document.getElementById('modalEmployeePosition').textContent = 'Position: ' + (position || 'N/A');
        document.getElementById('modalEmployeeDate').textContent = 'Date: ' + (date ? new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'N/A');

        // Format and set notes content
        const notesContent = document.getElementById('modalNotesContent');
        if (notes && notes.trim() !== '') {
            // Convert line breaks to <br> tags and preserve formatting
            const formattedNotes = notes.replace(/\n/g, '<br>');
            notesContent.innerHTML = formattedNotes;
            notesContent.classList.remove('text-muted', 'fst-italic');
        } else {
            notesContent.textContent = 'No notes available.';
            notesContent.classList.add('text-muted', 'fst-italic');
        }

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('employeeNotesModal'));
        modal.show();
    }

    // Optional: Add keyboard shortcut to close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('employeeNotesModal'));
            if (modal) {
                modal.hide();
            }
        }
    });
</script>