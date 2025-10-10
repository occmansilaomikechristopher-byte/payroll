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
        "ref_no" => $row['ref_no'],
        "employer_name" => $row['employer_name'],
        "period" => date("F d", strtotime($row['date_from'])) . " - " . date("F d, Y", strtotime($row['date_to'])),
        "category" => ($row['category'] == 0) ? "Sites" : "Cluster: <b>" . $row['cluster'] . "</b>",
        "type" => ($row['type'] == 5) ? "Monthly" : "Week " . $row['type'],
        "status" => getStatusBadge($row['status']),
        "action" => getActionButtons($row)
    );
}

// Function to return status badges
function getStatusBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge rounded-pill border border-primary text-primary">NEW</span>';
        case 1:
            return '<span class="badge rounded-pill border border-success text-success">Calculated</span>';
        case 2:
            return '<span class="badge rounded-pill border border-danger text-danger">Lock</span>';
        default:
            return '<span class="badge rounded-pill border border-secondary text-secondary">Unknown</span>';
    }
}

// Function to return action buttons
function getActionButtons($row)
{
    $buttons = '<div class="action-buttons">';

    if ($row['status'] != 2) {
        if ($row['status'] == 0) {
            $buttons .= '<button class="btn btn-sm btn-outline-secondary calculate_payroll" data-id="' . $row['id'] . '">Calculate</button>';
        } else {
            $buttons .= '<button class="btn btn-sm btn-outline-secondary view_payroll" data-id="' . $row['id'] . '">View</button>';
        }
        if ($row['status'] == 1) {
            $buttons .= '<button class="btn btn-sm btn-danger" onclick="recalculate(' . $row['id'] . ')">Recalculate</button>';
        }
        $buttons .= '<button class="btn btn-sm btn-outline-warning add_settings" data-id="' . $row['id'] . '" settings=\'' . json_encode(json_decode($row["settings"], true)) . '\'>Settings</button>';
        $buttons .= '<button class="btn btn-sm btn-outline-danger remove_payroll" data-id="' . $row['id'] . '">Delete</button>';
    } else {
        if ($_SESSION['login_role'] == 1) {
           $buttons .= '<button class="btn btn-sm btn-outline-danger " onclick="islock(' . $row['id'] . ',1)" >Unlock</button>';
        }
        $buttons .= '<button class="btn btn-sm btn-outline-secondary view_payroll" data-id="' . $row['id'] . '">View</button>';
       
    }
    $buttons .= '<button class="btn btn-sm btn-secondary" onclick="payroll_history(' . $row['id'] . ')">History</button>';
    $buttons .= '</div>';
    return $buttons;
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
