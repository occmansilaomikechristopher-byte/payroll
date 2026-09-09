<?php
if (!isset($_GET['id']) || !isset($_GET['device_id'])) {
    header("HTTP/1.1 405 Unauthorized");
    echo "Data not available";
    exit;
};

$id          = base64_decode($_GET['id']);
$device_id   = base64_decode($_GET['device_id']);
$branch_id   = base64_decode($_GET['branch_id'] ?? '');
$timekeeper_name = base64_decode($_GET['timekeeper_name'] ?? '');

$query = "SELECT dtr.*, branches.branch_code, branches.branch_name FROM dtr
    LEFT JOIN branches ON branches.id = dtr.branch_id
    WHERE dtr.id = ?";
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

$login_role = intval($_SESSION['login_role'] ?? 0);
$user_branch = intval($_SESSION['login_branch_id'] ?? 0);
if (!in_array($login_role, [1, 10], true) && $user_branch > 0 && intval($dtr['branch_id']) !== $user_branch) {
    header("HTTP/1.1 403 Forbidden");
    echo "You do not have access to this upload.";
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

function getDtrPrintTimes($entries)
{
    $times = [];

    foreach (array_values($entries) as $entryIndex => $entry) {
        $logs = json_decode($entry['logs'] ?? '', true);
        if (!is_array($logs)) {
            continue;
        }

        // A single log may be stored as an object instead of a list.
        if (isset($logs['dateTime']) || isset($logs['date_time'])) {
            $logs = [$logs];
        }

        $entryType = strtolower(trim((string)($entry['attendance_type'] ?? '')));
        foreach ($logs as $log) {
            if (!is_array($log)) {
                continue;
            }
            $dateTime = $log['dateTime'] ?? $log['date_time'] ?? '';
            $timestamp = $dateTime !== '' ? strtotime($dateTime) : false;
            if ($timestamp !== false) {
                $times[] = [
                    'timestamp' => $timestamp,
                    'type' => $entryType,
                    'index' => $entryIndex,
                ];
            }
        }
    }

    if (!$times) {
        return ['', ''];
    }

    usort($times, function ($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });

    $in = null;
    $out = null;
    foreach ($times as $time) {
        if (in_array($time['type'], ['in', '0'], true) && $in === null) {
            $in = $time;
        } elseif (in_array($time['type'], ['out', '1'], true) && $out === null) {
            $out = $time;
        }
    }

    // If types are missing or unreliable, chronological order is authoritative.
    $in = $in ?? $times[0];
    $out = $out ?? (count($times) > 1 ? $times[count($times) - 1] : null);

    return [
        date('g:i A', $in['timestamp']),
        $out ? date('g:i A', $out['timestamp']) : '',
    ];
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
                    FROM dtr_details a
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
#print-section { display: none; }

/* table header */
#table-1 thead th { background-color: #219688; color: #fff; border-color: #dddddd; }

/* DTR header: keep the header fixed vertically without freezing columns.
   The shared payroll table styles use a fixed 40px offset for column 2,
   which is narrower than this table's Date column and causes overlap. */
#dtr-table-responsive #table-1 thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 20 !important;
}
#dtr-table-responsive #table-1 thead tr:first-child th:nth-child(1),
#dtr-table-responsive #table-1 thead tr:first-child th:nth-child(2),
#dtr-table-responsive #table-1 tbody td:nth-child(1),
#dtr-table-responsive #table-1 tbody td:nth-child(2) {
    left: auto !important;
}
#dtr-table-responsive #table-1 tbody td:nth-child(1),
#dtr-table-responsive #table-1 tbody td:nth-child(2) {
    position: static !important;
    transform: none !important;
}

/* date group row */
#table-1 tbody tr.date-separator td {
    background: #e6f5f3 !important;
    color: #174f49 !important;
    padding: 7px 12px;
    font-weight: bold;
    border-top: 1px solid #9ed8d0 !important;
    border-bottom: 1px solid #9ed8d0 !important;
}
#table-1 tbody tr.date-separator td * { color: #174f49 !important; }
#table-1 tbody tr.date-separator td .dtr-date-ot { color: #8a5a00 !important; }
#table-1 tbody tr.date-separator td .dtr-date-late { color: #a32121 !important; }
.date-separator { cursor: pointer; user-select: none; }
.dtr-group-row.dtr-hidden { display: none !important; }

