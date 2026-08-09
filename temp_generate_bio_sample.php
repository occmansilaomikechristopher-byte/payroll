<?php
require_once('db_connect.php');
$result = $conn->query("SELECT eb.code, eb.employee_id, e.employee_no, e.firstname, e.lastname FROM employee_bio eb LEFT JOIN employee e ON eb.employee_id = e.id ORDER BY eb.code, eb.id");
if (!$result) {
    echo "Query failed: " . $conn->error . "\n";
    exit(1);
}
$seen = [];
while ($row = $result->fetch_assoc()) {
    $key = $row['code'] . '|' . $row['employee_id'];
    if (isset($seen[$key])) continue;
    $seen[$key] = true;
    echo json_encode($row) . "\n";
}
?>