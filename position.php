<style>
    .position-page .page-title-box {
        padding-top: 6px;
        padding-bottom: 20px;
    }

    .position-page .page-title-box h4 {
        color: #1f2937;
        font-weight: 600;
    }

    .position-list-card {
        overflow: hidden;
        border: 1px solid #e8edf3;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .position-list-card .card-header {
        padding: 18px 22px;
        border-bottom: 1px solid #eef2f6;
        background: #fff;
    }

    .position-list-card .card-title {
        color: #344054;
        font-size: 15px;
        font-weight: 600;
    }

    .position-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 7px;
        border-radius: 12px;
        background: #e6f5f3;
        color: #01544c;
        font-size: 11px;
        font-weight: 700;
        vertical-align: middle;
    }

    .position-create-btn {
        border: 0;
        border-radius: 6px;
        background: #01544c;
        box-shadow: 0 3px 8px rgba(1, 84, 76, 0.25);
    }

    .position-create-btn:hover,
    .position-create-btn:focus {
        background: #01433d;
    }

    .position-list-card .card-body {
        padding: 22px;
    }

    #data-table {
        border-color: #edf0f4;
    }

    #data-table thead th {
        padding: 12px 14px;
        border-color: #01433d !important;
        background: #01544c !important;
        color: #fff !important;
        font-size: 12px;
        font-weight: 600;
    }

    #data-table tbody td {
        padding: 13px 14px;
        border-color: #edf0f4;
        color: #475467;
        font-size: 13px;
    }

    #data-table tbody tr:hover td {
        background: #f7fbfa;
    }

    .pos-name {
        color: #344054;
        font-size: 13px;
        font-weight: 600;
    }

    .pos-index {
        display: inline-block;
        min-width: 30px;
        margin-right: 7px;
        color: #98a2b3;
        font-family: monospace;
        font-size: 11px;
    }

    #data-table .btn-outline-primary {
        border-color: #80aaa5;
        border-radius: 5px;
        color: #01544c;
        font-size: 12px;
    }

    #data-table .btn-outline-primary:hover {
        border-color: #01544c;
        background: #e6f5f3;
        color: #01544c;
    }

    .position-list-card .dataTables_wrapper .dataTables_length,
    .position-list-card .dataTables_wrapper .dataTables_filter {
        margin-bottom: 18px;
        color: #667085;
        font-size: 12px;
    }

    .position-list-card .dataTables_wrapper .dataTables_filter input,
    .position-list-card .dataTables_wrapper .dataTables_length select {
        border: 1px solid #dfe5ec;
        border-radius: 6px;
        color: #344054;
        outline: none;
    }

    .position-list-card .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #01544c;
        box-shadow: 0 0 0 3px rgba(1, 84, 76, 0.15);
    }

    @media (max-width: 575.98px) {
        .position-list-card .card-header {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 14px;
        }

        .position-list-card .card-body {
            padding: 16px 12px;
        }

        .position-list-card .dataTables_wrapper .dataTables_length,
        .position-list-card .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: left;
        }
    }
</style>

<div class="main-content position-page">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-briefcase-4-line me-2" style="color:#01544c;"></i>Position
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Position</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card position-list-card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-briefcase-4-line me-2" style="color:#01544c;"></i>Position List
                            <?php
                            $pos_count = $conn->query("SELECT COUNT(*) AS c FROM position")->fetch_assoc()['c'];
                            ?>
                            <span class="position-count ms-1"><?= $pos_count ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm text-white position-create-btn"
                            data-bs-toggle="modal" data-bs-target="#modal">
                            <i class="ri-add-circle-line me-1"></i>Create Position
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="data-table" class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-briefcase-4-line me-1"></i>Position</th>
                                        <th class="text-center" style="width:100px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    $position = $conn->query("SELECT id, name AS position_name FROM position ORDER BY name ASC");
                                    while ($row = $position->fetch_assoc()):
                                        $i++;
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="pos-index">#<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></span>
                                                <span class="pos-name"><?= htmlspecialchars($row['position_name']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    id="<?= $row['id'] ?>"
                                                    name="<?= htmlspecialchars($row['position_name']) ?>"
                                                    onclick="edit_function(this)"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Position">
                                                    <i class="ri-edit-line me-1"></i>Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'component/add_position_form.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
</script>
