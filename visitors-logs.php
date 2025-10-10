<style>
    td p {
        margin: unset;
    }

    .rem_att {
        cursor: pointer;
    }

    .select2-selection__choice__display {
        color: #000000 !important;
    }
    
    .site-wapper div {
        line-height: 1.2;
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Visitors Log</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript: void(0);">Pages</a>
                                </li>
                                <li class="breadcrumb-item active">Visitors Log</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Visitors Log List</h4>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-filter-add">
                            <i class="ri-filter-line align-bottom me-1"></i> Filter
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET["from"])) {  ?>
                            <h6><i class="icon-calendar"> </i> Date Visited: <b><?= $_GET["from"] ?> - <?= $_GET["to"] ?></b></h6>
                            <br>

                            <div class="table-responsive">
                                <table id="table-logs" class="table table-hover table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Site</th>
                                            <th>Date Visited</th>
                                            <th>Name</th>
                                            <th>Company</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $site_query = isset($_GET["site_id"]) && $_GET["site_id"] !== '' ? "AND site_id = '" . $_GET["site_id"] . "'" : '';
                                        $position = $conn->query("SELECT visitors_logs.*, sites.site_name, sites.site_address,sites.site_code FROM visitors_logs 
                                            LEFT JOIN sites ON visitors_logs.site_id = sites.id 
                                            WHERE date_visited between '" . $_GET["from"] . "' AND '" . $_GET["to"] . "' 
                                            " . $site_query . " 
                                            ORDER BY visitors_logs.date_visited DESC");
                                        while ($row = $position->fetch_assoc()) {
                                        ?>
                                            <tr>
                                                <td>
                                                    <img style="width: 30px; cursor: pointer;" 
                                                         src="uploads/<?php echo $row['image']; ?>" 
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#lightboxModal"
                                                         onclick="document.getElementById('lightboxImage').src = 'uploads/<?php echo $row['image']; ?>'"
                                                         class="img-fluid">
                                                </td>
                                                <td>
                                                    <div class="site-wapper">
                                                        <div><?= $row['site_code'] ?></div>
                                                        <div><?= $row['site_name'] ?></div>
                                                        <div><?= $row['site_address'] ?></div>
                                                    </div>
                                                </td>
                                                <td><?php
                                                    $date = strtotime($row['date_visited']);
                                                    $formattedDate = date("F j, Y, g:i a", $date);
                                                    echo $formattedDate;
                                                    ?></td>
                                                <td><?php echo $row['name'] ?></td>
                                                <td><?php echo $row['company'] ?></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="card-body text-center">
                                <div class="avatar-sm mx-auto mb-3">
                                    <div class="avatar-title bg-success-subtle text-success fs-17 rounded">
                                        <i class="ri-filter-line"></i>
                                    </div>
                                </div>
                                <h4 class="card-title">Filter To Continue</h4>
                            </div>
                        <?php } ?>
                    </div><!-- end card-body -->
                </div>
            </div>
            <!-- end page title -->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-labelledby="lightboxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lightboxModalLabel">Log Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lightboxImage" class="img-fluid" src="" alt="Enlarged Image" style="max-width: 100%;">
            </div>
        </div>
    </div>
</div>

<?php include 'component/filter_logs.php'; ?>

<!-- Add this JavaScript at the bottom of your page -->
<script>
    // This will handle the image click and set the src attribute
    document.addEventListener('DOMContentLoaded', function() {
        var lightboxModal = document.getElementById('lightboxModal');
        lightboxModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var imageSrc = button.getAttribute('src');
            var modalImage = lightboxModal.querySelector('.modal-body img');
            modalImage.src = imageSrc;
        });
    });
</script>