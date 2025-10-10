<div class="modal" id="addemployee" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-add" method="post" data-parsley-validate>
		<input type="hidden" name="id" value="<?php if (isset($_GET['id'])) {
													echo $_GET['id'];
												} ?>">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="create-title"> <?php echo isset($employee_no) ? 'Edit' : 'Create' ?> Employee</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row clearfix">
						<div class="col-12">
							<div class="form-group">
								<label>Clasification</label>
								<select id="clasification-select" class="form-select show-tick  select2" name="clasification_id" data-placeholder="Select clasification" data-parsley-required-message="Please select clasification." required>
									<option value="">Select Clasification</option>
									<?php
									$pos = $conn->query("SELECT * from clasification ");
									while ($row = $pos->fetch_assoc()) :
									?>
										<option class="opt" value="<?php echo $row['id'] ?>" <?php echo isset($clasification_id) && $clasification_id == $row['id'] ? " selected" : '' ?>><?php echo $row['clasification'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>First Name</label>
								<input type="text" class="form-control" value="<?php echo isset($employee_no) ? $firstname : '' ?>" placeholder="First Name" name="firstname" data-parsley-required-message="First name is required." required>
							</div>
						</div>
						<div class="col-md-12"  style="display: none;">
							<div class="form-group">
								<label>Middle Initial</label>
								<input type="text" value="A" class="form-control" value="<?php echo isset($employee_no) ? $middlename : '' ?>" placeholder="Middle Initial" name="middlename" data-parsley-maxlength="1" data-parsley-required-message="Middle initial is required." required>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label>Last Name</label>
								<input type="text" value="<?php echo isset($employee_no) ? $lastname : '' ?>" class="form-control" placeholder="Last Name" name="lastname" data-parsley-required-message="Last name is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Extension</label>
								<input type="text" class="form-control" value="<?php echo isset($ext) ? $ext : '' ?>" placeholder="SR/JR" name="ext" data-parsley-required-message="Extension is required." >
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Birtdate</label>
								<input type="text" class="form-control" value="<?php echo isset($bday) ? $bday : '' ?>" placeholder="Birtdate" name="bday" data-parsley-required-message="Birtdate is required." >
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label>Position</label>
								<select id="position-select" class="form-control show-tick  select2" name="position_id" data-placeholder="Select position" data-parsley-required-message="Please select position." required>
									<option value=""></option>
									<?php
									$pos = $conn->query("SELECT * from position order by name asc");
									while ($row = $pos->fetch_assoc()) :
									?>
										<option class="opt" value="<?php echo $row['id'] ?>" data-did="<?php echo $row['department_id'] ?>" <?php echo isset($position_id) && $position_id == $row['id'] ? " selected" : '' ?>><?php echo $row['name'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12 mt-4">
							<div class="form-group">
								<label>Monthly Basic Pay</label>
								<input type="text" value="<?php echo isset($basic_pay) ? $basic_pay : '' ?>" class="form-control filterme" placeholder="Enter amount " name="basic_pay" data-parsley-required-message="Amount is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Basic Daily Rate</label>
								<input type="text" value="<?php echo isset($salary) ? $salary : '' ?>" class="form-control filterme" placeholder="Enter amount " name="salary" data-parsley-required-message="Amount is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Overtime Rate</label>
								<input type="text" value="<?php echo isset($ot_rate) ? $ot_rate : '' ?>" class="form-control filterme" placeholder="Enter amount" name="ot_rate" data-parsley-required-message="Amount is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Allowance Rate</label>
								<input type="text" value="<?php echo isset($allowance_rate) ? $allowance_rate : '' ?>" class="form-control filterme" placeholder="Enter amount" name="allowance_rate" data-parsley-required-message="Amount is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>SSS PROVIDENT FUND</label>
								<input type="text" value="<?php echo isset($sss_fund) ? $sss_fund : 0 ?>" class="form-control filterme" placeholder="Enter amount" name="sss_fund" data-parsley-required-message="Amount is required." required>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-check form-switch">
								<input name="weekly_payroll" <?php echo isset($weekly_payroll) && $weekly_payroll == 1  ? 'checked' : '' ?> class="form-check-input" type="checkbox" role="switch">
								<label class="form-check-label" for="SwitchCheck1">Weekly Payroll<i><small>(Payroll Type)</small></i></label>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-check form-switch form-switch-secondary">
								<input class="form-check-input" name="isAutoDeduct" type="checkbox" role="switch" id="isAutoDeduct" <?php echo isset($isAutoDeduct) && $isAutoDeduct == 1  ? 'checked' : '' ?>>
								<label class="form-check-label" for="isAutoDeduct">Benefit Deductions(SSS,HDMF,PHIC)</label>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-check form-switch form-switch-success">
								<input class="form-check-input" name="status" type="checkbox" role="switch" id="status1" <?php echo isset($status) && $status == 1  ? 'checked' : '' ?>>
								<label class="form-check-label" for="status1">Active</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton"> <i class="fa fa-spinner fa-spin fa-spinner-button"></i> <?php echo isset($employee_no) ? 'Edit' : 'Create' ?></button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal" id="modal-deduction" tabindex="-1" role="dialog">
	<form id="employee-deduction" method="post" novalidate>
		<input type="hidden" name="employee_id" value="<?php echo $_GET['id'] ?>">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Add Deduction</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="col-md-12">
						<div class="form-group">
							<label>Deduction</label>
							<select class="form-select show-tick ms select2" id="deduction_id" name="deduction_id[]" data-placeholder="Select deduction" data-placeholder="Select deduction" data-parsley-required-message="Please select deduction." required>
								<option value="">Select Deduction</option>
								<?php
								$deduction = $conn->query("SELECT * FROM deductions order by deduction asc");
								while ($row = $deduction->fetch_assoc()) :
								?>
									<option value="<?php echo $row['id'] ?>"><?php echo $row['deduction'] ?></option>
								<?php endwhile; ?>
							</select>
						</div>
					</div>
					<div class="col-md-12" style="display: none" id="dfield">
						<div class="form-group">
							<label>Effective Date</label>
							<input value="<?= date("Y-m-d"); ?>" type="text" id="edate" class="form-control datetimepicker" name="effective_date[]" data-parsley-required-message="Please enter date." required>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>Amount</label>
							<input type="text" id="amount" name="amount[]" class="form-control filterme" data-placeholder="Select deduction" data-placeholder="Select deduction" data-parsley-required-message="Please enter amount." required>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton">Add Deduction</button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal" id="modal-contrition" tabindex="-1" role="dialog">
	<form id="employee-contribution" method="post" novalidate>
		<input type="hidden" id="contribution-id" name="id" value="">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Edit Contrition</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="col-md-12">
						<div class="form-group">
							<label>Contribution</label>
							<input  class="form-control" id="contribution-name"  type="text" disabled >
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>Amount</label>
							<input class="form-control" id="contribution-amount" name="amount" type="text" placeholder="0" min="0" max="20000" step="100" data-parsley-validation-threshold="1" data-parsley-trigger="keyup" data-parsley-type="number" data-parsley-required-message="Amount name is required." required />
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton"><i class="fa fa-spinner fa-spin fa-spinner-button"></i> <span>Save Changes</span></button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal" id="modal-allowance" tabindex="-1" role="dialog">
	<form id="employee-allowance" method="post" novalidate>
		<input type="hidden" name="employee_id" value="<?php echo $_GET['id'] ?>">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Add Allowance</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="col-md-12">
						<div class="form-group">
							<label>Allowance</label>
							<select class="form-control show-tick ms select2" id="allowance_id" name="allowance_id[]" data-placeholder="Select allowance" data-parsley-required-message="Please select allowance." required>
								<option value=""></option>
								<?php
								$allowance = $conn->query("SELECT * FROM allowances order by allowance asc");
								while ($row = $allowance->fetch_assoc()) :
								?>
									<option value="<?php echo $row['id'] ?>"><?php echo $row['allowance'] ?></option>
								<?php endwhile; ?>
							</select>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>Type</label>
							<select id="type2" class="form-control show-tick ms select2" name="type[]" data-placeholder="Select type" data-placeholder="Select deduction" data-placeholder="Select deduction" data-parsley-required-message="Please select type." required="">
								<option value=""></option>
								<option value="1">Monthly</option>
								<option value="2">Semi-Monthly</option>
								<option value="3">Once</option>
							</select>
						</div>
					</div>
					<div class="col-md-12" style="display: none" id="dfield2">
						<div class="form-group">
							<label>Effective Date</label>
							<input value="<?= date("Y-m-d"); ?>" type="text" id="edate2" class="form-control datetimepicker" name="effective_date[]" data-parsley-required-message="Please enter date." required>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>Amount</label>
							<input class="form-control" id="amount" name="amount[]" type="text" placeholder="0" min="0" max="20000" step="100" data-parsley-validation-threshold="1" data-parsley-trigger="keyup" data-parsley-type="number" data-parsley-required-message="Amount name is required." required />
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton"><i class="fa fa-spinner fa-spin fa-spinner-button"></i> <span>Add Deduction</span></button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal" id="modal-loan" tabindex="-1" role="dialog">
	<form id="employee-loan" method="post" novalidate>
		<input type="hidden" name="id"  id="loan_id" >
		<input type="hidden" value="<?= $emp_id ?>" name="employee_id"  id="loan_employee_id" >
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Add Loan</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="min-height: 400px;">
					<div class="col-12">
						<div class="form-group">
							<label>Loan Type</label>
							<select id="loan-select" class="form-select" name="loan_type" data-placeholder="Select type" data-parsley-required-message="Type is required." required>
								<option value="">Select Loan Type</option>
								<?php
								$pos = $conn->query("SELECT * from contribution_loan_types ");
								while ($row = $pos->fetch_assoc()) :
								?>
									<option class="opt" value="<?php echo $row['clt_id'] ?>"><?php echo $row['loan_type'] ?></option>
								<?php endwhile; ?>
							</select>
						</div>
					</div>
					<div class="col-md-12" id="dfield2">
						<div class="form-group">
							<label>Loan Date</label>
							<input type="text" id="loan_date" class="form-control datetimepicker" name="loan_date" data-parsley-required-message="Please select date." required>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>Amount</label>
							<input class="form-control" id="loan_amount" name="loan_amount" type="text" data-parsley-validation-threshold="1" data-parsley-trigger="keyup" data-parsley-type="number" data-parsley-required-message="Amount  is required." required />
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>Deduction Amount</label>
							<input class="form-control" id="damount" name="damount" type="text" data-parsley-validation-threshold="1" data-parsley-trigger="keyup" data-parsley-type="number" data-parsley-required-message="Amount amount is required." required />
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label>Balance Amount</label>
							<input class="form-control" id="loan_balance" name="loan_balance" type="text" data-parsley-validation-threshold="1" data-parsley-trigger="keyup" data-parsley-type="number" data-parsley-required-message="Amount amount is required." required />
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<div class="form-check form-switch form-switch-success">
								<input class="form-check-input" name="loan_status" type="checkbox" role="switch" id="loan_status">
								<label class="form-check-label" for="status1">Paid</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton"><i class="fa fa-spinner fa-spin fa-spinner-button"></i> <span>Save Loan</span></button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal" id="modal-upload" tabindex="-1" role="dialog">
	<form id="uploadForm"  method="post" novalidate>
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Import Excel File</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<input class="form-control" type="file" name="excelFile" id="excelFile"  data-parsley-required-message="Please select file." required="">
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton"><i class="fa fa-spinner fa-spin fa-spinner-button"></i> <span>Upload</span></button>
				</div>
			</div>
		</div>
	</form>
</div>
