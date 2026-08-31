<div class="modal fade" id="modal" tabindex="-1" role="dialog">
    <form id="form-add" novalidate autocomplete="off">
        <input type="hidden" name="id" id="id">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title title mb-0">
                        <i class="ri-user-add-line me-2" style="color:#009688;"></i>Create User
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                    

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-shield-check-line me-1"></i>Role <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="role" name="role"
                                data-placeholder="Select a role"
                                data-parsley-required-message="Please select role." required>
                                <option value=""></option>
                                <option value="9">Cashier</option>
                                <option value="10">Owner</option>
                            </select>
                        </div>

                        <div class="col-md-12" id="branch-wrapper">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-git-branch-line me-1"></i>Branch <span class="text-danger">*</span>
                            </label>
                            <select id="branch-select" class="form-control select2" name="branch_id"
                                data-placeholder="Select branch"
                                data-parsley-required-message="Please select branch." required>
                                <option value=""></option>
                                <option value="0" id="all-branches-option" style="display:none;">
                                    <i class="ri-earth-line me-1"></i>All Branches
                                </option>
                                <?php
                                $branch_forms = $conn->query("SELECT * FROM branches WHERE status = 1 ORDER BY branch_name ASC");
                                if ($branch_forms):
                                    while ($row_branch = $branch_forms->fetch_assoc()):
                                ?>
                                    <option value="<?= $row_branch['id'] ?>">
                                        <?= htmlspecialchars($row_branch['branch_name']) ?><?= $row_branch['city'] ? ' — ' . htmlspecialchars($row_branch['city']) : '' ?>
                                    </option>
                                <?php
                                    endwhile;
                                endif;
                                ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-user-3-line me-1"></i>Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" placeholder="e.g. Juan Dela Cruz"
                                name="name" id="name"
                                data-parsley-required-message="Name is required." required>
                        </div>

                        <div class="col-md-6" id="username-wrapper">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-at-line me-1"></i>Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" placeholder="username"
                                name="username" id="username"
                                data-parsley-required-message="Username is required." required autocomplete="off">
                        </div>

                        <div class="col-md-6" id="password-wrapper">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-lock-password-line me-1"></i>Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter password"
                                    data-parsley-required-message="Password is required."
                                    autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="ri-eye-off-line" id="toggleIcon"></i>
                                </button>
                            </div>
                            <!-- trick browser autocomplete -->
                            <input type="password" style="display:none" autocomplete="off">
                        </div>

                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="fa fa-spinner fa-spin fa-spinner-button"></i>
                        <i class="ri-save-line me-1"></i>Create
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Wait for modal to fully load
function setupRoleChangeListener() {
    var roleSelect = document.getElementById('role');
    if (!roleSelect) {
        setTimeout(setupRoleChangeListener, 100);
        return;
    }
    
    roleSelect.addEventListener('change', function () {
        var branchWrapper = document.getElementById('branch-wrapper');
        var branchSelect = document.getElementById('branch-select');
        if (!branchWrapper || !branchSelect) return;
        
        var branchLabel = branchWrapper.querySelector('label');
        var allBranchesOption = document.getElementById('all-branches-option');
        var role = this.value;
        
        console.log('Role changed to:', role);
        
        if (role === '5' || role === '6' || role === '9') {
            // Timekeeper, PIC, and cashier - show required branch
            branchWrapper.style.display = 'block';
            branchWrapper.style.visibility = 'visible';
            branchSelect.removeAttribute('disabled');
            branchSelect.setAttribute('required', 'required');
            if (allBranchesOption) allBranchesOption.style.display = 'none';
            // Ensure asterisk is there
            if (branchLabel && !branchLabel.innerHTML.includes('<span class="text-danger">*</span>')) {
                branchLabel.innerHTML = branchLabel.innerHTML.replace('</label>', '<span class="text-danger">*</span></label>');
            }
        } else if (role === '10') {
            // Owner - show branch, make optional, show "All Branches"
            branchWrapper.style.display = 'block';
            branchWrapper.style.visibility = 'visible';
            branchSelect.removeAttribute('disabled');
            branchSelect.removeAttribute('required');
            if (allBranchesOption) allBranchesOption.style.display = 'block';
            // Remove asterisk
            if (branchLabel) {
                branchLabel.innerHTML = branchLabel.innerHTML.replace(' <span class="text-danger">*</span>', '');
            }
        } else {
            // Other roles - hide branch
            branchWrapper.style.display = 'none';
            branchWrapper.style.visibility = 'hidden';
            branchSelect.setAttribute('disabled', 'disabled');
            if (allBranchesOption) allBranchesOption.style.display = 'none';
        }
    });
}

// Setup listeners on DOM ready and when modal shows
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupRoleChangeListener);
} else {
    setupRoleChangeListener();
}

// Also setup when modal is shown (in case it's dynamically loaded)
document.addEventListener('shown.bs.modal', setupRoleChangeListener);

// Password toggle
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('togglePassword');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            var pwd  = document.getElementById('password');
            var icon = document.getElementById('toggleIcon');
            if (pwd && icon) {
                if (pwd.type === 'password') {
                    pwd.type = 'text';
                    icon.className = 'ri-eye-line';
                } else {
                    pwd.type = 'password';
                    icon.className = 'ri-eye-off-line';
                }
            }
        });
    }
});
</script>
