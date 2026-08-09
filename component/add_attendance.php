<div class="modal" id="modal" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-add" method="post" novalidate>
		<input type="hidden" name="id" id="id">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Create Attendance</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="min-height: 590px;">
					<div class="row clearfix">
						<div class="col-md-12">
							<div class="form-group">
								<label>Employee</label>
								<select id="employee_id" name="employee_id" class="form-control show-tick ms select2" data-placeholder="Select employee" data-parsley-required-message="Please select employee." required>
									<option value=""></option>
									<?php
									$employee = $conn->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as ename FROM employee order by concat(lastname,', ',firstname,' ',middlename) asc");
									while ($row = $employee->fetch_assoc()) :
									?>
										<option value="<?php echo $row['id'] ?>"><?php echo $row['ename'] . ' | ' . $row['employee_no'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label> Date</label>
								<?php
								$dateFrom = new DateTime($dtr['date_from']);
								$dateTo = new DateTime($dtr['date_to']);
								// Generate the dropdown
								echo '<select id="date-picker" name="date_time"  class="form-control show-tick ms select2" data-placeholder="Select Date" data-parsley-required-message="Please select date." required>';
								echo '<option value="">Select a date</option>'; // Add empty option
								for ($date = $dateFrom; $date < $dateTo; $date->modify('+1 day')) {
									$dateValue = $date->format('Y-m-d');
									echo "<option value=\"$dateValue\">$dateValue</option>";
								}
								echo '</select>';
								?>
							</div>
						</div>
						<div class="col-md-12">
						<label>Logs</label>
						<div id="container-clone">
							<div class="item">
								<div class="row">
									<div class="col-md-9">
										<div class="form-group">
											<div id="id_1">
												<input value="08:00:00" type="text" name="datetime_log[]" class="form-control date" data-parsley-required-message="Please select date." required />
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<button type="button" class="btn btn-secondary cloneButton"><i class="fa fa-plus"></i></button>
										<button type="button" class="btn btn-danger removeButton"><i class="fa fa-minus"></i></button>
									</div>
								</div>
							</div>
						</div>
						</div>
						<!-- <div class="col-md-12">
							<div class="form-group">   
								<label>Date</label>   
								<input   id="reportrange" class="form-control" autocomplete="off" >                              
							</div>
						</div> -->
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton">Create</button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal fade" id="modal-filter" tabindex="-1" role="dialog">
	<form id="form-filter" novalidate>
		<div class="modal-dialog modal-md" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title mb-0">
						<i class="ri-filter-3-line me-2" style="color:#009688;"></i>Filter Attendance Records
					</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<!-- Date Range -->
					<div class="mb-3">
						<label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
							<i class="ri-calendar-range-line me-1"></i>Date Range <span class="text-danger">*</span>
						</label>
						<div class="row g-2">
							<div class="col-6">
								<label class="form-label small text-muted mb-1">From</label>
								<div class="input-group input-group-sm">
									<span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
									<input name="from" id="from" class="form-control"
										autocomplete="off" placeholder="YYYY-MM-DD"
										data-parsley-required-message="Select start date." required>
								</div>
							</div>
							<div class="col-6">
								<label class="form-label small text-muted mb-1">To</label>
								<div class="input-group input-group-sm">
									<span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
									<input name="to" id="to" class="form-control"
										autocomplete="off" placeholder="YYYY-MM-DD"
										data-parsley-required-message="Select end date." required>
								</div>
							</div>
						</div>
					</div>

					<!-- Employee -->
					<div class="mb-3">
						<label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
							<i class="ri-user-line me-1"></i>Employee <span class="text-danger">*</span>
						</label>
						<select id="employee-select" name="employee_id[]" class="form-control" multiple
							data-placeholder="Select one or more employees..." required
							data-parsley-required-message="Please select at least one employee.">
							<?php
							$employee = $conn->query("SELECT *, CONCAT(lastname,', ',firstname,' ',middlename) AS ename FROM employee WHERE status=1 ORDER BY lastname, firstname ASC");
							while ($row = $employee->fetch_assoc()):
							?>
								<option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['ename']) ?> | <?= htmlspecialchars($row['employee_no']) ?></option>
							<?php endwhile; ?>
						</select>
						<div class="form-text text-muted" style="font-size:11px;"><i class="ri-information-line me-1"></i>Hold Ctrl / Cmd to select multiple</div>
					</div>

					<!-- Branch -->
					<div class="mb-1">
						<label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
							<i class="ri-map-pin-2-line me-1"></i>Branch <span class="text-muted fw-normal">(optional)</span>
						</label>
						<select id="site-select" class="form-control" name="site_id" data-placeholder="All branches">
							<option value="">— All Branches —</option>
							<?php
							$sites = $conn->query("SELECT * FROM branches WHERE status = 1 ORDER BY branch_name ASC");
							if ($sites) while ($row_site = $sites->fetch_assoc()):
							?>
								<option value="<?= $row_site['id'] ?>">
									<?= htmlspecialchars($row_site['branch_name']) ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>

				</div>
				<div class="modal-footer" style="background:#f8f9fa;">
					<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
						<i class="ri-close-line me-1"></i>Cancel
					</button>
					<button type="submit" class="btn btn-sm text-white" style="background:#009688;border-color:#009688;">
						<i class="ri-search-line me-1"></i>Apply Filter
					</button>
				</div>
			</div>
		</div>
	</form>
</div>