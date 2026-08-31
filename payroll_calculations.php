<?php
if (!isset($_GET['id'])) {
    return;
}
$id = (int) $_GET['id'];


$filter_query = "";
$sid = '';
if (isset($_GET['site_id'])  && $_GET['site_id'] !== 'all') {
    $sid = $_GET['site_id'];
    $filter_query = " AND a.site_id = $sid  ";
}
// LEFT JOIN payroll g ON g.id = a.payroll_id 
// LEFT JOIN employers  h ON g.employer_id = h.id

$query2 = "SELECT payroll.*, branches.branch_name FROM payroll LEFT JOIN branches ON branches.id = JSON_UNQUOTE(JSON_EXTRACT(payroll.site_ids, '$[0]')) WHERE payroll.id = ?";
$stmt = $conn->prepare($query2);
if (!$stmt) { die(json_encode(['error' => $conn->error])); }
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();
$payroll_r = $payroll;
$site_ids = json_decode($payroll['site_ids'], true);
$site_ids = isset($site_ids) ? $site_ids : [];
$commaSeparatedSites = implode(',', $site_ids);
$status = $payroll['status'];

$i = 0;
$query = $conn->query("SELECT a.*, f.branch_code, f.branch_name, e.employee_no, e.lastname, e.firstname, e.middlename, e.basic_pay, d.name as department, p.name as position
FROM payroll_items a
INNER JOIN employee e ON a.employee_id = e.id
LEFT JOIN department d ON e.department_id = d.id
LEFT JOIN position p ON e.position_id = p.id
LEFT JOIN branches f ON f.id = a.site_id
WHERE a.payroll_id = $id ORDER BY e.lastname ASC");
if (!$query) { die('<p>Query error: ' . htmlspecialchars($conn->error) . '</p>'); }

$contributions_settings = json_decode($payroll['settings'], true) ?: [];

$refunds_settings  = array_filter($contributions_settings, function ($item) {
    return $item["type"] === 4;
});

$contributions_settings = array_filter($contributions_settings, function ($item) {
    return $item["type"] !== 4;
});

$contributions_settings = array_values($contributions_settings);



$payroll_type = $payroll['type'];

?>
<link rel="stylesheet" href="assets2/css/my-style.css">
<style>
/* remove freeze/sticky */
#table-1 thead th,
#table-1 tbody td:nth-child(1),
#table-1 tbody td:nth-child(2),
#table-1 tfoot th:nth-child(1),
#table-1 tfoot th:nth-child(2),
#table-1 thead tr:first-child th:nth-child(1),
#table-1 thead tr:first-child th:nth-child(2),
.net-content {
    position: static !important;
    z-index: auto !important;
    box-shadow: none !important;
    transform: none !important;
    left: auto !important;
    right: auto !important;
}
/* simple colors */
#table-1 thead th { background-color: #219688 !important; color: #fff !important; border-color: #cccccc !important; }
#table-1 thead tr:first-child th { background-color: #176358 !important; }
#table-1 tbody td:nth-child(1), #table-1 tfoot th:nth-child(1) { background-color: #fff !important; color: #333 !important; border-right: 1px solid #cccccc !important; font-weight: normal !important; }
#table-1 tbody td:nth-child(2), #table-1 tfoot th:nth-child(2) { background-color: #fff !important; color: #333 !important; border-right: 1px solid #cccccc !important; font-weight: normal !important; box-shadow: none !important; }
#table-1 tbody tr:nth-child(even) td { background-color: #fff !important; }
#table-1 tbody tr:hover td { background-color: #f0faf9 !important; }
.net-content { background-color: #fff !important; color: #333 !important; border-left: 1px solid #cccccc !important; font-weight: bold !important; }
.table-responsive2 { border: 1px solid #cccccc !important; height: auto !important; max-height: 600px; }
</style>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <div class="page-title-enhanced">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">Payroll Details</h4>
                                    <?php if ($status == 2): ?>
                                        <span class="payroll-status-badge bg-danger text-white"><i class="ri-lock-fill me-1"></i>Locked</span>
                                    <?php else: ?>
                                        <span class="payroll-status-badge bg-success text-white"><i class="ri-lock-unlock-line me-1"></i>Open</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <i class="ri-calendar-2-line me-1"></i>
                                    <?= date('M d', strtotime($payroll['date_from'])) ?> &ndash; <?= date('M d, Y', strtotime($payroll['date_to'])) ?>
                                    &nbsp;&bull;&nbsp;
                                    <i class="ri-building-line me-1"></i><?= htmlspecialchars($payroll['branch_name'] ?? '') ?>
                                </small>
                            </div>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Payroll</a></li>
                                <li class="breadcrumb-item active">Payroll Details</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="xl-panel" id="myDiv">
                    <div class="xl-ribbon">
                        <span class="xl-ribbon-title">
                            <i class="ri-file-excel-2-line"></i> Payroll List
                        </span>
                        <div class="xl-ribbon-actions">
                            <button data-toggle="tooltip" id="sf" title="Fullscreen" onclick="openFullscreen()" class="xl-btn"><i class="ri-fullscreen-line"></i></button>
                            <button style="display:none;" id="hf" data-toggle="tooltip" title="Exit Fullscreen" onclick="closeFullscreen()" class="xl-btn"><i class="ri-fullscreen-exit-line"></i></button>
                            <div class="xl-ribbon-sep"></div>
                            <a data-toggle="tooltip" title="Print Payroll for All Employees"
                                href="print-payroll.php?id=<?= $id ?>"
                                target="_self" class="xl-btn xl-btn-info">
                                <i class="ri-printer-line"></i> Print All Employees
                            </a>
                            <?php if ($status !== 2) { ?>
                                <div class="xl-ribbon-sep"></div>
                                <button data-toggle="tooltip" title="Lock Payroll" onclick="lockPayroll(<?= $id ?>)" class="xl-btn xl-btn-danger"><i class="ri-lock-line"></i> Lock</button>
                                <div class="xl-ribbon-sep"></div>
                                <button onclick="saveUnsaved()" id="btn-unsaved" title="Save Changes" class="xl-btn xl-btn-save" style="display:none;">
                                    <i class="bx bx-save"></i> Save
                                    <span id="counter-unsaved">0</span>
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="xl-panel-body">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <div class="xl-search-wrap">
                                <i class="ri-search-2-line"></i>
                                <input id="myInput" type="text" placeholder="Search...">
                            </div>
                        </div>
                        <form id="form-payroll">
                            <div class="table-responsive2" id="table-responsive2">
                                <?php if ($payroll_type == 5) { ?>

                                    <table cellspacing="0" id="table-1">
                                        <thead>
                                            <!-- ═══ ROW 1 : Section group banners ═══ -->
                                            <tr>
                                                <th rowspan="2" class="text-center">No.</th>
                                                <th rowspan="2" class="text-center">Name</th>
                                                <th rowspan="2" class="text-center">Position</th>
                                                
                                                <!-- Basic Earnings (3 cols) -->
                                                <th colspan="3" class="text-center">Basic Earnings</th>
                                                <!-- Allowance (3 cols) -->
                                                <th colspan="3" class="text-center">Allowance</th>
                                                <!-- Attendance (4 cols) -->
                                                <th colspan="4" class="text-center">Attendance</th>
                                                <!-- Holidays & Extra Duties (6 cols) -->
                                                <th colspan="6" class="text-center">Holidays &amp; Extra Duties</th>
                                                <!-- Overtime (3 cols) -->
                                                <th colspan="3" class="text-center">Overtime</th>
                                                <!-- Late (3 cols) -->
                                                <th colspan="3" class="text-center">Late</th>
                                                <th rowspan="2" class="text-center">Total Gross Pay</th>
                                                <!-- Deductions -->
                                                <th colspan="<?= count($contributions_settings) + 4 ?>" class="text-center">Deductions</th>
                                                <th rowspan="2" class="text-center">Total Deduction</th>
                                                <?php if (count($refunds_settings) > 0) { ?>
                                                    <th colspan="<?= count($refunds_settings) ?>" class="text-center">Refunds</th>
                                                <?php } ?>
                                                <th rowspan="2" class="text-center">Net Pay</th>
                                                <th rowspan="2" class="text-center">Actions</th>
                                                <th rowspan="2" class="text-center">No.</th>
                                            </tr>
                                            <!-- ═══ ROW 2 : Individual column labels ═══ -->
                                            <tr>
                                                <!-- Basic Earnings -->
                                                <th class="text-center">Monthly Basic Pay</th>
                                                <th class="text-center">Quinsena Pay</th>
                                                <th class="text-center">Basic Daily Rate</th>
                                                <!-- Allowance -->
                                                <th class="text-center">No. Days</th>
                                                <th class="text-center">Rate</th>
                                                <th class="text-center">Amount</th>
                                                <!-- Attendance -->
                                                <th class="text-center">No. of Duty</th>
                                                <th class="text-center">Absences</th>
                                                <th class="text-center">Amt. Absences</th>
                                                <th class="text-center">Total Amount</th>
                                                <!-- Holidays & Extra Duties -->
                                                <th class="text-center">Legal Holiday</th>
                                                <th class="text-center">Amount</th>
                                                <th class="text-center">Sunday Duty</th>
                                                <th class="text-center">Amount</th>
                                                <th class="text-center">Special Holiday</th>
                                                <th class="text-center">Amount</th>
                                                <!-- Overtime -->
                                                <th class="text-center">No. Hrs</th>
                                                <th class="text-center">Rate</th>
                                                <th class="text-center">Amount</th>
                                                <!-- Late -->
                                                <th class="text-center">Minutes</th>
                                                <th class="text-center">Rate</th>
                                                <th class="text-center">Amount</th>
                                                <!-- Deduction sub-headers -->
                                                <?php if (count($contributions_settings) > 0) {
                                                    foreach ($contributions_settings as $k) {
                                                        if ($k['type'] == 1) {
                                                            $query_con = "SELECT * FROM contributions WHERE id = ?";
                                                            $stmt_con = $conn->prepare($query_con);
                                                            $stmt_con->bind_param("i", $k['id']);
                                                            $stmt_con->execute();
                                                            $contribution = $stmt_con->get_result()->fetch_assoc();
                                                            $name_deduction = $contribution['contribution'];
                                                        } elseif ($k['type'] == 3) {
                                                            $query_con = "SELECT * FROM contribution_loan_types WHERE clt_id = ?";
                                                            $stmt_con = $conn->prepare($query_con);
                                                            $stmt_con->bind_param("i", $k['id']);
                                                            $stmt_con->execute();
                                                            $contribution = $stmt_con->get_result()->fetch_assoc();
                                                            $name_deduction = $contribution['loan_type'];
                                                        } elseif ($k['type'] == 2) {
                                                            $query_con = "SELECT * FROM deductions WHERE id = ?";
                                                            $stmt_con = $conn->prepare($query_con);
                                                            $stmt_con->bind_param("i", $k['id']);
                                                            $stmt_con->execute();
                                                            $contribution = $stmt_con->get_result()->fetch_assoc();
                                                            $name_deduction = $contribution['deduction'];
                                                        }
                                                ?>
                                                        <th class="text-center"><?= htmlspecialchars($name_deduction) ?></th>
                                                <?php } } ?>
                                                <th class="text-center">SSS Provident Fund</th>
                                                <th class="text-center">JEI Advance</th>
                                                <th class="text-center">JCC Advances</th>
                                                <th class="text-center">Tax</th>
                                                <!-- Refund sub-headers -->
                                                <?php if (count($refunds_settings) > 0) {
                                                    foreach ($refunds_settings as $k) {
                                                        $query_con = "SELECT * FROM refunds WHERE id = ?";
                                                        $stmt_con = $conn->prepare($query_con);
                                                        $stmt_con->bind_param("i", $k['id']);
                                                        $stmt_con->execute();
                                                        $rfund = $stmt_con->get_result()->fetch_assoc();
                                                ?>
                                                        <th class="text-center"><?= htmlspecialchars($rfund['refunds']) ?></th>
                                                <?php } } ?>
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
                                                $allowance_days = $row['allowance_days'];
                                                $total_allowance = $allowance_amount *  $allowance_days;
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

                                                $total_amount =  ($total_basic_rate    +  $total_allowance - $absent_amount) / 2;
                                                $t_total_amount += $total_amount;
                                                $gross_salary =  ($total_amount +   $overtime_amount   +  $legal_holiday_amount + $sunday_duty_amount +  $special_holiday_amount - $late_amount);

                                                $contributions = json_decode($row['contributions'], true);
                                                $deductions = json_decode($row['deductions'], true);
                                                $loans = json_decode($row['loans'], true);
                                                $refunds = json_decode($row['refunds'], true);
                                                $total_deductions =  0;
                                                $total_refunds =  0;



                                                $total_number_days += $row['present'];
                                                $t_basic_rate += $total_basic_rate;
                                                $t_per_day += $perDay;
                                                $t_allowance += $allowance_amount;
                                                $t_gross += $gross_salary;

                                            ?>
                                                <tr class="name-<?= $row['id'] ?>">
                                                    <td class="text-center" style="min-width: 40px;"><b><?= $i ?></b></td>
                                                    <td style="min-width:200px;">
                                                        <b><?= $row['lastname'] ?>, <?= $row['firstname'] ?></b>
                                                    </td>
                                                    <td style="min-width: 200px;" class="text-center">
                                                        <?= $row['position'] ?>
                                                    </td>
                                                    <td class="text-right" style="min-width: 130px;">
                                                        <b><?= number_format($row['basic_pay'], 2) ?></b>
                                                    </td>
                                                    <td class="text-right" style="min-width: 130px;">
                                                        <b><?= number_format($row['basic_pay'] / 2, 2) ?></b>
                                                    </td>
                                                    <!-- Late -->
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['allowance_days'] ?>" data-id="<?= $row['id'] ?>" data-type="allowance_days" class="form-control input-class" placeholder="Days" aria-label="Days" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'allowance_days')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['allowance_days'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="min-width: 100px;" class="text-right">
                                                        <b><?= number_format($allowance_amount, 2) ?></b>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($total_allowance, 2) ?></b>
                                                    </td>
                                                    <!-- /Late -->
                                                    <!-- <td style="min-width: 90px;" class="text-right">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $allowance_amount ?>" class="form-control input-class" placeholder="Allowance" aria-label="Allowance" aria-describedby="basic-addon2">
                                                                <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'allowance_amount')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div>
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= number_format($allowance_amount, 2) ?></b>
                                                        <?php } ?>
                                                    </td> -->
                                                    <!-- <td style="min-width: 90px;" class="text-right">
                                                        <b><?= number_format($allowance_amount / 2, 2) ?></b>
                                                    </td> -->
                                                    <!-- Basic Daily Rate -->
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <b><?= number_format($row['per_day'], 2) ?></b>
                                                    </td>
                                                    <!-- Allowance Daily Rate -->
                                                    <!-- <td class="text-right"><?= number_format($allowance_amount / 26, 2) ?></td> -->
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['present'] ?>" data-id="<?= $row['id'] ?>" data-type="present" class="form-control input-class" placeholder="No. of Days" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'present')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <?= $row['present'] ?>
                                                        <?php } ?>
                                                    </td>

                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['absent'] ?>" data-id="<?= $row['id'] ?>" data-type="absent" class="form-control input-class" placeholder="absent" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'absent')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['absent'] ?></b>
                                                        <?php } ?>
                                                    </td>

                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($absent_amount, 2) ?></b>
                                                    </td>
                                                    <!-- total amount -->
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($total_amount, 2) ?></b>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['legal_holiday'] ?>" data-id="<?= $row['id'] ?>" data-type="legal_holiday" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'legal_holiday')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['legal_holiday'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($legal_holiday_amount, 2) ?></b>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['sunday_duty'] ?>" data-id="<?= $row['id'] ?>" data-type="sunday_duty" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'sunday_duty')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['sunday_duty'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($sunday_duty_amount, 2) ?></b>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['special_holiday'] ?>" data-id="<?= $row['id'] ?>" data-type="special_holiday" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'special_holiday')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['special_holiday'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($special_holiday_amount, 2) ?></b>
                                                    </td>
                                                    <!-- ot -->
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['ot'] ?>" data-id="<?= $row['id'] ?>" data-type="ot" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'ot')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['ot'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <b><?= number_format($row['ot_rate'], 2) ?></b>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($overtime_amount, 2) ?></b>
                                                    </td>

                                                    <!-- /ot -->

                                                    <!-- Late -->
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['late'] ?>" data-id="<?= $row['id'] ?>" data-type="late" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'late')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['late'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="min-width: 100px;" class="text-right">
                                                        <b><?= number_format($perMinute, 2) ?></b>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($late_amount, 2) ?></b>
                                                    </td>
                                                    <!-- /Late -->
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($gross_salary, 2) ?></b>
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
                                                            <td style="min-width: 90px;" class="text-right">
                                                                <?php if ($status === 1) { ?>
                                                                    <div class="input-group mb-3">
                                                                        <input type="text" value="<?= $deduction_amount ?>" data-id="<?= $row['id'] ?>" data-type='<?= $k['type'] == 1 ? 'contribution' : ($k['type'] == 3 ? 'loan' : 'deduction') ?>' data-dd_id="<?= $k['id'] ?>" class="form-control input-class" placeholder="Enter Amount" aria-label="Enter Amount" aria-describedby="basic-addon2">
                                                                        <!-- <div class="input-group-append">
                                                                            <button
                                                                                onclick="updateData(this, <?= $row['id'] ?>, '<?= $k['type'] == 1 ? 'contribution' : ($k['type'] == 3 ? 'loan' : 'deduction') ?>', <?= $k['id'] ?>)"
                                                                                class="btn btn-success"
                                                                                data-toggle="tooltip"
                                                                                title="Save Changes"
                                                                                type="button">
                                                                                <i class="ri-save-fill"></i>
                                                                            </button>
                                                                        </div> -->
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <b><?= number_format($deduction_amount, 2) ?></b>
                                                                <?php } ?>
                                                            </td>
                                                        <?php } ?>
                                                    <?php  } else { ?>

                                                    <?php } ?>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $sss_fund ?>" data-id="<?= $row['id'] ?>" data-type="sss_fund" class="form-control input-class" placeholder="Enter Amount" aria-label="sss_fund" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'sss_fund')"   class="btn btn-success" data-toggle="tooltip" title="Save Changes" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= number_format($sss_fund, 2) ?></b>
                                                        <?php } ?>

                                                    </td>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $jei_advances ?>" data-id="<?= $row['id'] ?>" data-type="jei_advances" class="form-control input-class" placeholder="Enter Amount" aria-label="jei_advances" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'jei_advances')" class="btn btn-success" data-toggle="tooltip" title="Save Changes" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= number_format($jei_advances, 2) ?></b>
                                                        <?php } ?>

                                                    </td>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $jcc_advances ?>" data-id="<?= $row['id'] ?>" data-type="jcc_advances" class="form-control input-class" placeholder="Enter Amount" aria-label="jcc_advances" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'jcc_advances')" class="btn btn-success" data-toggle="tooltip" title="Save Changes" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= number_format($jcc_advances, 2) ?></b>
                                                        <?php } ?>

                                                    </td>

                                                    <td style="min-width: 90px;" class="text-right">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $tax ?>" class="form-control input-class" data-id="<?= $row['id'] ?>" data-type="tax" placeholder="Enter Amount" aria-label="Other Deduction" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'tax')" class="btn btn-success" data-toggle="tooltip" title="Save Changes" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= number_format($tax, 2) ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <?php $total_deductions = $total_deductions   + $tax + $jei_advances + $jcc_advances + $sss_fund;
                                                    $t_deduction += $total_deductions;  ?>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <b><?= number_format($total_deductions, 2) ?></b>
                                                    </td>
                                                    <!-- refunds -->
                                                    <?php if (count($refunds_settings) > 0) {;
                                                        foreach ($refunds_settings as $kd) {
                                                            $refund_amount = 0;
                                                            foreach ($refunds as $cd) {
                                                                if ($cd["refund_id"] == $kd["id"]) {
                                                                    $refund_amount = $cd["amount"];
                                                                }
                                                            }
                                                            $total_refunds += $refund_amount;





                                                    ?>
                                                            <td style="min-width: 90px;" class="text-right">
                                                                <?php if ($status === 1) { ?>
                                                                    <div class="input-group mb-3">
                                                                        <input type="text" value="<?= $refund_amount ?>" data-id="<?= $row['id'] ?>" data-dd_id="<?= $kd['id'] ?>" data-type="refund" class="form-control input-class" placeholder="Enter Amount" aria-label="Enter Amount" aria-describedby="basic-addon2">
                                                                        <!-- <div class="input-group-append">
                                                                        <button
                                                                            onclick="updateData(this, <?= $row['id'] ?>, 'refund', <?= $kd['id'] ?>)"
                                                                            class="btn btn-success"
                                                                            data-toggle="tooltip"
                                                                            title="Save Changes"
                                                                            type="button">
                                                                            <i class="ri-save-fill"></i>
                                                                        </button>
                                                                    </div> -->
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <b><?= number_format($refund_amount, 2) ?></b>
                                                                <?php } ?>
                                                            </td>
                                                    <?php
                                                        }
                                                    } ?>
                                                    <?php

                                                    $net = $gross_salary -  $total_deductions + $total_refunds;
                                                    $t_net += $net;
                                                    ?>
                                                    <td style="min-width: 90px;" class="text-right net-content">
                                                        <b><?= number_format($net, 2) ?></b>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <a href="view_payslip.php?id=<?= $row['id'] ?>" class="xl-btn" data-toggle="tooltip" title="View Payslip" id="<?= $row['id'] ?>" site_name="<?= htmlspecialchars($row['branch_name']) ?>" onclick="edit_function(this)">
                                                            <i class="ri-file-text-line"></i> View
                                                        </a>
                                                    </td>
                                                    <td class="text-center" style="min-width: 40px;"><b><?= $i ?></b></td>

                                                </tr>
                                                <input style="display: none;" name="id[]" value="<?= $row['id'] ?>" />
                                                <input style="display: none;" name="net[]" value="<?= $net ?>" />
                                                <input style="display: none;" class="net-class" did="<?= $row['id'] ?>" value="<?= $net ?>" />
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
                                                <th></th>
                                                <th class="text-right"><?= number_format($t_perDay, 2) ?></th>
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
                                                <th class="text-right"><?= number_format($t_gross, 2) ?></th>
                                                <th colspan="<?= count($contributions_settings) ?>"></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th class="text-right"><?= number_format($t_deduction, 2) ?></th>
                                                <?php if (count($refunds_settings) > 0) { ?>
                                                    <th colspan="<?= count($refunds_settings) ?>"></th>
                                                <?php } ?>
                                                <th class="text-right"><?= number_format($t_net, 2) ?></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>




                                <?php } else { ?>
                                    <table cellspacing="0" id="table-1">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="text-center">No.</th>
                                                <th rowspan="2" class="text-center">Name</th>
                                                <th rowspan="2" class="text-center">Position</th>
                                                <th rowspan="2" class="text-center">No. of Days</th>
                                                
                                                <th rowspan="2" class="text-center">Basic Rate</th>
                                                <th rowspan="2" class="text-center">Total Basic Rate</th>
                                                <th colspan="3" class="text-center">Allowance</th>
                                                <th colspan="3" class="text-center">Overtime</th>
                                                <th colspan="3" class="text-center">Late</th>
                                                <th rowspan="2" class="text-center">GROSS SALARY</th>
                                                <th colspan="<?= count($contributions_settings) ?>" class="text-center">Deduction</th>
                                                <th rowspan="2" class="text-center">Total Deduction</th>
                                                <?php if (count($refunds_settings) > 0) { ?>
                                                    <th colspan="<?= count($refunds_settings) ?>" class="text-center">Refunds</th>
                                                <?php } ?>
                                                <th rowspan="2" class="text-center">Net Pay</th>
                                                <th rowspan="2" class="text-center">Actions</th>
                                                <th rowspan="2" class="text-center">No.</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">No. dys</th>
                                                <th class="text-center">Rate</th>
                                                <th class="text-center">Amount</th>

                                                <th class="text-center">No. hr</th>
                                                <th class="text-center">Rate</th>
                                                <th class="text-center">Amount</th>

                                                <th class="text-center">Min</th>
                                                <th class="text-center">Rate</th>
                                                <th class="text-center">Amount</th>

                                                <?php if (count($contributions_settings) > 0) {
                                                    foreach ($contributions_settings as $k) {
                                                        if ($k['type'] == 1) {
                                                            $query_con = "SELECT * FROM contributions   WHERE id = ?";
                                                            $stmt_con = $conn->prepare($query_con);
                                                            $stmt_con->bind_param("i", $k['id']);
                                                            $stmt_con->execute();
                                                            $result_con = $stmt_con->get_result();
                                                            $contribution = $result_con->fetch_assoc();
                                                                $name_deduction = $contribution['contribution'] ?? '';
                                                        } else if ($k['type'] == 3) {
                                                            $query_con = "SELECT * FROM contribution_loan_types   WHERE clt_id = ?";
                                                            $stmt_con = $conn->prepare($query_con);
                                                            $stmt_con->bind_param("i", $k['id']);
                                                            $stmt_con->execute();
                                                            $result_con = $stmt_con->get_result();
                                                            $contribution = $result_con->fetch_assoc();
                                                                $name_deduction = $contribution['loan_type'] ?? '';
                                                        } else if ($k['type'] == 2) {
                                                            $query_con = "SELECT * FROM deductions   WHERE id = ?";
                                                            $stmt_con = $conn->prepare($query_con);
                                                            $stmt_con->bind_param("i", $k['id']);
                                                            $stmt_con->execute();
                                                            $result_con = $stmt_con->get_result();
                                                            $contribution = $result_con->fetch_assoc();
                                                                $name_deduction = $contribution['deduction'] ?? '';
                                                        }


                                                ?>
                                                        <th class="text-center"><?= $name_deduction ?></th>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                <?php } ?>
                                                <!-- <th class="text-center">SSS PROVIDENT FUND </th>
                                                <th class="text-center">JEI ADVANCE</th>
                                                <th class="text-center">JCC ADVANCES</th> -->
                                                <?php if (count($refunds_settings) > 0) {
                                                    foreach ($refunds_settings as $k) {
                                                        $query_con = "SELECT * FROM refunds   WHERE id = ?";
                                                        $stmt_con = $conn->prepare($query_con);
                                                        $stmt_con->bind_param("i", $k['id']);
                                                        $stmt_con->execute();
                                                        $result_con = $stmt_con->get_result();
                                                        $rfund = $result_con->fetch_assoc();
                                                        $name_refunds = $rfund['refunds'] ?? '';

                                                ?>
                                                        <th class="text-center"><?= $name_refunds ?></th>
                                                <?php }
                                                } ?>

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
                                                $total_basic_rate = $row['present'] * $row['per_day'];
                                                $overtime_amount = $row['ot'] * $row['ot_rate'];
                                                $t_ot_rate += $row['ot_rate'];
                                                $late_amount = $row['late'] * $perMinute;
                                                $t_late += $perMinute;
                                                $undertime_amount = $row['under_time'] * $perMinute;
                                                $allowance_amount = $row['allowance_amount'];
                                                $allowance_days = $row['allowance_days'];
                                                $total_allowance = $allowance_amount *  $allowance_days;
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

                                                $total_amount =  ($total_basic_rate    +  $total_allowance - $absent_amount) / 2;
                                                $t_total_amount += $total_amount;
                                                $gross_salary =  (($total_basic_rate +   $overtime_amount   +  $total_allowance)   - $late_amount);

                                                $contributions = json_decode($row['contributions'], true);
                                                $deductions = json_decode($row['deductions'], true);
                                                $loans = json_decode($row['loans'], true);
                                                $refunds = json_decode($row['refunds'], true);
                                                $total_deductions =  0;
                                                $total_refunds =  0;


                                                $total_number_days += $row['present'];
                                                $t_basic_rate += $total_basic_rate;
                                                $t_per_day += $perDay;
                                                $t_allowance += $allowance_amount;
                                                $t_gross += $gross_salary;

                                            ?>
                                                <tr class="name-<?= $row['id'] ?>">
                                                    <td class="text-center" style="min-width: 40px;"><b><?= $i ?></b></td>
                                                    <td style="min-width:200px;">
                                                        <b><?= $row['lastname'] ?>, <?= $row['firstname'] ?></b>
                                                    </td>
                                                    <td style="min-width: 200px;" class="text-center">
                                                        <?= $row['position'] ?>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['present'] ?>" data-id="<?= $row['id'] ?>" data-type="present" class="form-control input-class" placeholder="No. of Days" aria-describedby="basic-addon2">
                                                            </div>
                                                        <?php } else { ?>
                                                            <?= $row['present'] ?>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['per_day'] ?>" data-id="<?= $row['id'] ?>" data-type="per_day" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= number_format($row['per_day'], 2) ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-right" style="min-width: 130px;">
                                                        <b><?= number_format($total_basic_rate, 2) ?></b>
                                                    </td>
                                                    <!-- Late -->
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['allowance_days'] ?>" data-id="<?= $row['id'] ?>" data-type="allowance_days" class="form-control input-class" placeholder="Days" aria-label="Days" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'allowance_days')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['allowance_days'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="min-width: 100px;" class="text-right">
                                                        <b><?= number_format($allowance_amount, 2) ?></b>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($total_allowance, 2) ?></b>
                                                    </td>


                                                    <!-- ot -->
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['ot'] ?>" data-id="<?= $row['id'] ?>" data-type="ot" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'ot')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['ot'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <b><?= number_format($row['ot_rate'], 2) ?></b>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($overtime_amount, 2) ?></b>
                                                    </td>

                                                    <!-- /ot -->

                                                    <!-- Late -->
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <?php if ($status === 1) { ?>
                                                            <div class="input-group mb-3">
                                                                <input type="text" value="<?= $row['late'] ?>" data-id="<?= $row['id'] ?>" data-type="late" class="form-control input-class" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                                <!-- <div class="input-group-append">
                                                                    <button onclick="updateData(this, <?= $row['id'] ?>,'late')" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class="ri-save-fill"></i></button>
                                                                </div> -->
                                                            </div>
                                                        <?php } else { ?>
                                                            <b><?= $row['late'] ?></b>
                                                        <?php } ?>
                                                    </td>
                                                    <td style="min-width: 100px;" class="text-right">
                                                        <b><?= number_format($perMinute, 2) ?></b>
                                                    </td>
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($late_amount, 2) ?></b>
                                                    </td>
                                                    <!-- /Late -->
                                                    <td class="text-right" style="min-width: 90px;">
                                                        <b><?= number_format($gross_salary, 2) ?></b>
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
                                                            <td style="min-width: 90px;" class="text-right">
                                                                <?php if ($status === 1) { ?>
                                                                    <div class="input-group mb-3">
                                                                        <input type="text" value="<?= $deduction_amount ?>" data-id="<?= $row['id'] ?>" data-type='<?= $k['type'] == 1 ? 'contribution' : ($k['type'] == 3 ? 'loan' : 'deduction') ?>' data-dd_id="<?= $k['id'] ?>" class="form-control input-class" placeholder="Enter Amount" aria-label="Enter Amount" aria-describedby="basic-addon2">
                                                                        <!-- <div class="input-group-append">
                                                                            <button
                                                                                onclick="updateData(this, <?= $row['id'] ?>, '<?= $k['type'] == 1 ? 'contribution' : ($k['type'] == 3 ? 'loan' : 'deduction') ?>', <?= $k['id'] ?>)"
                                                                                class="btn btn-success"
                                                                                data-toggle="tooltip"
                                                                                title="Save Changes"
                                                                                type="button">
                                                                                <i class="ri-save-fill"></i>
                                                                            </button>
                                                                        </div> -->
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <b><?= number_format($deduction_amount, 2) ?></b>
                                                                <?php } ?>
                                                            </td>
                                                        <?php } ?>
                                                    <?php  } else { ?>

                                                    <?php } ?>
                                                    <?php $total_deductions = $total_deductions;
                                                    $t_deduction += $total_deductions;  ?>
                                                    <td style="min-width: 90px;" class="text-right">
                                                        <b><?= number_format($total_deductions, 2) ?></b>
                                                    </td>
                                                    <!-- refunds -->
                                                    <?php if (count($refunds_settings) > 0) {;
                                                        foreach ($refunds_settings as $kd) {
                                                            $refund_amount = 0;
                                                            foreach ($refunds as $cd) {
                                                                if ($cd["refund_id"] == $kd["id"]) {
                                                                    $refund_amount = $cd["amount"];
                                                                }
                                                            }
                                                            $total_refunds += $refund_amount;

                                                    ?>
                                                            <td style="min-width: 90px;" class="text-right">
                                                                <?php if ($status === 1) { ?>
                                                                    <div class="input-group mb-3">
                                                                        <input type="text" value="<?= $refund_amount ?>" data-id="<?= $row['id'] ?>" data-dd_id="<?= $kd['id'] ?>" data-type="refund" class="form-control input-class" placeholder="Enter Amount" aria-label="Enter Amount" aria-describedby="basic-addon2">
                                                                    <?php } else { ?>
                                                                        <b><?= number_format($refund_amount, 2) ?></b>
                                                                    <?php } ?>
                                                            </td>
                                                    <?php
                                                        }
                                                    } ?>
                                                    <?php

                                                    $net = $gross_salary -  $total_deductions + $total_refunds;
                                                    $t_net += $net;
                                                    ?>
                                                    <td style="min-width: 90px;" class="text-right net-content">
                                                        <b><?= number_format($net, 2) ?></b>
                                                    </td>
                                                    <td style="min-width: 90px;" class="text-center">
                                                        <a href="view_payslip.php?id=<?= $row['id'] ?>" class="xl-btn" data-toggle="tooltip" title="View Payslip" id="<?= $row['id'] ?>" site_name="<?= htmlspecialchars($row['branch_name']) ?>" onclick="edit_function(this)">
                                                            <i class="ri-file-text-line"></i> View
                                                        </a>
                                                    </td>
                                                    <td class="text-center" style="min-width: 40px;"><b><?= $i ?></b></td>

                                                </tr>
                                                <input style="display: none;" name="id[]" value="<?= $row['id'] ?>" />
                                                <input style="display: none;" name="net[]" value="<?= $net ?>" />
                                                <input style="display: none;" class="net-class" did="<?= $row['id'] ?>" value="<?= $net ?>" />

                                            <?php } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th colspan="2" class="text-center">TOTAL AMOUNT</th>
                                                <th class="text-center"><?= $total_number_days ?></th>
                                                <th class="text-right"><?= number_format($t_per_day, 2) ?></th>
                                                <th class="text-right"><?= number_format($t_basic_rate, 2) ?></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th class="text-right"><?= number_format($t_gross, 2) ?></th>
                                                <th colspan="<?= count($contributions_settings) ?>"></th>
                                                <th class="text-right"><?= number_format($t_deduction, 2) ?></th>
                                                <?php if (count($refunds_settings) > 0) { ?>
                                                    <th colspan="<?= count($refunds_settings) ?>"></th>
                                                <?php } ?>
                                                <th class="text-right"><?= number_format($t_net, 2) ?></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- end page title -->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

