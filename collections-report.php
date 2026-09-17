<?php
$check = $conn->query("SHOW COLUMNS FROM pos_sales LIKE 'collection_status'");
if ($check && $check->num_rows === 0) {
    $conn->query("ALTER TABLE pos_sales ADD COLUMN collection_status VARCHAR(20) NOT NULL DEFAULT 'Pending' AFTER change_due");
}

$branch_id = intval($_GET['branch_id'] ?? 0);
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$where = '1';
if ($branch_id > 0) $where .= ' AND s.branch_id = ' . $branch_id;
if ($from !== '') $where .= " AND s.created_at >= '" . $conn->real_escape_string($from) . " 00:00:00'";
if ($to !== '') $where .= " AND s.created_at <= '" . $conn->real_escape_string($to) . " 23:59:59'";

$branches = [];
$branchResult = $conn->query("SELECT id, branch_name, branch_code FROM branches WHERE status = 1 ORDER BY branch_name ASC");
if ($branchResult) while ($b = $branchResult->fetch_assoc()) $branches[] = $b;

$rows = [];
$result = $conn->query("SELECT s.*, b.branch_name, b.branch_code FROM pos_sales s LEFT JOIN branches b ON b.id = s.branch_id WHERE $where ORDER BY s.created_at DESC");
if ($result) while ($row = $result->fetch_assoc()) $rows[] = $row;

$approved = $pending = 0;
foreach ($rows as $row) {
    if (($row['collection_status'] ?? 'Pending') === 'Approved') $approved += floatval($row['payment']);
    else $pending += floatval($row['payment']);
}
?>
<style>
    .cr-card { border-top:3px solid #219688; border-radius:4px; background:#fff; }
    .cr-stat { text-align:center; padding:16px; }
    .cr-stat .v { font-size:22px; font-weight:800; color:#219688; }
    .cr-stat .l { font-size:11px; color:#666; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
    #collections-table thead th { background:#219688 !important; color:#fff !important; border-color:#176358 !important; }
    #collections-table tbody tr:hover td { background:#f0faf9; }
    .cr-filter { background:#219688; color:#fff; border-radius:4px; padding:10px 16px; margin-bottom:12px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .cr-filter input, .cr-filter select { border:0; border-radius:4px; padding:5px 9px; font-size:13px; }
</style>
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="row"><div class="col-12"><div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
        <h4 class="mb-sm-0"><i class="ri-cash-line me-2" style="color:#219688;"></i>Collections Report</h4>
        <div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item">Reports</li><li class="breadcrumb-item active">Collections</li></ol></div>
    </div></div></div>
    <div class="row">
        <div class="col-md-4"><div class="card cr-card"><div class="card-body cr-stat"><div class="v">₱ <?= number_format($approved, 2) ?></div><div class="l">Approved Collections</div></div></div></div>
        <div class="col-md-4"><div class="card cr-card"><div class="card-body cr-stat"><div class="v" style="color:#d97706;">₱ <?= number_format($pending, 2) ?></div><div class="l">Pending Collections</div></div></div></div>
        <div class="col-md-4"><div class="card cr-card"><div class="card-body cr-stat"><div class="v"><?= number_format(count($rows)) ?></div><div class="l">Sales Records</div></div></div></div>
    </div>
    <div class="card cr-card"><div class="card-body">
        <form method="get" action="index.php" class="cr-filter">
            <input type="hidden" name="page" value="collections-report">
            <span>Branch</span><select name="branch_id"><option value="0">All Branches</option><?php foreach ($branches as $b): ?><option value="<?= intval($b['id']) ?>" <?= $branch_id === intval($b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['branch_code'] . ' - ' . $b['branch_name']) ?></option><?php endforeach; ?></select>
            <span>From</span><input type="date" name="from" value="<?= htmlspecialchars($from) ?>"><span>To</span><input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
            <button type="submit" class="btn btn-sm btn-light"><i class="ri-search-line me-1"></i>Apply</button>
        </form>
        <div class="table-responsive"><table id="collections-table" class="table table-hover table-bordered align-middle"><thead><tr><th>Invoice</th><th>Branch</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Collected</th><th>Status</th><th class="text-center">Action</th></tr></thead><tbody>
        <?php foreach ($rows as $row): $isApproved = ($row['collection_status'] ?? 'Pending') === 'Approved'; ?>
            <tr><td><?= htmlspecialchars($row['invoice_no']) ?></td><td><?= htmlspecialchars(($row['branch_code'] ?? '') . ' ' . ($row['branch_name'] ?? '')) ?></td><td><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></td><td class="text-end">₱ <?= number_format($row['total'], 2) ?></td><td class="text-end">₱ <?= number_format($row['payment'], 2) ?></td><td><span class="badge <?= $isApproved ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $isApproved ? 'Approved' : 'Pending' ?></span></td><td class="text-center"><?php if (!$isApproved): ?><button type="button" class="btn btn-sm btn-primary approve-collection" data-id="<?= intval($row['id']) ?>" data-branch="<?= intval($row['branch_id']) ?>">Approve</button><?php else: ?><span class="text-success"><i class="ri-check-line"></i> Approved</span><?php endif; ?></td></tr>
        <?php endforeach; ?>
        </tbody></table><?php if (!$rows): ?><div class="alert alert-info">No collection records found.</div><?php endif; ?></div>
    </div></div>
</div></div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var table = document.getElementById('collections-table');
    if (!table) return;

    // DataTables is optional. Approval must not depend on jQuery being loaded.
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        window.jQuery(table).DataTable({order: [[2, 'desc']], pageLength: 25});
    }

    table.addEventListener('click', function (event) {
        var button = event.target.closest('.approve-collection');
        if (!button) return;

        event.preventDefault();
        var oldText = button.textContent;
        var saleId = button.getAttribute('data-id');
        var branchId = button.getAttribute('data-branch');
        var formData = new URLSearchParams({sale_id: saleId, branch_id: branchId});
        button.disabled = true;
        button.textContent = 'Approving...';

        fetch(new URL('ajax.php?action=mobile-pos-approve-collection', window.location.href), {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: formData.toString()
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.json();
            })
            .then(function (result) {
                if (!result.result) throw new Error(result.message || 'Unable to approve collection.');
                window.location.reload();
            })
            .catch(function (error) {
                alert(error.message || 'Unable to approve collection.');
                button.disabled = false;
                button.textContent = oldText;
            });
    });
});
</script>
