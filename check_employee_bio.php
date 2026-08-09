<?php
require_once('db_connect.php');

echo "=== Active Employees ===" . PHP_EOL;
$result = $conn->query("SELECT id, employee_no, firstname, lastname FROM employee ORDER BY id LIMIT 20");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | No.: " . $row['employee_no'] . " | Name: " . $row['lastname'] . ", " . $row['firstname'] . PHP_EOL;
    }
}

echo PHP_EOL . "=== Current employee_bio records ===" . PHP_EOL;
$result = $conn->query("SELECT id, code, employee_id, site_id FROM employee_bio ORDER BY code LIMIT 20");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Bio ID: " . $row['id'] . " | Code: " . $row['code'] . " | Emp ID: " . $row['employee_id'] . " | Site: " . $row['site_id'] . PHP_EOL;
    }
}
?>
