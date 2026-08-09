<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, set charset
$conn->set_charset("utf8mb4");

// Return connection object
return $conn;
// Note: no closing PHP tag to avoid accidental trailing output