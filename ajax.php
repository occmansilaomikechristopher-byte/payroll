<?php
// API response/security headers. Configure APP_ALLOWED_ORIGIN on production
// (for example: https://app.example.com). Native mobile requests have no Origin.
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigin = getenv('APP_ALLOWED_ORIGIN') ?: '';
if ($requestOrigin !== '' && $allowedOrigin !== '' && hash_equals($allowedOrigin, $requestOrigin)) {
	header('Access-Control-Allow-Origin: ' . $requestOrigin);
	header('Vary: Origin');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

// Production APIs must use HTTPS. Keep local XAMPP HTTP available for development.
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$isLocalHost = $host === 'localhost' || str_starts_with($host, '127.0.0.1') ||
	str_starts_with($host, '192.168.') || str_starts_with($host, '10.') ||
	str_starts_with($host, '172.16.') || str_starts_with($host, '172.17.') ||
	str_starts_with($host, '172.18.') || str_starts_with($host, '172.19.') ||
	str_starts_with($host, '172.2') || str_starts_with($host, '172.30.') ||
	str_starts_with($host, '172.31.');
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
	($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
if (!$isLocalHost && !$isHttps) {
	http_response_code(426);
	echo json_encode(['success' => false, 'message' => 'HTTPS is required.']);
	exit;
}

// Never expose database/runtime errors in an API response.
ini_set('display_errors', '0');
mysqli_report(MYSQLI_REPORT_OFF);

$staticToken = 'api_token9343876536753';
$encodedToken = base64_encode($staticToken);
define('API_TOKEN', $encodedToken );
ob_start();
if (!isset($_GET['action'])) {
	http_response_code(404);
	echo json_encode(['success' => false, 'message' => 'API action is required.']);
	exit();
}

$action = $_GET['action'];

// This aggregate report does not need Action initialization. Keeping it early
// prevents unrelated schema checks in admin_class.php from blanking the API
// response on installations with older quotation tables.
if ($action === 'owner-report-quotations') {
	$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
	if ($authHeader === '' && function_exists('getallheaders')) {
		foreach (getallheaders() as $headerName => $headerValue) {
			if (strtolower($headerName) === 'authorization') {
				$authHeader = trim($headerValue);
				break;
			}
		}
	}
	$authorized = preg_match('/Bearer\s+(.+)$/i', trim($authHeader), $matches) &&
		hash_equals(API_TOKEN, trim($matches[1]));
	if ((!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) && !$authorized) {
		http_response_code(403);
		echo json_encode(['success' => false, 'message' => 'Access Forbidden']);
		exit();
	}

	include 'db_connect.php';
	$count = 0;
	$total = 0;
	$branchColumn = $conn->query("SHOW COLUMNS FROM pos_quotations LIKE 'branch_id'");
	$quotationFilter = ($branchColumn && $branchColumn->num_rows > 0) ? ' WHERE branch_id = 1' : '';
	mysqli_report(MYSQLI_REPORT_OFF);
	$result = $conn->query("SELECT COUNT(*) AS count, IFNULL(SUM(total), 0) AS total_value FROM pos_quotations$quotationFilter");
	if ($result) {
		$row = $result->fetch_assoc();
		$count = intval($row['count'] ?? 0);
		$total = floatval($row['total_value'] ?? 0);
	}
	echo json_encode(['success' => true, 'branch_name' => 'Main Branch', 'count' => $count, 'total' => $total, 'total_value' => $total]);
	exit();
}

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

if ($action == "mobile-get-branches") {
	$data = $crud->mobile_get_branches();
	echo json_encode($data);
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

if ($action == "upload-biometric-dtr") {
	$save = $crud->upload_biometric_dtr();
	header('Content-Type: application/json; charset=utf-8');
	if (ob_get_length()) {
		ob_clean();
	}
	echo json_encode($save);
	return;
}

// ── Mobile POS ──
if ($action == "mobile-pos-products") {
	$save = $crud->mobile_pos_products();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-save-sale") {
	$save = $crud->mobile_pos_save_sale();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-sales") {
	$save = $crud->mobile_pos_sales();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-sale-details") {
	$save = $crud->mobile_pos_sale_details();
	echo json_encode($save);
	return;
}

if ($action == "save_pos_quotation") {
	$save = $crud->mobile_pos_save_quotation();
	echo json_encode($save);
	return;
}

if ($action == "get_pos_quotations") {
	$save = $crud->mobile_pos_quotations();
	echo json_encode($save);
	return;
}

if ($action == "get_pos_quotation_details") {
	$save = $crud->mobile_pos_quotation_details();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-update-product-stock") {
	$save = $crud->mobile_pos_update_product_stock();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-update-product-price") {
	$save = $crud->mobile_pos_update_product_price();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-save-damage") {
	$save = $crud->mobile_pos_save_damage();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-delete-damage") {
	$save = $crud->mobile_pos_delete_damage();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-approve-damage") {
	$cashierWebSession = isset($_SESSION['is_login']) && $_SESSION['is_login'] === true &&
		intval($_SESSION['login_role'] ?? 0) === 9;
	if (!$cashierWebSession && !isValidApiTokenRequest($action)) {
		header("HTTP/1.0 403 Forbidden");
		echo json_encode(['result' => false, 'message' => 'Access Forbidden']);
		return;
	}
	$save = $crud->mobile_pos_approve_damage();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-save-owner-requisition") {
	$save = $crud->mobile_pos_save_owner_requisition();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-update-owner-requisition-payment") {
	$save = $crud->mobile_pos_update_owner_requisition_payment();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-delete-owner-requisition") {
	$cashierWebSession = isset($_SESSION['is_login']) && $_SESSION['is_login'] === true &&
		intval($_SESSION['login_role'] ?? 0) === 9;
	if (!$cashierWebSession && !isValidApiTokenRequest($action)) {
		header("HTTP/1.0 403 Forbidden");
		echo json_encode(['result' => false, 'message' => 'Access Forbidden']);
		return;
	}
	$save = $crud->mobile_pos_delete_owner_requisition();
	echo json_encode($save);
	return;
}

if ($action == "mobile-pos-update-owner-requisition-status") {
	$cashierWebSession = isset($_SESSION['is_login']) && $_SESSION['is_login'] === true &&
		intval($_SESSION['login_role'] ?? 0) === 9;
	if (!$cashierWebSession && !isValidApiTokenRequest($action)) {
		header("HTTP/1.0 403 Forbidden");
		echo json_encode(['result' => false, 'message' => 'Access Forbidden']);
		return;
	}
	$save = $crud->mobile_pos_update_owner_requisition_status();
	echo json_encode($save);
	return;
}

if ($action == "mobile-notification-list") {
	$save = $crud->mobile_notification_list();
	echo json_encode($save);
	return;
}

if ($action == "mobile-notification-all") {
	$save = $crud->mobile_notification_all();
	echo json_encode($save);
	return;
}

if ($action == "mobile-notification-read") {
	$save = $crud->mobile_notification_read();
	echo json_encode($save);
	return;
}

if ($action == "mobile-notification-delete") {
	$save = $crud->mobile_notification_delete();
	echo json_encode($save);
	return;
}

if ($action == 'add_pos_product_stock') {
	$save = $crud->add_pos_product_stock();
	echo is_array($save) ? json_encode($save) : $save;
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

function getAuthorizationHeader() {
	if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
		return trim($_SERVER['HTTP_AUTHORIZATION']);
	}
	if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
		return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
	}
	if (function_exists('getallheaders')) {
		$headers = getallheaders();
		foreach ($headers as $name => $value) {
			if (strtolower($name) === 'authorization') {
				return trim($value);
			}
		}
	}
	return '';
}

function isValidApiTokenRequest($action) {
	$authHeader = getAuthorizationHeader();
	if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
		$token = trim($matches[1]);
		if ($token === API_TOKEN && preg_match('/^(mobile-|owner-report-)/', $action)) {
			return true;
		}
	}
	return false;
}

// Only administrator and cashier sessions may use the web application.
// Token-authenticated mobile/API requests remain independent of web roles.
if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true &&
	!in_array(intval($_SESSION['login_role'] ?? 0), [1, 9], true) &&
	!isValidApiTokenRequest($action)) {
	header("HTTP/1.0 403 Forbidden");
	echo json_encode(['success' => false, 'message' => 'This account cannot access the web application.']);
	exit();
}

