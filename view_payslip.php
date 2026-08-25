<?php
error_reporting(0);
include 'db_connect.php';
if (!isset($_GET['id'])) {
	return;
}
$id = $_GET['id'];
?>

<?php
$query = "SELECT a.*,
l.date_from, 
l.date_to, 
l.settings, 
f.site_code, 
f.site_name, 
f.site_address, 
e.employee_no, 
e.lastname, 
e.firstname, 
e.middlename, 
e.basic_pay, 
d.name as department, 
p.name as position
FROM payroll_items AS a
INNER JOIN payroll l ON l.id = a.payroll_id
INNER JOIN employee e ON a.employee_id = e.id 
LEFT JOIN department d ON e.department_id = d.id 
LEFT JOIN position p ON e.position_id = p.id 
LEFT JOIN sites f ON f.id = a.site_id 
WHERE a.id = ?
ORDER BY e.lastname ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();
$contributions_settings = json_decode($payroll['settings'], true);
$contributions_settings = isset($contributions_settings) ? $contributions_settings : [];
$overtime_amount = $payroll['ot'] * $payroll['ot_rate'];
$perMinute = $payroll['per_minute'];
$undertime_amount = $payroll['under_time'] * $perMinute;
$late_amount = $payroll['late'] * $perMinute;
$total_basic_rate = $payroll['present'] * $payroll['per_day'];
$allowance_rate = (float) $payroll['allowance_amount'];
$allowance_days = (float) ($payroll['allowance_days'] ?? 0);
$total_allowance = $allowance_rate * $allowance_days;
$gross_salary =  $total_basic_rate + $overtime_amount - $late_amount - $undertime_amount + $total_allowance;
$net = $gross_salary -  $total_deductions;

?>
<style>
	body {
		visibility: hidden;
	}

	.page-break {
		page-break-after: always;
	}

	.top-content {
		text-align: center;
	}

	p {
		line-height: 0.3;
	}

	.middle-content {

		height: 120px;


	}

	.middle-content .row {
		width: 50%;
		float: left;

	}

	.middle-content .row2 {
		width: 50%;
		float: right;

	}

	.border-center {
		border-left: 1px solid #000;
	}

	.table {
		border-collapse: collapse;
		border: 1px solid #000;
	}

	/* And this to your table's `td` elements. */
	.table td {
		padding: 0;
		margin: 0;
		vertical-align: baseline;
	}

	.text-right {
		text-align: right;
	}

	@media print {

		/* General Styles */
		body {
			visibility: visible;
		}


	}
</style>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<title>Payslip Details</title>

<head>
	<meta http-equiv="Content-Type" content="charset=utf-8" />
	<style type="text/css">
		* {
			font-family: "DejaVu Sans Mono", monospace;
			font-size: 11px;
		}
	</style>
	<style>
		/* body {
            visibility: hidden;
        } */

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
</head>

