<?php
session_start();
include 'db_connect.php';

// Ensure PHP warnings/notices do not break JSON output. Log errors instead of displaying.
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

// DataTable request parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$login_role = intval($_SESSION['login_role'] ?? 0);
$branch_filter = intval($_SESSION['login_branch_id'] ?? 0);

$where = "WHERE DTR.status = 2";
if (!in_array($login_role, [1, 10], true) && $branch_filter > 0) {
    $where .= " AND DTR.branch_id = " . $branch_filter;
}

// Query to get total records
$totalRecordsQuery = "SELECT COUNT(*) as total FROM DTR $where";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecordsRow = $totalRecordsResult ? $totalRecordsResult->fetch_assoc() : ['total' => 0];
$totalRecords = intval($totalRecordsRow['total'] ?? 0);

// Fetch data with LIMIT for pagination
$query = "SELECT DTR.*, branches.branch_code, branches.branch_name, branches.address AS branch_address, 
            timekeeper.name AS timekeeper_name, uploaded.name AS uploaded_by, 
            approved.name AS approve_by
          FROM DTR 
          LEFT JOIN branches ON DTR.branch_id = branches.id 
          LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id 
          LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id 
          LEFT JOIN users AS approved ON DTR.approved_by = approved.id  
          $where 
          ORDER BY DTR.id DESC 
          LIMIT $start, $length";

$result = $conn->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
    $period = date("M d", strtotime($row['date_from'])) . ' &ndash; ' . date("M j, Y", strtotime($row['date_to']));

    $site_info = '<span class="dtr-site-code">' . htmlspecialchars($row['branch_code'] ?? '') . '</span>'
               . '<div class="dtr-site-name">' . htmlspecialchars($row['branch_name'] ?? '—') . '</div>'
               . '<div class="dtr-site-addr">' . htmlspecialchars($row['branch_address'] ?? '') . '</div>';

    $action = '<div class="dtr-action">'
            . '<button class="btn btn-sm btn-outline-success view-dtr"'
            . ' data-id="' . base64_encode($row['id']) . '"'
            . ' data-timekeeper="' . base64_encode($row['timekeeper_name']) . '"'
            . ' data-device="' . base64_encode($row['device_id']) . '"'
            . ' data-site="' . base64_encode($row['branch_id']) . '"'
            . ' data-status="' . base64_encode($row['status']) . '"'
            . ' data-bs-toggle="tooltip" data-bs-placement="top" title="View DTR Details">'
            . '<i class="ri-eye-line me-1"></i>View</button>'
            . (in_array($login_role, [1, 10], true)
                ? '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDTR(' . intval($row['id']) . ')"'
                    . ' data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Approved DTR">'
                    . '<i class="ri-delete-bin-line"></i></button>'
                : '')
            . '</div>';

    $data[] = [
        "period"          => '<div class="dtr-period"><i class="ri-calendar-2-line me-1 text-muted"></i>' . $period . '</div>',
        "site"            => $site_info,
        "uploaded_by"     => '<div class="dtr-user"><i class="ri-user-3-line me-1 text-muted"></i>' . htmlspecialchars($row['uploaded_by']) . '</div>',
        "timekeeper_name" => '<div class="dtr-user"><i class="ri-user-settings-line me-1 text-muted"></i>' . htmlspecialchars($row['timekeeper_name']) . '</div>',
        "approve_by"      => '<div class="dtr-user"><i class="ri-shield-check-line me-1 text-muted"></i>' . htmlspecialchars($row['approve_by']) . '</div>',
        "action"          => $action,
    ];
    }
} else {
    // Query failed - include a user-safe message in the response and log the detailed error
    $err = $conn->error;
    error_log("dtr-server.php SQL error: " . $err);
    // keep data empty
}

// Clean any accidental output (warnings/html) produced earlier
// If any accidental output was produced before sending JSON, log it for debugging
$preOutput = '';
if (ob_get_length() !== false) {
    $preOutput = ob_get_contents();
}
if (is_string($preOutput) && trim($preOutput) !== '') {
    // Limit size to avoid huge logs
    error_log("dtr-server.php pre-output detected: " . substr($preOutput, 0, 1000));
}
ob_end_clean();

// Return JSON response
$response = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

header('Content-Type: application/json; charset=utf-8');
// If there was any buffered pre-output, include a short base64-encoded snippet
if (is_string($preOutput) && trim($preOutput) !== '') {
    $response['debug_pre_output_b64'] = base64_encode(substr($preOutput, 0, 2000));
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
