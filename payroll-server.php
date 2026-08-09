<?php
session_start();
include 'db_connect.php';

// Initialize response array
$response = array();

// Get the request parameters
$limit = $_POST['length'];
$offset = $_POST['start'];
$search = $_POST['search']['value'];
$orderColumnIndex = $_POST['order'][0]['column'];
$orderDirection = $_POST['order'][0]['dir'];
$p2 = $_POST['p2'];

// Define column mappings
$columns = ["ref_no", "employer_name", "period", "category", "type", "status"];
$orderColumn = $columns[$orderColumnIndex];

// Query to count total records
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM payroll";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Base query
$query = "SELECT payroll.*, employers.employer_name, clusters.cluster 
          FROM payroll  
          LEFT JOIN employers ON payroll.employer_id = employers.id   
          LEFT JOIN clusters ON clusters.id = payroll.category 
          WHERE payroll.p2 = '" . mysqli_real_escape_string($conn, $p2) . "'";

// Add search filter
if (!empty($search)) {
    $query .= " AND ref_no LIKE '%$search%' 
                OR employers.employer_name LIKE '%$search%' 
                OR clusters.cluster LIKE '%$search%' ";
}

// Add ordering and pagination
$query .= " ORDER BY $orderColumn $orderDirection LIMIT $limit OFFSET $offset";

$result = $conn->query($query);

// Prepare data for DataTables
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = array(
        "ref_no" => '<span class="payroll-ref">' . htmlspecialchars($row['ref_no']) . '</span>',
        "employer_name" => '<span class="payroll-employer">' . htmlspecialchars($row['employer_name']) . '</span>',
        "period" => '<span class="payroll-period"><i class="ri-calendar-2-line me-1 text-muted"></i>'
                  . date("M d", strtotime($row['date_from'])) . ' &ndash; ' . date("M d, Y", strtotime($row['date_to'])) . '</span>',
        "category" => ($row['category'] == 0)
            ? '<span class="badge bg-info-subtle text-info border border-info-subtle"><i class="ri-map-pin-2-line me-1"></i>Sites</span>'
            : '<span class="badge bg-purple-subtle text-purple border" style="background:#f3e5f5;color:#6a1b9a;border-color:#ce93d8!important;"><i class="ri-global-line me-1"></i>' . htmlspecialchars($row['cluster']) . '</span>',
        "type" => ($row['type'] == 5)
            ? '<span class="badge bg-dark type-badge"><i class="ri-calendar-check-line me-1"></i>Monthly</span>'
            : '<span class="badge bg-secondary type-badge"><i class="ri-calendar-2-line me-1"></i>Week ' . $row['type'] . '</span>',
        "status" => getStatusBadge($row['status']),
        "action" => getActionButtons($row)
    );
}

// Function to return status badges
function getStatusBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge rounded-pill bg-primary"><i class="ri-file-add-line me-1"></i>New</span>';
        case 1:
            return '<span class="badge rounded-pill bg-success"><i class="ri-check-circle-line me-1"></i>Calculated</span>';
        case 2:
            return '<span class="badge rounded-pill bg-danger"><i class="ri-lock-fill me-1"></i>Locked</span>';
        default:
            return '<span class="badge rounded-pill bg-secondary">Unknown</span>';
    }
}

// Function to return action buttons
function getActionButtons($row)
{
    $id       = $row['id'];
    $settings = htmlspecialchars(json_encode(json_decode($row['settings'], true)), ENT_QUOTES);
    $btn      = '<div class="action-buttons">';

    if ($row['status'] != 2) {
        // Primary action
        if ($row['status'] == 0) {
            $btn .= '<button class="btn btn-sm btn-primary calculate_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Calculate Payroll">'
                  . '<i class="ri-calculator-line me-1"></i>Calculate</button>';
        } else {
            $btn .= '<button class="btn btn-sm btn-success view_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="View Payroll Details">'
                  . '<i class="ri-eye-line me-1"></i>View</button>';
        }
        // Recalculate (status 1 only)
        if ($row['status'] == 1) {
            $btn .= '<button class="btn btn-sm btn-warning text-dark" onclick="recalculate(' . $id . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="Recalculate Payroll">'
                  . '<i class="ri-refresh-line me-1"></i>Recalculate</button>';
        }
        // Settings
        $btn .= '<button class="btn btn-sm btn-outline-secondary add_settings" data-id="' . $id . '" settings=\'' . $settings . '\' data-bs-toggle="tooltip" data-bs-placement="top" title="Payroll Settings">'
              . '<i class="ri-settings-3-line"></i></button>';
        // Delete
        $btn .= '<button class="btn btn-sm btn-outline-danger remove_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Payroll">'
              . '<i class="ri-delete-bin-line"></i></button>';
    } else {
        // Locked: view always, unlock for admin
        $btn .= '<button class="btn btn-sm btn-success view_payroll" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="View Payroll Details">'
              . '<i class="ri-eye-line me-1"></i>View</button>';
        if ($_SESSION['login_role'] == 1) {
            $btn .= '<button class="btn btn-sm btn-outline-warning" onclick="islock(' . $id . ',1)" data-bs-toggle="tooltip" data-bs-placement="top" title="Unlock Payroll">'
                  . '<i class="ri-lock-unlock-line me-1"></i>Unlock</button>';
        }
    }

    // History — always visible
    $btn .= '<button class="btn btn-sm btn-outline-secondary" onclick="payroll_history(' . $id . ')" data-bs-toggle="tooltip" data-bs-placement="top" title="View History">'
          . '<i class="ri-history-line"></i></button>';

    $btn .= '</div>';
    return $btn;
}

// Count filtered records
$filteredRecordsQuery = "SELECT COUNT(*) AS total FROM payroll";
$filteredRecordsResult = $conn->query($filteredRecordsQuery);
$filteredRecords = $filteredRecordsResult->fetch_assoc()['total'];

// Prepare response
$response = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $filteredRecords,
    "data" => $data
);

echo json_encode($response);
