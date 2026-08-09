<?php
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$login_role = intval($_SESSION['login_role'] ?? 0);
$can_manage_products = in_array($login_role, [1, 9, 10], true);
?>

<style>
    .pos-card { border-top:3px solid #219688; border-radius:4px; background:#fff; }
    .pos-stat { text-align:center; padding:20px; }
    .pos-stat-val { font-size:28px; font-weight:700; color:#219688; }
    .pos-stat-lbl { font-size:12px; color:#666; text-transform:uppercase; letter-spacing:.5px; margin-top:6px; }
    .pos-icon { width:48px; height:48px; border-radius:50%; background:#e6f5f3; display:flex; align-items:center; justify-content:center; font-size:20px; color:#219688; margin:0 auto 10px; }
    .table-striped tbody tr:hover { background-color:#f0faf9 !important; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <div>
                            <h4 class="mb-sm-0">
                                <i class="ri-shopping-cart-2-line me-2" style="color:#219688;"></i>POS System
                            </h4>
                            <p class="text-muted mb-0 small mt-1"><i class="ri-information-line"></i> Manage branches, product categories, and inventory</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">POS System</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nav Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs" role="tablist" style="border-bottom:2px solid #e9ecef;">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $tab === 'dashboard' ? 'active' : '' ?>" href="pos" style="<?= $tab === 'dashboard' ? 'border-bottom:2px solid #219688;color:#219688;' : '' ?>">
                                    <i class="ri-dashboard-line me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $tab === 'branches' ? 'active' : '' ?>" href="pos?tab=branches" style="<?= $tab === 'branches' ? 'border-bottom:2px solid #219688;color:#219688;' : '' ?>">
                                    <i class="ri-git-branch-line me-2"></i>Branches
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $tab === 'categories' ? 'active' : '' ?>" href="pos?tab=categories" style="<?= $tab === 'categories' ? 'border-bottom:2px solid #219688;color:#219688;' : '' ?>">
                                    <i class="ri-layout-grid-line me-2"></i>Categories
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $tab === 'products' ? 'active' : '' ?>" href="pos?tab=products" style="<?= $tab === 'products' ? 'border-bottom:2px solid #219688;color:#219688;' : '' ?>">
                                    <i class="ri-shopping-bag-2-line me-2"></i>Products
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php if ($tab === 'dashboard'): ?>
                <!-- Dashboard Stats -->
                <div class="row mt-4">
                    <?php
                    $branches = $conn->query("SELECT COUNT(*) as cnt FROM branches WHERE status=1")->fetch_assoc();
                    $categories = $conn->query("SELECT COUNT(*) as cnt FROM product_categories WHERE status=1")->fetch_assoc();
                    $products = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE status=1")->fetch_assoc();
                    $total_qty = $conn->query("SELECT SUM(quantity_on_hand) as total FROM products WHERE status=1")->fetch_assoc();
                    ?>
                    <div class="col-md-3">
                        <div class="card pos-card">
                            <div class="card-body pos-stat">
                                <div class="pos-icon"><i class="ri-git-branch-line"></i></div>
                                <div class="pos-stat-val"><?= $branches['cnt'] ?? 0 ?></div>
                                <div class="pos-stat-lbl">Active Branches</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card pos-card">
                            <div class="card-body pos-stat">
                                <div class="pos-icon"><i class="ri-layout-grid-line"></i></div>
                                <div class="pos-stat-val"><?= $categories['cnt'] ?? 0 ?></div>
                                <div class="pos-stat-lbl">Categories</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card pos-card">
                            <div class="card-body pos-stat">
                                <div class="pos-icon"><i class="ri-box-line"></i></div>
                                <div class="pos-stat-val"><?= $products['cnt'] ?? 0 ?></div>
                                <div class="pos-stat-lbl">Active Products</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card pos-card">
                            <div class="card-body pos-stat">
                                <div class="pos-icon"><i class="ri-stack-line"></i></div>
                                <div class="pos-stat-val"><?= number_format($total_qty['total'] ?? 0, 0) ?></div>
                                <div class="pos-stat-lbl">Total Inventory</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card pos-card">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="ri-lightbulb-flash-line me-2" style="color:#219688;"></i>Quick Actions</h5>
                                <div class="d-flex gap-3 flex-wrap">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-add-branch">
                                        <i class="ri-add-line me-1"></i>Add Branch
                                    </button>
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-add-category">
                                        <i class="ri-add-line me-1"></i>Add Category
                                    </button>
                                    <?php if ($can_manage_products): ?>
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-add-product">
                                        <i class="ri-add-line me-1"></i>Add Product
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab === 'branches'): ?>
                <?php include 'component/pos_branches.php'; ?>

            <?php elseif ($tab === 'categories'): ?>
                <?php include 'component/pos_categories.php'; ?>

            <?php elseif ($tab === 'products'): ?>
                <?php include 'component/pos_products.php'; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Include all modals -->
<?php $simple_add_product_modal = false; ?>
<?php include 'component/pos_modals.php'; ?>
<?php if ($tab === 'products'): ?>
    <script src="assets2/js/products.js"></script>
<?php endif; ?>
