<?php
$filter_query = '';
?>
<style>
    .dtr-period { font-weight:700; color: #0d6efd; font-size:13px; font-family:'Segoe UI',monospace; }
    .dtr-period small { display:block; font-size:10px; color: #888; font-weight:400; font-family:inherit; margin-top:1px; }
    .dtr-site-code { background: #0d6efd; color:#fff; padding:2px 7px; border-radius:3px; font-size:11px; font-weight:700; display:inline-block; margin-bottom:3px; }
    .dtr-site-name { font-size:12px; font-weight:600; color:#222; }
    .dtr-site-addr { font-size:11px; color: #888; }
    .dtr-user { font-size:13px; font-weight:600; }
    .dtr-user small { font-size:10px; color: #888; font-weight:400; display:block; }
    .dtr-action { display:flex; gap:4px; justify-content:center; }
    #data-table1 thead th { background-color: #009688 !important; border-color: #038479 !important; color: #fff !important; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-time-line me-2" style="color: #009688;"></i>Daily Time Record</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Daily Time Record</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #0d6efd;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-time-line me-2" style="color: #0d6efd;"></i>DTR List
                        </h4>
                        <button class="btn btn-sm btn-outline-primary" id="refresh-dtr-btn" title="Manually refresh tables">
                            <i class="ri-refresh-line me-1"></i>Refresh
                        </button>
                    </div>

                    <div class="card-body">
                        <ul class="nav nav-pills arrow-navtabs nav-success bg-light mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#arrow-new" role="tab">
                                    <i class="ri-file-add-line me-1"></i><span class="d-none d-sm-inline">New</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#arrow-approved" role="tab">
                                    <i class="ri-checkbox-circle-line me-1"></i><span class="d-none d-sm-inline">Approved</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content text-muted">

                            <!-- NEW TAB -->
                            <div class="tab-pane active" id="arrow-new" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="data-table1" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th><i class="ri-calendar-range-line me-1"></i>Period</th>
                                                <th><i class="ri-map-pin-2-line me-1"></i>Branch</th>
                                                <th><i class="ri-upload-2-line me-1"></i>Uploaded By</th>
                                                <th><i class="ri-user-3-line me-1"></i>Cashier</th>
                                                <th class="text-center" style="width:120px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $login_role = intval($_SESSION['login_role'] ?? 0);
                                            $branch_filter = intval($_SESSION['login_branch_id'] ?? 0);
                                            $where = "WHERE DTR.status = 1";
                                            if (!in_array($login_role, [1, 10], true) && $branch_filter > 0) {
                                                $where .= " AND DTR.branch_id = " . $branch_filter;
                                            }
                                            $query = $conn->query("SELECT DTR.*, 
                                                branches.branch_code, branches.branch_name, 
                                                timekeeper.name AS timekeeper_name, 
                                                uploaded.name AS uploaded_by_name 
                                                FROM DTR 
                                                LEFT JOIN branches ON DTR.branch_id = branches.id 
                                                LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id 
                                                LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id 
                                                $where 
                                                ORDER BY DTR.id DESC");
                                            if (!$query) { echo '<tr><td colspan="6">Query error: ' . htmlspecialchars($conn->error) . '</td></tr>'; } else
                                            while ($row = $query->fetch_assoc()):
                                                $period = date("M d", strtotime($row['date_from'])) . ' &ndash; ' . date("M j, Y", strtotime($row['date_to']));
                                                $viewUrl = "index.php?page=dtr-details&id=" . base64_encode($row['id'])
                                                    . "&timekeeper_name=" . base64_encode($row['timekeeper_name'])
                                                    . "&device_id=" . base64_encode($row['device_id'])
                                                    . "&branch_id=" . base64_encode($row['branch_id'])
                                                    . "&status=" . base64_encode($row['status']);
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="dtr-period">
                                                            <i class="ri-calendar-2-line me-1 text-muted"></i><?= $period ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="dtr-site-code"><?= htmlspecialchars($row['branch_code'] ?? '') ?></span>
                                                        <div class="dtr-site-name"><?= htmlspecialchars($row['branch_name'] ?? '—') ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="dtr-user">
                                                            <i class="ri-user-3-line me-1 text-muted"></i><?= htmlspecialchars($row['uploaded_by_name'] ?? '') ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="dtr-user">
                                                            <i class="ri-user-settings-line me-1 text-muted"></i><?= htmlspecialchars($row['timekeeper_name']) ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="dtr-action">
                                                            <a href="<?= $viewUrl ?>" class="btn btn-sm btn-outline-success"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" title="View DTR Details">
                                                                <i class="ri-eye-line me-1"></i>View
                                                            </a>
                                                            <button onclick="deleteDTR(<?= $row['id'] ?>)" type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Delete DTR">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- APPROVED TAB -->
                            <div class="tab-pane" id="arrow-approved" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th><i class="ri-calendar-range-line me-1"></i>Period</th>
                                                <th><i class="ri-map-pin-2-line me-1"></i>Branch</th>
                                                <th><i class="ri-upload-2-line me-1"></i>Uploaded By</th>
                                                <th><i class="ri-user-3-line me-1"></i>Cashier</th>
                                                <th><i class="ri-shield-check-line me-1"></i>Approved By</th>
                                                <th class="text-center" style="width:90px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'component/drt_form.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
</script>
