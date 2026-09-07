<!-- <?php
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

$id = intval($_GET['id'] ?? 0);
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_damage_status']) && $id > 0) {
    $status = trim($_POST['status'] ?? 'Pending');
    $allowed = ['Pending', 'Resolved', 'Rejected'];
    if (!in_array($status, $allowed, true)) {
        $message = 'Invalid status selected.';
        $message_type = 'danger';
    } else {
        $stmt = $conn->prepare("UPDATE damage_items SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();
        $message = 'Damage item status updated.';
        $message_type = 'success';
    }
}

$item = null;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT d.*, b.branch_name, b.branch_code, u.name AS reporter_name, p.product_name AS linked_product_name
        FROM damage_items d
        LEFT JOIN branches b ON b.id = d.branch_id
        LEFT JOIN users u ON u.id = d.reported_by
        LEFT JOIN products p ON p.id = d.product_id
        WHERE d.id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<style>
    .dd-card { border-top:3px solid #219688; border-radius:6px; background:#fff; }
    .dd-label { font-size:11px; color:#64748b; text-transform:uppercase; letter-spacing:.4px; }
    .dd-value { font-size:15px; font-weight:700; color:#0f172a; }
    .dd-code { background:#e6f5f3; color:#176358; padding:3px 8px; border-radius:3px; font-size:12px; font-weight:700; font-family:monospace; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <div>
                            <h4 class="mb-sm-0"><i class="ri-file-list-3-line me-2" style="color:#219688;"></i>Damage Items Details</h4>
                            <p class="text-muted mb-0 small mt-1"><i class="ri-information-line"></i> Review the selected damage item record.</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Cashier</a></li>
                                <li class="breadcrumb-item"><a href="damage-items">Damage Items</a></li>
                                <li class="breadcrumb-item active">Details</li>
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

            <?php if ($id <= 0 || !$item): ?>
                <div class="alert alert-warning">
                    <h5 class="alert-heading">No damage item selected</h5>
                    <p>Please go back to the damage items list and choose a record to view.</p>
                    <a href="damage-items" class="btn btn-sm btn-success"><i class="ri-arrow-left-line me-1"></i>Back to Damage Items</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="card dd-card h-100">
                            <div class="card-header d-flex align-items-center justify-content-between py-2">
                                <div>
                                    <div class="dd-label">Damage Code</div>
                                    <div class="dd-code"><?= htmlspecialchars($item['damage_code']) ?></div>
                                </div>
                                <span class="badge <?= $item['status'] === 'Resolved' ? 'bg-success' : ($item['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                    <?= htmlspecialchars($item['status']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6"><div class="dd-label">Item Name</div><div class="dd-value"><?= htmlspecialchars($item['linked_product_name'] ?: $item['item_name']) ?></div></div>
                                    <?php if (!empty($item['linked_product_name']) && $item['linked_product_name'] !== $item['item_name']): ?>
                                        <div class="col-md-6"><div class="dd-label">Manual Item Name</div><div class="dd-value"><?= htmlspecialchars($item['item_name']) ?></div></div>
                                    <?php endif; ?>
                                    <div class="col-md-6"><div class="dd-label">Quantity</div><div class="dd-value"><?= number_format($item['quantity'], 2) ?></div></div>
                                    <div class="col-md-6"><div class="dd-label">Branch</div><div class="dd-value"><?= htmlspecialchars(($item['branch_code'] ?? '—') . ' - ' . ($item['branch_name'] ?? '')) ?></div></div>
                                    <div class="col-md-6"><div class="dd-label">Reported By</div><div class="dd-value"><?= htmlspecialchars($item['reporter_name'] ?? '—') ?></div></div>
                                    <div class="col-12"><div class="dd-label">Description / Remarks</div><div class="dd-value" style="font-weight:500;line-height:1.7;"><?= nl2br(htmlspecialchars($item['description'] ?? '—')) ?></div></div>
                                    <div class="col-md-6"><div class="dd-label">Created At</div><div class="dd-value"><?= date('M j, Y g:i A', strtotime($item['created_at'])) ?></div></div>
                                    <div class="col-md-6"><div class="dd-label">Last Updated</div><div class="dd-value"><?= date('M j, Y g:i A', strtotime($item['updated_at'])) ?></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card dd-card mb-3">
                            <div class="card-header py-2"><strong>Update Status</strong></div>
                            <div class="card-body">
                                <form method="post">
                                    <label class="form-label">Current Status</label>
                                    <select name="status" class="form-select mb-3">
                                        <option value="Pending" <?= $item['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Resolved" <?= $item['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="Rejected" <?= $item['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                    <button type="submit" name="update_damage_status" class="btn btn-success w-100">
                                        <i class="ri-refresh-line me-1"></i>Update Status
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card dd-card">
                            <div class="card-header py-2"><strong>Quick Actions</strong></div>
                            <div class="card-body d-grid gap-2">
                                <a href="damage-items" class="btn btn-outline-primary"><i class="ri-arrow-left-line me-1"></i>Back to List</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> -->