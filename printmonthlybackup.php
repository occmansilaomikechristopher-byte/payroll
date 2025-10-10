<title>JEJORS Payroll</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="description" content="JEJORS Payroll">
<meta name="author" content="design by: Niel Daculan">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
include 'db_connect.php';
if (!isset($_GET['id'])) {
    return;
}
$id = $_GET['id'];


$filter_query = "";
$sid = '';
if (isset($_GET['site_id'])  && $_GET['site_id'] !== 'all') {
    $sid = $_GET['site_id'];
    $filter_query = " AND a.site_id = $sid  ";
}
// LEFT JOIN payroll g ON g.id = a.payroll_id 
// LEFT JOIN employers  h ON g.employer_id = h.id

$query = "SELECT  employer_name, category,  clusters.cluster FROM payroll  
        LEFT JOIN employers  ON payroll.employer_id = employers.id  
        LEFT JOIN clusters  ON clusters.id = payroll.category 
        WHERE payroll.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payroll_r = $result->fetch_assoc();

?>
<?php
$query2 = "SELECT * FROM payroll   WHERE id = ?";
$stmt = $conn->prepare($query2);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();
$site_ids = json_decode($payroll['site_ids'], true);
$site_ids = isset($site_ids) ? $site_ids : [];
$commaSeparatedSites = implode(',', $site_ids);
$status = $payroll['status'];

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

$payroll_type = $payroll['type'];

?>

