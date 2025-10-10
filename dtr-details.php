<?php


if (!isset($_GET['id']) || !isset($_GET['device_id']) || !isset($_GET['site_id'])) {
    header("HTTP/1.1 405 Unauthorized");
    echo "Data not available";
    exit;
};
$id =  base64_decode($_GET['id']);
$device_id =  base64_decode($_GET['device_id']);
$site_id =  base64_decode($_GET['site_id']);
$timekeeper_name = base64_decode($_GET['timekeeper_name']);
// $status = base64_decode($_GET['status']);
$query = "SELECT DTR.*,sites.site_code, sites.site_name, employer_name FROM DTR  
        LEFT JOIN sites ON sites.id = DTR.site_id  
        LEFT JOIN employers  ON DTR.employer_id = employers.id  
        WHERE DTR.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$dtr = $result->fetch_assoc();


if (!$dtr) {
    header("HTTP/1.1 405 Unauthorized");
    echo "Data not available";
    exit;
}

function stringToArrayBySpaces($string)
{
    $split_array = preg_split('/\s{7}+/', $string, -1, PREG_SPLIT_NO_EMPTY);
    return $split_array;
}

function getDataBySpaces($dataString)
{
    $splitData = [];
    $currentElement = "";

    foreach (str_split($dataString) as $char) {
        if (ctype_space($char)) {
            if ($currentElement) {
                $splitData[] = $currentElement;
                $currentElement = "";
            }
        } else {
            $currentElement .= $char;
        }
    }

    if ($currentElement) {
        $splitData[] = $currentElement;
    }

    return $splitData;
}

$decoded_data = base64_decode(explode(",", $dtr['file'])[1]);
$result_array = stringToArrayBySpaces($decoded_data);
$is_duplicate = false;

?>


