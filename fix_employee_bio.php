<?php
require_once('db_connect.php');

// Map employee codes to actual active employee IDs
// Active employees: 22464, 22465, 22466, 22467, 22468
$mapping = [
    '4' => 22464,      // mansilao, mike
    '16' => 22465,     // caharian, ariel
    '18' => 22466,     // caharian, Ariel
    '720' => 22467,    // bigtasin, mar
    '744' => 22468,    // gebe, barry
];

echo "=== Updating employee_bio records ===" . PHP_EOL;
$conn->begin_transaction();

try {
    foreach ($mapping as $code => $emp_id) {
        $stmt = $conn->prepare("UPDATE employee_bio SET employee_id = ? WHERE code = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param('is', $emp_id, $code);
        if (!$stmt->execute()) {
            throw new Exception("Update failed for code $code: " . $stmt->error);
        }
        echo "Updated code $code → Employee ID $emp_id (" . count($conn->query("SELECT id FROM employee_bio WHERE code = '$code'")->fetch_all()) . " records)" . PHP_EOL;
    }
    
    $conn->commit();
    echo PHP_EOL . "✓ All employee_bio records updated successfully!" . PHP_EOL;
} catch (Exception $e) {
    $conn->rollback();
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
}
?>
