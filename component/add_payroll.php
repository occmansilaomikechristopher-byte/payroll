<!-- ── Create Payroll ─────────────────────────────────────────────── -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog">
    <form id="form-submit" novalidate>
        <input type="hidden" name="id" id="id">
        <input type="hidden" name="p2" value="<?= (isset($_GET['p2']) && $_GET['p2'] === 'true') ? 'yes' : 'no' ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-money-dollar-circle-line me-2" style="color:#009688;"></i>Create Payroll
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-building-2-line me-1"></i>Branch <span class="text-danger">*</span>
                            </label>
                            <select id="employer-select" class="form-control select2" name="employer_id"
                                data-placeholder="Select branch"
                                data-parsley-required-message="Please select employer." required>
                                <option value=""></option>
                                <?php if (isset($_SESSION['login_role']) && in_array($_SESSION['login_role'], [1, 10])): ?>
                                    <option value="0">All Branches</option>
                                <?php endif; ?>
                                <?php
                                $user_forms = $conn->query("SELECT * FROM branches WHERE status=1 ORDER BY branch_name ASC");
                                if ($user_forms) while ($row_data_form = $user_forms->fetch_assoc()):
                                ?>
                                    <option value="<?= $row_data_form['id'] ?>"><?= htmlspecialchars($row_data_form['branch_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-calendar-2-line me-1"></i>Date From <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                <input type="text" name="date_from" class="form-control datetimepicker"
                                    autocomplete="off" placeholder="YYYY/MM/DD"
                                    data-parsley-required-message="Please select date." required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-calendar-2-line me-1"></i>Date To <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                <input type="text" name="date_to" class="form-control datetimepicker"
                                    autocomplete="off" placeholder="YYYY/MM/DD"
                                    data-parsley-required-message="Please select date." required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-list-check-2 me-1"></i>Payroll Type <span class="text-danger">*</span>
                            </label>
                            <select id="type" name="type" class="form-control select2"
                                data-placeholder="Select type"
                                data-parsley-required-message="Please select type." required>
                                <option value=""></option>
                                <option value="1"><i class="ri-calendar-2-line"></i> Week 1</option>
                                <option value="2">Week 2</option>
                                <option value="3">Week 3</option>
                                <option value="4">Week 4</option>
                                <!-- <option value="5">Monthly</option> -->
                            </select>
                        </div>

                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="ri-arrow-right-line me-1"></i>Next
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Payroll Settings ───────────────────────────────────────────── -->
<div class="modal fade" id="modal-settings" tabindex="-1" role="dialog">
    <form id="form-settings" novalidate>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-settings-3-line me-2" style="color:#009688;"></i>Payroll Settings
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="settings-id">

                    <?php
                    $sections = [
                        [
                            'title' => 'Contributions',
                            'icon'  => 'ri-hand-coin-line',
                            'query' => "SELECT id, contribution AS label FROM contributions ORDER BY id ASC",
                            'prefix'=> 'contributions',
                            'name'  => 'contributions[]',
                            'id_col'=> 'id',
                        ],
                        [
                            'title' => 'Deductions',
                            'icon'  => 'ri-subtract-line',
                            'query' => "SELECT id, deduction AS label FROM deductions ORDER BY id ASC",
                            'prefix'=> 'deductions',
                            'name'  => 'deductions[]',
                            'id_col'=> 'id',
                        ],
                        [
                            'title' => 'Loans',
                            'icon'  => 'ri-bank-card-line',
                            'query' => "SELECT clt_id AS id, loan_type AS label FROM contribution_loan_types ORDER BY clt_id ASC",
                            'prefix'=> 'loan',
                            'name'  => 'loans[]',
                            'id_col'=> 'id',
                        ],
                       
                    ];

                    foreach ($sections as $i => $sec):
                        $rows = $conn->query($sec['query']);
                    ?>
                    <?= $i > 0 ? '<hr class="my-3">' : '' ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="<?= $sec['icon'] ?>" style="color:#009688;font-size:16px;"></i>
                        <span class="fw-bold" style="font-size:13px;color:#009688;"><?= $sec['title'] ?></span>
                    </div>
                    <div class="row g-2">
                        <?php while ($row = $rows->fetch_assoc()): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check" style="border:1px solid #e8eaf6;border-radius:4px;padding:6px 10px 6px 32px;background:#f9f9ff;">
                                <input class="form-check-input" type="checkbox"
                                    id="<?= $sec['prefix'] ?>-<?= $row[$sec['id_col']] ?>"
                                    name="<?= $sec['name'] ?>"
                                    value="<?= $row[$sec['id_col']] ?>">
                                <label class="form-check-label" style="font-size:12px;font-weight:600;cursor:pointer;"
                                    for="<?= $sec['prefix'] ?>-<?= $row[$sec['id_col']] ?>">
                                    <?= htmlspecialchars($row['label']) ?>
                                </label>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endforeach; ?>

                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="ri-save-line me-1"></i>Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ── Payroll History ───────────────────────────────────────────── -->
<div class="modal fade" id="modal-payroll-history" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title mb-0">
                    <i class="ri-history-line me-2" style="color:#009688;"></i>Payroll History
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loanHistoryDiv"></div>
            </div>
        </div>
    </div>
</div>

