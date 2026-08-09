<?php include 'db_connect.php' ?>
<?php 

// Get the latest payroll record and redirect to print page
$query = "SELECT id FROM payroll ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

if($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $payroll_id = $row['id'];
    // Redirect to print page with the payroll ID
    header("Location: print-payroll.php?id=" . $payroll_id);
    exit();
} else {
    // If no payroll found, show error
    echo "No payroll records found.";
}
?>
