<?php
require_once('db_connect.php');
$content = trim(file_get_contents('sample_biometric_current.dat'));
$lines = preg_split('/\r\n|\r|\n/', $content);
$parsed = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;
    $parts = preg_split('/[\t,]+/', $line);
    $parsed[] = ['code'=>trim($parts[0]), 'date_time'=>trim($parts[1]), 'attendance_type'=>trim($parts[2])];
}
function testCode($code, $branch_id) {
    global $conn;
    $code_param = $code;
    if ($branch_id > 0) {
        $employee_bio_query = $conn->prepare('SELECT employee_id, device_id FROM employee_bio WHERE code = ? AND site_id = ? LIMIT 1');
        $employee_bio_query->bind_param('si', $code_param, $branch_id);
    } else {
        $employee_bio_query = $conn->prepare('SELECT employee_id, device_id FROM employee_bio WHERE code = ? LIMIT 1');
        $employee_bio_query->bind_param('s', $code_param);
    }
    $employee_bio_query->execute();
    $result_bio = $employee_bio_query->get_result();
    echo "code=$code branch=$branch_id bio_rows=" . ($result_bio ? $result_bio->num_rows : 'NULL') . "\n";
    if ($result_bio && $result_bio->num_rows > 0) {
        $bio = $result_bio->fetch_assoc();
        echo " bio=" . json_encode($bio) . "\n";
    }
    if ($result_bio && $result_bio->num_rows === 0 && $branch_id>0) {
        $fallback = $conn->prepare('SELECT employee_id, device_id FROM employee_bio WHERE code = ? LIMIT 1');
        $fallback->bind_param('s', $code_param);
        $fallback->execute();
        $fb = $fallback->get_result();
        echo " fallback_rows=" . ($fb ? $fb->num_rows : 'NULL') . "\n";
        if ($fb && $fb->num_rows > 0) echo " fb=".json_encode($fb->fetch_assoc())."\n";
    }
    if (preg_match('/^[\d-]+$/', $code_param)) {
        $stmt = $conn->prepare('SELECT id FROM employee WHERE employee_no = ? LIMIT 1');
        $stmt->bind_param('s', $code_param);
        $stmt->execute();
        $res = $stmt->get_result();
        echo " emp_no_rows=" . ($res ? $res->num_rows : 'NULL') . "\n";
    }
}
$branch_ids = [0, 1, 30, 31, 32];
foreach ($branch_ids as $branch) {
    echo "--- branch $branch ---\n";
    foreach (array_unique(array_column($parsed, 'code')) as $code) {
        testCode($code, $branch);
    }
}
?>