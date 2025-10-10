<?php
$filter_query = '';
// if ($login_role === 6) {
//     $site_id = $_SESSION["login_site_id"];
//     $filter_query = "AND sites.id = $site_id ";
// }

$site_ids = [];
if ($login_role === 6) {
    $user_id = $_SESSION["login_id"];
    $query = $conn->query("SELECT A.*
    FROM sites AS A WHERE pic =  $user_id
    ORDER BY A.site_name ASC");
    while ($row = $query->fetch_assoc()) {
        array_push($site_ids, $row['id']);
    }
    $commaSeparatedSites = implode(',', $site_ids);
    $filter_query = "AND sites.id  IN ($commaSeparatedSites) ";
}

?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Daily Time Record</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript: void(0);">Pages</a>
                                </li>
                                <li class="breadcrumb-item active">Daily Time Record</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Daily Time Record List</h4>
                        <button type="button" class="btn btn-success add-btn" onclick="uploadFile()"><i class="ri-upload-line align-bottom me-1"></i> Upload File</button>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-pills arrow-navtabs nav-success bg-light mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#arrow-new" role="tab" aria-selected="true">
                                    <span class="d-block d-sm-none"><i class="mdi mdi-home-variant"></i></span>
                                    <span class="d-none d-sm-block">New</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#arrow-approved" role="tab" aria-selected="false" tabindex="-1">
                                    <span class="d-block d-sm-none"><i class="mdi mdi-account"></i></span>
                                    <span class="d-none d-sm-block">Approved</span>
                                </a>
                            </li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content text-muted">
                            <div class="tab-pane active" id="arrow-new" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="data-table1" class="table table-bordered dt-responsive nowrap table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Period</th>
                                                <th>Employer</th>
                                                <th>Site</th>
                                                <th>Uploaded By</th>
                                                <th>Timekeeper</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = $conn->query("SELECT DTR.*, sites.site_code, sites.site_name, sites.site_address, timekeeper.name AS timekeeper_name, uploaded.name AS uploaded_by, employer_name
                                        FROM DTR 
                                        LEFT JOIN sites ON DTR.site_id = sites.id 
                                        LEFT JOIN users AS timekeeper ON DTR.timekeeper_id = timekeeper.id 
                                        LEFT JOIN users AS uploaded ON DTR.uploaded_by = uploaded.id 
                                        LEFT JOIN employers  ON DTR.employer_id = employers.id 
                                        WHERE DTR.status =  1
                                        $filter_query
                                        ORDER BY DTR.id DESC");
                                            while ($row = $query->fetch_assoc()) {
                                            ?>
                                                <tr>
                                                    <td>
                                                        <b>
                                                            <?php
                                                            $date = strtotime($row['date_from']);
                                                            $formattedDate = date("F d", $date);
                                                            echo $formattedDate;
                                                            ?>
                                                            - <?php
                                                                $date = strtotime($row['date_to']);
                                                                $formattedDate = date("F j, Y", $date);
                                                                echo $formattedDate;
                                                                ?>
                                                        </b>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['employer_name']); ?></td>
                                                    <td>
                                                        <div class="site-wapper">
                                                            <div><?= $row['site_code'] ?></div>
                                                            <div> <?= $row['site_name'] ?></div>
                                                            <div> <?= $row['site_address'] ?></div>
                                                        </div>
                                                    </td>
                                                    <td><?= $row['uploaded_by'] ?></td>
                                                    <td><?= $row['timekeeper_name'] ?></td>
                                                    <td class="text-center" width="100">
                                                        <?php if ($row['status'] == 1) { ?>
                                                            <button data-toggle="tooltip" title="Delete" onclick="deleteDTR(<?php echo $row['id'] ?>)" type="button" class="btn btn-sm btn-outline-danger" id="<?= $row['id'] ?>" site_name="<?= htmlspecialchars($row['site_name']) ?>">
                                                                Delete
                                                            </button>
                                                        <?php } ?>
                                                        <a data-toggle="tooltip" title="View" href="index.php?page=dtr-details&id=<?= base64_encode($row['id']) ?>&timekeeper_name=<?= base64_encode($row['timekeeper_name']) ?>&device_id=<?= base64_encode($row['device_id']) ?>&site_id=<?= base64_encode($row['site_id']) ?>&status=<?= base64_encode($row['status']) ?>" type="button" class="btn btn-sm btn-outline-secondary" title="Edit" id="<?= $row['id'] ?>" site_name="<?= htmlspecialchars($row['site_name']) ?>">View
                                                        </a>

                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <!-- <div id="table-container"></div> -->
                                </div>
                            </div>
                            <div class="tab-pane" id="arrow-approved" role="tabpanel">
                                <div class="table-responsive">
                                    <table style="width: 100% !important;" id="data-table" class="table table-bordered dt-responsive nowrap table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Period</th>
                                                <th>Employer</th>
                                                <th>Site</th>
                                                <th>Uploaded By</th>
                                                <th>Timekeeper</th>
                                                <th>Approved By</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>

                                </div>
                            </div>
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

<?php include 'component/drt_form.php'; ?>
<script>
    function createTable(data) {
        // Create the table element
        const table = document.createElement("table");
        table.classList.add("table");
        table.classList.add("table-hover");
        // Create the header row
        const headerRow = document.createElement("tr");
        headerRow.classList.add("thead-dark"); // Add class to header row


        // Add table headers for each property name (dateTime, device_id, updated_id)
        for (const key in data[0]) {
            const headerCell = document.createElement("th");
            const textNode = document.createTextNode(key);
            headerCell.appendChild(textNode);
            headerRow.appendChild(headerCell);
        }

        // Add the header row to the table
        table.appendChild(headerRow);

        // Loop through each data object and create table rows
        for (const item of data) {
            const row = document.createElement("tr");
            for (const key in item) {
                const cell = document.createElement("td");
                const textNode = document.createTextNode(item[key]);
                cell.appendChild(textNode);
                row.appendChild(cell);
            }
            table.appendChild(row);
        }

        return table;
    }
</script>