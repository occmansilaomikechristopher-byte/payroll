<?php
include 'db_connect.php';


$request = $_REQUEST;
$status = isset($request['status'])  && $request['status'] !== '' ? (int)$request['status'] : 2; // Default to 2 (all)
$weekly_payroll = isset($request['weekly_payroll'])  && $request['weekly_payroll'] !== '' ? (int)$request['weekly_payroll'] : 2; // Default to 2 (all)
$position_id = isset($request['position_id'])  && $request['position_id'] !== '' ? (int)$request['position_id'] : null;

$col = array(
    0   =>  'employee_no',
    1   =>  'name',
    2   =>  'department',
    3   =>  'position',
    4   =>  'salary',
    5   =>  'status'
);  //create column like table in database

// Apply status filter
$filter_status = '';
if ($status === 0 || $status === 1) {
    $filter_status = " AND e.status = $status";
}
$_filter_payroll_type = '';
if ($weekly_payroll === 0 || $weekly_payroll === 1) {
    $_filter_payroll_type = " AND e.weekly_payroll = $weekly_payroll";
}
$filter_position = '';
if ($position_id) {
    $filter_position = " AND e.position_id = $position_id";
}



$sql = "SELECT e.id, e.loan, e.employee_no, e.firstname, e.middlename, e.lastname, e.salary,e.basic_pay, e.ot_rate, e.status, e.weekly_payroll, d.name as department, p.name as position FROM employee e 
        LEFT JOIN department d ON e.department_id = d.id 
        LEFT JOIN position p ON e.position_id = p.id WHERE e.id !=0000  $filter_status   $_filter_payroll_type $filter_position ";

if (!empty($request['search']['value'])) {
    $searchValue = mysqli_real_escape_string($conn, $request['search']['value']);
    $sql .= " AND (e.firstname LIKE '%$searchValue%' 
                  OR e.lastname LIKE '%$searchValue%'
                  OR CONCAT(e.lastname, ', ', e.firstname) LIKE '%$searchValue%'
                  OR CONCAT(e.firstname, ' ', e.lastname) LIKE '%$searchValue%'
                  OR CONCAT(e.lastname, ' ', e.firstname) LIKE '%$searchValue%' ) ";
}

$query = mysqli_query($conn, $sql);
$totalData = mysqli_num_rows($query);
$totalFilter = $totalData;






$query = mysqli_query($conn, $sql);
$totalData = mysqli_num_rows($query);

$sql .= " ORDER BY " . $col[$request['order'][0]['column']] . "   " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "  ";
// Apply status filter

$query = mysqli_query($conn, $sql);

$data = array();

while ($row = mysqli_fetch_array($query)) {
    $subdata = array();
    $subdata[] = $row['employee_no'];
    $subdata[] = '<div class="d-flex align-items-center">
    <div class="flex-shrink-0 chat-user-img away align-self-center me-2 ms-0">
        <div class="avatar-xxs">
            <div class="avatar-title rounded-circle bg-primary text-white fs-10">'
        . strtoupper(substr($row['firstname'], 0, 1))
        . strtoupper(substr($row['lastname'], 0, 1)) .
        '</div>
        </div>
    </div>
    <div class="flex-grow-1 overflow-hidden">
        <p class="text-truncate mb-0">' . $row['lastname'] . ', ' . $row['firstname'] . '</p>
    </div>
  </div>';

    $subdata[] = $row['position'];
    $subdata[] = number_format($row['basic_pay'], 2);
    $subdata[] = number_format($row['salary'], 2);
    $subdata[] = number_format($row['ot_rate'], 2);
    $subdata[] = number_format($row['loan'], 2);
    $subdata[] = ($row['weekly_payroll'] == 1) ? '<span class="badge rounded-pill border border-primary text-primary">Weekly</span>' : '<span class="badge rounded-pill border border-info text-info">Monthly</span>';
    $subdata[] = ($row['status'] == 1) ? '<span class="badge rounded-pill border border-success text-success">Active</span>' : '<span class="badge rounded-pill border border-danger text-danger">Inactive</span>';
    $subdata[] = '<a   data-toggle="tooltip" title="View Employee"  href="index.php?page=employee-details&id=' . $row['id'] . '" class="btn btn-sm btn-outline-secondary emp-tool" title="View"> View</a>';
    $data[] = $subdata;
}

$json_data = array(
    "draw"              =>  intval($request['draw']),
    "recordsTotal"      =>  intval($totalData),
    "recordsFiltered"   =>  intval($totalFilter),
    "data"              =>  $data
);

echo json_encode($json_data);