<body>
	<div class="contriner-fluid">
		<div class="top">
			<div class="logo-area">
				<img style="width: 60px;" src="/payroll/assets/images/gv-logo.png" alt="Logo">
			</div>
			<div>
				<h1>GV Glass</h1>
				<h4>Opol Misamis Oriental</h4>
			</div>
		</div>
		<br><br>
		<div class="middle-content">
			<table style="width: 100%" class="table">
				<tbody>
					<tr>
						<td style="padding: 10px">
							<p>Employee ID : <b><?= $payroll['employee_no'] ?></b></p>
							<p>Employee Name : <b><?= $payroll['lastname'] ?> <?= $payroll['firstname'] ?> <?= $payroll['middlename'] ?>.</b></p>
							<p>Period: <?= date('F d', strtotime($payroll['date_from'])) ?> - <?= date('F d, Y', strtotime($payroll['date_to'])) ?></p>
						</td>
						<td style="border-left: 1px solid #000;padding: 10px">
							<p>Office: <b>Cagayan De oro </b></p>
							<p>Position: <b><?= $payroll['position'] ?></b></p>
						</td>
					</tr>
				</tbody>
			</table>
			<br><br>
			<table style="width: 100%" class="table">
				<tbody>
					<tr>
						<td style="padding: 10px">
							<table width="100%">
								<tbody>
									<tr>
										<td>Days of Present : </td>
										<td class="text-right"><b><?= $payroll['present'] ?></b></td>
									</tr>
									<tr>
										<td>Basic Rate : </td>
										<td class="text-right"><b><?= number_format($payroll['per_day'], 2) ?></b></td>
									</tr>
									<tr>
										<td>Salary : </td>
										<td class="text-right"><b><?= number_format($payroll['salary'], 2) ?></b></td>
									</tr>
									<tr>
										<td>Allowance : </td>
										<td class="text-right"><b><?= number_format($total_allowance, 2) ?></b></td>
									</tr>
									<tr>
										<td>Undertime : </td>
										<td class="text-right"><b><?= number_format($undertime_amount, 2) ?></b></td>
									</tr>
									<tr>
										<td>Late : </td>
										<td class="text-right"><b><?= number_format($late_amount, 2) ?></b></td>
									</tr>
									<tr>
										<td>Overtime : </td>
										<td class="text-right"><b><?= number_format($overtime_amount, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td style="border-left: 1px solid #000;padding: 10px">
							<p>Contribution </p>
							<br>
							<table width="100%">
								<tbody>
									<?php
									$total_contributions = 0;
									if (count($contributions_settings) > 0) {
										$typesOf1 = array();

										foreach ($contributions_settings as $item) {
											if ($item["type"] == 1) {
												$typesOf1[] = $item;
											}
										}
										foreach ($typesOf1 as $i2 =>  $k) {
											$deduction_amount = 0;
											if ($k['type'] == 1) {
												$query_con = "SELECT * FROM contributions   WHERE id = ?";
												$stmt_con = $conn->prepare($query_con);
												$stmt_con->bind_param("i", $k['id']);
												$stmt_con->execute();
												$result_con = $stmt_con->get_result();
												$contribution = $result_con->fetch_assoc();
												$name_deduction = $contribution['contribution'];
												$contributions = json_decode($payroll['contributions'], true);
												foreach ($contributions as $kd) {
													if ($kd["contribution_id"] == $k["id"]) {
														$deduction_amount = $kd["amount"];
													}
												}
											}
											$total_contributions += $deduction_amount;

									?>
											<tr>
												<td>
													<?= $name_deduction ?>
												</td>
												<td class="text-right">
													<b><?= number_format($deduction_amount, 2) ?></b>
												</td>
											</tr>
										<?php } ?>
									<?php } ?>
								</tbody>
							</table>
						</td>
						<td style="border-left: 1px solid #000;padding: 10px">
							<p>Deductions: </p>
							<br>
							<table width="100%">
								<tbody>
									<?php
									$total_deduction = 0;
									if (count($contributions_settings) > 0) {
										$typesOf2 = array();
										foreach ($contributions_settings as $item) {
											if ($item["type"] == 2) {
												$typesOf2[] = $item;
											}
										}
										foreach ($typesOf2 as $i2 =>  $k) {
											$deduction_amount = 0;
											if ($k['type'] == 2) {
												$query_con = "SELECT * FROM deductions   WHERE id = ?";
												$stmt_con = $conn->prepare($query_con);
												$stmt_con->bind_param("i", $k['id']);
												$stmt_con->execute();
												$result_con = $stmt_con->get_result();
												$contribution = $result_con->fetch_assoc();
												$name_deduction = $contribution['deduction'];
												$deductions = json_decode($payroll['deductions'], true);
												foreach ($deductions as $kd) {
													if ($kd["deduction_id"] == $k["id"]) {
														$deduction_amount = $kd["amount"];
													}
												}
											}
											$total_deduction += $deduction_amount;

									?>
											<tr>
												<td>
													<?= $name_deduction ?>
												</td>
												<td class="text-right">
													<b><?= number_format($deduction_amount, 2) ?></b>
												</td>
											</tr>
										<?php } ?>
									<?php } ?>
									<tr>
										<td>Other Deduction</td>
										<td class="text-right"><b><?php echo number_format($payroll['other_deduction'], 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>

				</tbody>
				<tfoot style="padding: 10px">
					<tr>
						<td style="border-right: 1px solid #000;padding: 10px">
							<table width="100%">
								<tbody>
									<tr>
										<td>Gross Salary</td>
										<td class="text-right"><b><?= number_format($gross_salary, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td style="border-right: 1px solid #000;padding: 10px">
							<table width="100%">
								<tbody>
									<tr>
										<td>Total Contributions</td>
										<td class="text-right"><b><?php echo number_format($total_contributions, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td style="padding: 10px">
							<table width="100%">
								<tbody>
									<tr>
										<td>Total Deduction</td>
										<td class="text-right"><b><?php echo number_format($total_deduction, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tfoot>
				<tfoot style="border: 1px solid #000;padding: 10px">
					<tr>
						<td style="border-right: 1px solid #000;padding: 10px"></td>
						<td style="border-right: 1px solid #000;padding: 10px"></td>
						<td style="padding: 10px">
							<b>Net Pay:</b>
							<b style="float: right"><?php echo number_format($payroll['net'], 2) ?></b>
						</td>
					</tr>
				</tfoot>
			</table>
			<br><br>
			<table style="width: 100%" class="table">
				<tbody>
					<tr>
						<td style="padding: 10px">
							<p>I hereby acknowledge receipt of the net pay amount: <b>₱<?php echo number_format($payroll['net'], 2) ?></b></p>
							<div style="width:200px">
								<div>_________________________________</div>
								<div style="text-align: center;">Signature </div>
							</div>
						</td>
					</tr>
					<tr>
						<td style="padding: 10px;text-align: center;">Date: <?= date('F,d Y') ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</body>
<script>
	window.addEventListener('afterprint', function () {
		window.location.href = 'home';
	});

	window.print();
</script>

</html>