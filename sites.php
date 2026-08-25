<style>
    .site-code-badge { background:#009688; color:#fff; padding:2px 7px; border-radius:3px; font-size:11px; font-weight:700; display:inline-block; margin-bottom:3px; font-family:monospace; }
    .site-name { font-size:13px; font-weight:600; color:#222; }
    .site-addr { font-size:11px; color:#888; }
    .site-user { font-size:13px; font-weight:600; }
    .site-cluster { font-size:12px; font-weight:600; color:#009688; }
    #data-table thead th { background-color:#009688 !important; border-color:#2d3d66 !important; color:#fff !important; }
    #data-table tbody tr:hover td { background:#f4f5fb; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-map-pin-2-line me-2" style="color:#009688;"></i>Sites
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Sites</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #009688;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-map-pin-2-line me-2" style="color:#009688;"></i>Sites List
                            <?php
                            $site_count = $conn->query("SELECT COUNT(*) AS c FROM sites")->fetch_assoc()['c'];
                            ?>
                            <span class="badge ms-1" style="background:#eef0f8;color:#009688;font-size:11px;font-weight:700;vertical-align:middle;"><?= $site_count ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm text-white" style="background:#009688;border-color:#009688;"
                            data-bs-toggle="modal" data-bs-target="#modal">
                            <i class="ri-add-circle-line me-1"></i>Create Site
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-map-pin-2-line me-1"></i>Site</th>
                                        <th><i class="ri-building-2-line me-1"></i>Employer</th>
                                        <th><i class="ri-user-settings-line me-1"></i>Cashier</th>
                                        <th><i class="ri-user-3-line me-1"></i>PIC</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-pulse-line me-1"></i>Status</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $conn->query("SELECT A.*, C.name AS cashier, P.name AS pic, P.id AS pic_id, C.id AS cashier_id, D.employer_name AS employer
                                        FROM sites AS A
                                        LEFT JOIN users AS C ON A.timekeeper_id = C.id
                                        LEFT JOIN users AS P ON A.pic = P.id
                                        LEFT JOIN employers AS D ON A.employer_id = D.id
                                        ORDER BY A.site_name ASC");
                                    while ($row = $query->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="site-code-badge"><?= htmlspecialchars($row['site_code']) ?></span>
                                                <div class="site-name"><?= htmlspecialchars($row['site_name']) ?></div>
                                                <div class="site-addr"><i class="ri-map-pin-line me-1"></i><?= htmlspecialchars($row['site_address']) ?></div>
                                            </td>
                                            <td>
                                                <span class="site-user"><i class="ri-building-2-line me-1 text-muted"></i><?= htmlspecialchars($row['employer']) ?></span>
                                            </td>
                                            <td>
                                                <span class="site-user"><i class="ri-user-settings-line me-1 text-muted"></i><?= htmlspecialchars($row['cashier'] ?? '—') ?></span>
                                            </td>
                                            <td>
                                                <span class="site-user"><i class="ri-user-3-line me-1 text-muted"></i><?= htmlspecialchars($row['pic'] ?? '—') ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($row['status'] == 1): ?>
                                                    <span class="badge rounded-pill bg-success"><i class="ri-checkbox-circle-line me-1"></i>Active</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-danger"><i class="ri-close-circle-line me-1"></i>Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    id="<?= $row['id'] ?>"
                                                    employer_id="<?= $row['employer_id'] ?>"
                                                    site_code="<?= htmlspecialchars($row['site_code']) ?>"
                                                    site_name="<?= htmlspecialchars($row['site_name']) ?>"
                                                    site_address="<?= htmlspecialchars($row['site_address']) ?>"
                                                    cashier_id="<?= htmlspecialchars($row['cashier_id']) ?>"
                                                    pic_id="<?= htmlspecialchars($row['pic_id']) ?>"
                                                    status="<?= htmlspecialchars($row['status']) ?>"
                                                    onclick="edit_function(this)"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Site">
                                                    <i class="ri-edit-line me-1"></i>Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'component/add_site_form.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
</script>
