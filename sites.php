<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Sites</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript: void(0);">Pages</a>
                                </li>
                                <li class="breadcrumb-item active">Sites</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Sites List</h4>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#modal"><i class="ri-add-line align-bottom me-1"></i> Create Site</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive  mt-3 mb-1">
                            <table id="data-table" class="table table-hover  table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Site</th>
                                        <th>Employer</th>
                                        <th>Cluster</th>
                                        <th>Timekeeper</th>
                                        <th>PIC</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $conn->query("SELECT A.*, B.cluster , C.name AS timekeeper, P.name AS pic, P.id AS pic_id , C.id AS timekeeper_id, D.employer_name AS employer
                                FROM sites AS A 
                                INNER JOIN clusters AS B ON A.cluster_id = B.id 
                                LEFT JOIN users AS C ON A.timekeeper_id = C.id 
                                LEFT JOIN users AS P ON A.pic = P.id 
                                LEFT JOIN employers AS D ON C.employer_id = D.id 
                                ORDER BY A.site_name ASC");
                                    while ($row = $query->fetch_assoc()) {
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="site-wapper">
                                                    <div><i class=" ri-hashtag"></i> <?= $row['site_code'] ?></div>
                                                    <div><i class="ri-radio-button-line"></i> <?= $row['site_name'] ?></div>
                                                    <div><i class="ri-map-pin-line"></i> <?= $row['site_address'] ?></div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['employer']); ?></td>
                                            <td><?php echo htmlspecialchars($row['cluster']); ?></td>
                                            <td><?php echo htmlspecialchars($row['timekeeper']); ?></td>
                                            <td><?php echo htmlspecialchars($row['pic']); ?></td>
                                            <td width="100" class="text-center">
                                                <?php if ($row['status'] == 1) { ?>
                                                    <span class="badge rounded-pill border border-success text-success">Active</span>
                                                <?php } else { ?>
                                                    <span class="badge rounded-pill border border-danger text-danger">Inactive</span>
                                                <?php } ?>
                                            </td>
                                            <td class="text-center" width="100">
                                            <button data-bs-toggle="tooltip" title="Edit Site"   id="<?= $row['id'] ?>" site_code="<?= htmlspecialchars($row['site_code']) ?>" site_name="<?= htmlspecialchars($row['site_name']) ?>" site_address="<?= htmlspecialchars($row['site_address']) ?>"
                                            timekeeper_id="<?= htmlspecialchars($row['timekeeper_id']) ?>" pic_id="<?= htmlspecialchars($row['pic_id']) ?>" cluster_id="<?= htmlspecialchars($row['cluster_id']) ?>" status="<?= htmlspecialchars($row['status']) ?>" onclick="edit_function(this)"  type="button"  onclick="edit_function(this)" class="btn btn-sm btn-outline-secondary">Edit</button>
                                               
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

</div>
<?php include 'component/add_site_form.php'; ?>