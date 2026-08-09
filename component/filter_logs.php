<div class="modal fade" id="modal-filter-add" tabindex="-1" role="dialog">
    <form id="form-filter" method="get" action="" novalidate>
        <input type="hidden" name="page" value="visitors-logs">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-filter-3-line me-2" style="color:#009688;"></i>Filter Visitor Logs
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <!-- Date Range -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                            <i class="ri-calendar-range-line me-1"></i>Date Range <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">From</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                    <input name="from" id="from" class="form-control datetimepicker"
                                        autocomplete="off" placeholder="YYYY/MM/DD"
                                        data-parsley-required-message="Select start date." required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">To</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                    <input name="to" id="to" class="form-control datetimepicker"
                                        autocomplete="off" placeholder="YYYY/MM/DD"
                                        data-parsley-required-message="Select end date." required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Site -->
                    <div class="mb-1">
                        <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                            <i class="ri-map-pin-2-line me-1"></i>Site <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <select id="site-select" class="form-control select2" name="site_id"
                            data-placeholder="— All Sites —">
                            <option value="">— All Sites —</option>
                            <?php
                            $user_forms = $conn->query("SELECT * FROM sites WHERE status = 1 ORDER BY site_name ASC");
                            while ($row_data_form = $user_forms->fetch_assoc()):
                            ?>
                                <option value="<?= $row_data_form['id'] ?>">
                                    <?= htmlspecialchars($row_data_form['site_name']) ?> (<?= htmlspecialchars($row_data_form['site_address']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white" style="background:#009688;border-color:#009688;">
                        <i class="ri-search-line me-1"></i>Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
