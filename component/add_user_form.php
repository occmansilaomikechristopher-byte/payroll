<div class="modal fade" id="modal" tabindex="-1" role="dialog">
    <form id="form-add" novalidate autocomplete="off">
        <input type="hidden" name="id" id="id">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title title mb-0">
                        <i class="ri-user-add-line me-2" style="color:#394b7c;"></i>Create User
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#394b7c;">
                                <i class="ri-building-2-line me-1"></i>Employer <span class="text-danger">*</span>
                            </label>
                            <select id="employer-select" class="form-control select2" name="employer_id"
                                data-placeholder="Select employer"
                                data-parsley-required-message="Please select employer." required>
                                <option value=""></option>
                                <?php
                                $user_forms = $conn->query("SELECT * FROM employers ORDER BY employer_name ASC");
                                while ($row_data_form = $user_forms->fetch_assoc()):
                                ?>
                                    <option value="<?= $row_data_form['id'] ?>">
                                        <?= htmlspecialchars($row_data_form['employer_name']) ?> — <?= htmlspecialchars($row_data_form['description']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#394b7c;">
                                <i class="ri-shield-check-line me-1"></i>Role <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="role" name="role"
                                data-placeholder="Select a role"
                                data-parsley-required-message="Please select role." required>
                                <option value=""></option>
                                <option value="4">Payroll Clerk</option>
                                <option value="5">Timekeeper</option>
                                <option value="7">Auditor</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#394b7c;">
                                <i class="ri-user-3-line me-1"></i>Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" placeholder="e.g. Juan Dela Cruz"
                                name="name" id="name"
                                data-parsley-required-message="Name is required." required>
                        </div>

                        <div class="col-md-6" id="username-wrapper">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#394b7c;">
                                <i class="ri-at-line me-1"></i>Username <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" placeholder="username"
                                name="username" id="username"
                                data-parsley-required-message="Username is required." required autocomplete="off">
                        </div>

                        <div class="col-md-6" id="password-wrapper">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#394b7c;">
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
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#394b7c;border-color:#394b7c;">
                        <i class="fa fa-spinner fa-spin fa-spinner-button"></i>
                        <i class="ri-save-line me-1"></i>Create
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function () {
    var pwd  = document.getElementById('password');
    var icon = document.getElementById('toggleIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'ri-eye-line';
    } else {
        pwd.type = 'password';
        icon.className = 'ri-eye-off-line';
    }
});
</script>
