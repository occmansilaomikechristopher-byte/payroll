<?php
$site_ids = [];
if ($login_role === 6) {
    $user_id = $_SESSION["login_id"];
    $query = $conn->query("SELECT A.* FROM sites AS A WHERE pic = $user_id ORDER BY A.site_name ASC");
    while ($row = $query->fetch_assoc()) {
        array_push($site_ids, $row['id']);
    }
    $commaSeparatedSites = implode(',', $site_ids);
    $filter_query = "AND sites.id IN ($commaSeparatedSites) ";
} else {
    $filter_query = '';
}
?>
<style>
    .dtr-period { font-weight:700; color:#394b7c; font-size:13px; font-family:'Segoe UI',monospace; }
    .dtr-period small { display:block; font-size:10px; color:#888; font-weight:400; font-family:inherit; margin-top:1px; }
    .dtr-site-code { background:#394b7c; color:#fff; padding:2px 7px; border-radius:3px; font-size:11px; font-weight:700; display:inline-block; margin-bottom:3px; }
    .dtr-site-name { font-size:12px; font-weight:600; color:#222; }
    .dtr-site-addr { font-size:11px; color:#888; }
    .dtr-user { font-size:13px; font-weight:600; }
    .dtr-user small { font-size:10px; color:#888; font-weight:400; display:block; }
    .dtr-action { display:flex; gap:4px; justify-content:center; }
    #data-table thead th,
    #data-table1 thead th { background-color:#394b7c !important; border-color:#2d3d66 !important; color:#fff !important; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-time-line me-2" style="color:#394b7c;"></i>Daily Time Record</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Daily Time Record</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #394b7c;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-time-line me-2" style="color:#394b7c;"></i>DTR List
                        </h4>
                        <button type="button" class="btn btn-sm text-white" style="background:#394b7c;border-color:#394b7c;" onclick="uploadFile()">
                            <i class="ri-upload-2-line me-1"></i>Upload File
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
                                        <thead>
                                            <tr>
                                                <th><i class="ri-calendar-range-line me-1"></i>Period</th>
                                                <th><i class="ri-building-2-line me-1"></i>Employer</th>
                                                <th><i class="ri-map-pin-2-line me-1"></i>Site</th>
                                                <th><i class="ri-upload-2-line me-1"></i>Uploaded By</th>
                                                <th><i class="ri-user-3-line me-1"></i>Timekeeper</th>
                                                <th class="text-center" style="width:120px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = $conn->query("SELECT DTR.*, sites.site_code, sites.site_name, sites.site_address,
                                                timekeeper.name AS timekeeper_name, uploaded.name AS uploaded_by, employer_name
                                                FROM DTR
                                                LEFT JOIN sites ON DTR.site_id = sites.id
                                                LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id
                                                LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id
                                                LEFT JOIN employers ON sites.employer_id = employers.id
                                                WHERE DTR.status = 1
                                                $filter_query
                                                ORDER BY DTR.id DESC");
                                            while ($row = $query->fetch_assoc()):
                                                $period = date("M d", strtotime($row['date_from'])) . ' &ndash; ' . date("M j, Y", strtotime($row['date_to']));
                                                $viewUrl = "index.php?page=dtr-details&id=" . base64_encode($row['id'])
                                                    . "&timekeeper_name=" . base64_encode($row['timekeeper_name'])
                                                    . "&device_id=" . base64_encode($row['device_id'])
                                                    . "&site_id=" . base64_encode($row['site_id'])
                                                    . "&status=" . base64_encode($row['status']);
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="dtr-period">
                                                            <i class="ri-calendar-2-line me-1 text-muted"></i><?= $period ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="dtr-user"><?= htmlspecialchars($row['employer_name']) ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="dtr-site-code"><?= htmlspecialchars($row['site_code']) ?></span>
                                                        <div class="dtr-site-name"><?= htmlspecialchars($row['site_name']) ?></div>
                                                        <div class="dtr-site-addr"><?= htmlspecialchars($row['site_address']) ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="dtr-user">
                                                            <i class="ri-user-3-line me-1 text-muted"></i><?= htmlspecialchars($row['uploaded_by']) ?>
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
                                                <th><i class="ri-building-2-line me-1"></i>Employer</th>
                                                <th><i class="ri-map-pin-2-line me-1"></i>Site</th>
                                                <th><i class="ri-upload-2-line me-1"></i>Uploaded By</th>
                                                <th><i class="ri-user-3-line me-1"></i>Timekeeper</th>
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
