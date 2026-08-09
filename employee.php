<style>
    .emp-id { font-weight:700; color:#1976d2; font-family:'Segoe UI',monospace; letter-spacing:.3px; }
    .emp-name { font-weight:600; font-size:13px; }
    .emp-position { font-size:11px; color:#555; }
    .emp-currency { font-weight:600; color:#009688; font-size:12px; font-family:'Segoe UI',monospace; }
    .emp-actions { display:flex; gap:4px; align-items:center; flex-wrap:nowrap; }
    .emp-actions .btn-sm { padding:3px 9px; font-size:11px; white-space:nowrap; }
    .filter-label { font-size:11px; color:#666; font-weight:600; margin-bottom:3px; }
    .emp-avatar { width:28px; height:28px; border-radius:50%; background:#009688; color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    
</style>
<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0"><i class="ri-group-line me-2 text-success"></i>Employee</h4>
						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
								<li class="breadcrumb-item active">Employee</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">
							<i class="ri-team-line me-2 text-success"></i>Employee List
						</h4>
						<div class="flex-shrink-0 d-flex gap-2">
							<?php if (in_array($login_role, $allowed_values_2)) { ?>
								<button type="button" class="btn btn-info add-btn" data-bs-toggle="modal" data-bs-target="#modal-upload">
									<i class="ri-upload-2-line align-bottom me-1"></i> Import
								</button>
								<button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#addemployee">
									<i class="ri-user-add-line align-bottom me-1"></i> Create Employee
								</button>
							<?php } ?>
						</div>
					</div>
					<div class="card-body">
						<div class="row g-2 mb-3">
							<div class="col-sm-2">
								<div class="filter-label"><i class="ri-pulse-line me-1"></i>Status</div>
								<select class="form-control form-control-sm" id="filter-status" data-placeholder="All Status">
									<option value=""></option>
									<option value="0">Inactive</option>
									<option value="1">Active</option>
								</select>
							</div>
							<div class="col-sm-2">
								<div class="filter-label"><i class="ri-calendar-check-line me-1"></i>Payroll Type</div>
								<select class="form-control form-control-sm" id="filter-ptype" data-placeholder="All Types">
									<option value=""></option>
									<option value="0">Monthly</option>
									<option value="1">Weekly</option>
								</select>
							</div>
							<div class="col-sm-3">
								<div class="filter-label"><i class="ri-briefcase-4-line me-1"></i>Position</div>
								<select class="form-control form-control-sm" id="filter-position" data-placeholder="All Positions">
									<option value=""></option>
									<?php
									$pos = $conn->query("SELECT * from position order by name asc");
									while ($row = $pos->fetch_assoc()) : ?>
										<option class="opt" value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>

						<div class="table-responsive mt-1 mb-1">
							<table id="table-employee" class="table table-hover table-bordered dt-responsive nowrap align-middle">
								<thead class="table-dark">
									<tr>
										<th><i class="ri-hashtag me-1"></i>ID111</th>
										<th><i class="ri-user-3-line me-1"></i>Name</th>
										<th><i class="ri-briefcase-4-line me-1"></i>Position</th>
										<th><i class="ri-money-dollar-circle-line me-1"></i>Basic Pay</th>
										<th><i class="ri-calendar-2-line me-1"></i>Daily Rate</th>
										<th><i class="ri-time-line me-1"></i>OT Rate</th>
										<th><i class="ri-list-check-2 me-1"></i>Payroll Type</th>
										<th><i class="ri-pulse-line me-1"></i>Status</th>
										<th class="text-center"><i class="ri-settings-3-line me-1"></i>Action</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- end page title -->
		</div>
		<!-- container-fluid -->
	</div>
	<!-- End Page-content -->
</div>
<?php include 'component/add_employee_form.php'; ?>
