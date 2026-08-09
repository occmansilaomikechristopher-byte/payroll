<?php
include 'db_connect.php';

$draw       = intval($_POST['draw'] ?? 1);
$start      = intval($_POST['start'] ?? 0);
$length     = intval($_POST['length'] ?? 25);
$search     = $_POST['search']['value'] ?? '';
$orderCol   = intval($_POST['order'][0]['column'] ?? 0);
$orderDir   = in_array(strtolower($_POST['order'][0]['dir'] ?? 'desc'), ['asc','desc'])
              ? strtolower($_POST['order'][0]['dir'])
              : 'desc';

$from         = mysqli_real_escape_string($conn, $_POST['from'] ?? '');
$to           = mysqli_real_escape_string($conn, $_POST['to'] ?? '');
$employee_ids = preg_replace('/[^0-9,]/', '', $_POST['employee_ids'] ?? '');
$site_id      = intval($_POST['site_id'] ?? 0);

// Orderable columns
$cols = [
    0 => 'a.date_time',
    1 => 'e.lastname',
    2 => 's.site_name',
    3 => 'a.work_hours',
    4 => 'a.overtime',
    5 => 'a.undertime',
    6 => 'a.late',
];
$orderColumn = $cols[$orderCol] ?? 'a.date_time';

// Base WHERE
$where = '1=1';
if ($from && $to) {
    $where .= " AND a.date_time BETWEEN '$from' AND '$to 23:59:59'";
}
if ($employee_ids) {
    $where .= " AND a.employee_id IN ($employee_ids)";
}
if ($site_id > 0) {
    $where .= " AND d.site_id = $site_id";
}
if ($search) {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (e.firstname LIKE '%$s%' OR e.lastname LIKE '%$s%'
                OR CONCAT(e.lastname,', ',e.firstname) LIKE '%$s%'
                OR s.site_name LIKE '%$s%' OR s.site_code LIKE '%$s%')";
}

$base_join = "FROM DTR_details a
              INNER JOIN employee e ON a.employee_id = e.id
              INNER JOIN DTR d ON a.ddtr_id = d.id
              LEFT JOIN sites s ON d.site_id = s.id";

// Stats (filtered, no pagination)
$stats_sql = "SELECT
    COUNT(*) AS total_records,
    COALESCE(SUM(a.work_hours),0) AS total_hours,
    COALESCE(SUM(a.overtime),0) AS total_overtime,
    COALESCE(AVG(a.undertime),0) AS avg_undertime,
    COALESCE(AVG(a.late),0) AS avg_late,
    COUNT(DISTINCT a.employee_id) AS unique_employees
$base_join WHERE $where";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Total filtered count
$filtered_count = (int) $conn->query("SELECT COUNT(*) AS c $base_join WHERE $where")->fetch_assoc()['c'];

// Total unfiltered count
$total_count = (int) $conn->query("SELECT COUNT(*) AS c FROM DTR_details")->fetch_assoc()['c'];

// Data query
$data_sql = "SELECT a.*, e.employee_no, e.lastname, e.firstname, e.middlename,
             d.status AS dtr_status, d.site_id,
             s.site_code, s.site_name, s.site_address
             $base_join
             WHERE $where
             ORDER BY $orderColumn $orderDir
             LIMIT $start, $length";

$result = $conn->query($data_sql);
$data   = [];

while ($row = $result->fetch_assoc()) {
    $logs    = json_decode($row['logs']);
    $logHtml = '';
    if ($logs && count($logs)) {
        foreach ($logs as $log) {
            $badge    = $log->type === 'bio'
                ? '<span class="badge att-log-bio">Bio</span>'
                : '<span class="badge att-log-manual">Manual</span>';
            $logHtml .= '<div class="d-flex align-items-center mb-1">' . $badge
                      . '<small class="ms-1 fw-semibold">'
                      . date('g:i A', strtotime($log->dateTime))
                      . '</small></div>';
        }
    } else {
        $logHtml = '<span class="text-muted small">No logs</span>';
    }

    $mi = $row['middlename'] ? ' ' . strtoupper(substr($row['middlename'], 0, 1)) . '.' : '';

    $data[] = [
        'date'       => '<div class="att-date-main">' . date('M j, Y', strtotime($row['date_time'])) . '</div>'
                      . '<small class="text-muted">' . date('l', strtotime($row['date_time'])) . '</small>',
        'employee'   => '<div class="fw-semibold">' . htmlspecialchars($row['lastname'] . ', ' . $row['firstname'] . $mi) . '</div>'
                      . '<small class="att-emp-id">ID: ' . htmlspecialchars($row['employee_no']) . '</small>',
        'site'       => '<span class="att-site-badge">' . htmlspecialchars($row['site_code'] ?? '—') . '</span>'
                      . '<div class="att-site-name">' . htmlspecialchars($row['site_name'] ?? '—') . '</div>'
                      . '<small class="text-muted" style="font-size:11px;">' . htmlspecialchars($row['site_address'] ?? '') . '</small>',
        'work_hours' => '<span class="att-pill att-pill-work">' . htmlspecialchars($row['work_hours']) . 'h</span>',
        'overtime'   => '<span class="att-pill att-pill-ot">' . htmlspecialchars($row['overtime']) . 'h</span>',
        'undertime'  => '<span class="att-pill att-pill-ut">' . htmlspecialchars($row['undertime']) . 'm</span>',
        'late'       => '<span class="att-pill att-pill-late">' . htmlspecialchars($row['late']) . 'm</span>',
        'logs'       => $logHtml,
        'status'     => ($row['dtr_status'] == 2)
            ? '<span class="badge rounded-pill bg-success"><i class="ri-check-line me-1"></i>Approved</span>'
            : '<span class="badge rounded-pill bg-secondary"><i class="ri-time-line me-1"></i>Pending</span>',
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total_count,
    'recordsFiltered' => $filtered_count,
    'data'            => $data,
    'stats'           => $stats,
]);
