<!-- ADD BRANCH MODAL -->
<div class="modal fade" id="modal-add-branch" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-git-branch-line me-2"></i>Add Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-add-branch" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" name="branch_code" class="form-control" required placeholder="e.g., BR001"
                            data-parsley-required-message="Branch code is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="branch_name" class="form-control" required placeholder="e.g., Main Branch"
                            data-parsley-required-message="Branch name is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" placeholder="City">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Phone number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Email address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background:#219688;">Save Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT BRANCH MODAL -->
<div class="modal fade" id="modal-edit-branch" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-edit-line me-2"></i>Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-edit-branch" novalidate>
                <input type="hidden" id="edit-branch-id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" id="edit-branch-code" name="branch_code" class="form-control" required
                            data-parsley-required-message="Branch code is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit-branch-name" name="branch_name" class="form-control" required
                            data-parsley-required-message="Branch name is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" id="edit-branch-city" name="city" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="tel" id="edit-branch-phone" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" id="edit-branch-email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select id="edit-branch-status" name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background:#219688;">Update Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="modal-add-category" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-layout-grid-line me-2"></i>Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-add-category" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Code <span class="text-danger">*</span></label>
                        <input type="text" name="category_code" class="form-control" required placeholder="e.g., CAT001"
                            data-parsley-required-message="Category code is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required placeholder="e.g., Laminated Glass"
                            data-parsley-required-message="Category name is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Category description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background:#219688;">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT CATEGORY MODAL -->
<div class="modal fade" id="modal-edit-category" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-edit-line me-2"></i>Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-edit-category" novalidate>
                <input type="hidden" id="edit-category-id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Code <span class="text-danger">*</span></label>
                        <input type="text" id="edit-category-code" name="category_code" class="form-control" required
                            data-parsley-required-message="Category code is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit-category-name" name="category_name" class="form-control" required
                            data-parsley-required-message="Category name is required.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea id="edit-category-desc" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select id="edit-category-status" name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background:#219688;">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $simple_add_product_modal = $simple_add_product_modal ?? false; ?>