// end mobile
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) {
	if (!isValidApiTokenRequest($action)) {
		header("HTTP/1.0 403 Forbidden");
		echo json_encode(['success' => false, 'message' => 'Access Forbidden']);
		exit();
	}
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
		echo json_encode($save);
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

if ($action == "get_pos_sale_details") {
	echo json_encode($crud->get_pos_sale_details());
}

// ── POS: Branches ──
if ($action == "add_pos_branch") {
	$save = $crud->add_pos_branch();
	echo $save;
}
if ($action == "update_pos_branch") {
	$save = $crud->update_pos_branch();
	echo $save;
}

// ── POS: Categories ──
if ($action == "add_pos_category") {
	$save = $crud->add_pos_category();
	echo $save;
}
if ($action == "update_pos_category") {
	$save = $crud->update_pos_category();
	echo $save;
}

// ── POS: Products ──
if ($action == "add_pos_product") {
	$save = $crud->add_pos_product();
	echo $save;
}
if ($action == "update_pos_product") {
	$save = $crud->update_pos_product();
	echo $save;
}

// ── OWNER: Reports (All Branches) ──
if ($action == "owner-report-sales") {
	global $conn;
	$totalSales = 0;
	$result = $conn->query("SELECT SUM(total) as total FROM pos_sales");
	if ($result) {
		$row = $result->fetch_assoc();
		$totalSales = floatval($row['total'] ?? 0);
	}
	echo json_encode(['success' => true, 'total' => $totalSales]);
	return;
}

if ($action == "owner-report-sales-branches") {
	global $conn;
	$branches = [];
	$result = $conn->query(
		"SELECT b.id, b.branch_name, IFNULL(SUM(s.total), 0) AS total_sales " .
		"FROM branches b LEFT JOIN pos_sales s ON s.branch_id = b.id " .
		"GROUP BY b.id, b.branch_name ORDER BY b.branch_name ASC"
	);
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$branches[] = [
				'id' => intval($row['id']),
				'branch_name' => $row['branch_name'],
				'total_sales' => floatval($row['total_sales']),
			];
		}
	}
	echo json_encode(['success' => true, 'branches' => $branches]);
	return;
}

