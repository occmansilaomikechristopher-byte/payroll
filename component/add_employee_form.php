<!-- ── Create / Edit Employee ─────────────────────────────────────── -->
<div class="modal fade" id="addemployee" tabindex="-1" role="dialog">
    <form id="form-add" data-parsley-validate>
        <input type="hidden" name="id" value="<?= isset($_GET['id']) ? (int)$_GET['id'] : '' ?>">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0" id="create-title">
                        <i class="ri-user-add-line me-2" style="color:#009688;"></i>
                        <?= isset($employee_no) ? 'Edit' : 'Create' ?> Employee
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php
                    $v = function($key) { return isset($$key) ? htmlspecialchars($$key) : ''; };
                    ?>

                    <!-- Personal Information -->
                    <div class="mb-1" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#009688;border-bottom:2px solid #eef0f8;padding-bottom:4px;margin-bottom:12px;">
                        <i class="ri-user-3-line me-1"></i>Personal Information
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-shield-check-line me-1"></i>Classification <span class="text-danger">*</span>
                            </label>
                            <select id="clasification-select" class="form-control select2" name="clasification_id"
                                data-placeholder="Select classification"
                                data-parsley-required-message="Please select classification." required>
                                <option value=""></option>
                                <?php
                                $pos = $conn->query("SELECT * FROM clasification");
                                while ($row = $pos->fetch_assoc()):
                                ?>
                                    <option class="opt" value="<?= $row['id'] ?>"
                                        <?= isset($clasification_id) && $clasification_id == $row['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['clasification']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="firstname"
                                value="<?= isset($employee_no) ? htmlspecialchars($firstname) : '' ?>"
                                placeholder="e.g. Juan"
                                data-parsley-required-message="First name is required." required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                M.I.
                            </label>
                            <input type="text" class="form-control" name="middlename" maxlength="1"
                                value="<?= isset($employee_no) ? htmlspecialchars($middlename) : '' ?>"
                                placeholder="A">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="lastname"
                                value="<?= isset($employee_no) ? htmlspecialchars($lastname) : '' ?>"
                                placeholder="e.g. Dela Cruz"
                                data-parsley-required-message="Last name is required." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Extension
                            </label>
                            <input type="text" class="form-control" name="ext"
                                value="<?= isset($ext) ? htmlspecialchars($ext) : '' ?>"
                                placeholder="SR / JR">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Birthdate
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-cake-line"></i></span>
                                <input type="text" class="form-control datetimepicker2emp" name="bday"
                                    value="<?= isset($bday) ? htmlspecialchars($bday) : '' ?>"
                                    placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-briefcase-4-line me-1"></i>Position <span class="text-danger">*</span>
                            </label>
                            <select id="position-select" class="form-control select2" name="position_id"
                                data-placeholder="Select position"
                                data-parsley-required-message="Please select position." required>
                                <option value=""></option>
                                <?php
                                $pos = $conn->query("SELECT * FROM position ORDER BY name ASC");
                                while ($row = $pos->fetch_assoc()):
                                ?>
                                    <option class="opt" value="<?= $row['id'] ?>" data-did="<?= $row['department_id'] ?>"
                                        <?= isset($position_id) && $position_id == $row['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Compensation -->
                    <div class="mb-1" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#009688;border-bottom:2px solid #eef0f8;padding-bottom:4px;margin-bottom:12px;">
                        <i class="ri-money-dollar-circle-line me-1"></i>Compensation
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Monthly Basic Pay <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input type="text" class="form-control filterme" name="basic_pay"
                                    value="<?= isset($basic_pay) ? htmlspecialchars($basic_pay) : '' ?>"
                                    placeholder="0.00"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Basic Daily Rate <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input type="text" class="form-control filterme" name="salary"
                                    value="<?= isset($salary) ? htmlspecialchars($salary) : '' ?>"
                                    placeholder="0.00"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Overtime Rate <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input type="text" class="form-control filterme" name="ot_rate"
                                    value="<?= isset($ot_rate) ? htmlspecialchars($ot_rate) : '' ?>"
                                    placeholder="0.00"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Allowance Rate <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input type="text" class="form-control filterme" name="allowance_rate"
                                    value="<?= isset($allowance_rate) ? htmlspecialchars($allowance_rate) : '' ?>"
                                    placeholder="0.00"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                SSS Provident Fund <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input type="text" class="form-control filterme" name="sss_fund"
                                    value="<?= isset($sss_fund) ? htmlspecialchars($sss_fund) : '0' ?>"
                                    placeholder="0.00"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="mb-1" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#009688;border-bottom:2px solid #eef0f8;padding-bottom:4px;margin-bottom:12px;">
                        <i class="ri-settings-3-line me-1"></i>Payroll Settings
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div style="border:1px solid #e8eaf6;border-radius:4px;padding:8px 12px;background:#f9f9ff;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" name="weekly_payroll" type="checkbox" role="switch" id="sw_weekly"
                                        <?= isset($weekly_payroll) && $weekly_payroll == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="sw_weekly" style="font-size:12px;">
                                        <i class="ri-calendar-2-line me-1"></i>Weekly Payroll
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="border:1px solid #e8eaf6;border-radius:4px;padding:8px 12px;background:#f9f9ff;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" name="isAutoDeduct" type="checkbox" role="switch" id="sw_autodeduct"
                                        <?= isset($isAutoDeduct) && $isAutoDeduct == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="sw_autodeduct" style="font-size:12px;">
                                        <i class="ri-subtract-line me-1"></i>Auto Deductions
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="border:1px solid #e8eaf6;border-radius:4px;padding:8px 12px;background:#f9f9ff;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" name="status" type="checkbox" role="switch" id="sw_status"
                                        <?= isset($status) && $status == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="sw_status" style="font-size:12px;">
                                        <i class="ri-checkbox-circle-line me-1"></i>Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="fa fa-spinner fa-spin fa-spinner-button"></i>
                        <i class="ri-save-line me-1"></i><?= isset($employee_no) ? 'Save Changes' : 'Create Employee' ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Add Deduction ─────────────────────────────────────────────── -->
<div class="modal fade" id="modal-deduction" tabindex="-1" role="dialog">
    <form id="employee-deduction" novalidate>
        <input type="hidden" name="employee_id" value="<?= isset($_GET['id']) ? (int)$_GET['id'] : '' ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-subtract-line me-2" style="color:#009688;"></i>Add Deduction
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Deduction <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="deduction_id" name="deduction_id[]"
                                data-placeholder="Select deduction"
                                data-parsley-required-message="Please select deduction." required>
                                <option value=""></option>
                                <?php
                                $deduction = $conn->query("SELECT * FROM deductions ORDER BY deduction ASC");
                                while ($row = $deduction->fetch_assoc()):
                                ?>
                                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['deduction']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12" style="display:none;" id="dfield">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Effective Date
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                <input type="text" id="edate" class="form-control datetimepicker" name="effective_date[]"
                                    value="<?= date('Y-m-d') ?>"
                                    data-parsley-required-message="Please enter date." required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input type="text" id="amount" name="amount[]" class="form-control filterme"
                                    placeholder="0.00"
                                    data-parsley-required-message="Please enter amount." required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="ri-add-line me-1"></i>Add Deduction
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Edit Contribution ─────────────────────────────────────────── -->
<div class="modal fade" id="modal-contrition" tabindex="-1" role="dialog">
    <form id="employee-contribution" novalidate>
        <input type="hidden" id="contribution-id" name="id" value="">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-hand-coin-line me-2" style="color:#009688;"></i>Edit Contribution
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Contribution
                            </label>
                            <input class="form-control" id="contribution-name" type="text" disabled
                                style="background:#eef0f8;font-weight:600;color:#009688;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input class="form-control" id="contribution-amount" name="amount" type="text"
                                    placeholder="0.00"
                                    data-parsley-type="number"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="fa fa-spinner fa-spin fa-spinner-button"></i>
                        <i class="ri-save-line me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Add Allowance ─────────────────────────────────────────────── -->
<div class="modal fade" id="modal-allowance" tabindex="-1" role="dialog">
    <form id="employee-allowance" novalidate>
        <input type="hidden" name="employee_id" value="<?= isset($_GET['id']) ? (int)$_GET['id'] : '' ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-gift-line me-2" style="color:#009688;"></i>Add Allowance
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Allowance <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="allowance_id" name="allowance_id[]"
                                data-placeholder="Select allowance"
                                data-parsley-required-message="Please select allowance." required>
                                <option value=""></option>
                                <?php
                                $allowance = $conn->query("SELECT * FROM allowances ORDER BY allowance ASC");
                                while ($row = $allowance->fetch_assoc()):
                                ?>
                                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['allowance']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Type <span class="text-danger">*</span>
                            </label>
                            <select id="type2" class="form-control select2" name="type[]"
                                data-placeholder="Select type"
                                data-parsley-required-message="Please select type." required>
                                <option value=""></option>
                                <option value="1">Monthly</option>
                                <option value="2">Semi-Monthly</option>
                                <option value="3">Once</option>
                            </select>
                        </div>
                        <div class="col-12" style="display:none;" id="dfield2">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Effective Date
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                <input type="text" id="edate2" class="form-control datetimepicker" name="effective_date[]"
                                    value="<?= date('Y-m-d') ?>"
                                    data-parsley-required-message="Please enter date." required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input class="form-control filterme" name="amount[]" type="text"
                                    placeholder="0.00"
                                    data-parsley-type="number"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="fa fa-spinner fa-spin fa-spinner-button"></i>
                        <i class="ri-add-line me-1"></i>Add Allowance
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Add / Edit Loan ───────────────────────────────────────────── -->
<div class="modal fade" id="modal-loan" tabindex="-1" role="dialog">
    <form id="employee-loan" novalidate>
        <input type="hidden" name="id" id="loan_id">
        <input type="hidden" name="employee_id" id="loan_employee_id" value="<?= isset($emp_id) ? (int)$emp_id : '' ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-bank-card-line me-2" style="color:#009688;"></i>Add Loan
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Loan Type <span class="text-danger">*</span>
                            </label>
                            <select id="loan-select" class="form-control select2" name="loan_type"
                                data-placeholder="Select loan type"
                                data-parsley-required-message="Type is required." required>
                                <option value=""></option>
                                <?php
                                $pos = $conn->query("SELECT * FROM contribution_loan_types ORDER BY loan_type ASC");
                                while ($row = $pos->fetch_assoc()):
                                ?>
                                    <option class="opt" value="<?= $row['clt_id'] ?>"><?= htmlspecialchars($row['loan_type']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Loan Date <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                <input type="text" id="loan_date" class="form-control datetimepicker" name="loan_date"
                                    autocomplete="off" placeholder="YYYY-MM-DD"
                                    data-parsley-required-message="Please select date." required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Loan Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input class="form-control" id="loan_amount" name="loan_amount" type="text"
                                    placeholder="0.00"
                                    data-parsley-type="number"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Deduction / Month <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input class="form-control" id="damount" name="damount" type="text"
                                    placeholder="0.00"
                                    data-parsley-type="number"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                Balance <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">&#8369;</span>
                                <input class="form-control" id="loan_balance" name="loan_balance" type="text"
                                    placeholder="0.00"
                                    data-parsley-type="number"
                                    data-parsley-required-message="Amount is required." required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div style="border:1px solid #e8eaf6;border-radius:4px;padding:8px 12px;background:#f9f9ff;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" name="loan_status" type="checkbox" role="switch" id="loan_status">
                                    <label class="form-check-label fw-semibold" for="loan_status" style="font-size:12px;">
                                        <i class="ri-checkbox-circle-line me-1"></i>Mark as Paid
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="fa fa-spinner fa-spin fa-spinner-button"></i>
                        <i class="ri-save-line me-1"></i>Save Loan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Import Employees ──────────────────────────────────────────── -->
<div class="modal fade" id="modal-upload" tabindex="-1" role="dialog">
    <form id="uploadForm" novalidate>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-file-excel-2-line me-2" style="color:#009688;"></i>Import Employees
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                        <i class="ri-upload-2-line me-1"></i>Excel File <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-file-excel-2-line"></i></span>
                        <input class="form-control" type="file" name="excelFile" id="excelFile"
                            accept=".xlsx,.xls,.csv"
                            data-parsley-required-message="Please select a file." required>
                    </div>
                    <div class="form-text text-muted mt-1" style="font-size:11px;">
                        <i class="ri-information-line me-1"></i>Accepted formats: .xlsx, .xls, .csv
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="fa fa-spinner fa-spin fa-spinner-button"></i>
                        <i class="ri-upload-2-line me-1"></i>Upload
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