<body>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
            visibility: visible;
            overflow-x: visible;
           
        }

    

        /* Hide unnecessary elements */
        .no-print,
        .no-print * {
            display: none !important;
        }

        /* Table Styles */


        table {
            width: 100%;
            border-collapse: collapse;
            
        }

        th,
        td {
            padding: 3px;
            text-align: center;
           
            
        }

        th {
            text-transform: uppercase;
            width: 80px;
            font-size: 10px !important;
            border: 1px solid #000 !important;
        }

        td {
            width: 80px;
            border: 1px solid #000 !important;
        }

        .text-right {
            text-align: right;
        }

        .company-wrapper {
            display: flex;
            flex-direction: row;
            font-weight: bold;
            line-height: 2;
        }

        .name {
            min-width: 250px;
        }

        .top {
            display: flex;
            justify-content: center;
        }

        .top h1,
        .top b {
            line-height: 0;
        }

        .logo-area {
            width: 200px;
            /* margin-top: 25px; */
        }

        .text-center {
            text-align: center;
        }

        p {
            line-height: 1;

        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div id="print-area">

                    <div class="top">
                        <div class="logo-area">
                            <img style="width: 60px;" src="assets2/images/logo.jpeg" alt="Logo">
                        </div>
                        <div>
                            <h1>JEJORS CONSTRUCTION CORPORATION</h1>
                            <h4>TIU SONS, BUILDING BARANGAY 33, GUILLERMO COGON CAGAYAN DE ORO CITY</h4>
                            <h6 class="text-center">PAYROLL PERIOD: <strong>
                                    <?php
                                    $date = strtotime($payroll['date_from']);
                                    $formattedDate = date("F d", $date);
                                    echo $formattedDate;
                                    ?>
                                    - <?php
                                        $date = strtotime($payroll['date_to']);
                                        $formattedDate = date("F j, Y", $date);
                                        echo $formattedDate;
                                        ?>
                                </strong></h6>
                        </div>
                    </div>
                    <table     cellspacing="0"  id="table-1" class="table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center primary-header" style="min-width: 40px;">No.</th>
                                <th rowspan="2" class="text-center primary-header" >Name</th>
                                <th rowspan="2" class="text-center primary-header" style="min-width: 120px;">Position</th>
                                <th rowspan="2" class="text-center primary-header">Project Code</th>
                                <th rowspan="2" class="text-center  primary-header">Monthly Basic Pay</th>
                                <th rowspan="2" class="text-center  primary-header">Quinsina Pay</th>
                                <th rowspan="2" class="text-center  primary-header">Allowance</th>
                                <th rowspan="2" class="text-center  primary-header">Allowance Quinsina</th>
                                <th rowspan="2" class="text-center  success-header">Basic Daily Rate</th>
                                <th rowspan="2" class="text-center  primary-header">Allowance Daily Rate</th>
                                <th rowspan="2" class="text-center  primary-header" >No. of Duty</th>
                                <th rowspan="2" class="text-center  primary-header">Absences</th>
                                <th rowspan="2" class="text-center  primary-header">Amount Absences</th>
                                <th rowspan="2" class="text-center  success-header">Total Amount</th>
                                <th rowspan="2" class="text-center  primary-header">Legal Holiday</th>
                                <th rowspan="2" class="text-center  primary-header">Amount</th>
                                <th rowspan="2" class="text-center  primary-header">Sunday Duty</th>
                                <th rowspan="2" class="text-center  primary-header">Amount</th>
                                <th rowspan="2" class="text-center  primary-header">Special Holiday</th>
                                <th rowspan="2" class="text-center  primary-header">Amount</th>
                                <th colspan="3" class="text-center info-header">Overtime</th>

                                <th colspan="3" class="text-center info-header">Late</th>
                                <th rowspan="2" class="text-center success-header">Total Gross PAY</th>
                                <th colspan="<?= count($contributions_settings) + 4 ?>" class="text-center danger-header">Deduction</th>
                                <th rowspan="2" class="text-center danger-header">Total Deduction</th>
                                <th rowspan="2" class="text-center success-header">Net Pay</th>
                                <th rowspan="2" class="text-center primary-header" style="min-width: 120px;">Signature</th>
                                <th rowspan="2" class="text-center  primary-header">No.</th>
                            </tr>
                            <tr>
                                <th class="text-center  info-header" style="min-width: 40px;" >No. hr</th>
                                <th class="text-center  info-header">Rate</th>
                                <th class="text-center  info-header">Amount</th>

                                <th class="text-center  info-header">Min</th>
                                <th class="text-center  info-header">Rate</th>
                                <th class="text-center  info-header">Amount</th>

                                <?php if (count($contributions_settings) > 0) {
                                    foreach ($contributions_settings as $k) {
                                        if ($k['type'] == 1) {
                                            $query_con = "SELECT * FROM contributions   WHERE id = ?";
                                            $stmt_con = $conn->prepare($query_con);
                                            $stmt_con->bind_param("i", $k['id']);
                                            $stmt_con->execute();
                                            $result_con = $stmt_con->get_result();
                                            $contribution = $result_con->fetch_assoc();
                                            $name_deduction = $contribution['contribution'];
                                        } else if ($k['type'] == 3) {
                                            $query_con = "SELECT * FROM contribution_loan_types   WHERE clt_id = ?";
                                            $stmt_con = $conn->prepare($query_con);
                                            $stmt_con->bind_param("i", $k['id']);
                                            $stmt_con->execute();
                                            $result_con = $stmt_con->get_result();
                                            $contribution = $result_con->fetch_assoc();
                                            $name_deduction =  $contribution['loan_type'];
                                        } else if ($k['type'] == 2) {
                                            $query_con = "SELECT * FROM deductions   WHERE id = ?";
                                            $stmt_con = $conn->prepare($query_con);
                                            $stmt_con->bind_param("i", $k['id']);
                                            $stmt_con->execute();
                                            $result_con = $stmt_con->get_result();
                                            $contribution = $result_con->fetch_assoc();
                                            $name_deduction =  $contribution['deduction'];
                                        }


                                ?>
                                        <th class="text-center danger-header" style="min-width: 100px;"><?= $name_deduction ?></th>
                                    <?php } ?>
                                <?php } else { ?>
                                    <th class="text-center danger-header">
                                        No deduction found
                                    </th>
                                <?php } ?>
                                <th class="text-center  danger-header">SSS PROVIDENT FUND </th>
                                <th class="text-center  danger-header">JEI ADVANCE</th>
                                <th class="text-center  danger-header">JCC ADVANCES</th>
                                <th class="text-center  danger-header">Tax</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_number_days = 0;
                            $t_basic_rate = 0;
                            $t_per_day = 0;
                            $t_allowance = 0;
                            $t_gross = 0;
                            $t_deduction = 0;
                            $t_net = 0;
                            $t_perDay = 0;
                            $t_total_amount = 0;
                            $t_ot_rate = 0;
                            $t_late = 0;

                            while ($row = $query->fetch_assoc()) {
                                $i++;
                                $minutesPerDay = 8 * 60;
                                $perMinute = $row['per_day'] / $minutesPerDay;
                                $employee_id = $row['employee_id'];
                                $total_basic_rate = $row['basic_pay'];
                                $overtime_amount = $row['ot'] * $row['ot_rate'];
                                $t_ot_rate += $row['ot_rate'];
                                $late_amount = $row['late'] * $perMinute;
                                $t_late += $perMinute;
                                $undertime_amount = $row['under_time'] * $perMinute;
                                $allowance_amount = $row['allowance_amount'];
                                $tax = $row['tax'];
                                $absent_amount = $row['absent'] *  $row['per_day'];
                                $jei_advances = $row['jei_advances'];
                                $jcc_advances = $row['jcc_advances'];
                                $sss_fund = $row['sss_fund'];
                                $perDay = $row['per_day'];
                                $t_perDay += $perDay;
                                $legal_holiday = $row['legal_holiday'];
                                $legal_holiday_amount =  $legal_holiday * $perDay;
                                $sunday_duty = $row['sunday_duty'];
                                $sunday_duty_amount =  $sunday_duty * $perDay;
                                $special_holiday = $row['special_holiday'];
                                $special_holiday_amount =  (($perDay / 8) * 2.4) *  $special_holiday;

                                $total_amount =  ($total_basic_rate    + $allowance_amount - $absent_amount) / 2;
                                $t_total_amount += $total_amount;
                                $gross_salary =  ($total_amount +   $overtime_amount   +  $legal_holiday_amount + $sunday_duty_amount +  $special_holiday_amount - $late_amount);

                                $contributions = json_decode($row['contributions'], true);
                                $deductions = json_decode($row['deductions'], true);
                                $loans = json_decode($row['loans'], true);
                                $total_deductions =  0;



                                $total_number_days += $row['present'];
                                $t_basic_rate += $total_basic_rate;
                                $t_per_day += $perDay;
                                $t_allowance += $allowance_amount;
                                $t_gross += $gross_salary;

                            ?>
                                <tr>
                                    <td class="text-center" style="min-width: 30px;  font-size: 10px !important"><b><?= $i ?></b></td>
                                    <td style="min-width:200px;  font-size: 10px !important">
                                        <b><?= $row['lastname'] ?>, <?= $row['firstname'] ?></b>
                                    </td>
                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['position'] ?>
                                    </td>
                                    <td class="text-center" style=" font-size: 10px !important">
                                       <?= $row['site_code'] ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                      <?= number_format($row['basic_pay'], 2) ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($row['basic_pay'] / 2, 2) ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($allowance_amount, 2) ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($allowance_amount / 2, 2) ?>
                                    </td>
                                    <!-- Basic Daily Rate -->
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($row['per_day'], 2) ?>
                                    </td>
                                    <!-- Allowance Daily Rate -->
                                    <td class="text-right" style=" font-size: 10px !important"><?= number_format($allowance_amount / 26, 2) ?></td>
                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['present'] ?>
                                    </td>

                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['absent'] ?>
                                    </td>

                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($absent_amount, 2) ?>
                                    </td>
                                    <!-- total amount -->
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($total_amount, 2) ?>
                                    </td>
                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['legal_holiday'] ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($legal_holiday_amount, 2) ?>
                                    </td>
                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['sunday_duty'] ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($sunday_duty_amount, 2) ?>
                                    </td>
                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['special_holiday'] ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                       <?= number_format($special_holiday_amount, 2) ?>
                                    </td>
                                    <!-- ot -->
                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['ot'] ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($row['ot_rate'], 2) ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($overtime_amount, 2) ?>
                                    </td>

                                    <!-- /ot -->

                                    <!-- Late -->
                                    <td class="text-center" style=" font-size: 10px !important">
                                        <?= $row['late'] ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($perMinute, 2) ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($late_amount, 2) ?>
                                    </td>
                                    <!-- /Late -->
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($gross_salary, 2) ?>
                                    </td>
                                    <?php

                                    if (count($contributions_settings) > 0) {
                                        foreach ($contributions_settings as $i2 =>  $k) {
                                            $deduction_amount = 0;
                                            if ($k['type'] == 1) {
                                                foreach ($contributions as $kd) {
                                                    if ($kd["contribution_id"] == $k["id"]) {
                                                        $deduction_amount = $kd["amount"];
                                                    }
                                                }
                                            }
                                            if ($k['type'] == 2) {
                                                foreach ($deductions as $kd) {
                                                    if ($kd["deduction_id"] == $k["id"]) {
                                                        $deduction_amount = $kd["amount"];
                                                    }
                                                }
                                            }
                                            if ($k['type'] == 3) {
                                                foreach ($loans as $kd) {
                                                    if ($kd["deduction_id"] == $k["id"]) {
                                                        $deduction_amount = $kd["amount"];
                                                    }
                                                }
                                            }

                                            $total_deductions += $deduction_amount;


                                    ?>
                                            <td class="text-right" style=" font-size: 10px !important">
                                                <?= number_format($deduction_amount, 2) ?>
                                            </td>
                                        <?php } ?>
                                    <?php  } else { ?>
                                        <td style="min-width: 200px;" class="text-center">
                                            No deduction found
                                        </td>
                                    <?php } ?>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($sss_fund, 2) ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($jei_advances, 2) ?>

                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($jcc_advances, 2) ?>

                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($tax, 2) ?>
                                    </td>


                                    <?php
                                    $total_deductions = $total_deductions   + $tax + $jei_advances + $jcc_advances + $sss_fund;
                                    $t_deduction += $total_deductions;
                                    $net = $gross_salary -  $total_deductions;
                                    $t_net += $net;
                                    ?>

                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($total_deductions, 2) ?>
                                    </td>
                                    <td class="text-right" style=" font-size: 10px !important">
                                        <?= number_format($net, 2) ?>
                                    </td>
                                    <td></td>
                                    <td class="text-center" style="min-width: 30px;  font-size: 10px !important"><b><?= $i ?></b></td>

                                </tr>
                                <input style="display: none;" name="id[]" value="<?= $row['id'] ?>" />
                                <input style="display: none;" name="net[]" value="<?= $net ?>" />
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th colspan="3" class="text-center">SUBTOTAL AMOUNT</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= number_format($t_perDay, 2) ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= number_format($t_total_amount, 2) ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= number_format($t_ot_rate, 2) ?></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= number_format($t_late, 2) ?></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= number_format($t_gross, 2) ?></th>
                                <th colspan="<?= count($contributions_settings) ?>"></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= number_format($t_deduction, 2) ?></th>
                                <th class="text-right"><?= number_format($t_net, 2) ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                    <div style="margin-top: 40px; display: flex;justify-content: space-evenly; font-size: 11px;">
                <div>
                    Prepared By:
                    <div style="margin-left: 20px;">
                        <p><b><?= $payroll['prepared_by'] ?></b></p>
                        <p><?= $payroll['prepared_by_role'] ?></p>
                    </div>
                </div>
                <div>
                    Verified By:
                    <div style="margin-left: 20px;">
                    <p><b><?= $payroll['verified_by'] ?></b></p>
                    <p><?= $payroll['verified_by_role'] ?></p>
                    </div>
                </div>
                <!-- <div>
                    Noted By:
                    <div style="margin-left: 20px;">
                        <p><b>Cristy Rapeza</b></p>
                        <p>HR HEAD</p>
                    </div>
                </div> -->
                <!-- <div>
                    Checked By:
                    <div style="margin-left: 20px;">
                        <p><b>Jovanie Alab</b></p>
                        <p> ACCOUNTING PAYABLE TEAM LEADER</p>
                    </div>
                </div> -->
                <div>
                    Approved By:
                    <p><b><?= $payroll['approved_by'] ?></b></p>
                    <p><?= $payroll['approved_by_role'] ?></p>
                </div>
            </div>


                </div>
            </div>
            <br>

            <br> </br>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

    </div>
</body>
<script src="html2pdf.bundle.min.js"></script>
<!-- <script src="xlsx.full.min.js"></script> -->
<script>
    // window.print();
    // window.onafterprint = function() {
    //     window.close();
    //     history.back();
    // };

    function generatePDF() {
        Swal.fire({
        title: "Downlooding, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
        // let table = document.getElementById("table-1");
        //     let workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });

        //     // Dynamic filename from PHP
        //     let filename = "<?php
        //         $dateFrom = date('F d', strtotime($payroll['date_from']));
        //         $dateTo = date('F j, Y', strtotime($payroll['date_to']));
        //         echo $dateFrom . ' - ' . $dateTo;
        //     ?>.xlsx";

        //     XLSX.writeFile(workbook, filename);
        // return
        const element = document.getElementById('print-area');
        const opt = {
            margin: 10,
            filename: `<?php
        $dateFrom = date("F d", strtotime($payroll['date_from']));
        $dateTo = date("F j, Y", strtotime($payroll['date_to']));
        echo $dateFrom . " - " . $dateTo;
    ?>.pdf`,
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'mm',
                format: [990, 216], 
                orientation: 'landscape' 
            }
        };
        html2pdf().set(opt).from(element).save().then(() => {
        setTimeout(() => {
            Swal.close();
            window.close();
            history.back();
        }, 0);
    });
    }
    //generatePDF();
</script>