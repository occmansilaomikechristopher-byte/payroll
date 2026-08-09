<style>
    .brn-code { background: #219688; color:#fff; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:700; display:inline-block; font-family:monospace; }
    .brn-name { font-weight:600; font-size:13px; }
    .brn-city { font-size:12px; color: #666; }
    .brn-contact { font-size:11px; color: #888; }
    #data-table thead th { background-color: #219688 !important; border-color:#176358 !important; color:#fff !important; }
    #data-table tbody tr:hover td { background:#f0faf9; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-git-branch-line me-2" style="color:#219688;"></i>Branches
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">POS System</a></li>
                                <li class="breadcrumb-item active">Branches</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #219688;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-git-branch-line me-2" style="color:#219688;"></i>Branches List
                            <?php
                            $branch_count = $conn->query("SELECT COUNT(*) AS c FROM branches WHERE status=1")->fetch_assoc()['c'];
                            ?>
                            <span class="badge ms-1" style="background:#e6f5f3;color:#219688;font-size:11px;font-weight:700;vertical-align:middle;"><?= $branch_count ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;"
                            data-bs-toggle="modal" data-bs-target="#modal-add-branch">
                            <i class="ri-add-line me-1"></i>Add Branch
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-git-branch-line me-1"></i>Branch</th>
                                        <th><i class="ri-map-pin-line me-1"></i>City</th>
                                        <th><i class="ri-phone-line me-1"></i>Phone</th>
                                        <th><i class="ri-mail-line me-1"></i>Email</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-pulse-line me-1"></i>Status</th>
                                        <th class="text-center" style="width:100px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                                    if ($query && $query->num_rows > 0):
                                        while ($row = $query->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="brn-code"><?= htmlspecialchars($row['branch_code']) ?></span>
                                                    <div>
                                                        <div class="brn-name"><?= htmlspecialchars($row['branch_name']) ?></div>
                                                        <div class="brn-contact"><i class="ri-building-line me-1"></i><?= htmlspecialchars($row['address'] ?? '—') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="brn-city"><?= htmlspecialchars($row['city'] ?? '—') ?></span></td>
                                            <td><?= htmlspecialchars($row['phone'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $row['status'] == 1 ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-branch"
                                                    data-id="<?= $row['id'] ?>" data-code="<?= htmlspecialchars($row['branch_code']) ?>"
                                                    data-name="<?= htmlspecialchars($row['branch_name']) ?>" data-city="<?= htmlspecialchars($row['city'] ?? '') ?>"
                                                    data-phone="<?= htmlspecialchars($row['phone'] ?? '') ?>" data-email="<?= htmlspecialchars($row['email'] ?? '') ?>"
                                                    data-status="<?= $row['status'] ?>">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
                                        endwhile;
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'component/pos_modals.php'; ?>
