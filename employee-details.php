<?php

// Check if 'id' parameter is set and is a valid integer
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Employee ID");
}

$emp_id = (int) $_GET['id']; // Cast to integer for security

// Prepare the SQL statement to prevent SQL injection
$stmt = $conn->prepare("
    SELECT e.*, p.name AS pname , c.clasification
    FROM employee e
    INNER JOIN position p ON e.position_id = p.id
    INNER JOIN clasification c ON e.clasification_id = c.id
    WHERE e.id = ?
");

// Bind the parameter and execute the query
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch employee data
$emp = $result->fetch_assoc();

// Check if an employee was found
if (!$emp) {
    die("Employee not found.");
}

// Assign values dynamically using variable variables ($$)
foreach ($emp as $k => $v) {
    $$k = $v;
}

// Close statement
$stmt->close();

$initials = strtoupper(substr($firstname, 0, 1)) . strtoupper(substr($lastname, 0, 1));
$fullname  = htmlspecialchars($lastname . ', ' . $firstname . ($middlename ? ' ' . substr($middlename, 0, 1) . '.' : ''));
?>
<style>
    .emp-profile-bar { display:flex; align-items:center; gap:16px; padding:14px 0 12px; border-bottom:2px solid #d0d7ee; margin-bottom:14px; flex-wrap:wrap; }
    .emp-big-avatar { width:52px; height:52px; border-radius:50%; background:#009688; color:#fff; font-size:20px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; letter-spacing:1px; }
    .emp-profile-name { font-size:17px; font-weight:700; color:#009688; line-height:1.2; }
    .emp-profile-sub { font-size:12px; color:#555; margin-top:3px; }
    .emp-profile-stats { display:flex; gap:20px; margin-left:auto; flex-wrap:wrap; }
    .emp-profile-stat { text-align:right; }
    .emp-profile-stat-val { font-size:13px; font-weight:700; color:#009688; font-family:'Segoe UI',monospace; }
    .emp-profile-stat-lbl { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:.3px; }
    .detail-section { border:1px solid #d0d7ee; border-radius:4px; margin-bottom:10px; overflow:hidden; }
    .detail-section-title { background:#009688; color:#fff; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:5px 12px; display:flex; align-items:center; gap:6px; }
    .detail-row { display:flex; flex-wrap:wrap; }
    .detail-item { padding:7px 14px; border-bottom:1px solid #eef0f8; border-right:1px solid #eef0f8; flex:1; min-width:200px; }
    .detail-item:last-child { border-right:none; }
    .detail-label { font-size:10px; color:#888; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:2px; }
    .detail-value { font-size:13px; font-weight:600; color:#1a1a1a; }
    .emp-currency-val { font-weight:700; color:#009688; font-family:'Segoe UI',monospace; }
    .cn-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(210px,1fr)); gap:10px; margin-top:4px; }
    .cn-item { border:1px solid #c5cde8; border-radius:4px; padding:10px 12px; background:#eef0f8; }
    .cn-item label { font-size:10px; color:#009688; font-weight:700; text-transform:uppercase; letter-spacing:.3px; display:block; margin-bottom:5px; }
    .cn-item .form-control { font-size:13px; font-weight:600; border-color:#c5cde8; }
    .barcode-wrap { background:#f8f9fa; border:1px solid #d0d7ee; border-radius:4px; padding:10px 16px; display:inline-block; margin-top:4px; }
    #table-loan thead.table-dark th,
    #table-contributions thead.table-dark th,
    #table-deductions thead.table-dark th,
    #table-sites thead.table-dark th { background-color:#009688 !important; border-color:#2d3d66 !important; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-user-3-line me-2 text-success"></i>
                            <?= $fullname ?>
                            <?php if ($status == 1): ?>
                                <span class="badge bg-success ms-2" style="font-size:11px;vertical-align:middle;"><i class="ri-checkbox-circle-line me-1"></i>Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger ms-2" style="font-size:11px;vertical-align:middle;"><i class="ri-close-circle-line me-1"></i>Inactive</span>
                            <?php endif; ?>
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item"><a href="index.php?page=employee">Employee</a></li>
                                <li class="breadcrumb-item active">Details</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header align-items-center d-flex py-2">
                        <div class="flex-grow-1">
                            <a href="index.php?page=employee" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="ri-arrow-left-line me-1"></i>Back
                            </a>
                        </div>
                        <div class="flex-shrink-0 d-flex gap-2">
                            <?php if (in_array($login_role, $allowed_values)): ?>
                                <?php if (in_array($login_role, $allowed_values_2)): ?>
                                    <button type="button" class="btn btn-sm btn-info" onclick="edit_details()">
                                        <i class="ri-edit-line me-1"></i>Edit Details
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="add_loans()">
                                    <i class="ri-bank-card-line me-1"></i>Add Loan
                                </button>
                                <button type="button" class="btn btn-sm btn-warning text-dark" onclick="add_deductions()">
                                    <i class="ri-subtract-line me-1"></i>Add Deduction
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Employee Profile Bar -->
                        <div class="emp-profile-bar">
                            <div class="emp-big-avatar"><?= $initials ?></div>
                            <div>
                                <div class="emp-profile-name"><?= $fullname ?></div>
                                <div class="emp-profile-sub">
                                    <i class="ri-briefcase-4-line me-1 text-success"></i><?= htmlspecialchars(ucwords($pname)) ?>
                                    &nbsp;&bull;&nbsp;
                                    <span class="badge badge-label <?= $clasification_array[$clasification_id] ?>">
                                        <i class="mdi mdi-circle-medium"></i><?= htmlspecialchars($clasification) ?>
                                    </span>
                                    &nbsp;&bull;&nbsp;
                                    <span class="emp-id" style="font-family:monospace;color:#1976d2;font-weight:700;"><?= htmlspecialchars($employee_no) ?></span>
                                </div>
                            </div>
                            <div class="emp-profile-stats">
                                <div class="emp-profile-stat">
                                    <div class="emp-profile-stat-val">&#8369;<?= number_format($basic_pay, 2) ?></div>
                                    <div class="emp-profile-stat-lbl">Basic Pay</div>
                                </div>
                                <div class="emp-profile-stat">
                                    <div class="emp-profile-stat-val">&#8369;<?= number_format($salary, 2) ?></div>
                                    <div class="emp-profile-stat-lbl">Daily Rate</div>
                                </div>
                                <div class="emp-profile-stat">
                                    <div class="emp-profile-stat-val">&#8369;<?= number_format($ot_rate, 2) ?></div>
                                    <div class="emp-profile-stat-lbl">OT Rate</div>
                                </div>
                                <div class="emp-profile-stat">
                                    <?php if ($weekly_payroll == 1): ?>
                                        <div><span class="badge bg-primary">Weekly</span></div>
                                    <?php else: ?>
                                        <div><span class="badge bg-dark">Monthly</span></div>
                                    <?php endif; ?>
                                    <div class="emp-profile-stat-lbl">Payroll Type</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <ul class="nav nav-pills arrow-navtabs nav-success bg-light mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#arrow-overview" role="tab">
                                    <i class="ri-user-3-line me-1"></i><span class="d-none d-sm-inline">Overview</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#arrow-profile" role="tab">
                                    <i class="ri-bank-card-line me-1"></i><span class="d-none d-sm-inline">Loans</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#arrow-contact" role="tab">
                                    <i class="ri-hand-coin-line me-1"></i><span class="d-none d-sm-inline">Contributions</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#arrow-cn" role="tab">
                                    <i class="ri-id-card-line me-1"></i><span class="d-none d-sm-inline">Contribution Numbers</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#arrow-deductions" role="tab">
                                    <i class="ri-subtract-line me-1"></i><span class="d-none d-sm-inline">Deductions</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#arrow-sites" role="tab">
                                    <i class="ri-map-pin-2-line me-1"></i><span class="d-none d-sm-inline">Sites</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content text-muted">

                            <!-- OVERVIEW TAB -->
                            <div class="tab-pane active" id="arrow-overview" role="tabpanel">

                                <!-- Personal Information -->
                                <div class="detail-section">
                                    <div class="detail-section-title"><i class="ri-user-3-line"></i>Personal Information</div>
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <div class="detail-label">First Name</div>
                                            <div class="detail-value"><?= htmlspecialchars($firstname) ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Middle Name</div>
                                            <div class="detail-value"><?= htmlspecialchars($middlename) ?: '<span class="text-muted">—</span>' ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Last Name</div>
                                            <div class="detail-value"><?= htmlspecialchars($lastname) ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Extension</div>
                                            <div class="detail-value"><?= htmlspecialchars($ext) ?: '<span class="text-muted">—</span>' ?></div>
                                        </div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <div class="detail-label">Birthdate</div>
                                            <div class="detail-value"><?= $bday ? date('F d, Y', strtotime($bday)) : '<span class="text-muted">—</span>' ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Employee Code</div>
                                            <div class="detail-value" style="font-family:monospace;"><?= htmlspecialchars($employee_code) ?: '<span class="text-muted">—</span>' ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Position</div>
                                            <div class="detail-value"><?= htmlspecialchars(ucwords($pname)) ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Classification</div>
                                            <div class="detail-value">
                                                <span class="badge badge-label <?= $clasification_array[$clasification_id] ?>">
                                                    <i class="mdi mdi-circle-medium"></i><?= htmlspecialchars($clasification) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-item" style="border-right:none;">
                                            <div class="detail-label">Employee ID / Barcode</div>
                                            <div class="barcode-wrap">
                                                <img alt="<?= htmlspecialchars($employee_no) ?>" src="includes/barcode.php?codetype=Code39&size=40&text=<?= urlencode($employee_no) ?>&print=true" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Compensation -->
                                <div class="detail-section">
                                    <div class="detail-section-title"><i class="ri-money-dollar-circle-line"></i>Compensation</div>
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <div class="detail-label">Basic Pay</div>
                                            <div class="detail-value emp-currency-val">&#8369; <?= number_format($basic_pay, 2) ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Daily Rate</div>
                                            <div class="detail-value emp-currency-val">&#8369; <?= number_format($salary, 2) ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Overtime Rate</div>
                                            <div class="detail-value emp-currency-val">&#8369; <?= number_format($ot_rate, 2) ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Allowance Rate</div>
                                            <div class="detail-value emp-currency-val">&#8369; <?= number_format($allowance_rate, 2) ?></div>
                                        </div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <div class="detail-label">SSS Provident Fund</div>
                                            <div class="detail-value emp-currency-val">&#8369; <?= number_format($sss_fund, 2) ?></div>
                                        </div>
                                        <div class="detail-item" style="flex:3;"></div>
                                    </div>
                                </div>

                                <!-- Payroll Settings -->
                                <div class="detail-section">
                                    <div class="detail-section-title"><i class="ri-settings-3-line"></i>Payroll Settings</div>
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <div class="detail-label">Payroll Type</div>
                                            <div class="detail-value">
                                                <?php if ($weekly_payroll == 1): ?>
                                                    <span class="badge bg-primary"><i class="ri-calendar-2-line me-1"></i>Weekly</span>
                                                <?php else: ?>
                                                    <span class="badge bg-dark"><i class="ri-calendar-check-line me-1"></i>Monthly</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Benefit Deductions (SSS/HDMF/PHIC)</div>
                                            <div class="detail-value">
                                                <?php if ($isAutoDeduct == 1): ?>
                                                    <span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Auto Deduct</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="ri-close-circle-line me-1"></i>Manual</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Status</div>
                                            <div class="detail-value">
                                                <?php if ($status == 1): ?>
                                                    <span class="badge rounded-pill bg-success"><i class="ri-checkbox-circle-line me-1"></i>Active</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-danger"><i class="ri-close-circle-line me-1"></i>Inactive</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="detail-item"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- LOANS TAB -->
                            <div class="tab-pane" id="arrow-profile" role="tabpanel">
                                <div class="table-responsive mt-2">
                                    <table id="table-loan" class="table table-hover table-bordered align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th><i class="ri-list-check-2 me-1"></i>Loan Type</th>
                                                <th><i class="ri-calendar-2-line me-1"></i>Loan Date</th>
                                                <th class="text-end"><i class="ri-money-dollar-circle-line me-1"></i>Amount</th>
                                                <th class="text-end"><i class="ri-scales-3-line me-1"></i>Balance</th>
                                                <th class="text-end"><i class="ri-subtract-line me-1"></i>Deduction</th>
                                                <th class="text-center"><i class="ri-pulse-line me-1"></i>Status</th>
                                                <th class="text-center" style="width:140px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $loans = $conn->query("SELECT loans.*, contribution_loan_types.loan_type, contribution_loan_types.clt_id AS loan_type_id FROM loans
                                            INNER JOIN employee ON loans.employee_id = employee.id
                                            INNER JOIN contribution_loan_types ON contribution_loan_types.clt_id = loans.loan_type
                                            WHERE loans.employee_id = $emp_id
                                            ORDER BY loan_id ASC");
                                            while ($row = $loans->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><span style="font-weight:600;"><?= htmlspecialchars($row['loan_type']) ?></span></td>
                                                    <td><span style="font-size:12px;color:#555;"><i class="ri-calendar-2-line me-1 text-muted"></i><?= htmlspecialchars($row['loan_date']) ?></span></td>
                                                    <td class="text-end"><span class="emp-currency-val">&#8369; <?= number_format($row['loan_amount'], 2) ?></span></td>
                                                    <td class="text-end"><span class="emp-currency-val">&#8369; <?= number_format($row['loan_balance'], 2) ?></span></td>
                                                    <td class="text-end"><span class="emp-currency-val">&#8369; <?= number_format($row['damount'], 2) ?></span></td>
                                                    <td class="text-center">
                                                        <?php if ($row['loan_status'] == 1): ?>
                                                            <span class="badge rounded-pill bg-success"><i class="ri-checkbox-circle-line me-1"></i>Paid</span>
                                                        <?php else: ?>
                                                            <span class="badge rounded-pill bg-danger"><i class="ri-time-line me-1"></i>Unpaid</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div style="display:flex;gap:4px;justify-content:center;">
                                                            <button class="btn btn-sm btn-outline-primary" type="button"
                                                                loan_id="<?= $row['loan_id'] ?>" employee_id="<?= $row['employee_id'] ?>"
                                                                loan_balance="<?= $row['loan_balance'] ?>" damount="<?= $row['damount'] ?>"
                                                                loan_amount="<?= $row['loan_amount'] ?>" loan_date="<?= $row['loan_date'] ?>"
                                                                loan_type="<?= $row['loan_type_id'] ?>" loan_status="<?= $row['loan_status'] ?>"
                                                                onclick="editLoan(this)"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Loan">
                                                                <i class="ri-edit-line"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                                onclick="loanHistory(<?= $row['loan_id'] ?>)"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" title="View History">
                                                                <i class="ri-history-line"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- CONTRIBUTIONS TAB -->
                            <div class="tab-pane" id="arrow-contact" role="tabpanel">
                                <div class="table-responsive mt-2">
                                    <table id="table-contributions" class="table table-hover table-bordered align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th><i class="ri-hand-coin-line me-1"></i>Contribution</th>
                                                <th class="text-end" style="width:200px;"><i class="ri-money-dollar-circle-line me-1"></i>Amount</th>
                                                <th class="text-center" style="width:100px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $contributions = $conn->query("SELECT ea.*,c.contribution,ea.id AS contribution_unique FROM employee_contributions ea LEFT JOIN contributions c ON ea.contribution_id = c.id WHERE ea.employee_id=" . $emp_id);
                                            while ($row = $contributions->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><span style="font-weight:600;"><?= htmlspecialchars($row['contribution']) ?></span></td>
                                                    <td class="text-end"><span class="emp-currency-val">&#8369; <?= number_format($row['amount'], 2) ?></span></td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['contribution']) ?>" data-amount="<?= $row['amount'] ?>"
                                                            class="btn btn-sm btn-outline-primary"
                                                            onclick="editContriAmount(this)"
                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Amount">
                                                            <i class="ri-edit-line"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- CONTRIBUTION NUMBERS TAB -->
                            <div class="tab-pane" id="arrow-cn" role="tabpanel">
                                <div class="cn-grid mt-2">
                                    <div class="cn-item">
                                        <label><i class="ri-id-card-line me-1"></i>SSS No.</label>
                                        <input type="text" class="form-control sss_no" data-id="sss_no" value="<?= htmlspecialchars($sss_no) ?>" name="sss_no" placeholder="Enter SSS No." />
                                    </div>
                                    <div class="cn-item">
                                        <label><i class="ri-id-card-line me-1"></i>HDMF No.</label>
                                        <input type="text" class="form-control sss_no" data-id="hdmf_no" value="<?= htmlspecialchars($hdmf_no) ?>" name="hdmf_no" placeholder="Enter HDMF No." />
                                    </div>
                                    <div class="cn-item">
                                        <label><i class="ri-id-card-line me-1"></i>PhilHealth No.</label>
                                        <input type="text" class="form-control sss_no" id="ph_no" data-id="ph_no" value="<?= htmlspecialchars($ph_no) ?>" placeholder="Enter PhilHealth No." />
                                    </div>
                                    <div class="cn-item">
                                        <label><i class="ri-id-card-line me-1"></i>TIN No.</label>
                                        <input type="text" class="form-control sss_no" data-id="tin_no" value="<?= htmlspecialchars($tin_no) ?>" placeholder="Enter TIN No." />
                                    </div>
                                </div>
                            </div>

                            <!-- DEDUCTIONS TAB -->
                            <div class="tab-pane" id="arrow-deductions" role="tabpanel">
                                <div class="table-responsive mt-2">
                                    <table id="table-deductions" class="table table-hover table-bordered align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th><i class="ri-subtract-line me-1"></i>Deduction Name</th>
                                                <th class="text-end" style="width:200px;"><i class="ri-money-dollar-circle-line me-1"></i>Amount</th>
                                                <th class="text-center" style="width:100px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $deductions = $conn->query("SELECT ea.*,d.deduction as dname FROM employee_deductions ea INNER JOIN deductions d ON d.id = ea.deduction_id WHERE ea.employee_id=" . $emp_id . " ORDER BY ea.type ASC, DATE(ea.effective_date) ASC, d.deduction ASC");
                                            while ($row = $deductions->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><span style="font-weight:600;"><?= htmlspecialchars($row['dname']) ?></span></td>
                                                    <td class="text-end"><span class="emp-currency-val">&#8369; <?= number_format($row['amount'], 2) ?></span></td>
                                                    <td class="text-center">
                                                        <button type="button" data-id="<?= $row['id'] ?>"
                                                            class="btn btn-sm btn-outline-danger remove_deduction"
                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Deduction">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- SITES TAB -->
                            <div class="tab-pane" id="arrow-sites" role="tabpanel">
                                <div class="table-responsive mt-2">
                                    <table id="table-sites" class="table table-hover table-bordered align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-center" style="width:90px;"><i class="ri-qr-code-line me-1"></i>Code</th>
                                                <th class="text-center" style="width:100px;"><i class="ri-device-line me-1"></i>Device ID</th>
                                                <th><i class="ri-map-pin-2-line me-1"></i>Site</th>
                                                <th><i class="ri-global-line me-1"></i>Cluster</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = $conn->query("SELECT A.*, B.cluster, C.name AS timekeeper, D.employer_name AS employer, E.device_id, E.code
                                                FROM sites AS A
                                                INNER JOIN clusters AS B ON A.cluster_id = B.id
                                                LEFT JOIN users AS C ON A.timekeeper_id = C.id
                                                LEFT JOIN employers AS D ON C.employer_id = D.id
                                                INNER JOIN employee_bio AS E ON E.site_id = A.id
                                                WHERE E.employee_id=" . $emp_id . "
                                                GROUP BY E.site_id
                                                ORDER BY A.site_name ASC");
                                            while ($row = $query->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td class="text-center"><span style="font-family:monospace;font-weight:700;color:#1976d2;"><?= htmlspecialchars($row['code']) ?></span></td>
                                                    <td class="text-center"><span style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($row['device_id']) ?></span></td>
                                                    <td>
                                                        <div style="font-weight:600;font-size:13px;"><i class="ri-radio-button-line text-success me-1"></i><?= htmlspecialchars($row['site_name']) ?></div>
                                                        <div style="font-size:11px;color:#666;"><i class="ri-hashtag text-muted me-1"></i><?= htmlspecialchars($row['site_code']) ?></div>
                                                        <div style="font-size:11px;color:#888;"><i class="ri-map-pin-line text-muted me-1"></i><?= htmlspecialchars($row['site_address']) ?></div>
                                                    </td>
                                                    <td><span style="font-weight:600;"><?= htmlspecialchars($row['cluster']) ?></span></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div><!-- end tab-content -->
                    </div><!-- end card-body -->
                </div>
            </div>
        </div>
    </div>

    <!-- Loan History Modal -->
    <div class="modal fade" id="modal-loan-history" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="ri-history-line me-2 text-success"></i>Loan History</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loanHistoryDiv"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let employee_id = "<?= $emp_id ?>";

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el, { trigger: 'hover' });
            });
        });
    </script>
    <?php include 'component/add_employee_form.php'; ?>
