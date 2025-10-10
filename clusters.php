<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Cluster</h4>

						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">Cluster</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">Cluster List</h4>
						<div class="flex-shrink-0">
							<?php if (in_array($login_role, $allowed_values_2)) {   ?>
								<button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#modal"><i class="ri-add-line align-bottom me-1"></i> Create Cluster</button>
							<?php } ?>
						</div>

					</div>
					<div class="card-body">
						<div class="table-responsive  mt-3 mb-1">
							<table id="data-table" class="table table-bordered dt-responsive nowrap table-striped align-middle">
								<thead class="table-light">
									<tr>
										<th>Cluster</th>
										<th>No. of Sites</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$position = $conn->query("SELECT c.*,(SELECT COUNT(*) FROM sites s WHERE s.cluster_id = c.id) AS site_count FROM clusters c ORDER BY cluster ASC");
									while ($row = $position->fetch_assoc()) {
									?>
										<tr>
											<td>
												<p> <b><?php echo $row['cluster'] ?></b></p>
											</td>
											<td class="text-center" width="100"><?php echo $row['site_count'] ?></td>
											<td class="text-center" width="100">
												<button  data-bs-toggle="tooltip" title="Edit Cluster" id="<?= $row['id'] ?>" cluster="<?= $row['cluster'] ?>" type="button"  onclick="edit_function(this)" class="btn btn-sm btn-outline-secondary"> Edit</button>
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
<?php include 'component/add_cluster_form.php'; ?>
