<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Contributions</h4>
						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">Contributions</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">Contribution List</h4>
						
                    </div>
					<div class="card-body">
						<div class="table-responsive  mt-3 mb-1">
						<table id="data-table" class="table table-bordered dt-responsive nowrap table-striped align-middle">
								<thead class="table-light">
								<tr>
									<th>Name</th>
								
								</tr>
							</thead>
							<tbody>
							<?php
								$query = $conn->query("SELECT * FROM contributions order by id asc");
									while($row=$query->fetch_assoc()){
										
							?>
								<tr>
									<td><?php echo $row['contribution']?></td>
									
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
