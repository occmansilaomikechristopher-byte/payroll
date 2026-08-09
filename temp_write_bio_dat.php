<?php
$codes = ['2026-58843','2026-44182','2026-35396','2026-31821','2026-37458'];
$dates = ['2026-08-10','2026-08-11','2026-08-12','2026-08-13','2026-08-14','2026-08-15','2026-08-16'];
$device_id = '19';
$rows = [];
foreach ($dates as $date) {
    foreach ($codes as $code) {
        $rows[] = $code . "\t" . $date . " 08:00:00\t" . $device_id;
        $rows[] = $code . "\t" . $date . " 12:00:00\t" . $device_id;
        $rows[] = $code . "\t" . $date . " 13:00:00\t" . $device_id;
        $rows[] = $code . "\t" . $date . " 17:00:00\t" . $device_id;
    }
}
$content = implode(PHP_EOL, $rows) . PHP_EOL;
file_put_contents('sample_biometric_next_week.dat', $content);
echo 'WROTE ' . count($rows) . ' lines to sample_biometric_next_week.dat' . PHP_EOL;
