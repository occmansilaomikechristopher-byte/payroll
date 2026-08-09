<?php
require_once('db_connect.php');

$bio_ids = [22200, 20613, 22201, 20614, 16893, 22196, 16894, 16895, 22197];

echo "=== Checking if employee_bio IDs exist in employee table ===" . PHP_EOL;
$result = $conn->query("SELECT id FROM employee WHERE id IN (" . implode(',', $bio_ids) . ")");
$existing_ids = [];
while ($row = $result->fetch_assoc()) {
    $existing_ids[] = $row['id'];
    echo "✓ ID: " . $row['id'] . PHP_EOL;
}

echo PHP_EOL . "=== Missing IDs ===" . PHP_EOL;
$missing = array_diff($bio_ids, $existing_ids);
foreach ($missing as $id) {
    echo "✗ ID: " . $id . " (MISSING!)" . PHP_EOL;
}
?>