if ($action == "owner-report-inventory-branches") {
	global $conn;
	$branches = [];
	$result = $conn->query(
		"SELECT b.id, b.branch_name, IFNULL(COUNT(p.id), 0) AS total_products " .
		"FROM branches b LEFT JOIN products p ON p.branch_id = b.id AND p.status = 1 " .
		"GROUP BY b.id, b.branch_name ORDER BY b.branch_name ASC"
	);
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$branches[] = [
				'id' => intval($row['id']),
				'branch_name' => $row['branch_name'],
				'total_products' => intval($row['total_products']),
			];
		}
	}
	echo json_encode(['success' => true, 'branches' => $branches]);
	return;
}

if ($action == "owner-report-attendance-branches") {
	global $conn;
	$branches = [];
	$result = $conn->query(
		"SELECT b.id, b.branch_name, " .
		"IFNULL(SUM(CASE WHEN dd.attendance_type IN ('1','3') THEN 1 ELSE 0 END), 0) AS present_count, " .
		"IFNULL(COUNT(dd.id), 0) AS total_logs " .
		"FROM branches b " .
		"LEFT JOIN dtr d ON d.branch_id = b.id " .
		"LEFT JOIN dtr_details dd ON dd.ddtr_id = d.id " .
		"GROUP BY b.id, b.branch_name ORDER BY b.branch_name ASC"
	);
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$totalLogs = intval($row['total_logs']);
			$present = intval($row['present_count']);
			$attendanceRate = $totalLogs > 0 ? round(($present / $totalLogs) * 100, 1) : 0;
			$branches[] = [
				'id' => intval($row['id']),
				'branch_name' => $row['branch_name'],
				'attendance_rate' => $attendanceRate,
			];
		}
	}
	echo json_encode(['success' => true, 'branches' => $branches]);
	return;
}

if ($action == "owner-report-payroll-branches") {
	global $conn;
	$branches = [];
	$result = $conn->query(
		"SELECT b.id, b.branch_name, IFNULL(COUNT(DISTINCT dd.employee_id), 0) AS employee_count " .
		"FROM branches b " .
		"LEFT JOIN dtr d ON d.branch_id = b.id " .
		"LEFT JOIN dtr_details dd ON dd.ddtr_id = d.id " .
		"GROUP BY b.id, b.branch_name ORDER BY b.branch_name ASC"
	);
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$branches[] = [
				'id' => intval($row['id']),
				'branch_name' => $row['branch_name'],
				'employee_count' => intval($row['employee_count']),
			];
		}
	}
	echo json_encode(['success' => true, 'branches' => $branches]);
	return;
}

if ($action == "owner-report-inventory") {
	global $conn;
	$totalProducts = 0;
	$result = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 1");
	if ($result) {
		$row = $result->fetch_assoc();
		$totalProducts = intval($row['count'] ?? 0);
	}
	echo json_encode(['success' => true, 'count' => $totalProducts]);
	return;
}

if ($action == "owner-report-attendance") {
	global $conn;
	$attendanceRate = 0;
	$totalDays = $conn->query("SELECT COUNT(DISTINCT DATE(datetime_log)) as days FROM attendance");
	$presentLogs = $conn->query("SELECT COUNT(*) as presents FROM attendance WHERE log_type IN (1,3)");

	if ($totalDays && $presentLogs) {
		$totalRow = $totalDays->fetch_assoc();
		$presentRow = $presentLogs->fetch_assoc();
		$total = intval($totalRow['days'] ?? 0);
		$present = intval($presentRow['presents'] ?? 0);
		$attendanceRate = $total > 0 ? ($present / ($total * 2)) * 100 : 0;
	}
	echo json_encode(['success' => true, 'rate' => round($attendanceRate, 1)]);
	return;
}

