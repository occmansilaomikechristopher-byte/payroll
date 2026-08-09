<?php
// Simple diagnostic script to check employee_bio codes
session_start();

// Check authentication
if (empty($_SESSION['login_id'])) {
    die("Unauthorized");
}

require_once('db_connect.php');

$output = [];

// Get all employee_bio records with their codes
$result = $conn->query("SELECT code, employee_id, site_id FROM employee_bio ORDER BY code");
$output['employee_bio_codes'] = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output['employee_bio_codes'][] = $row;
    }
}

// Get all DTR uploads
$result = $conn->query("SELECT id, date_from, date_to, status FROM dtr ORDER BY id DESC LIMIT 10");
$output['recent_dtr_uploads'] = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output['recent_dtr_uploads'][] = $row;
    }
}

// Get count of dtr_details records
$result = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as approved FROM dtr_details");
if ($result && $result->num_rows > 0) {
    $output['dtr_details_summary'] = $result->fetch_assoc();
}

// Get latest dtr_details records
$result = $conn->query("SELECT dd.id, dd.employee_id, dd.date_time, dd.attendance_type, dd.status, e.employee_no, CONCAT(e.lastname, ', ', e.firstname) as name FROM dtr_details dd LEFT JOIN employee e ON dd.employee_id = e.id ORDER BY dd.id DESC LIMIT 20");
$output['latest_dtr_details'] = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output['latest_dtr_details'][] = $row;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
