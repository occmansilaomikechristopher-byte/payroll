<div class="modal fade" id="modal" tabindex="-1" role="dialog">
    <form id="form-add" novalidate>
        <input type="hidden" name="id" id="id">
        <input type="hidden" name="cluster_id" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title title mb-0">
                        <i class="ri-global-line me-2" style="color:#009688;"></i>Create Cluster
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-1">
                        <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                            <i class="ri-global-line me-1"></i>Cluster Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" placeholder="e.g. North Cluster"
                            name="cluster" id="cluster"
                            data-parsley-required-message="Cluster name is required." required>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="ri-save-line me-1"></i>Create
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
