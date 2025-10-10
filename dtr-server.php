<?php
include 'db_connect.php';

// DataTable request parameters
$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];

// Query to get total records
$totalRecordsQuery = "SELECT COUNT(*) as total FROM DTR WHERE status = 2";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Fetch data with LIMIT for pagination
$query = "SELECT DTR.*, sites.site_code, sites.site_name, sites.site_address, 
                timekeeper.name AS timekeeper_name, uploaded.name AS uploaded_by, 
                employer_name, approved.name AS approve_by
          FROM DTR 
          LEFT JOIN sites ON DTR.site_id = sites.id 
          LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id 
          LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id 
          LEFT JOIN users AS approved ON DTR.approved_by = approved.id  
          LEFT JOIN employers ON DTR.employer_id = employers.id 
          WHERE DTR.status = 2 
          ORDER BY DTR.id DESC 
          LIMIT $start, $length";

$result = $conn->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $period = date("F d", strtotime($row['date_from'])) . " - " . date("F j, Y", strtotime($row['date_to']));

    $site_info = '<div class="site-wapper">
                    <div><i class="ri-hashtag"></i> ' . $row['site_code'] . '</div>
                    <div><i class="ri-radio-button-line"></i> ' . $row['site_name'] . '</div>
                    <div><i class="ri-map-pin-line"></i> ' . $row['site_address'] . '</div>
                  </div>';

    $action = '<div class="action-buttons">
                    <button class="btn btn-sm btn-outline-secondary view-dtr"
                        data-id="' . base64_encode($row['id']) . '" 
                        data-timekeeper="' . base64_encode($row['timekeeper_name']) . '"
                        data-device="' . base64_encode($row['device_id']) . '"
                        data-site="' . base64_encode($row['site_id']) . '"
                        data-status="' . base64_encode($row['status']) . '">View</button>
               </div>';

    $data[] = [
        "period" => "<b>$period</b>",
        "employer_name" => htmlspecialchars($row['employer_name']),
        "site" => $site_info,
        "uploaded_by" => $row['uploaded_by'],
        "timekeeper_name" => $row['timekeeper_name'],
        "approve_by" => $row['approve_by'],
        "action" => $action
    ];
}

// Return JSON response
$response = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

echo json_encode($response);
?>