if ($action == "owner-report-payroll") {
	global $conn;
	$employeeCount = 0;
	$result = $conn->query("SELECT COUNT(*) as count FROM employee WHERE status = 1");
	if ($result) {
		$row = $result->fetch_assoc();
		$employeeCount = intval($row['count'] ?? 0);
	}
	echo json_encode(['success' => true, 'count' => $employeeCount]);
	return;
}

if ($action == "owner-report-quotations") {
	global $conn;
	$count = 0;
	$total = 0;
	$result = $conn->query("SELECT COUNT(*) AS count, IFNULL(SUM(total), 0) AS total_value FROM pos_quotations");
	if ($result) {
		$row = $result->fetch_assoc();
		$count = intval($row['count'] ?? 0);
		$total = floatval($row['total_value'] ?? 0);
	}
	echo json_encode(['success' => true, 'count' => $count, 'total' => $total, 'total_value' => $total]);
	return;
}

if ($action == "owner-report-payable") {
	global $conn;
	// Get outstanding POS sales payable
	$psCount = 0;
	$psTotal = 0;
	$res1 = $conn->query("SELECT COUNT(*) AS count, IFNULL(SUM(total - payment), 0) AS total_payable FROM pos_sales WHERE total > payment");
	if ($res1) {
		$r1 = $res1->fetch_assoc();
		$psCount = intval($r1['count'] ?? 0);
		$psTotal = floatval($r1['total_payable'] ?? 0);
	}

	// Get owner requisitions payable (approved) minus any amount_paid
	$orqCount = 0;
	$orqTotal = 0;
	$res2 = $conn->query(
		"SELECT COUNT(*) AS count, IFNULL(SUM(CASE WHEN (r.quantity * COALESCE(p.unit_price,0) - IFNULL(r.amount_paid,0)) > 0 " .
		"THEN (r.quantity * COALESCE(p.unit_price,0) - IFNULL(r.amount_paid,0)) ELSE 0 END), 0) AS total_payable " .
		"FROM owner_requisitions r " .
		"LEFT JOIN products p ON p.product_name COLLATE utf8mb4_unicode_ci = r.item_name COLLATE utf8mb4_unicode_ci " .
		"AND p.status = 1 AND p.branch_id = r.branch_id " .
		"WHERE r.status = 'Approved'"
	);
	if ($res2) {
		$r2 = $res2->fetch_assoc();
		$orqCount = intval($r2['count'] ?? 0);
		$orqTotal = floatval($r2['total_payable'] ?? 0);
	}

	$totalCount = $psCount + $orqCount;
	$totalPayable = $psTotal + $orqTotal;
	echo json_encode([
		'success' => true,
		'count' => $totalCount,
		'total' => $totalPayable,
	]);
	return;
}

if ($action == "owner-report-payable-branches") {
	global $conn;
	$branches = [];
	$result = $conn->query(
		"SELECT b.id, b.branch_name, " .
		"IFNULL(ps.payable_count, 0) + IFNULL(orq.payable_count, 0) AS payable_count, " .
		"IFNULL(ps.total_payable, 0) + IFNULL(orq.total_payable, 0) AS total_payable " .
		"FROM branches b " .
		"LEFT JOIN (" .
			"SELECT branch_id AS pos_branch_id, COUNT(*) AS payable_count, IFNULL(SUM(total - payment), 0) AS total_payable " .
			"FROM pos_sales WHERE total > payment GROUP BY branch_id" .
		") ps ON ps.pos_branch_id = b.id " .
		"LEFT JOIN (" .
			"SELECT r.branch_id AS orq_branch_id, COUNT(*) AS payable_count, IFNULL(SUM(GREATEST(r.quantity * COALESCE(p.unit_price, 0) - IFNULL(r.amount_paid, 0), 0)), 0) AS total_payable " .
			"FROM owner_requisitions r " .
			"LEFT JOIN products p ON p.product_name COLLATE utf8mb4_unicode_ci = r.item_name COLLATE utf8mb4_unicode_ci " .
			"AND p.status = 1 AND p.branch_id = r.branch_id " .
			"WHERE r.status = 'Approved' AND (r.quantity * COALESCE(p.unit_price, 0) - IFNULL(r.amount_paid, 0)) > 0 GROUP BY r.branch_id" .
		") orq ON orq.orq_branch_id = b.id " .
		"GROUP BY b.id, b.branch_name ORDER BY b.branch_name ASC"
	);
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$branches[] = [
				'id' => intval($row['id']),
				'branch_name' => $row['branch_name'],
				'payable_count' => intval($row['payable_count']),
				'total_payable' => floatval($row['total_payable']),
			];
		}
	}
	echo json_encode(['success' => true, 'branches' => $branches]);
	return;
}






