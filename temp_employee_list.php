<?php
require_once('db_connect.php');
$result = $conn->query("SELECT id, employee_no, firstname, lastname, status FROM employee WHERE status = 1 ORDER BY id DESC LIMIT 20");
if (!$result) {
    echo "Query failed: " . $conn->error . "\n";
    exit(1);
}
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
?>