<style>
    .cat-code { background:#219688; color:#fff; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:700; display:inline-block; font-family:monospace; }
    .cat-name { font-weight:600; font-size:13px; }
    .cat-desc { font-size:11px; color:#888; }
    #data-table thead th { background-color:#219688 !important; border-color:#176358 !important; color:#fff !important; }
    #data-table tbody tr:hover td { background:#f0faf9; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-layout-grid-line me-2" style="color:#219688;"></i>Product Categories
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">POS System</a></li>
                                <li class="breadcrumb-item active">Categories</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #219688;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-layout-grid-line me-2" style="color:#219688;"></i>Categories List
                            <?php
                            $cat_count = $conn->query("SELECT COUNT(*) AS c FROM product_categories WHERE status=1")->fetch_assoc()['c'];
                            ?>
                            <span class="badge ms-1" style="background:#e6f5f3;color:#219688;font-size:11px;font-weight:700;vertical-align:middle;"><?= $cat_count ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;"
                            data-bs-toggle="modal" data-bs-target="#modal-add-category">
                            <i class="ri-add-line me-1"></i>Add Category
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-layout-grid-line me-1"></i>Category</th>
                                        <th><i class="ri-file-text-line me-1"></i>Description</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-pulse-line me-1"></i>Status</th>
                                        <th class="text-center" style="width:100px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $conn->query("SELECT * FROM product_categories ORDER BY category_name ASC");
                                    if ($query && $query->num_rows > 0):
                                        while ($row = $query->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="cat-code"><?= htmlspecialchars($row['category_code']) ?></span>
                                                    <div class="cat-name"><?= htmlspecialchars($row['category_name']) ?></div>
                                                </div>
                                            </td>
                                            <td><span class="cat-desc"><?= htmlspecialchars($row['description'] ?? '—') ?></span></td>
                                            <td class="text-center">
                                                <span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $row['status'] == 1 ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-category"
                                                    data-id="<?= $row['id'] ?>" data-code="<?= htmlspecialchars($row['category_code']) ?>"
                                                    data-name="<?= htmlspecialchars($row['category_name']) ?>" data-desc="<?= htmlspecialchars($row['description'] ?? '') ?>"
                                                    data-status="<?= $row['status'] ?>">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
                                        endwhile;
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'component/pos_modals.php'; ?>
