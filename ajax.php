<?php
$staticToken = 'jejors_api_token9343876536753';
$encodedToken = base64_encode($staticToken);
define('API_TOKEN', $encodedToken );
ob_start();
if (!isset($_GET['action'])) {
	header("HTTP/1.0 404 Not Found");
	echo "<h1>404 Error: Page Not Found</h1>";
	exit();
}

$action = $_GET['action'];
include 'admin_class.php';
$crud = new Action();


if ($action == "mobile-login-check") {
	$save = $crud->loginMobile();
	echo json_encode($save);
	return;
}


if ($action == 'login') {
	$save = $crud->login();
	echo json_encode($save);
	return;
}

// mobile

if ($action == "mobile-all-employee") {
	$save = $crud->gel_all_employee();
	echo json_encode($save);
	return;
}

if ($action == "mobile-sync-local") {
	// var_dump(API_TOKEN);
	$save = $crud->localSync();
	echo json_encode($save);
	return;
}

if ($action == "mobile-save-logs") {
	$save = $crud->saveLogs();
	echo json_encode($save);
	return;
}

if ($action == "mobile-push-dtr") {
	$save = $crud->save_employee_attendance_mobile();
	echo json_encode($save);
	return;
}

if ($action == "manual-push-dtr") {
	$save = $crud->save_employee_attendance_manual();
	echo json_encode($save);
	return;
}


if ($action == 'login2') {
	$login = $crud->login2();
	if ($login)
		echo $login;
		return;
}
if ($action == 'logout') {
	$logout = $crud->logout();
	if ($logout)
		echo $logout;
		return;
}
if ($action == 'logout2') {
	$logout = $crud->logout2();
	if ($logout)
		echo $logout;
	return;
}


// end mobile
if (!isset($_SESSION['is_login']) && !$_SESSION['is_login'] === true) {
	header("HTTP/1.0 403 Forbidden");
	echo "<h1>403 Error: Access Forbidden</h1>";
	exit();
}



if ($action == "isLock222") {  
	$save = $crud->isLock();
	echo json_encode($save);
}


if ($action == 'signup') {
	$save = $crud->signup();
	if ($save)
		echo $save;
}
// if ($action == "save_settings") {
// 	$save = $crud->save_settings();
// 	if ($save)
// 		echo $save;
// }
if ($action == "save_employee") {
	$save = $crud->save_employee();
	if ($save)
		echo $save;
}

if ($action == "save_employee_contribution") {
	$save = $crud->save_employee_contribution();
	if ($save)
		echo $save;
}



if ($action == "delete_employee") {
	$save = $crud->delete_employee();
	if ($save)
		echo $save;
}
if ($action == "save_department") {
	$save = $crud->save_department();
	if ($save)
		echo $save;
}
if ($action == "delete_department") {
	$save = $crud->delete_department();
	if ($save)
		echo $save;
}
if ($action == "save_position") {
	$save = $crud->save_position();
	if ($save)
		echo $save;
}
if ($action == "delete_position") {
	$save = $crud->delete_position();
	if ($save)
		echo $save;
}
if ($action == "save_allowances") {
	$save = $crud->save_allowances();
	if ($save)
		echo $save;
}
if ($action == "delete_allowances") {
	$save = $crud->delete_allowances();
	if ($save)
		echo $save;
}

if ($action == "save_employee_allowance") {
	$save = $crud->save_employee_allowance();
	if ($save)
		echo $save;
}
if ($action == "delete_employee_allowance") {
	$save = $crud->delete_employee_allowance();
	if ($save)
		echo $save;
}
if ($action == "delete_employee_contribution") {
	$save = $crud->delete_employee_contribution();
	if ($save)
		echo $save;
}
if ($action == "save_deductions") {
	$save = $crud->save_deductions();
	if ($save)
		echo $save;
}
if ($action == "delete_deductions") {
	$save = $crud->delete_deductions();
	if ($save)
		echo $save;
}
if ($action == "save_employee_deduction") {
	$save = $crud->save_employee_deduction();
	if ($save)
		echo $save;
}
if ($action == "delete_employee_deduction") {
	$save = $crud->delete_employee_deduction();
	if ($save)
		echo $save;
}

