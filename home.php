<?php if ($login_role === 6) { ?>
    <?php
    echo "<script> location.href='index.php?page=dtr'; </script>";
    exit;
    ?>
<?php } ?>

<?php

$total_employee = $conn->query("SELECT count(*) AS total FROM  employee  ")->fetch_array();
$total_positions = $conn->query("SELECT count(*) AS total FROM  position ")->fetch_array();
$total_clusters = $conn->query("SELECT count(*) AS total FROM  clusters  ")->fetch_array();
$total_sites = $conn->query("SELECT count(*) AS total FROM  sites WHERE status=1  ")->fetch_array();
$filter_query = '';
// if ($login_role === 4) {
//     $employer_id = $_SESSION["login_employer_id"];
//     $filter_query = "WHERE id = $employer_id ";
// }

?>

<?php

// Get the current day of the week (0 = Sunday, 1 = Monday, etc.)
$dayOfWeek = date('w');

// Calculate the number of days to subtract to get to the previous Monday
$daysToSubtract = $dayOfWeek === 0 ? 6 : $dayOfWeek - 1;

// Get the timestamp for the previous Monday
$mondayTimestamp = strtotime("-$daysToSubtract days");

// Get the Monday and Sunday dates in desired format
$startDateFrom = '';
$startDateTo = '';

$site_filter  = "AND DTR.site_id = 1";

?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Dashboard</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript: void(0);">Pages</a>
                                </li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card crm-widget">
                            <div class="card-body p-0">
                                <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 g-0">
                                    <div class="col">
                                        <div class="py-4 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">Employees</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-group-line display-6 text-muted cfs-22"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 cfs-22"><span class="counter-value" data-target="<?= $total_employee[0] ?>"><?= $total_employee[0] ?></span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end col -->
                                    <div class="col">
                                        <div class="mt-3 mt-md-0 py-4 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">Positions</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-user-location-line display-6 text-muted cfs-22"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 cfs-22"><span class="counter-value" data-target="<?= $total_positions[0] ?>">4<?= $total_positions[0] ?></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end col -->
                                    <div class="col">
                                        <div class="mt-3 mt-lg-0 py-4 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">Clusters</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class=" ri-pin-distance-fill display-6 text-muted cfs-22"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 cfs-22"><span class="counter-value" data-target="<?= $total_clusters[0] ?>"><?= $total_clusters[0] ?></span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end col -->
                                    <div class="col">
                                        <div class="mt-3 mt-lg-0 py-4 px-3">
                                            <h5 class="text-muted text-uppercase fs-13">Sites</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class=" ri-map-pin-3-line display-6 text-muted cfs-22"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h2 class="mb-0 cfs-22"><span class="counter-value" data-target="<?= $total_sites[0] ?>"><?= $total_sites[0] ?></span></h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end col -->
                                </div><!-- end row -->
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div>
                <div class="col-12">
                    
                    
                </div>

            </div>
            <!-- end page title -->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

</div>