<!-- ADD PRODUCT MODAL -->
<div class="modal fade" id="modal-add-product" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-box-line me-2"></i>Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-add-product" enctype="multipart/form-data" novalidate>
                <div class="modal-body">
                    <?php if (!empty($simple_add_product_modal)): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" class="form-control" required placeholder="Product name"
                                        data-parsley-required-message="Product name is required.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Branch <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-control select2" required
                                        data-parsley-required-message="Please select a branch.">
                                        <option value="">— Select Branch —</option>
                                        <?php
                                        $brn = $conn->query("SELECT * FROM branches WHERE status=1 ORDER BY branch_name");
                                        while ($b = $brn->fetch_assoc()):
                                        ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['branch_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                                    <input type="number" name="unit_price" class="form-control" required step="0.01" min="0" placeholder="0.00"
                                        data-parsley-required-message="Price is required.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Stock <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity_on_hand" class="form-control" required step="0.01" min="0" placeholder="0.00"
                                        data-parsley-required-message="Stock is required.">
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center mb-3">
                            <label class="form-label fw-semibold d-block">Product Image</label>
                            <img id="add-product-preview" src="assets/images/no-image.svg" alt="Preview"
                                style="width:90px;height:90px;object-fit:cover;border:2px solid #aad5d0;border-radius:6px;cursor:pointer;background:#f0faf9;"
                                onclick="document.getElementById('add-product-image').click()">
                            <div>
                                <input type="file" id="add-product-image" name="image" accept="image/*" class="d-none">
                                <small class="text-muted">Click image to upload (JPG/PNG)</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Product Code <span class="text-danger">*</span></label>
                                    <input type="text" name="product_code" class="form-control" required placeholder="e.g., PROD001"
                                        data-parsley-required-message="Product code is required.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" class="form-control" required placeholder="Product name"
                                        data-parsley-required-message="Product name is required.">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-control select2" required
                                        data-parsley-required-message="Please select a category.">
                                        <option value="">— Select Category —</option>
                                        <?php
                                        $cats = $conn->query("SELECT * FROM product_categories WHERE status=1 ORDER BY category_name");
                                        while ($c = $cats->fetch_assoc()):
                                        ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Branch <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-control select2" required
                                        data-parsley-required-message="Please select a branch.">
                                        <option value="">— Select Branch —</option>
                                        <?php
                                        $brn = $conn->query("SELECT * FROM branches WHERE status=1 ORDER BY branch_name");
                                        while ($b = $brn->fetch_assoc()):
                                        ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['branch_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Unit Price <span class="text-danger">*</span></label>
                                    <input type="number" name="unit_price" class="form-control" required step="0.01" min="0" placeholder="0.00"
                                        data-parsley-required-message="Unit price is required.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Cost Price</label>
                                    <input type="number" name="cost_price" class="form-control" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Quantity on Hand</label>
                                    <input type="number" name="quantity_on_hand" class="form-control" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                    <select id="add-product-unit" name="unit" class="form-control select2" required
                                        data-parsley-required-message="Please select a unit.">
                                        <option value="" disabled selected>— Select Unit —</option>
                                        <option value="pcs">pcs</option>
                                        <option value="box">box</option>
                                        <option value="pack">pack</option>
                                        <option value="pair">pair</option>
                                        <option value="set">set</option>
                                        <option value="meter">meter</option>
                                        <option value="roll">roll</option>
                                        <option value="kg">kg</option>
                                        <option value="liter">liter</option>
                                        <option value="tube">tube</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reorder Level</label>
                            <input type="number" name="reorder_level" class="form-control" step="0.01" min="0" placeholder="10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Product description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background:#219688;">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD STOCK MODAL -->
<div class="modal fade" id="modal-add-stock" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-add-line me-2"></i>Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-add-stock" novalidate>
                <input type="hidden" id="add-stock-product-id" name="id">
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="fw-semibold" style="color:#219688;">Stock Update</div>
                        <div class="small text-muted">Enter the quantity to add to inventory.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" id="add-stock-qty" name="quantity_to_add" class="form-control" step="0.01" min="0.01" placeholder="0.00" required
                            data-parsley-required-message="Please enter quantity to add.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background:#219688;">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$inventory_modal_mode = $inventory_modal_mode ?? 'full';
$is_cashier_modal = isset($_SESSION['login_role']) && intval($_SESSION['login_role']) === 9;
$is_cashier_stock_modal = $is_cashier_modal && $inventory_modal_mode === 'stock';
?>

<!-- EDIT PRODUCT MODAL -->
<div class="modal fade" id="modal-edit-product" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-edit-line me-2"></i><?= $is_cashier_stock_modal ? 'Add Stock' : 'Edit Product' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-edit-product" enctype="multipart/form-data" novalidate data-mode="<?= $is_cashier_stock_modal ? 'stock' : 'full' ?>">
                <input type="hidden" id="edit-product-id" name="id">
                <input type="hidden" id="edit-product-current-image" name="current_image">
                <div class="modal-body">
                    <?php if ($is_cashier_stock_modal): ?>
                        <div class="alert alert-light border mb-3">
                            <div class="fw-semibold" style="color:#219688;">Stock Update</div>
                            <div class="small text-muted">Add quantity to the current inventory for this product.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Current Stock</label>
                                    <input type="text" id="edit-product-current-stock" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Quantity to Add <span class="text-danger">*</span></label>
                                    <input type="number" id="edit-product-qty" name="quantity_to_add" class="form-control" step="0.01" min="0.01" placeholder="0.00" required
                                        data-parsley-required-message="Please enter quantity to add.">
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center mb-3">
                            <label class="form-label fw-semibold d-block">Product Image</label>
                            <img id="edit-product-preview" src="assets/images/no-image.svg" alt="Preview"
                                style="width:90px;height:90px;object-fit:cover;border:2px solid #aad5d0;border-radius:6px;cursor:pointer;background:#f0faf9;"
                                onclick="document.getElementById('edit-product-image').click()">
                            <div>
                                <input type="file" id="edit-product-image" name="image" accept="image/*" class="d-none">
                                <small class="text-muted">Click image to change (JPG/PNG)</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Product Code <span class="text-danger">*</span></label>
                                    <input type="text" id="edit-product-code" name="product_code" class="form-control" required
                                        data-parsley-required-message="Product code is required.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" id="edit-product-name" name="product_name" class="form-control" required
                                        data-parsley-required-message="Product name is required.">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                    <select id="edit-product-category" name="category_id" class="form-control select2" required
                                        data-parsley-required-message="Please select a category.">
                                        <option value="">— Select Category —</option>
                                        <?php
                                        $cats = $conn->query("SELECT * FROM product_categories WHERE status=1 ORDER BY category_name");
                                        while ($c = $cats->fetch_assoc()):
                                        ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Branch <span class="text-danger">*</span></label>
                                    <select id="edit-product-branch" name="branch_id" class="form-control select2" required
                                        data-parsley-required-message="Please select a branch.">
                                        <option value="">— Select Branch —</option>
                                        <?php
                                        $brn = $conn->query("SELECT * FROM branches WHERE status=1 ORDER BY branch_name");
                                        while ($b = $brn->fetch_assoc()):
                                        ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['branch_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Unit Price <span class="text-danger">*</span></label>
                                    <input type="number" id="edit-product-price" name="unit_price" class="form-control" required step="0.01" min="0"
                                        data-parsley-required-message="Unit price is required.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Cost Price</label>
                                    <input type="number" id="edit-product-cost" name="cost_price" class="form-control" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Quantity on Hand</label>
                                    <input type="number" id="edit-product-qty" name="quantity_on_hand" class="form-control" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                    <select id="edit-product-unit" name="unit" class="form-control select2" required
                                        data-parsley-required-message="Please select a unit.">
                                        <option value="" disabled>— Select Unit —</option>
                                        <option value="pcs">pcs</option>
                                        <option value="box">box</option>
                                        <option value="pack">pack</option>
                                        <option value="pair">pair</option>
                                        <option value="set">set</option>
                                        <option value="meter">meter</option>
                                        <option value="roll">roll</option>
                                        <option value="kg">kg</option>
                                        <option value="liter">liter</option>
                                        <option value="tube">tube</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reorder Level</label>
                            <input type="number" id="edit-product-reorder" name="reorder_level" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea id="edit-product-desc" name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select id="edit-product-status" name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background:#219688;"><?= $is_cashier_stock_modal ? 'Add Stock' : 'Update Product' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AJAX handlers are now in individual pages: branches.php, categories.php, products.php -->
