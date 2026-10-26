<style>
    .deduction-name {
        color: #344054;
        font-size: 13px;
        font-weight: 600;
    }

    .deduction-icon {
        align-items: center;
        background: #e6f5f3;
        border-radius: 8px;
        color: #219688;
        display: inline-flex;
        height: 32px;
        justify-content: center;
        width: 32px;
    }

    #data-table thead th {
        background-color: #219688 !important;
        border-color: #176358 !important;
        color: #fff !important;
        font-size: 12px;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    #data-table tbody tr:hover td {
        background: #f0faf9;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-money-dollar-circle-line me-2" style="color:#219688;"></i>Deductions
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Deductions</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #219688;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-money-dollar-circle-line me-2" style="color:#219688;"></i>Deduction List
                            <?php
                            $query = $conn->query("SELECT id, deduction, description FROM deductions ORDER BY deduction ASC");
                            $deductions = [];
                            $deductionsQueryError = $query === false;
                            if (!$deductionsQueryError) {
                                while ($row = $query->fetch_assoc()) {
                                    $deductions[] = $row;
                                }
                            }
                            ?>
                            <span class="badge ms-1" style="background:#e6f5f3;color:#219688;font-size:11px;font-weight:700;vertical-align:middle;">
                                <?= count($deductions) ?>
                            </span>
                        </h4>
                        <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;"
                            data-bs-toggle="modal" id="create-btn" data-bs-target="#modal">
                            <i class="ri-add-line me-1"></i>Create Deduction
                        </button>
                    </div>

                    <div class="card-body">
                        <?php if ($deductionsQueryError): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                                <i class="ri-error-warning-line"></i>
                                Unable to load deductions. Please try again.
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-money-dollar-circle-line me-1"></i>Deduction</th>
                                        <th class="text-center" style="width:110px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($deductions) > 0): ?>
                                        <?php foreach ($deductions as $row): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="deduction-icon"><i class="ri-file-list-3-line"></i></span>
                                                        <span class="deduction-name"><?= htmlspecialchars($row['deduction'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" data-toggle="tooltip"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Edit Deduction"
                                                        id="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                        name="<?= htmlspecialchars($row['deduction'], ENT_QUOTES, 'UTF-8') ?>"
                                                        description="<?= htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="edit_function(this)">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">
                                                <i class="ri-inbox-line d-block fs-24 mb-2"></i>
                                                No deductions found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'component/add_deduction_form.php'; ?>
