<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Employee</h4>

						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">Employee</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">Employee List</h4>
						<div class="flex-shrink-0">
							<?php if (in_array($login_role, $allowed_values_2)) {   ?>
								<button type="button" class="btn btn-info add-btn" data-bs-toggle="modal" data-bs-target="#modal-upload"><i class="ri-add-line align-bottom me-1"></i> Import Employee</button>
								<button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#addemployee"><i class="ri-add-line align-bottom me-1"></i> Create Employee</button>
							<?php } ?>
						</div>

					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-sm-2">
								<div class="input-light">
									<select class="form-control" id="filter-status">
										<option value="">All Status</option>
										<option value="0">Inactive</option>
										<option value="1">Active</option>
									</select>
								</div>
							</div>
							<div class="col-sm-2">
								<div class="input-light">
									<select class="form-control" id="filter-ptype">
										<option value="">All Payroll Type</option>
										<option value="0">Monthly</option>
										<option value="1">Weekly</option>
									</select>
								</div>
							</div>
							<div class="col-sm-2">
								<div class="input-light">
									<select class="form-control" id="filter-position">
										<option value="">All Position</option>
										<?php
										$pos = $conn->query("SELECT * from position order by name asc");
										while ($row = $pos->fetch_assoc()) :
										?>
											<option class="opt" value="<?php echo $row['id'] ?>" ?><?php echo $row['name'] ?></option>
										<?php endwhile; ?>
									</select>
								</div>
							</div>
						</div>
						<!--end row-->

						<div class="table-responsive  mt-3 mb-1">
							<div class="table-responsive" style="max-width: 95%;" >
								<table id="table-employee" class="table table-bordered dt-responsive nowrap table-striped align-middle">
									<thead class="table-light">
										<tr>
											<th>ID</th>
											<th>Name</th>
											<th>Position</th>
											<th>Basic Pay</th>
											<th>Daily Rate</th>
											<th>OT Rate</th>
											<th>Payroll Type</th>
											<th>Status</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
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