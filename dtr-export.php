<?php
require 'db_connect.php';

if (!isset($_GET['ddtr']) || !isset($_GET['employee_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Missing parameters.';
    exit;
}

$ddtrId = base64_decode($_GET['ddtr']);
$employeeId = base64_decode($_GET['employee_id']);

if ($ddtrId === false || $employeeId === false) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid parameters.';
    exit;
}

$ddtrId = intval($ddtrId);
$employeeId = intval($employeeId);

// Get DTR master info
$query = $conn->prepare("SELECT DTR.*, branches.branch_code, branches.branch_name FROM DTR LEFT JOIN branches ON branches.id = DTR.branch_id WHERE DTR.id = ? LIMIT 1");
$query->bind_param('i', $ddtrId);
$query->execute();
$result = $query->get_result();
$dtr = $result->fetch_assoc();
$query->close();

if (!$dtr) {
    header('HTTP/1.1 404 Not Found');
    echo 'DTR not found.';
    exit;
}

// Get employee info and all attendance entries for this DTR
$detailQuery = $conn->prepare(
    "SELECT a.*, e.employee_no, e.lastname, e.firstname, e.middlename, d.name AS department, p.name AS position
        FROM DTR_details a
        INNER JOIN employee e ON a.employee_id = e.id
        LEFT JOIN department d ON e.department_id = d.id
        LEFT JOIN position p ON e.position_id = p.id
        WHERE a.ddtr_id = ? AND a.employee_id = ?
        ORDER BY a.date_time ASC"
);
$detailQuery->bind_param('ii', $ddtrId, $employeeId);
$detailQuery->execute();
$detailsResult = $detailQuery->get_result();

if ($detailsResult->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    echo 'No DTR details found for this employee.';
    exit;
}

$employee = $detailsResult->fetch_assoc();
$detailsResult->data_seek(0);

$employeeName = trim($employee['lastname'] . ', ' . $employee['firstname'] . ' ' . $employee['middlename']);
$filenameDateRange = date('Ymd', strtotime($dtr['date_from'])) . '_to_' . date('Ymd', strtotime($dtr['date_to']));
$filename = sprintf('DTR_%s_%s_%s.txt', preg_replace('/[^A-Za-z0-9_-]/', '_', $employee['employee_no']), $employeeName ?: 'employee', $filenameDateRange);
$filename = str_replace(' ', '_', $filename);

$lines = [];
$lines[] = 'Payroll DTR Export';
$lines[] = '=====================';
$lines[] = 'Branch: ' . ($dtr['branch_name'] ?? '—') . ' (' . ($dtr['branch_code'] ?? '—') . ')';
$lines[] = 'Period: ' . date('F d, Y', strtotime($dtr['date_from'])) . ' - ' . date('F d, Y', strtotime($dtr['date_to']));
$lines[] = 'Employee: ' . $employeeName;
$lines[] = 'Employee No: ' . ($employee['employee_no'] ?? '—');
$lines[] = 'Department: ' . ($employee['department'] ?? '—');
$lines[] = 'Position: ' . ($employee['position'] ?? '—');
$lines[] = 'Generated: ' . date('F j, Y g:i A');
$lines[] = '';
$lines[] = 'Date Time | Type | Hours | Overtime | Undertime | Late | Logs';
$lines[] = str_repeat('-', 100);

$totalWork = 0.0;
$totalOvertime = 0.0;
$totalUndertime = 0.0;
$totalLate = 0.0;

while ($row = $detailsResult->fetch_assoc()) {
    $logText = '';
    $logs = null;

    if (!empty($row['logs'])) {
        $decoded = json_decode($row['logs'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $parts = [];
            foreach ($decoded as $item) {
                if (isset($item['dateTime'])) {
                    $parts[] = date('Y-m-d H:i:s', strtotime($item['dateTime'])) . ' (' . ($item['type'] ?? 'log') . ')';
                }
            }
            $logText = implode(' ; ', $parts);
        } else {
            $logText = trim($row['logs']);
        }
    }

    $workHours = floatval($row['work_hours']);
    $overtime = floatval($row['overtime']);
    $undertime = floatval($row['undertime']);
    $late = floatval($row['late']);

    $totalWork += $workHours;
    $totalOvertime += $overtime;
    $totalUndertime += $undertime;
    $totalLate += $late;

    $lines[] = sprintf(
        "%s | %s | %.2f | %.2f | %.2f | %.2f | %s",
        date('Y-m-d H:i:s', strtotime($row['date_time'])),
        trim($row['attendance_type'] ?? 'N/A'),
        $workHours,
        $overtime,
        $undertime,
        $late,
        $logText
    );
}

$lines[] = '';
$lines[] = 'Summary';
$lines[] = '-------';
$lines[] = sprintf('Total Hours: %.2f', $totalWork);
$lines[] = sprintf('Total Overtime: %.2f', $totalOvertime);
$lines[] = sprintf('Total Undertime: %.2f', $totalUndertime);
$lines[] = sprintf('Total Late: %.2f', $totalLate);

$content = implode("\n", $lines);

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . strlen($content));

echo $content;
exit;
