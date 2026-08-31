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
f.branch_code AS site_code, 
f.branch_name AS site_name, 
f.address AS site_address, 
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
LEFT JOIN branches f ON f.id = a.site_id 
WHERE a.id = ?
ORDER BY e.lastname ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payroll = $result->fetch_assoc();
$contributions_settings = json_decode($payroll['settings'], true);
$contributions_settings = isset($contributions_settings) ? $contributions_settings : [];
$contributions = json_decode($payroll['contributions'], true) ?: [];
$deductions = json_decode($payroll['deductions'], true) ?: [];
$loans = json_decode($payroll['loans'], true) ?: [];
$other_deduction = (float) ($payroll['other_deduction'] ?? 0);
$total_deductions = $other_deduction;
$total_deductions += array_sum(array_map(function ($item) {
	return (float) ($item['amount'] ?? 0);
}, $deductions));
$total_deductions += array_sum(array_map(function ($item) {
	return (float) ($item['amount'] ?? 0);
}, $loans));
$overtime_amount = $payroll['ot'] * $payroll['ot_rate'];
$perMinute = $payroll['per_minute'];
$undertime_amount = $payroll['under_time'] * $perMinute;
$late_amount = $payroll['late'] * $perMinute;
$total_basic_rate = $payroll['present'] * $payroll['per_day'];
$allowance_rate = (float) $payroll['allowance_amount'];
$allowance_days = (float) ($payroll['allowance_days'] ?? 0);
$total_allowance = $allowance_rate * $allowance_days;
$gross_salary = $total_basic_rate + $overtime_amount - $late_amount - $undertime_amount + $total_allowance;
$net = $gross_salary - $total_deductions;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Payslip Details</title>
	<style>
		* {
			font-family: "DejaVu Sans Mono", monospace;
			font-size: 11px;
			box-sizing: border-box;
		}

		body {
			visibility: hidden;
			margin: 0;
			padding: 15px;
		}

		p {
			line-height: 1;
			margin: 4px 0;
		}

		.top-content {
			text-align: center;
		}

		.middle-content {
			width: 100%;
		}

		.border-center {
			border-left: 1px solid #000;
		}

		.table {
			border-collapse: collapse;
			border: 1px solid #000;
		}

		.table td {
			padding: 0;
			margin: 0;
			vertical-align: top;
		}

		.text-right {
			text-align: right;
		}

		.text-center {
			text-align: center;
		}

		.top {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 15px;
		}

		.top h1 {
			margin: 0;
			font-size: 16px;
		}

		.top h4 {
			margin: 4px 0 0 0;
			font-size: 12px;
		}

		.logo-area {
			display: flex;
			align-items: center;
		}

		@media print {
			body {
				visibility: visible;
			}
		}
	</style>
</head>

