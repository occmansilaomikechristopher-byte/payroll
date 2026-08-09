<?php
require_once('db_connect.php');

echo "=== Employee Codes in Database ===" . PHP_EOL;
$result = $conn->query("SELECT id, code, employee_id FROM employee_bio ORDER BY code");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Code: " . $row['code'] . " | Employee ID: " . $row['employee_id'] . PHP_EOL;
    }
} else {
    echo "No employee_bio records found" . PHP_EOL;
}

echo PHP_EOL . "=== Employee List ===" . PHP_EOL;
$result = $conn->query("SELECT id, employee_no, firstname, lastname FROM employee ORDER BY employee_no LIMIT 20");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | No.: " . $row['employee_no'] . " | Name: " . $row['lastname'] . ", " . $row['firstname'] . PHP_EOL;
    }
} else {
    echo "No employees found" . PHP_EOL;
}
?>