<link rel="stylesheet" href="assets/css/my-style.css">
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">DTR Details</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript: void(0);">Pages</a>
                                </li>
                                <li class="breadcrumb-item active">DTR Details</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 col-md-3">
                                <div class="d-flex mt-3">
                                    <div class="flex-shrink-0 avatar-xs align-self-center me-3">
                                        <div class="avatar-title bg-light rounded-circle fs-16 text-primary material-shadow">
                                            <i class="ri-calendar-fill"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="mb-1">Period :</p>
                                        <h6 class="fw-semibold"><?= date('F d', strtotime($dtr['date_from'])) ?> - <?= date('F d, Y', strtotime($dtr['date_to'])) ?></h6>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-6 col-md-3">
                                <div class="d-flex mt-3">
                                    <div class="flex-shrink-0 avatar-xs align-self-center me-3">
                                        <div class="avatar-title bg-light rounded-circle fs-16 text-primary material-shadow">
                                            <i class="ri-user-2-fill"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="mb-1">Timekeeper :</p>
                                        <h6 class="fw-semibold"><?= $timekeeper_name  ?></h6>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="d-flex mt-3">
                                    <div class="flex-shrink-0 avatar-xs align-self-center me-3">
                                        <div class="avatar-title bg-light rounded-circle fs-16 text-primary material-shadow">
                                            <i class="ri-global-line"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="mb-1">Site :</p>
                                        <h6 class="fw-semibold"><?= $dtr['site_name']  ?>(<?= $dtr['site_code']  ?>)</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="d-flex mt-3">
                                    <div class="flex-shrink-0 avatar-xs align-self-center me-3">
                                        <div class="avatar-title bg-light rounded-circle fs-16 text-primary material-shadow">
                                            <i class="ri-user-2-fill"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="mb-1">Employer :</p>
                                        <h6 class="fw-semibold"><?= $dtr['employer_name']  ?></h6>
                                    </div>
                                </div>
                            </div>

                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end card-body-->
                </div>

                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Attendace List</h4>
                        <div>
                            <?php if ($dtr['status'] === 0 && $login_role  !== 6) { ?>
                                <button data-toggle="tooltip" title="Add Attendance" onclick="addSchedule(<?= $id ?>)" type="button" class="btn btn-outline-warning" title="Refresh">Add Attendance</button>
                            <?php } ?>

                            <?php if ($dtr['status'] === 1) { ?>
                                <button data-toggle="tooltip" title="Approve DTR" <?= $is_duplicate ? 'disabled' : ''  ?> onclick="approveDtr(<?= $id ?>)" type="button" class="btn btn-outline-secondary ml-1" title="Refresh">Approve</button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 20px; width: 200px;">
                            <div class="search-box">
                                <input id="myInput" type="text" class="form-control bg-light border-light" placeholder="Search here...">
                                <i class="ri-search-2-line search-icon"></i>
                            </div>
                        </div>
                        <div class="table-scrollable">
                            <table cellspacing="0" id="table-1" class="table table-sm table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Full Name</th>
                                        <th>Hours Worked</th>
                                        <th>Overtime</th>
                                        <th>Undertime(min)</th>
                                        <th>Late(min)</th>
                                        <th>Logs</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = $conn->query("SELECT  a.*, e.employee_no, e.lastname, e.firstname, e.middlename,  d.name as department, p.name as position 
                                    FROM DTR_details a 
                                    INNER JOIN employee e ON a.employee_id = e.id 
                                    LEFT JOIN department d ON e.department_id = d.id 
                                    LEFT JOIN position p ON e.position_id = p.id  

                                    WHERE  a.ddtr_id = $id ORDER BY a.date_time ASC ");
                                    while ($row = $query->fetch_assoc()) {
                                        $logs = json_decode($row['logs']);
                                        $logs = isset($logs) ? $logs : [];


                                    ?>
                                        <tr>
                                            <td width="120"><?= date("F j, Y",  strtotime($row['date_time'])) ?></td>
                                            <td style="min-width: 200px;">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 chat-user-img away align-self-center me-2 ms-0">
                                                        <div class="avatar-xxs">
                                                            <div class="avatar-title rounded-circle bg-primary text-white fs-10"><?= strtoupper(substr($row['firstname'], 0, 1)) ?><?= strtoupper(substr($row['lastname'], 0, 1)) ?></div></span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <p class="text-truncate mb-0"><?= $row['lastname'] ?> <?= $row['firstname'] ?> <?= $row['middlename'] ?>.</p>
                                                        <small><?= $row['position'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td width="120" class="text-center">
                                                <?php if ($login_role  !== 6) { ?>
                                                    <div class="input-group mb-3">
                                                        <input type="text" value="<?= $row['work_hours'] ?>" class="form-control" placeholder="Hours Worked" aria-label="Hours Worked" aria-describedby="basic-addon2">
                                                        <div class="input-group-append">
                                                            <button onclick="updateHoursWork(this, <?= $row['id'] ?>)" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class=" ri-save-line"></i></button>
                                                        </div>
                                                    </div>
                                                <?php } else { ?>
                                                    <?= $row['work_hours'] ?>
                                                <?php } ?>
                                            </td>
                                            <td width="120" class="text-center">
                                                <?php if ($login_role  !== 6) { ?>
                                                    <div class="input-group mb-3">
                                                        <input type="text" value="<?= $row['overtime'] ?>" class="form-control" placeholder="Overtime" aria-label="Overtime" aria-describedby="basic-addon2">
                                                        <div class="input-group-append">
                                                            <button onclick="updateOvertime(this, <?= $row['id'] ?>)" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class=" ri-save-line"></i></button>
                                                        </div>
                                                    </div>
                                                <?php } else { ?>
                                                    <?= $row['overtime'] ?>
                                                <?php } ?>
                                            </td>
                                            <td width="120" class="text-center">
                                                <?php if ($login_role  !== 6) { ?>
                                                    <div class="input-group mb-3">
                                                        <input type="text" value="<?= $row['undertime'] ?>" class="form-control" placeholder="Undertime" aria-label="Undertime" aria-describedby="basic-addon2">
                                                        <div class="input-group-append">
                                                            <button onclick="updateUndertime(this, <?= $row['id'] ?>)" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class=" ri-save-line"></i></button>
                                                        </div>
                                                    </div>
                                                <?php } else { ?>
                                                    <?= $row['undertime'] ?>
                                                <?php } ?>
                                            </td>
                                            <td width="120" class="text-center">
                                                <?php if ($login_role  !== 6) { ?>
                                                    <div class="input-group mb-3">
                                                        <input type="text" value="<?= $row['late'] ?>" class="form-control" placeholder="Late" aria-label="Late" aria-describedby="basic-addon2">
                                                        <div class="input-group-append">
                                                            <button onclick="updateLate(this, <?= $row['id'] ?>)" data-toggle="tooltip" title="Save Changes" class="btn btn-success" type="button"><i class=" ri-save-line"></i></button>
                                                        </div>
                                                    </div>
                                                <?php } else { ?>
                                                    <?= $row['late'] ?>
                                                <?php } ?>
                                            </td>
                                            <?php
                                            $date_check = date("Y-m-d",  strtotime($row['date_time']));
                                            $employee_id = $row['employee_id'];
                                            // var_dump("SELECT DTR.*, timekeeper.name AS timekeeper_name,  manager.name AS manager_name, uploaded.name AS uploaded_by
                                            //     FROM DTR_details
                                            //     LEFT JOIN DTR ON DTR_details.ddtr_id = DTR.id
                                            //     LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id
                                            //     LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id 
                                            //     LEFT JOIN users AS manager ON DTR.manager_id = manager.id
                                            //     where date_time = '$date_check'  
                                            //     AND employee_id = '$employee_id' 
                                            //     AND ddtr_id != '$id'  
                                            //     GROUP BY date_time  
                                            // ");
                                            $check_duplicate = $conn->query("SELECT DTR.*, timekeeper.name AS timekeeper_name,  uploaded.name AS uploaded_by
                                                FROM DTR_details
                                                LEFT JOIN DTR ON DTR_details.ddtr_id = DTR.id
                                                LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id
                                                LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id 
                                                where date_time = '$date_check'  
                                                AND employee_id = '$employee_id' 
                                                AND ddtr_id != '$id'  
                                                GROUP BY date_time  
                                            ");
                                            $timekeeper_name = '';
                                            $device_id2 = '';
                                            $status = '';
                                            $site_id2 = '';
                                            $id_dtr = '';
                                            $site_name = '';

                                            if ($check_duplicate->num_rows) {
                                                $is_duplicate = true;
                                                while ($row_check = $check_duplicate->fetch_assoc()) {

                                                    $timekeeper_name = $row_check['timekeeper_name'];
                                                    $device_id2 = $row_check['device_id'];
                                                    $status = $row_check['status'];
                                                    $site_id2 = $row_check['site_id'];
                                                    $id_dtr = $row_check['id'];
                                                    $site_name = $row_check['site_id'];
                                                }
                                            } else {
                                                $is_duplicate = false;
                                            }


                                            ?>
                                            <?php if ($is_duplicate) { ?>
                                                <td style="background-color: #ffbbd2; margin-left:10px">
                                                    <div>
                                                        <?php foreach ($logs  as $log) {  ?>
                                                            <div class="mt-1">
                                                                <?php if ($log->type === 'bio') { ?>
                                                                    <span class="badge rounded-pill border border-success text-success">Biometric</span>
                                                                <?php } else { ?>
                                                                    <span class="badge rounded-pill border border-warning text-warning">Manual</span>
                                                                <?php } ?>
                                                                <?= date("g:i A",  strtotime($log->dateTime)) ?>
                                                            </div>

                                                        <?php }  ?>
                                                    </div>
                                                    <div class="mt-1">
                                                        <?php if ($dtr['status'] === 1) { ?>
                                                            <button data-toggle="tooltip" title="Delete Attendance" onclick="deleteDTRLogs(<?= $row['id'] ?>)" class="btn btn-danger ml-1 btn-sm">
                                                                <i class=" ri-delete-bin-line"></i>
                                                            </button>
                                                        <?php } ?>
                                                        <a data-toggle="tooltip" title="View DTR" target="new" href="index.php?page=dtr-details&id=<?= base64_encode($id_dtr) ?>&timekeeper_name=<?= base64_encode($timekeeper_name) ?>&device_id=<?= base64_encode($device_id2) ?>&site_id=<?= base64_encode($site_id2) ?>&status=<?= base64_encode($status) ?>" type="button" class="btn btn-sm btn-secondary" title="Edit" id="<?= $id_dtr ?>" site_name="<?= htmlspecialchars($site_name) ?>" onclick="edit_function(this)">
                                                            <i class=" ri-eye-line"></i>
                                                        </a>

                                                    </div>
                                                </td>
                                            <?php  } else { ?>
                                                <td>
                                                    <?php foreach ($logs  as $log) {  ?>
                                                        <div class="m-1">
                                                            <?php if ($log->type === 'bio') { ?>
                                                                <span class="badge rounded-pill border border-success text-success">Biometric</span>
                                                            <?php } else { ?>
                                                                <span class="badge rounded-pill border border-warning text-warning">Manual</span>
                                                            <?php } ?>
                                                            <?= date("g:i A",  strtotime($log->dateTime)) ?>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                            <td class="text-center">
                                                <?php if ($login_role  !== 6) { ?>
                                                    <button data-toggle="tooltip" title="Delete Attendance" onclick="deleteDTRLogs(<?= $row['id'] ?>)" class="btn btn-danger ml-1 btn-sm">
                                                        <i class=" ri-delete-bin-line"></i>
                                                    </button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- end card-body -->
                </div>
            </div>
            <!-- end page title -->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

</div>
<?php include 'component/add_attendance.php'; ?>
<script src="assets/js/dtr-details.js"></script>