<?php
$host = "localhost";
$db_name = "u109581358_payroll_db";
$username = "root";
$password = "";
// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, set charset
$conn->set_charset("utf8mb4");

// Keep payroll records compatible with adjustment-aware calculations.
foreach ([
    'adjustment_amount' => 'DECIMAL(12,2) NOT NULL DEFAULT 0.00',
    'adjustment_remarks' => 'TEXT NULL',
] as $column => $definition) {
    $columnResult = $conn->query("SHOW COLUMNS FROM payroll_items LIKE '$column'");
    if ($columnResult && $columnResult->num_rows === 0) {
        $conn->query("ALTER TABLE payroll_items ADD COLUMN $column $definition");
    }
}

// Return connection object
return $conn;
// Note: no closing PHP tag to avoid accidental trailing output