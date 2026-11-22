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
$profileName = trim((string) $profileUser['name']);
$profileInitials = 'U';
if ($profileName !== '') {
	$profileNameParts = preg_split('/\s+/', $profileName);
	$profileInitials = strtoupper(substr($profileNameParts[0], 0, 1));
	if (count($profileNameParts) > 1) {
		$profileInitials .= strtoupper(substr($profileNameParts[count($profileNameParts) - 1], 0, 1));
	}
}
?>
<div class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			<div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
				<div>
					<h4 class="mb-1">Profile</h4>
					<p class="text-muted mb-0">Manage your account information and security settings.</p>
				</div>
				<div class="page-title-right">
					<ol class="breadcrumb m-0">
						<li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
						<li class="breadcrumb-item active">Profile</li>
					</ol>
				</div>
			</div>

			<div class="row justify-content-center">
				<div class="col-12 col-md-9 col-lg-8 col-xl-7">
					<div class="card profile-settings-card border-0">
						<div class="profile-settings-summary">
							<div class="profile-settings-avatar"><?= htmlspecialchars($profileInitials, ENT_QUOTES, 'UTF-8') ?></div>
							<div class="flex-grow-1">
								<span class="profile-settings-eyebrow">Account profile</span>
								<h4 class="mb-1"><?= htmlspecialchars($profileName ?: 'User', ENT_QUOTES, 'UTF-8') ?></h4>
								<p class="mb-0"><?= htmlspecialchars((string) $profileUser['username'], ENT_QUOTES, 'UTF-8') ?></p>
							</div>
							<div class="profile-settings-status">
								<i class="ri-shield-check-line me-1"></i>
								<span><?= htmlspecialchars(getRole($_SESSION['login_role'] ?? ''), ENT_QUOTES, 'UTF-8') ?> account</span>
							</div>
						</div>

						<div class="card-body p-4 p-md-5">
							<div class="mb-4">
								<h5 class="mb-1">Profile details</h5>
								<p class="text-muted mb-0">Keep your personal and login information up to date.</p>
							</div>

							<form class="form-auth-small" id="profile-form" method="post" novalidate>
								<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($profileCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
								<div class="row g-4">
									<div class="col-md-6">
										<label for="profile-name" class="form-label">Full name</label>
										<input id="profile-name" type="text" value="<?= htmlspecialchars($profileUser['name'], ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-lg" placeholder="Enter your full name" name="name" maxlength="200" data-parsley-required-message="Name is required." required>
									</div>
									<div class="col-md-6">
										<label for="profile-username" class="form-label">Username</label>
										<input id="profile-username" type="text" value="<?= htmlspecialchars($profileUser['username'], ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-lg" placeholder="Enter your username" name="username" maxlength="100" data-parsley-required-message="Username is required." required autocomplete="username">
									</div>
									<div class="col-12">
										<div class="profile-settings-divider"></div>
										<div class="mb-3">
											<h6 class="mb-1">Change password</h6>
											<p class="text-muted mb-0">Use a strong password of at least 8 characters. Leave this blank to keep your current password.</p>
										</div>
										<label for="profile-password" class="form-label">New password <span class="text-muted fw-normal">(optional)</span></label>
										<input id="profile-password" type="password" class="form-control form-control-lg" placeholder="Enter a new password" name="password" minlength="8" maxlength="72" autocomplete="new-password">
									</div>
									<div class="col-12">
										<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pt-2">
											<span class="text-muted small"><i class="ri-lock-line me-1 align-middle"></i>Your information is securely stored.</span>
											<button type="submit" class="btn btn-info submitbutton px-4">
												<i class="ri-save-line me-1 align-middle"></i>Save changes
											</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.profile-settings-card {
		max-width: 680px;
		margin: 0 auto;
		overflow: hidden;
		border-radius: 12px;
		box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
	}

	.profile-settings-summary {
		display: flex;
		align-items: center;
		gap: 18px;
		padding: 22px 26px;
		color: #fff;
		background: linear-gradient(120deg, #01433d 0%, #01544c 100%);
	}

	.profile-settings-avatar {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 56px;
		height: 56px;
		flex: 0 0 56px;
		border: 3px solid rgba(255, 255, 255, 0.35);
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.16);
		font-size: 18px;
		font-weight: 600;
		letter-spacing: 0.04em;
	}

	.profile-settings-eyebrow {
		display: block;
		margin-bottom: 5px;
		color: rgba(255, 255, 255, 0.72);
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 0.1em;
		text-transform: uppercase;
	}

	.profile-settings-summary h4 {
		color: #fff;
		font-weight: 600;
	}

	.profile-settings-summary p {
		color: rgba(255, 255, 255, 0.8);
	}

	.profile-settings-status {
		align-self: flex-start;
		padding: 7px 12px;
		border: 1px solid rgba(255, 255, 255, 0.24);
		border-radius: 20px;
		color: rgba(255, 255, 255, 0.9);
		font-size: 12px;
		white-space: nowrap;
	}

	.profile-settings-card .form-label {
		margin-bottom: 8px;
		color: #344054;
		font-size: 13px;
		font-weight: 600;
	}

	.profile-settings-card .form-control {
		border-color: #e2e8f0;
		border-radius: 8px;
		box-shadow: none;
	}

	.profile-settings-card .card-body {
		padding: 28px 32px !important;
	}

	.profile-settings-card .form-control:focus {
		border-color: #01544c;
		box-shadow: 0 0 0 3px rgba(1, 84, 76, 0.15);
	}

	.profile-settings-divider {
		height: 1px;
		margin-bottom: 24px;
		background: #eef2f6;
	}

	.profile-settings-card .btn-info {
		border: 0;
		background: #01544c;
	}

	.profile-settings-card .btn-info:hover,
	.profile-settings-card .btn-info:focus {
		background: #01433d;
	}

	@media (max-width: 575.98px) {
		.profile-settings-summary {
			align-items: flex-start;
			flex-wrap: wrap;
			padding: 24px 20px;
		}

		.profile-settings-card .card-body {
			padding: 24px 20px !important;
		}

		.profile-settings-status {
			margin-left: 82px;
		}
	}
</style>