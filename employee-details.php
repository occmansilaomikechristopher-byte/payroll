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
?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Employee Details</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript: void(0);">Pages</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="javascript: void(0);">Employee</a>
                                </li>
                                <li class="breadcrumb-item active">Employee Details</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1"></h4>
                        <div class="flex-shrink-0">
                            <?php if (in_array($login_role, $allowed_values)) {   ?>
                                <div class="pull-right">
                                    <div class="">
                                        <?php if (in_array($login_role, $allowed_values_2)) {   ?>
                                            <button type="button" class="btn btn-info" onclick="edit_details()"> <span class="icon-pencil"></span> Edit Details</button>
                                        <?php } ?>
                                        <!-- <button type="button" class="btn btn-primary" onclick="add_allowance()"> <span class="icon-plus"></span> Add Allowance</button> -->
                                        <button type="button" class="btn btn-secondary" onclick="add_loans()"> <span class="icon-plus"></span> Add Loan</button>
                                        <!-- <button type="button" class="btn btn-success" onclick="add_contritions()"> <span class="icon-plus"></span> Add Contribution</button> -->
                                        <button type="button" class="btn btn-warning" onclick="add_deductions()"> <span class="icon-plus"></span> Add Deduction</button>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="col-lg-12 col-md-12">

                            <ul class="nav nav-pills arrow-navtabs nav-success bg-light mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#arrow-overview" role="tab" aria-selected="true">
                                        <span class="d-block d-sm-none"><i class="mdi mdi-home-variant"></i></span>
                                        <span class="d-none d-sm-block">Overview</span>
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#arrow-profile" role="tab" aria-selected="false" tabindex="-1">
                                        <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                                        <span class="d-none d-sm-block">Loans</span>
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#arrow-contact" role="tab" aria-selected="false" tabindex="-1">
                                        <span class="d-block d-sm-none"><i class="mdi mdi-email"></i></span>
                                        <span class="d-none d-sm-block">Contributions</span>
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#arrow-cn" role="tab" aria-selected="false" tabindex="-1">
                                        <span class="d-block d-sm-none"><i class="mdi mdi-email"></i></span>
                                        <span class="d-none d-sm-block">Contribution Number</span>
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#arrow-deductions" role="tab" aria-selected="false" tabindex="-1">
                                        <span class="d-block d-sm-none"><i class="mdi mdi-email"></i></span>
                                        <span class="d-none d-sm-block">Deductions</span>
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#arrow-sites" role="tab" aria-selected="false" tabindex="-1">
                                        <span class="d-block d-sm-none"><i class="mdi mdi-email"></i></span>
                                        <span class="d-none d-sm-block">Sites</span>
                                    </a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content text-muted">
                                <div class="tab-pane active" id="arrow-overview" role="tabpanel">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <small>Clasification</small>
                                            <p>
                                                <span class="badge badge-label <?= $clasification_array[$clasification_id] ?>"><i class="mdi mdi-circle-medium"></i> <?= $clasification ?></span>
                                            </p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Employee ID</small>
                                            <div>
                                                <img alt="<?= $employee_no ?>" src="includes/barcode.php?codetype=Code39&size=40&text=<?= $employee_no; ?>&print=true" />
                                                <div>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Employee Code</small>
                                            <p><?= $employee_code ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>First Name</small>
                                            <p><?= $firstname ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Middle Initial</small>
                                            <p><?= $middlename ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Last Name</small>
                                            <p><?= $lastname ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Extension</small>
                                            <p><?= $ext ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Birtdate</small>
                                            <p><?= $bday ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Position</small>
                                            <p><?php echo ucwords($pname) ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Basic Pay</small>
                                            <p><?= number_format($basic_pay, 2) ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Daily Rate</small>
                                            <p><?= number_format($salary, 2) ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Overtime Rate</small>
                                            <p><?= number_format($ot_rate, 2) ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Allowance Rate</small>
                                            <p><?= number_format($allowance_rate, 2) ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>SSS PROVIDENT FUND</small>
                                            <p><?= number_format($sss_fund, 2) ?></p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Payroll Type</small>
                                            <p>
                                                <?php if ($weekly_payroll == 1) { ?>
                                                    <span class="badge rounded-pill border border-primary text-primary">Weekly</span>
                                                <?php } else { ?>
                                                    <span class="badge rounded-pill border border-info text-info">Monthly</span>
                                                <?php } ?>
                                            </p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Benefit Deductions(SSS,HDMF,PHIC)</small>
                                            <p>
                                                <?php if ($isAutoDeduct == 1) { ?>
                                                    <span class="badge rounded-pill border border-success text-success">Yes</span>
                                                <?php } else { ?>
                                                    <span class="badge rounded-pill border border-danger text-danger">No</span>
                                                <?php } ?>
                                            </p>
                                        </li>
                                        <li class="list-group-item">
                                            <small>Status</small>
                                            <p>
                                                <?php if ($status == 1) { ?>
                                                    <span class="badge rounded-pill border border-success text-success">Active</span>
                                                <?php } else { ?>
                                                    <span class="badge rounded-pill border border-danger text-danger">Inactive</span>
                                                <?php } ?>
                                            </p>
                                        </li>



                                    </ul>
                                </div>
                                <div class="tab-pane" id="arrow-profile" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="table-loan" class="table table-hover  table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Loan Type</th>
                                                    <th scope="col">Loan Date</th>
                                                    <th scope="col" class="text-right">Amount</th>
                                                    <th scope="col" class="text-right">Balance</th>
                                                    <th scope="col" class="text-right">Deduction</th>
                                                    <th scope="col" class="text-center">Status</th>
                                                    <th scope="col" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 0;
                                                $loans = $conn->query("SELECT loans.*, contribution_loan_types.loan_type, contribution_loan_types.clt_id AS loan_type_id FROM loans  
                                                inner join employee ON loans.employee_id = employee.id  
                                                inner join contribution_loan_types ON contribution_loan_types.clt_id = loans.loan_type  
                                                WHERE loans.employee_id = $emp_id 
                                                ORDER BY loan_id   asc ");
                                                while ($row = $loans->fetch_assoc()) :
                                                    $i++;
                                                ?>

                                                    <div class="modal" id="modal-loan-details<?= $row['loan_id'] ?>" tabindex="-1" role="dialog">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h6 class="modal-title" id="defaultModalLabel">Select Sites</h6>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body" style="min-height: 500px;">
                                                                    <div class="row" id="show-sites">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit" class="btn btn-info submitbutton">Create</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <tr>
                                                        <td><?php echo $row['loan_type'] ?></td>
                                                        <td><?php echo $row['loan_date'] ?></td>
                                                        <td class="text-right"><b><?php echo number_format($row['loan_amount'], 2) ?></b></td>
                                                        <td class="text-right"><b><?php echo number_format($row['loan_balance'], 2) ?></b></td>
                                                        <td class="text-right"><b><?php echo number_format($row['damount'], 2) ?></b></td>
                                                        <td width="120" class="text-center">
                                                            <?php if ($row['loan_status'] == 1) { ?>
                                                                <span class="badge rounded-pill border border-success text-success">Paid</span>
                                                            <?php } else { ?>
                                                                <span class="badge rounded-pill border border-danger text-danger">Unpaid</span>
                                                            <?php } ?>
                                                        </td>
                                                        <td width="120" class="text-center">
                                                            <button data-toggle="tooltip" class="btn btn-sm btn-info " type="button" loan_id="<?= $row['loan_id'] ?>" employee_id="<?= $row['employee_id'] ?>" loan_balance="<?= $row['loan_balance'] ?>" damount="<?= $row['damount'] ?>" loan_amount="<?= $row['loan_amount'] ?>" loan_date="<?= $row['loan_date'] ?>" loan_type="<?= $row['loan_type_id'] ?>" loan_status="<?= $row['loan_status'] ?>" data-bs-original-title="Edit Details" onclick="editLoan(this)">Edit</button>
                                                            <button data-toggle="tooltip" class="btn btn-sm btn-outline-secondary " type="button" data-bs-original-title="View History" onclick="loanHistory(<?php echo $row['loan_id'] ?>)">History</button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                                <div class="tab-pane" id="arrow-contact" role="tabpanel">
                                    <div class="table-responsive mt-4">
                                        <table id="table-contributions" class="table table-hover  table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Contribution</th>
                                                    <!-- <th scope="col">Type</th> -->
                                                    <th scope="col" class="text-right" width="200">Amount</th>
                                                    <th scope="col" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $contributions = $conn->query("SELECT ea.*,c.contribution,ea.id AS contribution_unique  FROM employee_contributions  ea left join contributions c  on ea.contribution_id = c.id where ea.employee_id=" . $emp_id  . "  ");
                                                while ($row = $contributions->fetch_assoc()) :
                                                ?>
                                                    <tr>
                                                        <td scope="row"><?php echo $row['contribution'] ?></td>
                                                        <td class="text-right"><b><?php echo number_format($row['amount'], 2) ?></b></td>
                                                        <td style="max-width: 120px;" class="text-center"><button type="button" data-toggle="tooltip" title="Edit Contribution Amount" data-id="<?= $row['id']  ?>"   data-name="<?= $row['contribution']  ?>"   data-amount="<?= $row['amount']  ?>" class="btn btn-sm btn-outline-secondary " onclick="editContriAmount(this)" title="Delete">Edit</button></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="arrow-cn" role="tabpanel">
                                    <div class="table-responsive mt-4">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>SSS NO</label>
                                                <input type="text" class="form-control sss_no" data-id="sss_no" value="<?= $sss_no ?>" name="sss_no" />
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <div class="form-group">
                                                <label>HDMF NO</label>
                                                <input type="text" class="form-control sss_no" data-id="hdmf_no" value="<?= $hdmf_no ?>" name="sss_no" />
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <div class="form-group">
                                                <label>PhilHealth NO</label>
                                                <input type="text" class="form-control sss_no" id="ph_no" data-id="ph_no" value="<?= $ph_no ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <div class="form-group">
                                                <label>TIN NO</label>
                                                <input type="text" class="form-control sss_no" data-id="tin_no" value="<?= $tin_no ?>" required="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="arrow-deductions" role="tabpanel">
                                    <div class="table-responsive mt-4">
                                        <table id="table-deductions" class="table table-hover  table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Deduction Name</th>
                                                    <th scope="col" class="text-right" width="200">Amount</th>
                                                    <th scope="col" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 0;
                                                $deductions = $conn->query("SELECT ea.*,d.deduction as dname FROM employee_deductions ea inner join deductions d on d.id = ea.deduction_id where ea.employee_id=" . $_GET['id'] . " order by ea.type asc,date(ea.effective_date) asc, d.deduction asc ");
                                                $t_arr = array(1 => "Monthly", 2 => "Semi-Monthly", 3 => "Once");
                                                while ($row = $deductions->fetch_assoc()) :
                                                    $i++;
                                                ?>
                                                    <tr>
                                                        <td scope="row"><?php echo $row['dname'] ?></td>
                                                        <td class="text-right text-bold" width="200"><?php echo number_format($row['amount'], 2) ?></td>
                                                        <td width="100" class="text-center">
                                                            <button type="button" data-id="<?= $row['id']  ?>" class="btn btn-sm btn-outline-danger  remove_deduction">Delete</button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="arrow-sites" role="tabpanel">
                                    <div class="table-responsive mt-4">
                                        <table id="table-sites" class="table table-hover  table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center">Code</th>
                                                    <th class="text-center">Device ID</th>
                                                    <th>Site</th>
                                                    <th>Cluster</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = $conn->query("SELECT A.*, B.cluster , C.name AS timekeeper , D.employer_name AS employer, E.device_id, E.code
                                            FROM sites AS A 
                                           INNER JOIN clusters AS B ON A.cluster_id = B.id 
                                            LEFT JOIN users AS C ON A.timekeeper_id = C.id 
                                            LEFT JOIN employers AS D ON C.employer_id = D.id 
                                            INNER JOIN employee_bio AS E ON E.site_id = A.id
                                            WHERE  E.employee_id=" . $_GET['id'] . "
                                            GROUP BY E.site_id
                                            ORDER BY A.site_name ASC");
                                                while ($row = $query->fetch_assoc()) {
                                                ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo htmlspecialchars($row['code']); ?></td>
                                                        <td class="text-center"><?php echo htmlspecialchars($row['device_id']); ?></td>
                                                        <td>
                                                            <div class="site-wapper">
                                                                <div><i class=" ri-hashtag"></i> <?= $row['site_code'] ?></div>
                                                                <div><i class="ri-radio-button-line"></i> <?= $row['site_name'] ?></div>
                                                                <div><i class="ri-map-pin-line"></i> <?= $row['site_address'] ?></div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['cluster']); ?></td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card-body -->
                    </div>
                </div>
                <!-- end page title -->
            </div>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

    </div>
    <div class="modal" id="modal-loan-history" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="defaultModalLabel">Loan History</h6>
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
    </script>
    <?php include 'component/add_employee_form.php'; ?>