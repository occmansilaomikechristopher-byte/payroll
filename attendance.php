<style>
	td p {
		margin: unset;
	}

	.rem_att {
		cursor: pointer;
	}

	.select2-selection__choice__display {
		color: #000000 !important;
	}
</style>

<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Attendance Record</h4>
						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">Attendance Record</li>
							</ol>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">Attendance Record List</h4>
						<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-filter">
							<i class="ri-filter-line align-bottom me-1"></i> Filter
						</button>

					</div>
					<div class="card-body">
						<?php if (isset($_GET['employee_id'])) { ?>
							<div style="width: 300px;">
								<div class="input-group">
									<input type="text" class="form-control border-0 minimal-border dash-filter-picker shadow flatpickr-input" data-provider="flatpickr" data-range-date="true" value="<?php																																							echo (date("M d,Y", strtotime($_GET["from"]))) . ' - ' . (date("M d,Y", strtotime($_GET["to"])));
																																																?>" readonly="readonly">
									<div class="input-group-text bg-primary border-primary text-white">
										<i class="ri-calendar-2-line"></i>
									</div>
								</div>
							</div>

							<br>

							<div class="table-responsive">
								<table cellspacing="0" id="table-1" class="table table-sm table-bordered table-striped">
									<thead class="thead-dark">
										<tr>
											<th>Date</th>
											<th>Full Name</th>
											<th>Site</th>
											<th>Hours Worked</th>
											<th>Overtime</th>
											<th>Undertime</th>
											<th>Late</th>
											<th>Logs</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$employee_ids = $_GET['employee_id'];
										$commaSeparatedString = "";

										foreach ($employee_ids as $key => $value) {
											if ($key > 0) {
												$commaSeparatedString .= ",";
											}
											$commaSeparatedString .= $value;
										}

										$site_query =  isset($_GET["site_id"]) && $_GET["site_id"] !== '' ?  "AND site_id = '" .  $_GET["site_id"] . "'" : '';

										$query = $conn->query("SELECT  a.*, e.employee_no, e.lastname, e.firstname, e.middlename, d.status AS dtr_status , d.site_id , s.site_code, s.site_name, s.site_address
                                    FROM DTR_details a 
                                    INNER JOIN employee e ON a.employee_id = e.id  INNER JOIN DTR d ON a.ddtr_id = d.id LEFT JOIN sites  AS s ON d.site_id = s.id   WHERE   date_time between  '" . $_GET["from"] . "' AND '" . $_GET["to"] . "'   AND a.employee_id IN ('$commaSeparatedString')  " . $site_query . " ORDER BY a.date_time ASC ");

										while ($row = $query->fetch_assoc()) {
											$logs = json_decode($row['logs']);
											$employee_id = $row['employee_id'];

										?>
											<tr>
												<td width="120"><?= date("F j, Y",  strtotime($row['date_time'])) ?></td>
												<td width="200">
													<b><?= $row['lastname'] ?> <?= $row['firstname'] ?> <?= $row['middlename'] ?>.</b>

												</td>
												<td>
													<div class="site-wapper">
														<div><?= $row['site_code'] ?></div>
														<div> <?= $row['site_name'] ?></div>
														<div> <?= $row['site_address'] ?></div>
													</div>
												</td>
												<td width="150" class="text-center">
													<b><?= $row['work_hours'] ?></b>
												</td>
												<td width="150" class="text-center">
													<b><?= $row['overtime'] ?></b>
												</td>
												<td width="150" class="text-center">
													<b><?= $row['undertime'] ?></b>
												</td>
												<td width="150" class="text-center">
													<b><?= $row['late'] ?></b>
												</td>
												<?php
												$date_check = date("Y-m-d",  strtotime($row['date_time']));
												$employee_id = $row['employee_id'];
												$check_duplicate = $conn->query("SELECT * FROM DTR_details where date_time = '$date_check'  AND employee_id = '$employee_id'  GROUP BY date_time  ");
												while ($row_check = $check_duplicate->fetch_assoc()) {
													$is_duplicate = true;
												}
												?>
												<td>
													<?php if (isset($logs)) {
														foreach ($logs  as $log) {   ?>
															<div class="m-1">
																<?php if ($log->type === 'bio') { ?>
																	<span class="badge badge-success">Biometric</span>
																<?php } else { ?>
																	<span class="badge badge-danger">Manual</span>
																<?php } ?>
																<?= date("g:i A",  strtotime($log->dateTime)) ?>
															</div>
													<?php }
													} ?>
												</td>
												<td width="100">
													<?php if ($row['dtr_status'] == 2) { ?>
														<span class="badge  badge-success">Approved</span>
													<?php } else { ?>
														<span class="badge badge-secondary">New</span>
													<?php } ?>
												</td>

											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						<?php } else { ?>
							<div class="card-body text-center">
								<div class="avatar-sm mx-auto mb-3">
									<div class="avatar-title bg-success-subtle text-success fs-17 rounded">
										<i class="ri-filter-line"></i>
									</div>
								</div>
								<h4 class="card-title">Filter To Continue</h4>
							</div>
						<?php } ?>
					</div><!-- end card-body -->
				</div>
			</div>
			<!-- end page title -->
		</div>
		<!-- container-fluid -->
	</div>
	<!-- End Page-content -->

</div>
<?php include 'component/add_attendance.php'; ?>