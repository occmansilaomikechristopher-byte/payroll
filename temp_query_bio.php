<?php
require 'db_connect.php';
$res = $conn->query("SELECT eb.code, eb.employee_id, e.employee_no, e.firstname, e.lastname, eb.site_id, eb.device_id FROM employee_bio eb LEFT JOIN employee e ON eb.employee_id=e.id ORDER BY eb.code");
if (!$res) {
    echo 'QUERY_FAILED: ' . $conn->error . PHP_EOL;
    exit(1);
}
while ($row = $res->fetch_assoc()) {
    echo $row['code'] . '|' . $row['employee_id'] . '|' . $row['employee_no'] . '|' . $row['firstname'] . '|' . $row['lastname'] . '|' . $row['site_id'] . '|' . $row['device_id'] . PHP_EOL;
}
