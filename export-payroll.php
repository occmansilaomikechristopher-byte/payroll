<?php
include 'db_connect.php';
if (!isset($_GET['id'])) {
    return;
}
$id = $_GET['id'];
$site_id = $_GET['site_id'];

$filter_query = "";
$sid = '';
if( isset($_GET['site_id'])  && $_GET['site_id'] !== 'all' ){
    $sid = $_GET['site_id'];
    $filter_query = " AND a.site_id = $sid  ";
 
}
// LEFT JOIN payroll g ON g.id = a.payroll_id 
// LEFT JOIN employers  h ON g.employer_id = h.id

$query = "SELECT  employer_name FROM payroll  
        LEFT JOIN employers  ON payroll.employer_id = employers.id  
        WHERE payroll.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payroll_r = $result->fetch_assoc();


$query2 = "SELECT * FROM payroll   WHERE id = ?";
$stmt = $conn->prepare($query2);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();
$site_ids = json_decode($payroll['site_ids'], true);
$site_ids = isset($site_ids) ? $site_ids : [];
$commaSeparatedSites = implode(',', $site_ids);

$i = 0;
$query = $conn->query("SELECT  a.*, f.site_code,f.site_name,f.site_address, e.employee_no, e.lastname, e.firstname, e.middlename, e.basic_pay, d.name as department, p.name as position
FROM payroll_items a 
INNER JOIN employee e ON a.employee_id = e.id 
LEFT JOIN department d ON e.department_id = d.id 
LEFT JOIN position p ON e.position_id = p.id 
LEFT JOIN sites f ON f.id = a.site_id 
WHERE  a.payroll_id = $id $filter_query  ORDER BY lastname ASC ");
$contributions_settings = json_decode($payroll['settings'], true);
$contributions_settings = isset($contributions_settings) ? $contributions_settings : [];

// Include PhpSpreadsheet classes
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Set active sheet
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'No.');
$sheet->setCellValue('B1', 'Name of Employee');
$sheet->setCellValue('C1', 'Position');
$sheet->setCellValue('D1', 'No. of Days');
$sheet->setCellValue('E1', 'Basic Rate');
$sheet->setCellValue('F1', 'Total Basic Rate');
$sheet->setCellValue('G1', 'Allowance');
$sheet->setCellValue('H1', 'Overtime');
$sheet->setCellValue('I1', 'GROSS SALARY');
$sheet->setCellValue('J1', 'Other Deduction');
$sheet->setCellValue('K1', 'Total Deduction');
$sheet->setCellValue('L1', 'Net Pay');

$data = [];

$total_number_days = 0;
$t_basic_rate = 0;
$t_per_day = 0;
$t_allowance = 0;
$t_gross = 0;
$t_deduction = 0;
$t_net = 0;
while ($row = $query->fetch_assoc()) {
    $i++;
    $perMinute = $row['per_minute'];
    $total_basic_rate = $row['present'] * $row['per_day'];
    $overtime_amount = $row['ot'] * $row['ot_rate'];
    $late_amount = $row['late'] * $perMinute;
    $undertime_amount = $row['under_time'] * $perMinute;
    $allowance_amount = $row['allowance_amount'];

    $gross_salary =  $total_basic_rate +   $overtime_amount - $late_amount - $undertime_amount + $allowance_amount;
    $contributions = json_decode($row['contributions'], true);
    $deductions = json_decode($row['deductions'], true);
    $total_deductions =  0;
    $other_deduction = $row['other_deduction'];
    $perDay = $row['per_day'];

    $total_number_days += $row['present'];
    $t_basic_rate += $total_basic_rate;
    $t_per_day += $perDay;
    $t_allowance += $allowance_amount;
    $t_gross += $gross_salary;

    if (count($contributions_settings) > 0) {
        foreach ($contributions_settings as $i2 =>  $k) {
            $deduction_amount = 0;
            if ($k['type'] == 1) {
                foreach ($contributions as $kd) {
                    if ($kd["contribution_id"] == $k["id"]) {
                        $deduction_amount = $kd["amount"];
                    }
                }
            } else {
                foreach ($deductions as $kd) {
                    if ($kd["deduction_id"] == $k["id"]) {
                        $deduction_amount = $kd["amount"];
                    }
                }
            }
        }
        $total_deductions += $deduction_amount;
            
    }

    $total_deductions = $total_deductions  + $other_deduction;
    $t_deduction+=$total_deductions;
    $net = $gross_salary -  $total_deductions;
    $t_net+=$net;

    {
        $newItem = [
            $i,
            $row['lastname'] . ' ' .$row['firstname'],
            $row['position'],
            $row['present'],
            number_format($row['per_day'], 2),
            number_format($total_basic_rate, 2),
            number_format($allowance_amount, 2),
            number_format($overtime_amount),
            number_format($gross_salary, 2),
            number_format($other_deduction, 2) ,
            number_format($total_deductions, 2),
            number_format($net),
        ];
        array_push($data, $newItem);
    }
}

foreach ($data as $row => $rowData) {
    foreach ($rowData as $col => $value) {
        $sheet->setCellValueByColumnAndRow($col + 1, $row + 2, $value); // Adjusting row index to start from 2
    }
}

// Calculate the last row index
$lastRow = count($data) + 5;

// Add "Created by" footer
// $sheet->setCellValue('A' . ($lastRow + 1), 'Created by');
// $sheet->setCellValue('B' . ($lastRow + 1), 'Your Name'); // Replace with actual creator's name or username

// Merge cells for the "Created by" footer (optional)
$sheet->mergeCells('A' . ($lastRow + 1) . ':D' . ($lastRow + 1));

// Set alignment for the footer text (optional)
$sheet->getStyle('A' . ($lastRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Adjust column widths (optional)
foreach(range('A','N') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}


// Create Xlsx file in memory
$writer = new Xlsx($spreadsheet);

// // Redirect output to a client’s web browser (Excel2007)
$fileName = 'payroll_' . date('Ymd_His') . '_' . uniqid() . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;

?>