if ($action == "save_employee_attendance") {
	$save = $crud->save_employee_attendance();
	echo json_encode($save);
}
if ($action == "delete_employee_attendance") {
	$save = $crud->delete_employee_attendance();
	if ($save)
		echo $save;
}
if ($action == "delete_employee_attendance_single") {
	$save = $crud->delete_employee_attendance_single();
	if ($save)
		echo $save;
}
if ($action == "save_payroll") {
	$save = $crud->save_payroll();
	echo json_encode($save);
}
if ($action == "delete_payroll") {
	$save = $crud->delete_payroll();
	if ($save)
		echo $save;
}
if ($action == "calculate_payroll") {
	$save = $crud->calculate_payroll();
	echo json_encode($save);
}

if ($action == "save_contribution") {
	$save = $crud->save_contribution();
	if ($save)
		echo $save;
}

if ($action == "save_time_logs") {
	$save = $crud->save_time_logs();
	if ($save)
		echo $save;
}

if ($action == "delete_employee_timelogs") {
	$save = $crud->delete_employee_timelogs();
	if ($save)
		echo $save;
}

if ($action == "filter_attendance") {
	$save = $crud->filter_attendance();
	if ($save)
		echo $save;
}



if ($action == "save_site") {
	$save = $crud->save_site();
	if ($save)
		echo json_encode($save);
}

if ($action == "save_user") {
	$save = $crud->save_user();
	if ($save)
		echo json_encode($save);
}

if ($action == "update_status_user") {
	$save = $crud->update_status_user();
	if ($save)
		echo json_encode($save);
}

if ($action == "update_status_dtr") {
	$save = $crud->update_status_dtr();
	if ($save)
		echo json_encode($save);
}

if ($action == "delete_dtr") {
	$save = $crud->delete_dtr();
	if ($save)
		echo $save;
}


if ($action == "get_sites") {
	$save = $crud->get_sites();
	if ($save)
		echo $save;
}

if ($action == "save_settings") {
	$save = $crud->save_payroll_settings();
	if ($save)
		echo json_encode($save);
}

if ($action == "delete_dtr_logs") {
	$save = $crud->delete_dtr_logs();
		echo json_encode($save);
}

if ($action == "update_dtr_logs") {
	$save = $crud->update_dtr_logs();
	echo json_encode($save);
}

if ($action == "update_payroll_item") {
	$save = $crud->update_payroll_item();
	echo json_encode($save);
}

if ($action == "update_payroll_item_new") {
	$save = $crud->update_payroll_item_new();
	echo json_encode($save);
}

if ($action == "save_payroll_amount") {
	$save = $crud->save_payroll_amount();
	echo json_encode($save);
}


if ($action == "save_cluster") {
	$save = $crud->save_cluster();
	if ($save)
		echo $save;
}

if ($action == "save_employee_loan") {
	$save = $crud->save_employee_loan();
	if ($save)
		echo $save;
}

if ($action == "active_employee_loan") {
	$save = $crud->active_employee_loan();
	if ($save)
		echo $save;
}

if ($action == "update_payroll_status") {
	$save = $crud->update_payroll_status();
	echo json_encode($save);
}

if ($action == "loan_history_details") {
	$save = $crud->loan_history_details();
}

if ($action == "payroll_history_details") {
	$save = $crud->payroll_history_details();
}

if ($action == "update_payroll_print") {
	$save = $crud->update_payroll_print();
}

if ($action == "save_refunds") {
	$save = $crud->save_refunds();
	if ($save)
		echo $save;
}

if ($action == "import_employee") {
	$save = $crud->import_employee();
	if ($save)
		echo $save;
}






