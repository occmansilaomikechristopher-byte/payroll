<?php
$db = new mysqli('localhost', 'root', '', 'payroll_management');
if ($db->connect_error) {
    echo 'CONNERR: ' . $db->connect_error . PHP_EOL;
    exit(1);
}
$res = $db->query('SHOW COLUMNS FROM DTR');
if (!$res) {
    echo 'ERR: ' . $db->error . PHP_EOL;
    exit(1);
}
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\t" . $row['Type'] . PHP_EOL;
}
echo "---\n";
$res2 = $db->query('SHOW COLUMNS FROM employee_bio');
if (!$res2) {
    echo 'ERR: ' . $db->error . PHP_EOL;
    exit(1);
}
while ($row = $res2->fetch_assoc()) {
    echo $row['Field'] . "\t" . $row['Type'] . PHP_EOL;
}
