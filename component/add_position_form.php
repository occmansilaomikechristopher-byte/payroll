<div class="modal fade" id="modal" tabindex="-1" role="dialog">
    <form id="form-add" novalidate>
        <input type="hidden" name="id" id="id">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-2">
                    <h6 class="modal-title title mb-0">
                        <span class="position-modal-icon me-2"><i class="ri-briefcase-4-line"></i></span>Create Position
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="mb-2">
                        <label class="form-label fw-semibold position-modal-label">
                            <i class="ri-briefcase-4-line me-1"></i>Position Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" placeholder="e.g. Software Engineer"
                            name="name" id="name"
                            data-parsley-required-message="Position name is required." required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm text-white submitbutton position-modal-submit">
                        <i class="ri-save-line me-1"></i>Create
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    #modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
    }

    #modal .modal-title {
        color: #344054;
        font-weight: 600;
    }

    .position-modal-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #e6f5f3;
        color: #01544c;
    }

    .position-modal-label {
        color: #344054;
        font-size: 12px;
    }

    #modal #name {
        border-color: #dfe5ec;
        border-radius: 7px;
    }

    #modal #name:focus {
        border-color: #01544c;
        box-shadow: 0 0 0 3px rgba(1, 84, 76, 0.15);
    }

    .position-modal-submit {
        border: 0;
        border-radius: 6px;
        background: #01544c;
    }

    .position-modal-submit:hover,
    .position-modal-submit:focus {
        background: #01433d;
    }
</style>
