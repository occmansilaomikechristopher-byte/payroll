<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['login_role']) || $_SESSION['login_role'] !== 9) {
    header('location: home');
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS owner_requisitions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    requisition_code VARCHAR(60) NOT NULL UNIQUE,
    item_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    branch_id INT NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_branch_id (branch_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_owner_requisition'])) {
    $item_name = trim($_POST['item_name'] ?? '');
    $quantity = floatval($_POST['quantity'] ?? 0);
    $branch_id = intval($_POST['branch_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($item_name === '' || $quantity <= 0) {
        $message = 'Please provide a valid item name and quantity.';
        $message_type = 'danger';
    } else {
        $requisition_code = 'REQ-' . date('YmdHis') . '-' . rand(100, 999);
        $stmt = $conn->prepare("INSERT INTO owner_requisitions (requisition_code, item_name, quantity, branch_id, description, status)
            VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param('ssdss', $requisition_code, $item_name, $quantity, $branch_id, $description);
        $stmt->execute();
        $stmt->close();
        $message = 'Owner requisition saved successfully.';
        $message_type = 'success';
    }
}

$branches = [];
$branch_query = $conn->query("SELECT id, branch_code, branch_name FROM branches WHERE status=1 ORDER BY branch_name ASC");
if ($branch_query) {
    while ($row = $branch_query->fetch_assoc()) {
        $branches[] = $row;
    }
}

$items = [];
$query = $conn->query("SELECT r.*, b.branch_name, b.branch_code
    FROM owner_requisitions r
    LEFT JOIN branches b ON b.id = r.branch_id
    ORDER BY r.created_at DESC");
if ($query) {
    while ($row = $query->fetch_assoc()) {
        $items[] = $row;
    }
}

$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
];
$stats_query = $conn->query("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected
    FROM owner_requisitions");
if ($stats_query) {
    $stats = array_merge($stats, $stats_query->fetch_assoc() ?: []);
}
?>
<style>
    .or-card { border-top:3px solid #219688; border-radius:6px; background:#fff; }
    .or-stat { padding:16px; border-radius:16px; background:#fff; box-shadow:0 1px 4px rgba(57,75,124,.07); }
    .or-stat .v { font-size:24px; font-weight:800; color:#219688; line-height:1; }
    .or-stat .l { font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.4px; margin-top:4px; }
    .or-code { background:#e6f5f3; color:#176358; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:700; font-family:monospace; }
    .or-name { font-weight:700; }
    .or-desc { font-size:11px; color:#6b7280; }
    #owner-table thead th { background-color:#219688 !important; border-color:#176358 !important; color:#fff !important; }
    #owner-table tbody tr:hover td { background:#f0faf9; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <div>
                            <h4 class="mb-sm-0"><i class="ri-file-list-3-line me-2" style="color:#219688;"></i>Owner Requisition</h4>
                            <p class="text-muted mb-0 small mt-1"><i class="ri-information-line"></i> Manage owner requisition requests and approvals.</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Cashier</a></li>
                                <li class="breadcrumb-item active">Owner Requisition</li>
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

            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="or-stat"><div class="v"><?= intval($stats['total']) ?></div><div class="l">Total Requests</div></div></div>
                <div class="col-md-3"><div class="or-stat"><div class="v" style="color:#f59e0b;"><?= intval($stats['pending']) ?></div><div class="l">Pending</div></div></div>
                <div class="col-md-3"><div class="or-stat"><div class="v" style="color:#16a34a;"><?= intval($stats['approved']) ?></div><div class="l">Approved</div></div></div>
                <div class="col-md-3"><div class="or-stat"><div class="v" style="color:#dc2626;"><?= intval($stats['rejected']) ?></div><div class="l">Rejected</div></div></div>
            </div>

            <div class="card or-card mb-3">
                <div class="card-header align-items-center d-flex justify-content-between py-2">
                    <h4 class="card-title mb-0">
                        <i class="ri-file-list-3-line me-2" style="color:#219688;"></i>Requisition List
                        <span class="badge ms-1" style="background:#e6f5f3;color:#219688;font-size:11px;font-weight:700;vertical-align:middle;"><?= count($items) ?></span>
                    </h4>
                    <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-owner-requisition">
                        <i class="ri-add-line me-1"></i>Add Requisition
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="owner-table" class="table table-hover table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Item</th>
                                    <th class="text-center" style="width:90px;">Qty</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($items) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No owner requisitions recorded yet.</td></tr>
                                <?php else: foreach ($items as $row): ?>
                                    <tr>
                                        <td><span class="or-code"><?= htmlspecialchars($row['requisition_code']) ?></span></td>
                                        <td>
                                            <div class="or-name"><?= htmlspecialchars($row['item_name']) ?></div>
                                            <div class="or-desc"><?= htmlspecialchars($row['description'] ?? '—') ?></div>
                                        </td>
                                        <td class="text-center"><?= number_format($row['quantity'], 2) ?></td>
                                        <td><?= htmlspecialchars(($row['branch_code'] ?? '—') . ' - ' . ($row['branch_name'] ?? 'All branches')) ?></td>
                                        <td>
                                            <span class="badge <?= $row['status'] === 'Approved' ? 'bg-success' : ($row['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-owner-requisition" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header" style="border-bottom:2px solid #219688;">
                    <h5 class="modal-title" style="color:#219688;"><i class="ri-file-list-3-line me-2"></i>Add Owner Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Item / Product</label>
                            <select name="item_name" id="req-item-select" class="form-select">
                                <option value="">-- Select product or choose Other --</option>
                                <?php
                                $prod_q = $conn->query("SELECT id, product_name FROM products WHERE status=1 ORDER BY product_name ASC");
                                while ($pr = $prod_q->fetch_assoc()):
                                ?>
                                    <option value="<?= htmlspecialchars($pr['product_name']) ?>"><?= htmlspecialchars($pr['product_name']) ?></option>
                                <?php endwhile; ?>
                                    <option value="__other__">Other (enter manual name)</option>
                            </select>
                            <input type="text" id="req-item-manual" name="item_name_manual" class="form-control mt-2 d-none" placeholder="Enter item name when Other selected">
                            <input type="hidden" id="req-item-final" name="item_name" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="1" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="0">All branches</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= intval($branch['id']) ?>"><?= htmlspecialchars($branch['branch_code'] . ' - ' . $branch['branch_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / Remarks</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Describe the requisition"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_owner_requisition" class="btn btn-success">
                        <i class="ri-save-line me-1"></i>Save Requisition
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function(){
        var select = document.getElementById('req-item-select');
        var manual = document.getElementById('req-item-manual');
        var finalInput = document.getElementById('req-item-final');
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

<script>
if (window.jQuery && $.fn.DataTable) {
    $('#owner-table').DataTable({ order: [[5, 'desc']], pageLength: 25 });
}
</script>