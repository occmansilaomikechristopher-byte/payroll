<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Position</h4>

						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">Position</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">Position List</h4>
						<div class="flex-shrink-0">
							<button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#modal"><i class="ri-add-line align-bottom me-1"></i> Create Position</button>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive  mt-3 mb-1">
							<table id="data-table" class="table table-hover  table-bordered table-striped">
								<thead class="table-light">
									<tr>
										<th>Position</th>
										<!-- <th>Department</th> -->
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$position = $conn->query("SELECT p.id,p.name AS position_name FROM position p  order by position_name asc");
									while ($row = $position->fetch_assoc()) {
									?>
										<tr>
											<td>
												<p> <b><?php echo $row['position_name'] ?></b></p>
											</td>
											<!-- <td><p> <b><?php echo $row['department_name'] ?></b></p></td> -->
											<td class="text-center" width="100">
												<button type="button" class="btn btn-sm btn-outline-secondary  "  data-toggle="tooltip" title="Edit Position" id="<?= $row['id'] ?>" department_id="<?= $row['department_id'] ?>" name="<?= $row['position_name'] ?>" onclick="edit_function(this)">Edit</button>
											</td>
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
			<!-- end page title -->
		</div>
		<!-- container-fluid -->
	</div>
	<!-- End Page-content -->

</div>
<?php include 'component/add_position_form.php'; ?>