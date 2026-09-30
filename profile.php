<?php
$profileUserId = (int) ($_SESSION['login_id'] ?? 0);
$profileStatement = $conn->prepare('SELECT name, username FROM users WHERE id = ? LIMIT 1');
$profileStatement->bind_param('i', $profileUserId);
$profileStatement->execute();
$profileUser = $profileStatement->get_result()->fetch_assoc() ?: ['name' => '', 'username' => ''];
$profileStatement->close();

if (empty($_SESSION['profile_csrf_token'])) {
	$_SESSION['profile_csrf_token'] = bin2hex(random_bytes(32));
}
$profileCsrfToken = $_SESSION['profile_csrf_token'];
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
							<form class="form-auth-small" id="profile-form" method="post" novalidate>
								<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($profileCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
								<div class="col-md-12">
									<div class="form-group">
										<label>Name</label>
										<input type="text" value="<?= htmlspecialchars($profileUser['name'], ENT_QUOTES, 'UTF-8') ?>" class="form-control" placeholder="Last Name" name="name" maxlength="200" data-parsley-required-message="Name is required." required>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label>Username</label>
										<input type="text" value="<?= htmlspecialchars($profileUser['username'], ENT_QUOTES, 'UTF-8') ?>" class="form-control" placeholder="Username" name="username" maxlength="100" data-parsley-required-message="Username is required." required autocomplete="username">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label>Password</label>
										<input type="password" class="form-control" placeholder="Password" name="password" minlength="8" maxlength="72" autocomplete="new-password">
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