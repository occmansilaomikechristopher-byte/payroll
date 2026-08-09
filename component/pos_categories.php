<div class="row">
    <div class="col-12">
        <div class="card pos-card">
            <div class="card-header align-items-center d-flex py-2">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-layout-grid-line me-2" style="color:#219688;"></i>Product Categories
                </h4>
                <button type="button" class="btn btn-sm text-white" style="background:#219688;border-color:#219688;" data-bs-toggle="modal" data-bs-target="#modal-add-category">
                    <i class="ri-add-line me-1"></i>Add Category
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead style="background:#e6f5f3;">
                            <tr>
                                <th style="width:80px;" class="text-center">Code</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th style="width:100px;" class="text-center">Status</th>
                                <th style="width:100px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $categories = $conn->query("SELECT * FROM product_categories ORDER BY category_name ASC");
                            if ($categories->num_rows > 0):
                                while ($row = $categories->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= htmlspecialchars($row['category_code']) ?></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td><small><?= htmlspecialchars($row['description'] ?? '—') ?></small></td>
                                <td class="text-center">
                                    <span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $row['status'] == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-category" data-id="<?= $row['id'] ?>" data-code="<?= htmlspecialchars($row['category_code']) ?>" data-name="<?= htmlspecialchars($row['category_name']) ?>" data-desc="<?= htmlspecialchars($row['description'] ?? '') ?>" data-status="<?= $row['status'] ?>">
                                        <i class="ri-edit-line"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="ri-inbox-line me-2"></i>No categories found
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
    $('[data-bs-target="#modal-edit-category"]').click(function(){
        var btn = $(this);
        $('#edit-category-id').val(btn.data('id'));
        $('#edit-category-code').val(btn.data('code'));
        $('#edit-category-name').val(btn.data('name'));
        $('#edit-category-desc').val(btn.data('desc'));
        $('#edit-category-status').val(btn.data('status'));
    });
});
</script>
