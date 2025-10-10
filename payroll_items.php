<?php if ($login_role === 4 || $login_role === 5 || $login_role === 6) {?>
    <?php
    echo "<script> location.href='index.php?page=dtr'; </script>";
    exit;
    ?>
<?php } ?>
<?php include 'db_connect.php'; ?>
<?php
$pay = $conn->query("SELECT * FROM payroll where id = " . $_GET['id'])->fetch_array();
$pt = array(1 => "Monhtly", 2 => "Semi-Monthly");
?>
<div class="container-fluid">
	<?php $title = 'Payroll Details -' . '<small>' . $pay["ref_no"] . '</small>';
	include 'includes/breadcrumb.php'; ?>
	<div class="row clearfix">
		<div class="col-lg-12">
			<div class="card">
				<div class="body">
					<div class="row">
						<div class="col-sm-6">
							<form id="navbar-search" class="navbar-form search-form">
								<input id="search-input" class="form-control" placeholder="Search here..." type="text">
								<button type="button" class="btn btn-default"><i class="icon-magnifier"></i></button>
							</form>
						</div>
						<div class="col-sm-6 text-right">
							<button class="btn btn-outline-info" type="button" id="new_payroll_btn"><span class="fa fa-refresh"></span> Re-Caclulate Payroll</button>
							<a href="payroll_items_pdf.php?id=<?php echo $_GET['id'] ?>" target="blank" class="btn btn-sm btn-outline-danger" type="button"><i class="fa fa-file-pdf-o"></i> View PDF</a>
							<!-- <button class="btn btn-outline-danger" type="button" id=""><span class="fa fa-file-pdf-o"></span> Print in PDF</button> -->
						</div>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="header">
					<p>Payroll Range: <b><?php echo date("M d, Y", strtotime($pay['date_from'])) . " - " . date("M d, Y", strtotime($pay['date_to'])) ?></b> </p>
					<p>Payroll Type: <b><?php echo $pt[$pay['type']] ?></b></p>
				</div>
				<div class="body">
					<div class="table-responsive">
						<table id="data-table" class="table table-hover  dataTable table-custom table-striped m-b-0 c_list">
							<thead class="thead-dark">
								<tr>
									<th>ID</th>
									<th>Name</th>
									<th>Overtime</th>
									<th>Late</th>
									<th>Total Allowance</th>
									<th>Total Contribution</th>
									<th>Total Deduction</th>
									<th>Net</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php

								$payroll = $conn->query("SELECT p.*,concat(e.lastname,', ',e.firstname,' ',e.middlename) as ename,e.employee_no FROM payroll_items p inner join employee e on e.id = p.employee_id where p.payroll_id=" . $_GET['id'] . " ") or die(mysqli_error());
								while ($row = $payroll->fetch_array()) {
								?>
									<tr>
										<td><?php echo $row['employee_no'] ?></td>
										<td><?php echo ucwords($row['ename']) ?></td>
										<td class="text-right"><?= number_format($row['time_log_amount'], 2) ?></td>
										<td class="text-right"><?= number_format($row['late'], 2) ?></td>
										<td class="text-right"><?= number_format($row['allowance_amount'], 2) ?></td>
										<td class="text-right"><?= number_format($row['contribute_amount'], 2) ?></td>
										<td class="text-right"><?= number_format($row['deduction_amount'], 2) ?></td>
										<td class="text-right"><?= number_format($row['net'], 2) ?></td>
										<td class="text-center"><a href="view_payslip.php?id=<?php echo $row['id'] ?>" target="blank" class="btn btn-sm btn-outline-danger" type="button"><i class="fa fa-file-pdf-o"></i> View</a></td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	var id = "<?= $_GET['id'] ?>";
</script>