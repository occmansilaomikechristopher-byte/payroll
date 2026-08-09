<style>
    .prod-code { background:#219688; color:#fff; padding:2px 8px; border-radius:3px; font-size:10px; font-weight:700; display:inline-block; font-family:monospace; }
    .prod-name { font-weight:600; font-size:13px; }
    .prod-cat { font-size:11px; color:#888; }
    .prod-price { font-weight:600; color:#219688; }
    .prod-qty { font-weight:600; }
    .prod-low { background:#fff3cd; color:#856404; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:700; }
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
                            <i class="ri-shopping-bag-2-line me-2" style="color:#219688;"></i>Products
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">POS System</a></li>
                                <li class="breadcrumb-item active">Products</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #219688;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-shopping-bag-2-line me-2" style="color:#219688;"></i>Products List
                            <?php
                            $prod_count = $conn->query("SELECT COUNT(*) AS c FROM products WHERE status=1")->fetch_assoc()['c'];
                            $login_role = intval($_SESSION['login_role'] ?? 0);
                            $can_manage_products = in_array($login_role, [1, 9, 10], true);
                            ?>
                            <span class="badge ms-1" style="background:#e6f5f3;color:#219688;font-size:11px;font-weight:700;vertical-align:middle;"><?= $prod_count ?></span>
                        </h4>
                        <?php if ($can_manage_products): ?>
                            <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;"
                                data-bs-toggle="modal" data-bs-target="#modal-add-product">
                                <i class="ri-add-line me-1"></i>Add Product
                            </button>

                            
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <?php if ($can_manage_products): ?>
                        <div class="mb-3">
                            <!-- <button type="button" class="btn text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-product">
                                <i class="ri-add-line me-1"></i>Add Product
                            </button> -->
                        </div>
                        <?php endif; ?>
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:60px;"><i class="ri-image-line"></i></th>
                                        <th><i class="ri-box-line me-1"></i>Product</th>
                                        <th><i class="ri-layout-grid-line me-1"></i>Category</th>
                                        <th><i class="ri-git-branch-line me-1"></i>Branch</th>
                                        <th class="text-center" style="width:80px;"><i class="ri-stack-line me-1"></i>Qty</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-money-dollar-circle-line me-1"></i>Price</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-pulse-line me-1"></i>Status</th>
                                        <th class="text-center" style="width:100px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $conn->query("SELECT p.*, c.category_name, b.branch_name FROM products p
                                        LEFT JOIN product_categories c ON p.category_id = c.id
                                        LEFT JOIN branches b ON p.branch_id = b.id
                                        ORDER BY p.product_name ASC");
                                    if ($query && $query->num_rows > 0):
                                        while ($row = $query->fetch_assoc()):
                                            $isLow = floatval($row['quantity_on_hand']) <= floatval($row['reorder_level']);
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <img src="<?= !empty($row['image']) ? 'uploads/products/' . htmlspecialchars($row['image']) : 'assets/images/no-image.svg' ?>"
                                                    alt="" class="prod-thumb"
                                                    style="width:38px;height:38px;object-fit:cover;border-radius:4px;border:1px solid #d0d7ee;">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="prod-code"><?= htmlspecialchars($row['product_code']) ?></span>
                                                    <div class="prod-name"><?= htmlspecialchars($row['product_name']) ?></div>
                                                </div>
                                            </td>
                                            <td><span class="prod-cat"><?= htmlspecialchars($row['category_name'] ?? '—') ?></span></td>
                                            <td><?= htmlspecialchars($row['branch_name'] ?? '—') ?></td>
                                            <td class="text-center">
                                                <span class="prod-qty <?= $isLow ? 'prod-low' : '' ?>">
                                                    <?= number_format($row['quantity_on_hand'], 2) ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><span class="prod-price">₱ <?= number_format($row['unit_price'], 2) ?></span></td>
                                            <td class="text-center">
                                                <span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $row['status'] == 1 ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-product"
                                                    data-id="<?= $row['id'] ?>" data-code="<?= htmlspecialchars($row['product_code']) ?>"
                                                    data-name="<?= htmlspecialchars($row['product_name']) ?>" data-category="<?= $row['category_id'] ?>"
                                                    data-branch="<?= $row['branch_id'] ?>" data-qty="<?= $row['quantity_on_hand'] ?>"
                                                    data-price="<?= $row['unit_price'] ?>" data-cost="<?= $row['cost_price'] ?>"
                                                    data-unit="<?= htmlspecialchars($row['unit'] ?? '') ?>" data-reorder="<?= $row['reorder_level'] ?>"
                                                    data-image="<?= htmlspecialchars($row['image'] ?? '') ?>"
                                                    data-desc="<?= htmlspecialchars($row['description'] ?? '') ?>" data-status="<?= $row['status'] ?>">
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
