<style>
    .usr-avatar { width:30px; height:30px; border-radius:50%; background:#394b7c; color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .usr-name { font-weight:600; font-size:13px; }
    .usr-username { font-family:monospace; font-size:12px; color:#394b7c; font-weight:600; }
    .usr-employer { font-size:13px; font-weight:600; }
    .usr-site-code { background:#394b7c; color:#fff; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:700; font-family:monospace; }
    .usr-site-name { font-size:11px; font-weight:600; color:#333; }
    .usr-site-addr { font-size:10px; color:#888; }
    .usr-site-item { border:1px solid #d0d7ee; border-radius:4px; padding:4px 8px; margin-bottom:4px; background:#f7f8fc; }
    .usr-action { display:flex; gap:4px; justify-content:center; flex-wrap:nowrap; }
    #data-table thead th { background-color:#394b7c !important; border-color:#2d3d66 !important; color:#fff !important; }
    #data-table tbody tr:hover td { background:#f4f5fb; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">
                            <i class="ri-shield-user-line me-2" style="color:#394b7c;"></i>User Management
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Pages</a></li>
                                <li class="breadcrumb-item active">Users</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card" style="border-top:3px solid #394b7c;">
                    <div class="card-header align-items-center d-flex py-2">
                        <h4 class="card-title mb-0 flex-grow-1">
                            <i class="ri-shield-user-line me-2" style="color:#394b7c;"></i>User List
                            <?php
                            $user_count = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role != 1")->fetch_assoc()['c'];
                            ?>
                            <span class="badge ms-1" style="background:#eef0f8;color:#394b7c;font-size:11px;font-weight:700;vertical-align:middle;"><?= $user_count ?></span>
                        </h4>
                        <button type="button" class="btn btn-sm text-white" style="background:#394b7c;border-color:#394b7c;"
                            data-bs-toggle="modal" data-bs-target="#modal">
                            <i class="ri-user-add-line me-1"></i>Create User
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive mt-2 mb-1">
                            <table id="data-table" class="table table-hover table-bordered dt-responsive nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="ri-user-3-line me-1"></i>User</th>
                                        <th><i class="ri-shield-check-line me-1"></i>Role</th>
                                        <th><i class="ri-building-2-line me-1"></i>Employer</th>
                                        <th class="text-center" style="width:90px;"><i class="ri-pulse-line me-1"></i>Status</th>
                                        <th class="text-center" style="width:160px;"><i class="ri-settings-3-line me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $conn->query("
                                        SELECT users.*,
                                            employers.employer_name,
                                            GROUP_CONCAT(CONCAT(sites.site_code,'|',sites.site_name,'|',sites.site_address) SEPARATOR '||') AS site_data
                                        FROM users
                                        LEFT JOIN employers ON employers.id = users.employer_id
                                        LEFT JOIN sites ON sites.timekeeper_id = users.id
                                        WHERE users.role != 1
                                        GROUP BY users.id
                                        ORDER BY users.name ASC
                                    ");
                                    while ($row = $query->fetch_assoc()):
                                        $initials = strtoupper(substr($row['name'], 0, 1))
                                                  . strtoupper(substr(strstr($row['name'], ' ') ?: $row['name'], 1, 1));
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="usr-avatar"><?= $initials ?></div>
                                                    <div>
                                                        <div class="usr-name"><?= htmlspecialchars($row['name']) ?></div>
                                                        <div class="usr-username"><i class="ri-at-line me-1"></i><?= htmlspecialchars($row['username']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?= getRole($row['role']) ?>
                                                <?php if ($row['role'] == 5 && !empty($row['site_data'])): ?>
                                                    <div class="mt-1">
                                                        <?php
                                                        foreach (explode('||', $row['site_data']) as $s):
                                                            [$code, $name, $address] = array_pad(explode('|', $s), 3, '');
                                                        ?>
                                                            <div class="usr-site-item">
                                                                <span class="usr-site-code"><?= htmlspecialchars($code) ?></span>
                                                                <span class="usr-site-name ms-1"><?= htmlspecialchars($name) ?></span>
                                                                <div class="usr-site-addr"><i class="ri-map-pin-line me-1"></i><?= htmlspecialchars($address) ?></div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php elseif ($row['role'] == 5): ?>
                                                    <div class="text-muted" style="font-size:11px;margin-top:3px;"><i class="ri-information-line me-1"></i>No site assigned</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="usr-employer"><i class="ri-building-2-line me-1 text-muted"></i><?= htmlspecialchars($row['employer_name'] ?? '—') ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($row['status'] == 1): ?>
                                                    <span class="badge rounded-pill bg-success"><i class="ri-checkbox-circle-line me-1"></i>Active</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-danger"><i class="ri-close-circle-line me-1"></i>Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="usr-action">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        id="<?= $row['id'] ?>"
                                                        name="<?= htmlspecialchars($row['name']) ?>"
                                                        username="<?= htmlspecialchars($row['username']) ?>"
                                                        employer_id="<?= htmlspecialchars($row['employer_id']) ?>"
                                                        role="<?= htmlspecialchars($row['role']) ?>"
                                                        onclick="edit_function(this)"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit User">
                                                        <i class="ri-edit-line me-1"></i>Edit
                                                    </button>
                                                    <?php if ($row['status'] == 1): ?>
                                                        <button onclick="updateUserStatus(<?= $row['id'] ?>, 2)"
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Set Inactive">
                                                            <i class="ri-forbid-line"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="updateUserStatus(<?= $row['id'] ?>, 1)"
                                                            class="btn btn-sm btn-outline-success"
                                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Set Active">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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

<?php include 'component/add_user_form.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
</script>