<body>
	<div class="container-fluid">
		<div class="top">
			<div class="logo-area">
				<!-- GI-FIX: Gihimong relative path para mo-load sa subfolder -->
				<img style="width: 60px; height: auto;" src="assets/images/gv-logo.png" alt="GV Logo">
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
						<td style="padding: 10px; width: 50%;">
							<p>Employee ID : <b><?= htmlspecialchars($payroll['employee_no']) ?></b></p>
							<p>Employee Name : <b><?= htmlspecialchars($payroll['lastname']) ?> <?= htmlspecialchars($payroll['firstname']) ?> <?= htmlspecialchars($payroll['middlename']) ?>.</b></p>
							<p>Period: <?= date('F d', strtotime($payroll['date_from'])) ?> - <?= date('F d, Y', strtotime($payroll['date_to'])) ?></p>
						</td>
						<td style="border-left: 1px solid #000; padding: 10px; width: 50%;">
							<p>Office: <b>Danao Patag Opol Misamis Oriental </b></p>
							<p>Position: <b><?= htmlspecialchars($payroll['position']) ?></b></p>
						</td>
					</tr>
				</tbody>
			</table>
			<br><br>
			<table style="width: 100%" class="table">
				<tbody>
					<tr>
						<td style="padding: 10px; width: 33.33%;">
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
						<td style="border-left: 1px solid #000; padding: 10px; width: 33.33%;">
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
										foreach ($typesOf1 as $i2 => $k) {
											$deduction_amount = 0;
											if ($k['type'] == 1) {
												$query_con = "SELECT * FROM contributions WHERE id = ?";
												$stmt_con = $conn->prepare($query_con);
												$stmt_con->bind_param("i", $k['id']);
												$stmt_con->execute();
												$result_con = $stmt_con->get_result();
												$contribution = $result_con->fetch_assoc();
												$name_deduction = $contribution['contribution'];
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
													<?= htmlspecialchars($name_deduction) ?>
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
						<td style="border-left: 1px solid #000; padding: 10px; width: 33.33%;">
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
										foreach ($typesOf2 as $i2 => $k) {
											$deduction_amount = 0;
											if ($k['type'] == 2) {
												$query_con = "SELECT * FROM deductions WHERE id = ?";
												$stmt_con = $conn->prepare($query_con);
												$stmt_con->bind_param("i", $k['id']);
												$stmt_con->execute();
												$result_con = $stmt_con->get_result();
												$contribution = $result_con->fetch_assoc();
												$name_deduction = $contribution['deduction'];
												foreach ($deductions as $kd) {
													if ($kd["deduction_id"] == $k["id"]) {
														$deduction_amount = $kd["amount"];
													}
												}
											}
									?>
											<tr>
												<td>
													<?= htmlspecialchars($name_deduction) ?>
												</td>
												<td class="text-right">
													<b><?= number_format($deduction_amount, 2) ?></b>
												</td>
											</tr>
										<?php } ?>
									<?php } ?>
									<?php foreach ($contributions_settings as $k) {
										if (($k['type'] ?? null) != 3) continue;
										$stmt_loan = $conn->prepare("SELECT loan_type FROM contribution_loan_types WHERE clt_id = ?");
										$stmt_loan->bind_param("i", $k['id']);
										$stmt_loan->execute();
										$loan_type = $stmt_loan->get_result()->fetch_assoc();
										$loan_amount = 0;
										foreach ($loans as $loan) {
											if (($loan['deduction_id'] ?? null) == $k['id']) {
												$loan_amount += (float) ($loan['amount'] ?? 0);
											}
										}
									?>
									<tr>
										<td><?= htmlspecialchars($loan_type['loan_type'] ?? 'Salary Loan') ?></td>
										<td class="text-right"><b><?= number_format($loan_amount, 2) ?></b></td>
									</tr>
									<?php } ?>
									<tr>
										<td>Other Deduction</td>
										<td class="text-right"><b><?= number_format($other_deduction, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>

				</tbody>
				<tfoot style="padding: 10px">
					<tr>
						<td style="border-right: 1px solid #000; padding: 10px">
							<table width="100%">
								<tbody>
									<tr>
										<td>Gross Salary</td>
										<td class="text-right"><b><?= number_format($gross_salary, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td style="border-right: 1px solid #000; padding: 10px">
							<table width="100%">
								<tbody>
									<tr>
										<td>Total Contributions</td>
										<td class="text-right"><b><?= number_format($total_contributions, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
						<td style="padding: 10px">
							<table width="100%">
								<tbody>
									<tr>
										<td>Total Deduction</td>
										<td class="text-right"><b><?= number_format($total_deductions, 2) ?></b></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tfoot>
				<tfoot style="border: 1px solid #000; padding: 10px">
					<tr>
						<td style="border-right: 1px solid #000; padding: 10px"></td>
						<td style="border-right: 1px solid #000; padding: 10px"></td>
						<td style="padding: 10px">
							<b>Net Pay:</b>
							<b style="float: right"><?= number_format($payroll['net'], 2) ?></b>
						</td>
					</tr>
				</tfoot>
			</table>
			<br><br>
			<table style="width: 100%" class="table">
				<tbody>
					<tr>
						<td style="padding: 10px">
							<p>I hereby acknowledge receipt of the net pay amount: <b>₱<?= number_format($payroll['net'], 2) ?></b></p>
							<br>
							<div style="width:200px">
								<div>_________________________________</div>
								<div style="text-align: center;">Signature </div>
							</div>
						</td>
					</tr>
					<tr>
						<td style="padding: 10px; text-align: center;">Date: <?= date('F, d Y') ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<script>
		window.addEventListener('afterprint', function () {
			window.location.href = 'home';
		});

		window.print();
	</script>
</body>
</html>