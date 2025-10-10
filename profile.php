<?php
$emp = $conn->query("SELECT * FROM users WHERE id=" . $_SESSION['login_id'] . " ")->fetch_array();
foreach ($emp as $k => $v) {
	$$k = $v;
}
?>
<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<!-- start page title -->
			<div class="row">
				<div class="col-12">
					<div
						class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
						<h4 class="mb-sm-0">Profile</h4>

						<div class="page-title-right">
							<ol class="breadcrumb m-0">
								<li class="breadcrumb-item">
									<a href="javascript: void(0);">Pages</a>
								</li>
								<li class="breadcrumb-item active">Profile</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header align-items-center d-flex">
						<h4 class="card-title mb-0 flex-grow-1">Profile Details</h4>

					</div>
					<div class="card-body">
						<div class="table-responsive  mt-3 mb-1">
							<form class="form-auth-small" id="form-add" method="post" novalidate>
								<div class="col-md-12">
									<div class="form-group">
										<label>Name</label>
										<input type="text" value="<?= $name ?>" class="form-control" placeholder="Last Name" name="lastname" data-parsley-required-message="Last name is required." required>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label>Username</label>
										<input readonly="" type="text" value="<?= $username ?>" class="form-control" placeholder="Last Name" name="lastname" data-parsley-required-message="Last name is required." required>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label>Password</label>
										<input type="password" class="form-control" placeholder="Password" name="password">
										<span class="help-block">Leave blank if you don't want to change it</span>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<button type="submit" class="btn btn-info submitbutton">Save Changes</button>
									</div>
								</div>

							</form>
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