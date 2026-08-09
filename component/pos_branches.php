<div class="row">
    <div class="col-12">
        <div class="card pos-card">
            <div class="card-header align-items-center d-flex py-2">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-git-branch-line me-2" style="color:#219688;"></i>Branches
                </h4>
                <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-branch">
                    <i class="ri-add-line me-1"></i>Add Branch
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead style="background:#e6f5f3;">
                            <tr>
                                <th style="width:80px;" class="text-center">Code</th>
                                <th>Branch Name</th>
                                <th>City</th>
                                <th style="width:120px;">Phone</th>
                                <th>Email</th>
                                <th style="width:100px;" class="text-center">Status</th>
                                <th style="width:100px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
                            if ($branches->num_rows > 0):
                                while ($row = $branches->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= htmlspecialchars($row['branch_code']) ?></td>
                                <td><?= htmlspecialchars($row['branch_name']) ?></td>
                                <td><?= htmlspecialchars($row['city'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($row['phone'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $row['status'] == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-branch" data-id="<?= $row['id'] ?>" data-code="<?= htmlspecialchars($row['branch_code']) ?>" data-name="<?= htmlspecialchars($row['branch_name']) ?>" data-city="<?= htmlspecialchars($row['city'] ?? '') ?>" data-phone="<?= htmlspecialchars($row['phone'] ?? '') ?>" data-email="<?= htmlspecialchars($row['email'] ?? '') ?>" data-status="<?= $row['status'] ?>">
                                        <i class="ri-edit-line"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="ri-inbox-line me-2"></i>No branches found
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('[data-bs-target="#modal-edit-branch"]').click(function(){
        var btn = $(this);
        $('#edit-branch-id').val(btn.data('id'));
        $('#edit-branch-code').val(btn.data('code'));
        $('#edit-branch-name').val(btn.data('name'));
        $('#edit-branch-city').val(btn.data('city'));
        $('#edit-branch-phone').val(btn.data('phone'));
        $('#edit-branch-email').val(btn.data('email'));
        $('#edit-branch-status').val(btn.data('status'));
    });
});
</script>
