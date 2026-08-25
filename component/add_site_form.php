<div class="modal fade" id="modal" tabindex="-1" role="dialog">
    <form id="form-add" novalidate>
        <input type="hidden" name="id" id="id">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title title mb-0">
                        <i class="ri-map-pin-2-line me-2" style="color:#009688;"></i>Create Site
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-hashtag me-1"></i>Site Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" placeholder="e.g. SITE-001"
                                name="site_code" id="site_code"
                                data-parsley-required-message="Code is required." required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-map-pin-2-line me-1"></i>Site Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" placeholder="e.g. Main Branch"
                                name="site_name" id="site_name"
                                data-parsley-required-message="Name is required." required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-road-map-line me-1"></i>Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="site_address" name="site_address"
                                placeholder="Enter full address" rows="2"
                                required data-parsley-required-message="Address is required."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-building-2-line me-1"></i>Employer <span class="text-danger">*</span>
                            </label>
                            <select id="employer_id" class="form-control select2" name="employer_id"
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                                <i class="ri-user-settings-line me-1"></i>Cashier
                            </label>
                            <select id="timekeeper-select" class="form-control select2" name="timekeeper_id"
                                data-placeholder="Select cashier">
                                <option value=""></option>
                                <?php
                                $pos = $conn->query("SELECT * FROM users WHERE role = 5 ORDER BY name ASC");
                                while ($row = $pos->fetch_assoc()):
                                ?>
                                    <option class="opt" value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input name="status" class="form-check-input" type="checkbox" id="status2" style="width:36px;height:18px;">
                                <label class="form-check-label ms-1 fw-semibold" for="status2" style="font-size:13px;">Active</label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="ri-save-line me-1"></i><span class="fa fa-spinner fa-spin fa-spinner-button"></span>Create
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
