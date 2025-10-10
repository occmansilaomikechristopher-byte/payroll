<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Refunds</h4>
						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">Refunds</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">Refunds List</h4>
						<button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#modal"><i class="ri-add-line align-bottom me-1"></i> Create Refund</button>
					</div>
					<div class="card-body">
						<div class="table-responsive  mt-3 mb-1">
						<table id="data-table" class="table table-bordered dt-responsive nowrap table-striped align-middle">
								<thead class="table-light">
								<tr>
									<th>Name</th>
									<!-- <th>Description</th> -->
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$query = $conn->query("SELECT * FROM refunds order by id asc");
									while($row=$query->fetch_assoc()){
										
							?>
								<tr>
									<td><?php echo $row['refunds']?></td>
									<!-- <td><?php echo $row['refunds']?></td> -->
									<td class="text-center" width="100">
										<button type="button" data-toggle="tooltip" class="btn btn-sm btn-outline-secondary " title="Edit Deduction" id="<?=$row['id']?>" refunds="<?=$row['refunds']?>"  onclick="edit_function(this)">Edit</button>
									   
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
<?php include 'component/add_refunds_form.php'; ?>