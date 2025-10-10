<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">User Profile</h4>

						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">User Profile</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">User List</h4>
						<div class="flex-shrink-0">
							<button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#modal"><i class="ri-add-line align-bottom me-1"></i> Create User</button>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive  mt-3 mb-1">
							<table id="data-table" class="table table-hover  table-bordered table-striped">
								<thead class="table-light">
									<tr>
									    <th>Username</th>
										<th>Name</th>
										<th>Role</th>
										<th>Employer</th>
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$query = $conn->query("SELECT users.*, sites.site_code, sites.site_name, sites.site_address, employer_name from users LEFT JOIN sites ON users.site_id = sites.id  LEFT JOIN employers ON employers.id = users.employer_id  WHERE role !=1 order by name asc");
									while ($row = $query->fetch_assoc()) {

									?>
										<tr>
										    <td width="120"><?php echo $row['username'] ?></td>
											<td width="200"><?php echo $row['name'] ?></td>
											<td width="200">
												<?= getRole($row['role']) ?>
												<div>
													<?php if ($row['role'] == '5' ) { ?>
														<div class="site-wapper">
															<div><i class=" ri-hashtag"></i> <?= $row['site_code'] ?></div>
															<div><i class="ri-radio-button-line"></i> <?= $row['site_name'] ?></div>
															<div><i class="ri-map-pin-line"></i> <?= $row['site_address'] ?></div>
														</div>
													<?php } ?>
												</div>
											</td>
											<td><?php echo $row['employer_name'] ?></td>
											<td width="100" class="text-center">
												<?php if ($row['status'] == 1) { ?>
													<span class="badge rounded-pill border border-success text-success">Active</span>
												<?php } else { ?>
													<span class="badge rounded-pill border border-danger text-danger">Inactive</span>
												<?php } ?>
											</td>
											<td class="text-center" width="150">
												<?php if ($row['status'] == 1) { ?>
													<button onclick="updateUserStatus(<?= $row['id'] ?>,2)" type="button" class="btn btn-outline-danger">Set Inactive</button>
												<?php } else { ?>
													<button onclick="updateUserStatus(<?= $row['id'] ?>,1)" class="btn btn-outline-secondary">Set Active</button>
												<?php } ?>
												<!-- <button data-toggle="tooltip" type="button" class="btn btn-sm btn-outline-secondary" title="Edit Site" id="<?= $row['id'] ?>" 
                                            name="<?= htmlspecialchars($row['name']) ?>" 
                                            employer_id="<?= htmlspecialchars($row['employer_id']) ?>" 
                                            role="<?= htmlspecialchars($row['role']) ?>" 
                                            onclick="edit_function(this)">
                                                <i class="fa fa-edit"></i>
                                            </button> -->
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
<?php include 'component/add_user_form.php'; ?>