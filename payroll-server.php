<?php
session_start();
include 'db_connect.php';

$limit            = (int) $_POST['length'];
$offset           = (int) $_POST['start'];
$search           = $conn->real_escape_string($_POST['search']['value'] ?? '');
$orderColumnIndex = (int) ($_POST['order'][0]['column'] ?? 0);
$orderDirection   = ($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
$p2               = $conn->real_escape_string($_POST['p2'] ?? 'no');

$columns     = ['payroll.ref_no', 'payroll.date_from', 'payroll.type', 'payroll.status'];
$orderColumn = $columns[min($orderColumnIndex, count($columns) - 1)];

$totalRecords = 0;
$r = $conn->query("SELECT COUNT(*) AS total FROM payroll");
if ($r) $totalRecords = (int) $r->fetch_assoc()['total'];

$where = "WHERE payroll.p2 = '$p2'";
if (!empty($search)) {
    $where .= " AND (payroll.ref_no LIKE '%$search%')";
}

$query  = "SELECT payroll.* FROM payroll $where ORDER BY $orderColumn $orderDirection LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'ref_no'  => '<span class="payroll-ref">' . htmlspecialchars($row['ref_no']) . '</span>',
            'period'  => '<span class="payroll-period"><i class="ri-calendar-2-line me-1 text-muted"></i>'
                       . date('M d', strtotime($row['date_from'])) . ' &ndash; ' . date('M d, Y', strtotime($row['date_to'])) . '</span>',
            'type'    => ($row['type'] == 5)
                ? '<span class="badge bg-dark"><i class="ri-calendar-check-line me-1"></i>Monthly</span>'
                : '<span class="badge bg-secondary"><i class="ri-calendar-2-line me-1"></i>Week ' . (int)$row['type'] . '</span>',
            'status'  => getStatusBadge($row['status']),
            'action'  => getActionButtons($row),
        ];
    }
}

$filteredRecords = $totalRecords;
if (!empty($search)) {
    $fr = $conn->query("SELECT COUNT(*) AS total FROM payroll $where");
    if ($fr) $filteredRecords = (int) $fr->fetch_assoc()['total'];
}

function getStatusBadge($status)
{
    switch ($status) {
        case 0: return '<span class="badge rounded-pill bg-primary"><i class="ri-file-add-line me-1"></i>New</span>';
        case 1: return '<span class="badge rounded-pill bg-success"><i class="ri-check-circle-line me-1"></i>Calculated</span>';
        case 2: return '<span class="badge rounded-pill bg-danger"><i class="ri-lock-fill me-1"></i>Locked</span>';
        default: return '<span class="badge rounded-pill bg-secondary">Unknown</span>';
    }
}

function getActionButtons($row)
{
    $id       = $row['id'];
    $settings = htmlspecialchars(json_encode(json_decode($row['settings'], true)), ENT_QUOTES);
    $btn      = '<div class="action-buttons">';

    if ($row['status'] != 2) {
        if ($row['status'] == 0) {
            $btn .= '<button class="btn btn-sm btn-primary calculate_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" title="Calculate Payroll"><i class="ri-calculator-line me-1"></i>Calculate</button>';
        } else {
            $btn .= '<button class="btn btn-sm btn-success view_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" title="View Payroll Details"><i class="ri-eye-line me-1"></i>View</button>';
        }
        if ($row['status'] == 1) {
            $btn .= '<button class="btn btn-sm btn-warning text-dark" onclick="recalculate(' . $id . ')" data-bs-toggle="tooltip" title="Recalculate"><i class="ri-refresh-line me-1"></i>Recalculate</button>';
        }
        $btn .= '<button class="btn btn-sm btn-outline-secondary add_settings" data-id="' . $id . '" settings=\'' . $settings . '\' data-bs-toggle="tooltip" title="Settings"><i class="ri-settings-3-line"></i></button>';
        $btn .= '<button class="btn btn-sm btn-outline-danger remove_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" title="Delete"><i class="ri-delete-bin-line"></i></button>';
    } else {
        $btn .= '<button class="btn btn-sm btn-success view_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" title="View"><i class="ri-eye-line me-1"></i>View</button>';
        if ($_SESSION['login_role'] == 1) {
            $btn .= '<button class="btn btn-sm btn-outline-warning" onclick="islock(' . $id . ',1)" data-bs-toggle="tooltip" title="Unlock"><i class="ri-lock-unlock-line me-1"></i>Unlock</button>';
        }
    }

    $btn .= '<button class="btn btn-sm btn-outline-secondary" onclick="payroll_history(' . $id . ')" data-bs-toggle="tooltip" title="History"><i class="ri-history-line"></i></button>';
    $btn .= '</div>';
    return $btn;
}

echo json_encode([
    'draw'            => (int) ($_POST['draw'] ?? 1),
    'recordsTotal'    => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data'            => $data,
]);
