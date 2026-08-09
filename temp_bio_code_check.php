<?php
require_once('db_connect.php');
$result = $conn->query("SELECT eb.code, eb.employee_id, e.employee_no, e.firstname, e.lastname FROM employee_bio eb LEFT JOIN employee e ON eb.employee_id = e.id ORDER BY eb.code, eb.id");
if (!$result) { echo 'Query failed: ' . $conn->error . "\n"; exit(1); }
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
?>