<style>
    .cluster-name { font-weight:600; font-size:13px; color:#222; }
    .cluster-index { font-size:11px; color:#aaa; font-family:monospace; margin-right:6px; }
    .site-count-badge { display:inline-flex; align-items:center; gap:4px; background:#eef0f8; color:#009688; border:1px solid #d0d7ee; border-radius:12px; padding:2px 10px; font-size:12px; font-weight:700; }
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
                            <i class="ri-global-line me-2" style="color:#009688;"></i>Cluster
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Cluster</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #009688;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-global-line me-2" style="color:#009688;"></i>Cluster List
                            <?php
                            $cluster_count = $conn->query("SELECT COUNT(*) AS c FROM clusters")->fetch_assoc()['c'];
                            ?>
                            <span class="badge ms-1" style="background:#eef0f8;color:#009688;font-size:11px;font-weight:700;vertical-align:middle;"><?= $cluster_count ?></span>
                        </h4>
                        <?php if (in_array($login_role, $allowed_values_2)): ?>
                            <button type="button" class="btn btn-sm text-white" style="background:#009688;border-color:#009688;"
                                data-bs-toggle="modal" data-bs-target="#modal">
                                <i class="ri-add-circle-line me-1"></i>Create Cluster
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-global-line me-1"></i>Cluster</th>
                                        <th class="text-center" style="width:130px;"><i class="ri-map-pin-2-line me-1"></i>No. of Sites</th>
                                        <th class="text-center" style="width:100px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    $clusters = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM sites s WHERE s.cluster_id = c.id) AS site_count FROM clusters c ORDER BY cluster ASC");
                                    while ($row = $clusters->fetch_assoc()):
                                        $i++;
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="cluster-index">#<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></span>
                                                <span class="cluster-name"><?= htmlspecialchars($row['cluster']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="site-count-badge">
                                                    <i class="ri-map-pin-2-line"></i><?= $row['site_count'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    id="<?= $row['id'] ?>"
                                                    cluster="<?= htmlspecialchars($row['cluster']) ?>"
                                                    onclick="edit_function(this)"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Cluster">
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

<?php include 'component/add_cluster_form.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
</script>
