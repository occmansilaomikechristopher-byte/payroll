<?php
if (!isset($_SESSION)) {
    session_start();
}
if (intval($_SESSION['login_role'] ?? 0) !== 9) {
    header('location: home');
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS damage_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    damage_code VARCHAR(60) NOT NULL UNIQUE,
    product_id INT NULL DEFAULT NULL,
    item_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    description TEXT NULL,
    branch_id INT NOT NULL,
    reported_by INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_branch_id (branch_id),
    KEY idx_reported_by (reported_by),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$columnCheck = $conn->query("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
    WHERE table_schema = DATABASE() AND table_name = 'damage_items' AND column_name = 'product_id'");
if ($columnCheck) {
    $col = $columnCheck->fetch_assoc();
    if (intval($col['cnt']) === 0) {
        $conn->query("ALTER TABLE damage_items ADD COLUMN product_id INT NULL DEFAULT NULL AFTER damage_code");
    }
}

$cashier_id = intval($_SESSION['login_id'] ?? 0);
$cashier = $conn->query("SELECT u.name, u.branch_id, b.branch_name, b.branch_code
    FROM users u
    LEFT JOIN branches b ON b.id = u.branch_id
    WHERE u.id = $cashier_id")->fetch_assoc();

$branch_id = intval($cashier['branch_id'] ?? 0);
$branch_name = htmlspecialchars($cashier['branch_name'] ?? '');
$branch_code = htmlspecialchars($cashier['branch_code'] ?? '');
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_damage_item'])) {
        $item_name = trim($_POST['item_name'] ?? '');
        $quantity = floatval($_POST['quantity'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $product_id = null;

        if ($branch_id <= 0) {
            $message = 'Your cashier account has no branch assigned.';
            $message_type = 'warning';
        } elseif ($item_name === '' || $quantity <= 0) {
            $message = 'Please provide a valid item name and quantity.';
            $message_type = 'danger';
        } else {
            $damage_code = 'DMG-' . date('YmdHis') . '-' . rand(100, 999);
            $stmt = $conn->prepare("INSERT INTO damage_items (damage_code, product_id, item_name, quantity, description, branch_id, reported_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param('sisdiii', $damage_code, $product_id, $item_name, $quantity, $description, $branch_id, $cashier_id);
            $stmt->execute();
            $stmt->close();
            $message = 'Damage item saved successfully.';
            $message_type = 'success';
        }
    } elseif (isset($_POST['delete_damage_item'])) {
        $delete_id = intval($_POST['delete_damage_item'] ?? 0);
        if ($delete_id > 0) {
            $stmt = $conn->prepare("DELETE FROM damage_items WHERE id = ?");
            $stmt->bind_param('i', $delete_id);
            if ($stmt->execute()) {
                $message = 'Damage item deleted successfully.';
                $message_type = 'success';
            } else {
                $message = 'Failed to delete damage item.';
                $message_type = 'danger';
            }
            $stmt->close();
        }
    }
}

$stats = [
    'total' => 0,
    'pending' => 0,
    'resolved' => 0,
];

$stats_query = $conn->query("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved
    FROM damage_items");
if ($stats_query) {
    $stats = array_merge($stats, $stats_query->fetch_assoc() ?: []);
}

$items = [];
$query = $conn->query("SELECT d.*, b.branch_name, b.branch_code, u.name AS reporter_name, p.product_name AS linked_product_name
    FROM damage_items d
    LEFT JOIN branches b ON b.id = d.branch_id
    LEFT JOIN users u ON u.id = d.reported_by
    LEFT JOIN products p ON p.id = d.product_id
    ORDER BY d.created_at DESC");
if ($query) {
    while ($row = $query->fetch_assoc()) {
        $items[] = $row;
    }
}

// Fetch products for this branch to populate select dropdown
$damage_products = [];
if ($branch_id > 0) {
    $pqr = $conn->query("SELECT id, product_name FROM products WHERE status=1 AND branch_id = $branch_id ORDER BY product_name ASC");
    if ($pqr) {
        while ($pr = $pqr->fetch_assoc()) {
            $damage_products[] = $pr;
        }
    }
}
?>
<style>
    .di-card { border-top:3px solid #219688; border-radius:6px; background:#fff; }
    .di-stat { padding:16px; border-radius:16px; background:#fff; box-shadow:0 1px 4px rgba(57,75,124,.07); }
    .di-stat .v { font-size:24px; font-weight:800; color:#219688; line-height:1; }
    .di-stat .l { font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.4px; margin-top:4px; }
    .di-code { background:#e6f5f3; color:#176358; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:700; font-family:monospace; }
    .di-name { font-weight:700; }
    .di-sub { font-size:11px; color:#6b7280; }
    #damage-table thead th { background-color:#219688 !important; border-color:#176358 !important; color:#fff !important; }
    #damage-table tbody tr:hover td { background:#f0faf9; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <div>
                            <h4 class="mb-sm-0"><i class="ri-file-damage-line me-2" style="color:#219688;"></i>Manage Damage Items</h4>
                            <p class="text-muted mb-0 small mt-1"><i class="ri-information-line"></i> Track damaged products reported by the cashier.</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Cashier</a></li>
                                <li class="breadcrumb-item active">Damage Items</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($branch_id <= 0): ?>
                <div class="alert alert-warning">
                    <h5 class="alert-heading">Branch not assigned</h5>
                    <p>Your cashier account has no branch assigned. Please ask the administrator to assign a branch first.</p>
                </div>
            <?php else: ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><div class="di-stat"><div class="v"><?= intval($stats['total']) ?></div><div class="l">Total Reports</div></div></div>
                    <div class="col-md-4"><div class="di-stat"><div class="v" style="color:#f59e0b;"><?= intval($stats['pending']) ?></div><div class="l">Pending</div></div></div>
                    <div class="col-md-4"><div class="di-stat"><div class="v" style="color:#16a34a;"><?= intval($stats['resolved']) ?></div><div class="l">Resolved</div></div></div>
                </div>

                <div class="card di-card mb-3">
                    <div class="card-header align-items-center d-flex justify-content-between py-2">
                        <h4 class="card-title mb-0">
                            <i class="ri-tools-line me-2" style="color:#219688;"></i>Damage Items List
                            <span class="badge ms-1" style="background:#e6f5f3;color:#219688;font-size:11px;font-weight:700;vertical-align:middle;"><?= count($items) ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-damage">
                            <i class="ri-add-line me-1"></i>Add Damage Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="damage-table" class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Item</th>
                                        <th class="text-center" style="width:90px;">Qty</th>
                                        <th class="text-center" style="width:120px;">Status</th>
                                        <th>Branch</th>
                                        <th>Reporter</th>
                                        <th>Date</th>
                                        <th class="text-center" style="width:90px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($items) === 0): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">No damaged items recorded yet.</td></tr>
                                    <?php else: foreach ($items as $row): ?>
                                        <tr>
                                            <td><span class="di-code"><?= htmlspecialchars($row['damage_code']) ?></span></td>
                                            <td>
                                                <div class="di-name">
                                                    <?= htmlspecialchars($row['linked_product_name'] ?: $row['item_name']) ?>
                                                </div>
                                                <?php if (!empty($row['linked_product_name']) && $row['linked_product_name'] !== $row['item_name']): ?>
                                                    <div class="di-sub">Manual entry: <?= htmlspecialchars($row['item_name']) ?></div>
                                                <?php endif; ?>
                                                <div class="di-sub"><?= htmlspecialchars($row['description'] ?? '—') ?></div>
                                            </td>
                                            <td class="text-center"><?= number_format($row['quantity'], 2) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= in_array($row['status'], ['Approved', 'Resolved'], true) ? 'bg-success' : ($row['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(($row['branch_code'] ?? '—') . ' - ' . ($row['branch_name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($row['reporter_name'] ?? '—') ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if ($row['status'] === 'Pending'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success btn-approve-damage"
                                                            data-id="<?= intval($row['id']) ?>" title="Approve damage item">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <form method="post" onsubmit="return confirm('Delete this damage item?');" style="display:inline;">
                                                        <input type="hidden" name="delete_damage_item" value="<?= intval($row['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-damage" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header" style="border-bottom:2px solid #219688;">
                    <h5 class="modal-title" style="color:#219688;"><i class="ri-file-damage-line me-2"></i>Add Damage Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Item / Product</label>
                            <select name="item_name_select" id="damage-item-select" class="form-select">
                                <option value="">-- Select product or choose Other --</option>
                                <?php foreach ($damage_products as $dp): ?>
                                    <option value="<?= htmlspecialchars($dp['product_name']) ?>"><?= htmlspecialchars($dp['product_name']) ?></option>
                                <?php endforeach; ?>
                                <option value="__other__">Other (enter manual name)</option>
                            </select>
                            <input type="text" id="damage-item-manual" name="item_name_manual" class="form-control mt-2 d-none" placeholder="Enter item name when Other selected">
                            <input type="hidden" id="damage-item-final" name="item_name" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / Remarks</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Describe the damage or incident"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_damage_item" class="btn btn-success">
                        <i class="ri-save-line me-1"></i>Save Damage Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($branch_id > 0): ?>
<script>
    (function(){
        var select = document.getElementById('damage-item-select');
        var manual = document.getElementById('damage-item-manual');
        var finalInput = document.getElementById('damage-item-final');
        if (!select) return;
        select.addEventListener('change', function(){
            var v = this.value || '';
            if (v === '__other__') {
                manual.classList.remove('d-none');
                finalInput.value = '';
            } else {
                manual.classList.add('d-none');
                finalInput.value = v;
            }
        });
        if (manual) {
            manual.addEventListener('input', function(){
                finalInput.value = this.value;
            });
        }
        // On submit, if final is empty and manual visible, copy manual
        var form = select.closest('form');
        if (form) {
            form.addEventListener('submit', function(){
                if (finalInput.value.trim() === '' && manual && !manual.classList.contains('d-none')) {
                    finalInput.value = manual.value.trim();
                }
            });
        }
    })();
</script>
<?php endif; ?>

<?php if ($branch_id > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery) {
        return;
    }

    $(document).on('click', '.btn-approve-damage', function () {
        var button = $(this);
        var damageId = parseInt(button.data('id'), 10);

        if (!damageId || !confirm('Approve this damage item? Inventory will not be deducted again.')) {
            return;
        }

        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i>');

        $.ajax({
            url: 'ajax.php?action=mobile-pos-approve-damage',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ damage_id: damageId })
        }).done(function (response) {
            if (response && response.result) {
                location.reload();
                return;
            }
            alert(response && response.message ? response.message : 'Unable to approve damage item.');
            button.prop('disabled', false).html('<i class="ri-check-line"></i>');
        }).fail(function () {
            alert('Unable to approve damage item. Please try again.');
            button.prop('disabled', false).html('<i class="ri-check-line"></i>');
        });
    });

    if ($.fn.DataTable) {
        $('#damage-table').DataTable({ order: [[6, 'desc']], pageLength: 25 });
    }
});
</script>
<?php endif; ?>