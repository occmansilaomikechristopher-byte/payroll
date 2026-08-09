<style>
    .ir-card { border-top:3px solid #219688; border-radius:4px; background:#fff; }
    .ir-stat { text-align:center; padding:16px; }
    .ir-stat .v { font-size:22px; font-weight:800; color:#219688; }
    .ir-stat .l { font-size:11px; color:#666; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
    #inventory-table thead th { background-color:#219688 !important; border-color:#176358 !important; color:#fff !important; }
    #inventory-table tbody tr:hover td { background:#f0faf9; }
</style>

<?php
$inventory_modal_mode = 'stock';
$totalInventory = 0;
$inventoryResult = $conn->query("SELECT COUNT(*) as total FROM products WHERE status = 1");
if ($inventoryResult) {
    $inventoryRow = $inventoryResult->fetch_assoc();
    $totalInventory = intval($inventoryRow['total'] ?? 0);
}

$products = $conn->query("SELECT p.*, b.branch_name FROM products p LEFT JOIN branches b ON b.id = p.branch_id WHERE p.status = 1 ORDER BY p.product_name ASC");
$login_role = intval($_SESSION['login_role'] ?? 0);
$is_cashier = ($login_role === 9);
$can_manage_products = in_array($login_role, [1, 10], true);
$simple_add_product_modal = false;
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-store-3-line me-2" style="color:#219688;"></i>Inventory Report</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Reports</a></li>
                                <li class="breadcrumb-item active">Inventory</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4"><div class="card ir-card"><div class="card-body ir-stat"><div class="v"><?= number_format($totalInventory) ?></div><div class="l">Total Inventory</div></div></div></div>
            </div>

            <div class="card ir-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Products</h5>
                        <?php if ($can_manage_products): ?>
                        <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-product">
                            <i class="ri-add-line me-1"></i>Add Product
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table id="inventory-table" class="table table-hover table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Branch</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($products): while ($product = $products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['product_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($product['branch_name'] ?? '—') ?></td>
                                        <td class="text-end">&#8369; <?= number_format($product['unit_price'] ?? 0, 2) ?></td>
                                        <td class="text-end"><?= number_format($product['quantity_on_hand'] ?? 0, 2) ?></td>
                                    </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    <!-- Include product modals so add/edit work from Inventory page -->
                    <?php include 'component/pos_modals.php'; ?>
                    <?php $include_products_js = true; // Defer including products.js to the footer so it loads after jQuery ?>
