<style>
    .action-buttons { display:flex; gap:4px; align-items:center; flex-wrap:nowrap; }
    .action-buttons .btn-sm { padding:3px 9px; font-size:11px; white-space:nowrap; }
    .payroll-ref { font-weight:700; color:#1976d2; font-family:'Segoe UI',monospace; letter-spacing:.3px; }
    .payroll-employer { font-weight:600; }
    .payroll-period { font-size:12px; color:#444; white-space:nowrap; }
    .type-badge { font-size:11px; }
</style>
<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Payroll</h4>
						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
								<li class="breadcrumb-item active">Payroll</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">
							<i class="ri-money-dollar-circle-line me-2 text-success"></i>Payroll List
						</h4>
						<div class="flex-shrink-0">
							<button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#modal">
								<i class="ri-add-circle-line align-bottom me-1"></i> Create Payroll
							</button>
						</div>
					</div>
					<div class="card-body">
						<div class="table-responsive mt-3 mb-1">
							<table id="table" class="table table-hover table-bordered align-middle">
								<thead class="table-dark">
									<tr>
										<th><i class="ri-hashtag me-1"></i>Payroll ID</th>
										<th><i class="ri-building-2-line me-1"></i>Employer</th>
										<th><i class="ri-calendar-range-line me-1"></i>Period</th>
										<th><i class="ri-map-pin-2-line me-1"></i>Category</th>
										<th><i class="ri-list-check-2 me-1"></i>Type</th>
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
<?php include 'component/add_payroll.php'; ?>
