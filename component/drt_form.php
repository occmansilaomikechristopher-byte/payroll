<div class="modal fade" id="modal-dtr" tabindex="-1" role="dialog">
    <form id="fileUploadForm" enctype="multipart/form-data" novalidate data-parsley-validate>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title mb-0">
                        <i class="ri-upload-2-line me-2" style="color:#009688;"></i>Upload DTR Files
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#009688;">
                            <i class="ri-fingerprint-line me-1"></i>Biometric File
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:#eef0f8;border-color:#c5cde8;">
                                <i class="ri-file-text-line" style="color:#009688;"></i>
                            </span>
                            <input type="file" name="fileBiometric" id="fileBiometric" class="form-control"
                                accept=".dat" required
                                data-parsley-required-message="Biometric file is required.">
                        </div>
                        <div class="form-text text-muted" style="font-size:11px;">
                            <i class="ri-information-line me-1"></i>Expected: <code>fileBiometric.dat</code>
                        </div>
                    </div>


                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton" style="background:#009688;border-color:#009688;">
                        <i class="ri-upload-2-line me-1"></i>Upload
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