/* employee sub-header */
#table-1 tbody tr.employee-header td { background: #e6f5f3; border-top: 1px solid #cccccc; }

/* grand total */
#table-1 tbody tr.grand-total-row td { background: #219688; color: #fff; font-weight: bold; }

/* duplicate */
#table-1 tbody tr.duplicate-entry td { background: #fff5f5; border-left: 3px solid #f06548; }

/* simple chips */
.dtr-time-chip { display: inline-block; padding: 2px 6px; font-size: 11px; border: 1px solid #cccccc; border-radius: 3px; }
.dtr-time-chip.in  { background: #e6f5f3; color: #219688; }
.dtr-time-chip.out { background: #fce4ec; color: #c62828; }
.dtr-time-chip.na  { background: #f5f5f5; color: #888; }
.dtr-log-chip { display: inline-block; padding: 1px 5px; font-size: 10px; border: 1px solid #cccccc; border-radius: 3px; margin-bottom: 1px; }
.dtr-log-chip.bio    { background: #e6f5f3; color: #219688; }
.dtr-log-chip.manual { background: #fff8e1; color: #c98a00; }

/* stat boxes */
.dtr-stats-row { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
.dtr-stat-box { flex: 1; min-width: 120px; border: 1px solid #dddddd; border-top: 3px solid #219688; background: #fff; padding: 10px 14px; }
.dtr-stat-box .stat-val { font-size: 18px; font-weight: bold; }
.dtr-stat-box .stat-lbl { font-size: 11px; color: #666; }

/* editable */
.editable-field { display: flex; align-items: center; gap: 2px; justify-content: center; }
.editable-field .form-control { font-size: 11px; padding: 2px 5px; height: 24px; border: 1px solid #cccccc; }
.update-dtr-field { width: 22px; height: 22px; padding: 0; font-size: 11px; cursor: pointer; border: 1px solid #cccccc; background: #f5f5f5; color: #219688; }

/* misc */
.logs-container { max-height: 90px; overflow-y: auto; }
.dtr-emp-init { width: 26px; height: 26px; border-radius: 50%; background: #219688; color: #fff; font-size: 10px; font-weight: bold; display: inline-flex; align-items: center; justify-content: center; }
.dtr-emp-name { font-size: 12px; font-weight: 600; color: #333; }
.dtr-emp-pos  { font-size: 11px; color: #666; }
.dtr-tot-item { display: inline-flex; flex-direction: column; align-items: center; min-width: 38px; }
.dtr-tot-item .tot-lbl { font-size: 9px; color: #888; text-transform: uppercase; }
.dtr-tot-item .tot-val { font-size: 12px; font-weight: bold; color: #219688; }
.dtr-tot-item.ot .tot-val { color: #c98a00; }
.dtr-tot-item.ut .tot-val { color: #1565c0; }
.dtr-tot-item.late .tot-val { color: #c62828; }
.dtr-emp-totals { display: flex; gap: 8px; }
.dtr-date-totals { display: flex; gap: 12px; font-size: 11px; }
.dtr-emp-count { margin-left: 8px; font-weight: 600; color: #5b7772 !important; }
.dtr-chevron { font-size: 14px; }

/* print */
@media print {
    body * { visibility: hidden; }
    #print-section, #print-section * { visibility: visible; }
    #print-section { display: block !important; position: absolute; left: 0; top: 0; width: 100%; padding: 20px; font-size: 12px; }
    .print-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .print-table th, .print-table td { border: 1px solid #ddd; padding: 4px; }
    .print-table th { background: #f5f5f5; font-weight: bold; }
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
                                    &nbsp;&bull;&nbsp;<i class="ri-building-line me-1"></i><?= htmlspecialchars($dtr['branch_name'] ?? '—') ?> (<?= htmlspecialchars($dtr['branch_code'] ?? '') ?>)
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
                            <p><strong>Branch:</strong> <?= htmlspecialchars($dtr['branch_name'] ?? '—') ?> (<?= htmlspecialchars($dtr['branch_code'] ?? '') ?>)</p>
                            <p><strong>Cashier:</strong> <?= $timekeeper_name ?></p>
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
                                    [$timeIn, $timeOut] = getDtrPrintTimes($entries);
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
                <!-- Attendance List panel -->
                <div class="xl-panel" id="dtrDiv">
                    <div class="xl-ribbon">
                        <span class="xl-ribbon-title">
                            <i class="ri-time-line"></i> DTR Attendance
                        </span>
                        <div class="xl-ribbon-actions">
                            <div class="xl-search-wrap">
                                <i class="ri-search-2-line"></i>
                                <input id="myInput" type="text" placeholder="Search employee...">
                            </div>
                            <div class="xl-ribbon-sep"></div>
                            <button onclick="toggleAllGroups(true)"  class="xl-btn"><i class="ri-expand-up-down-line"></i> Expand All</button>
                            <button onclick="toggleAllGroups(false)" class="xl-btn"><i class="ri-contract-up-down-line"></i> Collapse All</button>
                            <div class="xl-ribbon-sep"></div>
                            <button onclick="printDTRTable()" class="xl-btn"><i class="ri-printer-line"></i> Print</button>
                            <?php if ($dtr['status'] === 0 && $login_role !== 6): ?>
                                <div class="xl-ribbon-sep"></div>
                                <button onclick="addSchedule(<?= $id ?>)" class="xl-btn"><i class="ri-add-line"></i> Add</button>
                            <?php endif; ?>
                            <?php if ($dtr['status'] === 1): ?>
                                <div class="xl-ribbon-sep"></div>
                                <button <?= $is_duplicate ? 'disabled' : '' ?> onclick="approveDtr(<?= $id ?>)" class="xl-btn xl-btn-save"><i class="ri-checkbox-circle-line"></i> Approve</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="xl-panel-body">
                        <!-- Summary stat cards -->
                        <div class="dtr-stats-row">
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#e6f5f3;color:#219688;"><i class="ri-time-line"></i></div>
                                <div>
                                    <div class="stat-val" style="color:#219688;"><?= number_format($grandTotals['work_hours'], 2) ?></div>
                                    <div class="stat-lbl">Work Hours</div>
                                </div>
                            </div>
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#fff8e1;color:#f7b84b;"><i class="ri-sun-line"></i></div>
                                <div>
                                    <div class="stat-val" style="color:#c98a00;"><?= number_format($grandTotals['overtime'], 2) ?></div>
                                    <div class="stat-lbl">Overtime</div>
                                </div>
                            </div>
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#e3f2fd;color:#50a5f1;"><i class="ri-arrow-down-line"></i></div>
                                <div>
                                    <div class="stat-val" style="color:#1565c0;"><?= number_format($grandTotals['undertime'], 2) ?></div>
                                    <div class="stat-lbl">Undertime</div>
                                </div>
                            </div>
                            <div class="dtr-stat-box">
                                <div class="stat-icon" style="background:#fce4ec;color:#f06548;"><i class="ri-alarm-warning-line"></i></div>
                                <div>
                                    <div class="stat-val" style="color:#c62828;"><?= number_format($grandTotals['late'], 2) ?></div>
                                    <div class="stat-lbl">Late (min)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Notes Modal -->
                        <div class="modal fade" id="employeeNotesModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header" style="border-bottom:2px solid #219688;">
                                        <h5 class="modal-title" style="color:#219688;"><i class="ri-sticky-note-line me-2"></i>Employee Notes</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h6 id="modalEmployeeName" class="mb-1" style="color:#219688;font-weight:600;"></h6>
                                        <p id="modalEmployeePosition" class="text-muted small mb-0"></p>
                                        <p id="modalEmployeeDate" class="text-muted small mb-2"></p>
                                        <label class="form-label fw-semibold small">Notes:</label>
                                        <div id="modalNotesContent" class="p-3 bg-light rounded border small"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
                                        <th class="text-center primary-header">Time In</th>
                                        <th class="text-center primary-header">Time Out</th>
                                        <th class="text-center primary-header">Hours</th>
                                        <th class="text-center primary-header">OT</th>
                                        <th class="text-center primary-header">Undertime</th>
                                        <th class="text-center primary-header">Late</th>
                                        <th class="text-center primary-header">Logs</th>
                                        <th class="text-center primary-header">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($groupedData as $date => $employees):
                                        $dateTotal = $dateTotals[$date];
                                        $dateKey   = 'dg-' . md5($date);
                                    ?>
                                        <!-- Date header (collapsible) -->
                                        <tr class="date-separator" data-toggle-group="<?= $dateKey ?>">
                                            <td colspan="11">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-arrow-down-s-line dtr-chevron me-2"></i>
                                                        <span class="dtr-date-label"><?= date("l, F j, Y", strtotime($date)) ?></span>
                                                        <span class="dtr-emp-count"><?= count($employees) ?> employees</span>
                                                    </div>
                                                    <div class="dtr-date-totals">
                                                        <span><i class="ri-time-line me-1"></i><?= number_format($dateTotal['work_hours'], 2) ?> hrs</span>
                                                        <span class="dtr-date-ot">OT <?= number_format($dateTotal['overtime'], 2) ?></span>
                                                        <span class="dtr-date-late">Late <?= number_format($dateTotal['late'], 2) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <?php
                                        foreach ($employees as $employeeId => $employeeData):
                                            $entries  = $employeeData['entries'];
                                            $displayEntries = array_values($entries);
                                            [$employeeTimeIn, $employeeTimeOut] = getDtrPrintTimes($displayEntries);
                                            $empWH = $empOT = $empUT = $empLate = 0;
                                            foreach ($entries as $entry) {
                                                $empWH   += floatval($entry['work_hours']);
                                                $empOT   += floatval($entry['overtime']);
                                                $empUT   += floatval($entry['undertime']);
                                                $empLate += floatval($entry['late']);
                                            }
                                            $initials = strtoupper(
                                                substr($employeeData['employee_info']['firstname'], 0, 1) .
                                                substr($employeeData['employee_info']['lastname'],  0, 1)
                                            );
                                        ?>
                                            <!-- Employee sub-header -->
                                            <tr class="employee-header dtr-group-row" data-group="<?= $dateKey ?>">
                                                <td colspan="11">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="dtr-emp-init"><?= $initials ?></div>
                                                            <div>
                                                                <span class="dtr-emp-name"><?= $employeeData['employee_info']['lastname'] ?>, <?= $employeeData['employee_info']['firstname'] ?> <?= $employeeData['employee_info']['middlename'] ?></span>
                                                                <span class="dtr-emp-pos ms-2"><?= $employeeData['employee_info']['position'] ?></span>
                                                                <?php if (!empty($employeeData['employee_info']['notes'])): ?>
                                                                    <span class="badge bg-warning text-dark ms-1" style="font-size:10px;cursor:pointer;"
                                                                        onclick="showEmployeeNotes('<?= htmlspecialchars($employeeData['employee_info']['lastname'].', '.$employeeData['employee_info']['firstname'].' '.$employeeData['employee_info']['middlename']) ?>','<?= htmlspecialchars($employeeData['employee_info']['position'] ?? '') ?>','<?= htmlspecialchars($date) ?>',`<?= htmlspecialchars($employeeData['employee_info']['notes']) ?>`)">
                                                                        <i class="ri-sticky-note-line"></i> Notes
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="dtr-emp-totals">
                                                            <div class="dtr-tot-item"><span class="tot-lbl">Hrs</span><span class="tot-val"><?= number_format($empWH, 2) ?></span></div>
                                                            <div class="dtr-tot-item ot"><span class="tot-lbl">OT</span><span class="tot-val"><?= number_format($empOT, 2) ?></span></div>
                                                            <div class="dtr-tot-item ut"><span class="tot-lbl">UT</span><span class="tot-val"><?= number_format($empUT, 2) ?></span></div>
                                                            <div class="dtr-tot-item late"><span class="tot-lbl">Late</span><span class="tot-val"><?= number_format($empLate, 2) ?></span></div>
                                                        </div>
                                                        <div>
                                                            <a href="dtr-export.php?ddtr=<?= base64_encode($id) ?>&employee_id=<?= base64_encode($employeeId) ?>" class="btn btn-sm btn-outline-secondary">
                                                                <i class="ri-download-line"></i> Export DTR
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Individual entries -->
                                            <?php foreach ($displayEntries as $entryIndex => $row):
                                                $logs = json_decode($row['logs']);
                                                $logs = isset($logs) ? $logs : [];
                                                $date_check  = date("Y-m-d", strtotime($row['date_time']));
                                                $employee_id = $row['employee_id'];
                                                $check_duplicate = $conn->query("SELECT dtr.*, timekeeper.name AS timekeeper_name, uploaded.name AS uploaded_by
                                                    FROM dtr_details
                                                    LEFT JOIN dtr ON dtr_details.ddtr_id = dtr.id
                                                    LEFT JOIN users AS timekeeper ON dtr.timekeeper_id = timekeeper.id
                                                    LEFT JOIN users AS uploaded ON dtr.uploaded_by = uploaded.id
                                                    WHERE date_time = '$date_check' AND employee_id = '$employee_id' AND ddtr_id != '$id'
                                                    GROUP BY date_time");
                                                $timekeeper_name = $device_id2 = $status = $branch_id2 = $id_dtr = $branch_name2 = '';
                                                if ($check_duplicate->num_rows) {
                                                    $is_duplicate = true;
                                                    while ($row_check = $check_duplicate->fetch_assoc()) {
                                                        $timekeeper_name = $row_check['timekeeper_name'];
                                                        $device_id2  = $row_check['device_id'];
                                                        $status      = $row_check['status'];
                                                        $branch_id2  = $row_check['branch_id'];
                                                        $id_dtr      = $row_check['id'];
                                                        $branch_name2 = $row_check['branch_id'];
                                                    }
                                                } else {
                                                    $is_duplicate = false;
                                                }
                                                $timeIn = $entryIndex === 0 ? $employeeTimeIn : '';
                                                $timeOut = $entryIndex === 0 ? $employeeTimeOut : '';
                                            ?>
                                                <tr class="attendance-entry dtr-group-row <?= $is_duplicate ? 'duplicate-entry' : '' ?>" data-group="<?= $dateKey ?>">
                                                    <td class="text-center">
                                                        <div class="fw-semibold" style="font-size:11px;"><?= date("M j", strtotime($row['date_time'])) ?></div>
                                                        <div class="text-muted" style="font-size:10px;"><?= date("D", strtotime($row['date_time'])) ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold" style="font-size:11px;"><?= $row['lastname'] ?>, <?= $row['firstname'] ?></div>
                                                        <div class="text-muted" style="font-size:10px;"><?= $row['employee_no'] ?></div>
                                                    </td>
                                                    <td><span class="dtr-pos-chip"><?= $row['position'] ?></span></td>
                                                    <td class="text-center"><span class="dtr-time-chip in"><?= $timeIn ?: '—' ?></span></td>
                                                    <td class="text-center"><span class="dtr-time-chip <?= ($timeOut === 'N/A' || !$timeOut) ? 'na' : 'out' ?>"><?= $timeOut ?: '—' ?></span></td>
                                                    <td class="text-center">
                                                        <?php if ($login_role !== 6): ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['work_hours'] ?>" class="form-control form-control-sm text-center" style="width:68px;">
                                                                <button type="button" class="update-dtr-field" data-id="<?= $row['id'] ?>" data-field="work_hours"><i class="ri-save-line"></i></button>
                                                            </div>
                                                        <?php else: ?><span><?= $row['work_hours'] ?></span><?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($login_role !== 6): ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['overtime'] ?>" class="form-control form-control-sm text-center" style="width:68px;">
                                                                <button type="button" class="update-dtr-field" data-id="<?= $row['id'] ?>" data-field="overtime"><i class="ri-save-line"></i></button>
                                                            </div>
                                                        <?php else: ?><span><?= $row['overtime'] ?></span><?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($login_role !== 6): ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['undertime'] ?>" class="form-control form-control-sm text-center" style="width:68px;">
                                                                <button type="button" class="update-dtr-field" data-id="<?= $row['id'] ?>" data-field="undertime"><i class="ri-save-line"></i></button>
                                                            </div>
                                                        <?php else: ?><span><?= $row['undertime'] ?></span><?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($login_role !== 6): ?>
                                                            <div class="editable-field">
                                                                <input type="text" value="<?= $row['late'] ?>" class="form-control form-control-sm text-center" style="width:68px;">
                                                                <button type="button" class="update-dtr-field" data-id="<?= $row['id'] ?>" data-field="late"><i class="ri-save-line"></i></button>
                                                            </div>
                                                        <?php else: ?><span><?= $row['late'] ?></span><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="logs-container">
                                                            <?php foreach ($logs as $log): ?>
                                                                <div class="log-entry">
                                                                    <?php if ($log->type === 'bio'): ?>
                                                                        <span class="dtr-log-chip bio"><i class="ri-fingerprint-line"></i><?= date("g:i A", strtotime($log->dateTime)) ?></span>
                                                                    <?php else: ?>
                                                                        <span class="dtr-log-chip manual"><i class="ri-edit-line"></i><?= date("g:i A", strtotime($log->dateTime)) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <?php if ($login_role !== 6): ?>
                                                                <button title="Delete" onclick="deleteDTRLogs(<?= $row['id'] ?>)" class="btn btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                                            <?php endif; ?>
                                                            <?php if ($is_duplicate): ?>
                                                                <a target="_blank" title="Duplicate"
                                                                    href="index.php?page=dtr-details&id=<?= base64_encode($id_dtr) ?>&timekeeper_name=<?= base64_encode($timekeeper_name) ?>&device_id=<?= base64_encode($device_id2) ?>&branch_id=<?= base64_encode($branch_id2) ?>&status=<?= base64_encode($status) ?>"
                                                                    class="btn btn-outline-warning"><i class="ri-alert-line"></i></a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>

                                    <!-- Grand Total Row -->
                                    <tr class="grand-total-row">
                                        <td colspan="5" class="text-end">GRAND TOTAL</td>
                                        <td class="text-center"><?= number_format($grandTotals['work_hours'], 2) ?></td>
                                        <td class="text-center"><?= number_format($grandTotals['overtime'], 2) ?></td>
                                        <td class="text-center"><?= number_format($grandTotals['undertime'], 2) ?></td>
                                        <td class="text-center"><?= number_format($grandTotals['late'], 2) ?></td>
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
<script>
    // Fit table height to viewport
    function fitDtrTable() {
        const c = document.getElementById('dtr-table-responsive');
        if (!c) return;
        const top = c.getBoundingClientRect().top + window.scrollY;
        const available = window.innerHeight - (top - window.scrollY) - 24;
        c.style.height = Math.max(available, 200) + 'px';
    }
    document.addEventListener('DOMContentLoaded', fitDtrTable);
    window.addEventListener('resize', fitDtrTable);

    // ── Collapsible date groups ──
    function setGroupVisible(key, visible) {
        document.querySelectorAll('.dtr-group-row[data-group="' + key + '"]').forEach(function (row) {
            row.style.display = visible ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.date-separator').forEach(function (sep) {
            sep.addEventListener('click', function () {
                var key = sep.getAttribute('data-toggle-group');
                var isCollapsed = sep.classList.toggle('collapsed');
                setGroupVisible(key, !isCollapsed);
            });
        });
    });

    function toggleAllGroups(expand) {
        document.querySelectorAll('.date-separator').forEach(function (sep) {
            var key = sep.getAttribute('data-toggle-group');
            if (expand) { sep.classList.remove('collapsed'); } else { sep.classList.add('collapsed'); }
            setGroupVisible(key, expand);
        });
    }
</script>


<script>
    // ── Search ──
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('myInput');
        if (!searchInput) return;
        searchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase().trim();
            document.querySelectorAll('#table-1 tbody tr.attendance-entry').forEach(function (row) {
                if (row.classList.contains('dtr-hidden')) return;
                row.style.display = (filter === '' || row.textContent.toLowerCase().includes(filter)) ? '' : 'none';
            });
            // Show/hide emp headers and date rows based on visible entries
            document.querySelectorAll('.date-separator').forEach(function (sep) {
                var key = sep.getAttribute('data-toggle-group');
                var hasVisible = Array.from(document.querySelectorAll('.attendance-entry[data-group="' + key + '"]'))
                    .some(function (r) { return r.style.display !== 'none'; });
                sep.style.display = hasVisible ? '' : 'none';
                document.querySelectorAll('.employee-header[data-group="' + key + '"]').forEach(function (h) {
                    h.style.display = hasVisible ? '' : 'none';
                });
            });
        });
    });
</script>
<script>
    function printDTRTable() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        const printContent = document.getElementById('print-section').innerHTML;

        // Write the print content to the new window
        printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>DTR Details - <?= htmlspecialchars($dtr['branch_name'] ?? '') ?></title>
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