</div>

<!-- Print Settings Modal -->
<div class="modal fade" id="modal-print-settings" tabindex="-1" role="dialog" aria-labelledby="modalPrintSettingsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalPrintSettingsLabel"><i class="ri-settings-3-line me-2"></i>Print Settings</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="form-auth-small" id="form-print-settings" method="post" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-header bg-light py-2 fw-semibold"><i class="ri-edit-line me-1"></i>Prepared By</div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label small text-muted">Name</label>
                                        <input class="form-control" value="<?= htmlspecialchars($payroll['prepared_by']) ?>" name="prepared_by" type="text" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label small text-muted">Position</label>
                                        <input class="form-control" name="prepared_by_role" type="text" value="<?= htmlspecialchars(isset($payroll['prepared_by_role']) && trim($payroll['prepared_by_role']) !== '' ? $payroll['prepared_by_role'] : 'PAYROLL OFFICER') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-header bg-light py-2 fw-semibold"><i class="ri-checkbox-circle-line me-1"></i>Verified By</div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label small text-muted">Name</label>
                                        <input class="form-control" name="verified_by" value="<?= htmlspecialchars($payroll['verified_by']) ?>" type="text" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label small text-muted">Position</label>
                                        <input class="form-control" name="verified_by_role" type="text" value="<?= htmlspecialchars(isset($payroll['verified_by_role']) && trim($payroll['verified_by_role']) !== '' ? $payroll['verified_by_role'] : 'Compensation & Benefits Supervisor') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-header bg-light py-2 fw-semibold"><i class="ri-shield-check-line me-1"></i>Approved By</div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label small text-muted">Name</label>
                                        <input class="form-control" value="<?= htmlspecialchars(isset($payroll['approved_by']) && trim($payroll['approved_by']) !== '' ? $payroll['approved_by'] : 'Jordan G. Tiu/Jerry G. Tiu/Jean T. Chin') ?>" name="approved_by" type="text" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label small text-muted">Position</label>
                                        <input class="form-control" name="approved_by_role" type="text" value="<?= htmlspecialchars(isset($payroll['approved_by_role']) && trim($payroll['approved_by_role']) !== '' ? $payroll['approved_by_role'] : 'MANAGEMENT') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="ri-save-line me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="modal-sites-2" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-sitesModalLabel">Branches</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table id="data-table" class="table table-hover dataTable table-custom table-striped m-b-0 c_list">
                    <thead class="thead-dark">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Cashier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($site_ids)) {
                            foreach ($site_ids as $k) {  ?>

                                <?php
                                $query_site = "SELECT branches.*, branches.branch_code,
                                    branches.branch_name,
                                    branches.address AS branch_address,
                                    users.name AS timekeeper
                                    FROM branches
                                    LEFT JOIN users ON users.branch_id = branches.id
                                    WHERE branches.id = ?";
                                $stmt_site = $conn->prepare($query_site);
                                if ($stmt_site) {
                                    $stmt_site->bind_param("i", $k);
                                    $stmt_site->execute();
                                    $site_details = $stmt_site->get_result()->fetch_assoc();
                                } else {
                                    $site_details = [];
                                }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($site_details['branch_code'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($site_details['branch_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($site_details['branch_address'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($site_details['timekeeper'] ?? ''); ?></td>
                                    <td class="text-center" width="100">
                                        <a data-toggle="tooltip" title="Print Payroll on this Branch" href="print-payroll.php?id=<?= $id ?>&type=site&site_id=<?= $site_details['id'] ?? '' ?>" class="btn btn-sm btn-outline-info mr-1"><i class="fa fa-print"></i></a>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    let id = "<?= $id ?>";
    let status = "<?= $status ?>";

    function fitTableToViewport() {
        const container = document.getElementById('table-responsive2');
        if (!container) return;
        const top = container.getBoundingClientRect().top + window.scrollY;
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const visibleTop = top - scrollTop;
        const available = window.innerHeight - visibleTop - 24;
        container.style.height = Math.max(available, 200) + 'px';
    }

    function fixStickyHeaderGap() {
        const thead = document.querySelector('#table-1 thead');
        if (!thead) return;
        const row1 = thead.querySelector('tr:first-child');
        if (!row1) return;
        const row1Height = row1.getBoundingClientRect().height;
        thead.querySelectorAll('tr:nth-child(2) th').forEach(th => {
            th.style.top = row1Height + 'px';
        });
    }

    function fixFrozenColumns() {
        const table = document.getElementById('table-1');
        if (!table) return;
        // Measure the rendered width of the first body/header cell
        const col1Ref = table.querySelector('tbody tr td:nth-child(1)')
                     || table.querySelector('thead tr:first-child th:nth-child(1)');
        if (!col1Ref) return;
        const col1W = col1Ref.getBoundingClientRect().width;
        // Set left offset on every col-2 cell
        table.querySelectorAll(
            'tbody td:nth-child(2), tfoot th:nth-child(2), thead tr:first-child th:nth-child(2)'
        ).forEach(el => { el.style.left = col1W + 'px'; });
    }

    document.addEventListener('DOMContentLoaded', () => {
        fitTableToViewport();
        fixStickyHeaderGap();
        fixFrozenColumns();
    });
    window.addEventListener('resize', () => {
        fitTableToViewport();
        fixStickyHeaderGap();
        fixFrozenColumns();
    });
</script>
<?php include 'component/add_payroll.php'; ?>