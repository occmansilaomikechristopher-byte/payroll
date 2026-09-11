<?php 
$login_role = intval($_SESSION['login_role'] ?? 0); 
$is_cashier = ($login_role === 9); 
$can_manage_products = in_array($login_role, [1, 9, 10], true);
// Debug: Always show button for testing
$show_add_button = true;
$unit_labels = [
    'pcs' => 'PCS',
    'box' => 'BOX',
    'pack' => 'PACK',
    'pair' => 'PAIR',
    'set' => 'SET',
    'sqm' => 'SQM (Square Meter)',
    'sqft' => 'SQFT (Square Foot)',
    'meter' => 'METER',
    'length' => 'LENGTH',
    'sheet' => 'SHEET',
    'roll' => 'ROLL',
    'tube' => 'TUBE',
];
?>
<div class="row">
    <div class="col-12">
        <div class="card pos-card">
            <div class="card-header align-items-center d-flex py-2">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-box-line me-2" style="color:#219688;"></i>Products
                </h4>
                <?php if ($show_add_button): ?>
                <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-product">
                    <i class="ri-add-line me-1"></i>Add Product
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($show_add_button): ?>
                <div class="mb-3">
                    <button type="button" class="btn text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-product">
                        <i class="ri-add-line me-1"></i>Add Product
                    </button>
                </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead style="background:#e6f5f3;">
                            <tr>
                                <th style="width:90px;" class="text-center">Code</th>
                                <th>Product Name</th>
                                <th style="width:100px;">Category</th>
                                <th style="width:80px;">Branch</th>
                                <th style="width:80px;" class="text-center">Qty</th>
                                <th style="width:90px;" class="text-center">Unit Price</th>
                                <th style="width:100px;" class="text-center">Status</th>
                                <?php if ($is_cashier): ?><th style="width:100px;" class="text-center">Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $products = $conn->query("SELECT p.*, c.category_name, b.branch_name FROM products p
                                LEFT JOIN product_categories c ON p.category_id = c.id
                                LEFT JOIN branches b ON p.branch_id = b.id
                                ORDER BY p.product_name ASC");
                            if ($products->num_rows > 0):
                                while ($row = $products->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><small><?= htmlspecialchars($row['product_code']) ?></small></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><small><?= htmlspecialchars($row['category_name'] ?? '—') ?></small></td>
                                <td><small><?= htmlspecialchars($row['branch_name'] ?? '—') ?></small></td>
                                <td class="text-center">
                                    <span class="<?= floatval($row['quantity_on_hand']) <= floatval($row['reorder_level']) ? 'badge bg-warning text-dark' : '' ?>">
                                        <?php
                                        $unit_key = strtolower(trim((string) ($row['unit'] ?? 'pcs')));
                                        echo number_format($row['quantity_on_hand'], 2) . ' ' . htmlspecialchars($unit_labels[$unit_key] ?? strtoupper($unit_key));
                                        ?>
                                    </span>
                                </td>
                                <td class="text-center">₱ <?= number_format($row['unit_price'], 2) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $row['status'] == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <?php if ($is_cashier): ?>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary edit-product-btn" data-bs-toggle="modal" data-bs-target="#modal-edit-product"
                                        data-id="<?= $row['id'] ?>"
                                        data-code="<?= htmlspecialchars($row['product_code']) ?>"
                                        data-name="<?= htmlspecialchars($row['product_name']) ?>"
                                        data-category="<?= $row['category_id'] ?>"
                                        data-branch="<?= $row['branch_id'] ?>"
                                        data-qty="<?= $row['quantity_on_hand'] ?>"
                                        data-price="<?= $row['unit_price'] ?>"
                                        data-cost="<?= $row['cost_price'] ?>"
                                        data-unit="<?= htmlspecialchars($row['unit'] ?? '') ?>"
                                        data-reorder="<?= $row['reorder_level'] ?>"
                                        data-desc="<?= htmlspecialchars($row['description'] ?? '') ?>"
                                        data-status="<?= $row['status'] ?>">
                                        <i class="ri-edit-line"></i> Edit
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="ri-inbox-line me-2"></i>No products found
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

<script>
$(document).ready(function(){
    $('.edit-product-btn').click(function(){
        var btn = $(this);
        $('#edit-product-id').val(btn.data('id'));
        $('#edit-product-code').val(btn.data('code'));
        $('#edit-product-name').val(btn.data('name'));
        $('#edit-product-category').val(btn.data('category'));
        $('#edit-product-branch').val(btn.data('branch'));
        $('#edit-product-qty').val(btn.data('qty'));
        $('#edit-product-price').val(btn.data('price'));
        $('#edit-product-cost').val(btn.data('cost'));
        $('#edit-product-unit').val(btn.data('unit'));
        $('#edit-product-reorder').val(btn.data('reorder'));
        $('#edit-product-desc').val(btn.data('desc'));
        $('#edit-product-status').val(btn.data('status'));
    });
});
</script>
