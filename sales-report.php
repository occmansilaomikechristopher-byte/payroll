<style>
    .sr-card { border-top:3px solid #219688; border-radius:4px; background:#fff; }
    .sr-stat { text-align:center; padding:16px; }
    .sr-stat .v { font-size:22px; font-weight:800; color:#219688; }
    .sr-stat .l { font-size:11px; color:#666; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
    .sr-inv { font-family:monospace; font-weight:700; color:#219688; font-size:12px; }
    .sr-branch { background:#e6f5f3; color:#176358; padding:1px 7px; border-radius:3px; font-size:11px; font-weight:700; }
    #sales-table thead th { background-color:#219688 !important; border-color:#176358 !important; color:#fff !important; }
    #sales-table tbody tr:hover td { background:#f0faf9; }
    .sr-filter { background:#219688; color:#fff; border-radius:4px; padding:10px 16px; margin-bottom:12px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .sr-filter input, .sr-filter select { border:none; border-radius:4px; padding:5px 9px; font-size:13px; }
</style>

<?php
$login_role = intval($_SESSION['login_role'] ?? 0);
$current_user_id = intval($_SESSION['login_id'] ?? 0);
$branch_id = intval($_GET['branch_id'] ?? 0);
$user_id = intval($_GET['user_id'] ?? 0);
if ($login_role === 9) {
    $user_id = $current_user_id;
}

// Date and branch filters
$from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : '';
$to   = isset($_GET['to'])   && $_GET['to']   !== '' ? $_GET['to']   : '';
$where = "1";
if ($branch_id > 0) { $where .= ' AND s.branch_id = ' . $branch_id; }
if ($from !== '') { $where .= " AND s.created_at >= '" . $conn->real_escape_string($from) . " 00:00:00'"; }
if ($to   !== '') { $where .= " AND s.created_at <= '" . $conn->real_escape_string($to)   . " 23:59:59'"; }

$branches = [];
$branchResult = $conn->query("SELECT id, branch_name, branch_code FROM branches WHERE status = 1 ORDER BY branch_name ASC");
if ($branchResult) {
    while ($branch = $branchResult->fetch_assoc()) {
        $branches[] = $branch;
    }
}

$users = [];
if ($branch_id > 0) {
    $userWhere = 'branch_id = ' . $branch_id . ' AND role = 9 AND status = 1';
    if ($login_role === 9) { $userWhere .= ' AND id = ' . $current_user_id; }
    $userResult = $conn->query("SELECT id, name FROM users WHERE $userWhere ORDER BY name ASC");
    if ($userResult) {
        while ($user = $userResult->fetch_assoc()) {
            $users[] = $user;
        }
    }

    $validUserIds = array_map('intval', array_column($users, 'id'));
    if ($user_id > 0 && !in_array($user_id, $validUserIds, true)) {
        $user_id = 0;
    }
}

if ($login_role === 9) {
    $where .= ' AND s.cashier_id = ' . $current_user_id;
} elseif ($branch_id > 0 && $user_id > 0) {
    $where .= ' AND s.cashier_id = ' . $user_id;
}

$sales = $conn->query("SELECT s.*, b.branch_name, b.branch_code, u.name AS cashier_name
                       FROM pos_sales s
                       LEFT JOIN branches b ON b.id = s.branch_id
                       LEFT JOIN users u ON u.id = s.cashier_id
                       WHERE $where
                       ORDER BY s.created_at DESC");

$tot_count = $tot_sales = $tot_discount = 0;
$rows = [];
if ($sales) {
    while ($r = $sales->fetch_assoc()) {
        $rows[] = $r;
        $tot_count++;
        $tot_sales    += floatval($r['total']);
        $tot_discount += floatval($r['discount']);
    }
}
?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-money-dollar-circle-line me-2" style="color:#219688;"></i>Sales Report</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Reports</a></li>
                                <li class="breadcrumb-item active">Sales</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="row">
                <div class="col-md-4"><div class="card sr-card"><div class="card-body sr-stat"><div class="v"><?= number_format($tot_count) ?></div><div class="l">Transactions</div></div></div></div>
                <div class="col-md-4"><div class="card sr-card"><div class="card-body sr-stat"><div class="v">&#8369; <?= number_format($tot_sales, 2) ?></div><div class="l">Total Sales</div></div></div></div>
                <div class="col-md-4"><div class="card sr-card"><div class="card-body sr-stat"><div class="v" style="color:#c62828;">&#8369; <?= number_format($tot_discount, 2) ?></div><div class="l">Total Discount</div></div></div></div>
            </div>

            <div class="card sr-card">
                <div class="card-body">
                    <!-- Branch and date filters -->
                    <form method="get" action="index.php" class="sr-filter">
                        <input type="hidden" name="page" value="sales-report">
                        <i class="ri-git-branch-line"></i>
                        <span style="font-size:12px;">Branch</span>
                        <select name="branch_id" onchange="this.form.elements.user_id.value = '0'; this.form.submit();">
                            <option value="0" <?= $branch_id === 0 ? 'selected' : '' ?>>All Branches</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= intval($branch['id']) ?>" <?= $branch_id === intval($branch['id']) ? 'selected' : '' ?>><?= htmlspecialchars($branch['branch_code'] . ' - ' . $branch['branch_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($branch_id > 0): ?>
                            <span style="font-size:12px;">User</span>
                            <select name="user_id" <?= $login_role === 9 ? 'disabled' : '' ?>>
                                <option value="0" <?= $user_id === 0 ? 'selected' : '' ?>>All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= intval($user['id']) ?>" <?= $user_id === intval($user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($login_role === 9): ?><input type="hidden" name="user_id" value="<?= $current_user_id ?>"><?php endif; ?>
                        <?php else: ?>
                            <input type="hidden" name="user_id" value="0">
                        <?php endif; ?>
                        <span style="font-size:12px;">From</span>
                        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
                        <span style="font-size:12px;">To</span>
                        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
                        <button type="submit" class="btn btn-sm btn-light"><i class="ri-search-line me-1"></i>Apply</button>
                        <?php if ($from || $to || $branch_id > 0): ?>
                            <a href="index.php?page=sales-report" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;"><i class="ri-close-line me-1"></i>Clear</a>
                        <?php endif; ?>
                    </form>

                    <div class="table-responsive">
                        <table id="sales-table" class="table table-hover table-bordered align-middle" data-has-sales="<?= $rows ? '1' : '0' ?>">
                            <thead>
                                <tr>
                                    <th><i class="ri-file-list-3-line me-1"></i>Invoice</th>
                                    <th><i class="ri-git-branch-line me-1"></i>Branch</th>
                                    <th><i class="ri-calendar-2-line me-1"></i>Date</th>
                                    <th>User</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center" style="width:80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($sales): foreach ($rows as $r): ?>
                                    <tr>
                                        <td><span class="sr-inv"><?= htmlspecialchars($r['invoice_no']) ?></span></td>
                                        <td><span class="sr-branch"><?= htmlspecialchars($r['branch_code'] ?? '—') ?></span> <?= htmlspecialchars($r['branch_name'] ?? '') ?></td>
                                        <td><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($r['cashier_name'] ?? '—') ?></td>
                                        <td class="text-end">&#8369; <?= number_format($r['subtotal'], 2) ?></td>
                                        <td class="text-end" style="color:#c62828;">&#8369; <?= number_format($r['discount'], 2) ?></td>
                                        <td class="text-end fw-bold">&#8369; <?= number_format($r['total'], 2) ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-sale" data-id="<?= $r['id'] ?>" onclick="openSaleDetails(this.getAttribute('data-id'))"><i class="ri-eye-line"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                <?php if ($sales && !$rows): ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">No data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if (!$sales): ?>
                            <div class="alert alert-warning"><i class="ri-error-warning-line me-1"></i>
                                Sales table not found. Run <code>SQL_POS_SALES.sql</code>.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sale Details Modal -->
<div class="modal fade" id="modal-sale-details" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:2px solid #219688;">
                <h5 class="modal-title" style="color:#219688;"><i class="ri-receipt-line me-2"></i><span id="sd-invoice">Sale Details</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between text-muted small mb-2">
                    <span id="sd-branch"></span>
                    <span id="sd-date"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead style="background:#e6f5f3;">
                            <tr>
                                <th>Product</th>
                                <th class="text-end" style="width:110px;">Price</th>
                                <th class="text-center" style="width:70px;">Qty</th>
                                <th class="text-end" style="width:120px;">Line Total</th>
                            </tr>
                        </thead>
                        <tbody id="sd-items"></tbody>
                    </table>
                </div>
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm">
                            <tr><td>Subtotal</td><td class="text-end" id="sd-subtotal"></td></tr>
                            <tr><td>Discount</td><td class="text-end text-danger" id="sd-discount"></td></tr>
                            <tr class="fw-bold" style="font-size:15px;"><td>Total</td><td class="text-end" id="sd-total" style="color:#219688;"></td></tr>
                            <tr><td>Cash Paid</td><td class="text-end" id="sd-payment"></td></tr>
                            <tr><td>Change</td><td class="text-end text-success" id="sd-change"></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var peso = function (n) { return '₱ ' + parseFloat(n || 0).toFixed(2); };

function openSaleDetails(id) {
    id = Number(id);
    if (!id) {
        $('#sd-items').html('<tr><td colspan="4" class="text-danger text-center">Invalid sale ID.</td></tr>');
        $('#modal-sale-details').modal('show');
        return;
    }

    $('#sd-items').html('<tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>');

    $.ajax({
        url: 'ajax.php?action=get_pos_sale_details',
        method: 'POST',
        data: { sale_id: id },
        dataType: 'json',
        success: function (res) {
            if (!res || !res.result) {
                $('#sd-items').html('<tr><td colspan="4" class="text-danger text-center">' + (res && res.message ? res.message : 'Failed to load sale.') + '</td></tr>');
                $('#modal-sale-details').modal('show');
                return;
            }

            var s = res.sale || {};
            $('#sd-invoice').text(s.invoice_no || 'Sale Details');
            $('#sd-branch').html('<i class="ri-git-branch-line me-1"></i>' + (s.branch_name || '—') + (s.cashier_name ? ' &middot; Cashier: ' + s.cashier_name : ''));
            $('#sd-date').text(s.created_at ? new Date(s.created_at.replace(' ', 'T')).toLocaleString() : '');
            $('#sd-subtotal').text(peso(s.subtotal));
            $('#sd-discount').text('- ' + peso(s.discount));
            $('#sd-total').text(peso(s.total));
            $('#sd-payment').text(peso(s.payment));
            $('#sd-change').text(peso(s.change_due));

            var html = '';
            (res.items || []).forEach(function (it) {
                html += '<tr><td>' + (it.product_name || 'Product') + '</td><td class="text-end">' + peso(it.price) +
                        '</td><td class="text-center">' + parseFloat(it.qty || 0) + '</td><td class="text-end fw-semibold">' + peso(it.line_total) + '</td></tr>';
            });
            $('#sd-items').html(html || '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>');
            $('#modal-sale-details').modal('show');
        },
        error: function () {
            $('#sd-items').html('<tr><td colspan="4" class="text-danger text-center">Failed to load sale details.</td></tr>');
            $('#modal-sale-details').modal('show');
        }
    });
}

$(function () {
    if ($.fn.DataTable && $('#sales-table').attr('data-has-sales') === '1') {
        $('#sales-table').DataTable({ order: [[2, 'desc']], pageLength: 25 });
    }

    $('#sales-table').on('click', '.view-sale', function () {
        openSaleDetails($(this).data('id'));
    });
});
</script>
