<?php
ini_set('serialize_precision', '-1');
session_start();

ini_set('display_errors', 0);
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require 'vendor/autoload.php';  // Keep this at the top

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Action
{
    private $db;

    public function __construct()
    {
        ob_start();

        global $conn;
        include 'db_connect.php';

        $this->db = $conn;
        $this->ensureNotificationTable();
        $this->ensureQuotationTables();
    }

    function __destruct()
    {
        $this->db->close();

        ob_end_flush();
    }

    private function ensureNotificationTable()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS notifications (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            target_role INT NULL,
            target_user_id INT NULL,
            branch_id INT NULL,
            type VARCHAR(100) NOT NULL DEFAULT 'admin',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_target_role (target_role),
            KEY idx_target_user_id (target_user_id),
            KEY idx_branch_id (branch_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $result = $this->db->query("SHOW COLUMNS FROM notifications LIKE 'branch_id'");
        if ($result && $result->num_rows === 0) {
            $this->db->query("ALTER TABLE notifications ADD COLUMN branch_id INT NULL AFTER target_user_id");
        }
    }

    private function ensureQuotationTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS pos_quotations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            quotation_no VARCHAR(50) NOT NULL UNIQUE,
            branch_id INT NOT NULL DEFAULT 1,
            subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $branchColumn = $this->db->query("SHOW COLUMNS FROM pos_quotations LIKE 'branch_id'");
        if ($branchColumn && $branchColumn->num_rows === 0) {
            $this->db->query("ALTER TABLE pos_quotations ADD COLUMN branch_id INT NOT NULL DEFAULT 1 AFTER quotation_no");
        }

        $customerColumn = $this->db->query("SHOW COLUMNS FROM pos_quotations LIKE 'customer_name'");
        if ($customerColumn && $customerColumn->num_rows > 0) {
            $this->db->query("ALTER TABLE pos_quotations DROP COLUMN customer_name");
        }
        $quotationStatusColumn = $this->db->query("SHOW COLUMNS FROM pos_quotations LIKE 'status'");
        if ($quotationStatusColumn && $quotationStatusColumn->num_rows > 0) {
            $this->db->query("ALTER TABLE pos_quotations DROP COLUMN status");
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS pos_quotation_items (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            quotation_id INT UNSIGNED NOT NULL,
            product_id INT NOT NULL DEFAULT 0,
            product_name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            qty DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            line_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_quotation_id (quotation_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    function log_cashier_notification($title, $message, $user_id = null, $branch_id = null)
    {
        $titleEscaped = $this->db->real_escape_string($title);
        $messageEscaped = $this->db->real_escape_string($message);
        $targetUserId = $user_id !== null ? intval($user_id) : 'NULL';
        $branchIdValue = $branch_id !== null ? intval($branch_id) : 'NULL';
        $query = "INSERT INTO notifications (title, message, target_role, target_user_id, branch_id) VALUES ('$titleEscaped', '$messageEscaped', 9, $targetUserId, $branchIdValue)";
        return $this->db->query($query);
    }

    function mobile_notification_list()
    {
        $role = intval($_GET['role'] ?? 0);
        $branch_id = intval($_GET['branch_id'] ?? 0);
        $where = [];
        if ($role > 0) {
            $where[] = "(target_role = $role OR target_role IS NULL)";
        }
        if ($branch_id > 0) {
            $where[] = "(branch_id IS NULL OR branch_id = $branch_id)";
        }
        $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $query = "SELECT id, title, message, is_read, created_at FROM notifications $whereClause ORDER BY created_at DESC LIMIT 100";
        $result = $this->db->query($query);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return ['result' => true, 'data' => $items];
    }

    function mobile_notification_all()
    {
        return $this->mobile_notification_list();
    }

    function mobile_notification_delete()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $notification_id = intval($input['notification_id'] ?? 0);

        if ($notification_id <= 0) {
            return ['result' => false, 'message' => 'Invalid notification ID.'];
        }

        $stmt = $this->db->prepare('DELETE FROM notifications WHERE id = ?');
        if (!$stmt) {
            return ['result' => false, 'message' => 'Failed to prepare delete statement.'];
        }

        $stmt->bind_param('i', $notification_id);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return ['result' => false, 'message' => 'Error: ' . $error];
        }

        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        return [
            'result' => true,
            'deleted' => $deleted,
            'message' => $deleted ? 'Notification deleted successfully.' : 'Notification not found.',
        ];
    }

    function login()
    {
        $password = $this->db->real_escape_string($_POST['password']);
        $username =  $this->db->real_escape_string($_POST['username']);
        $stmt =  $this->db->prepare("SELECT * FROM users WHERE username = ?  AND status = 1");
        if ($stmt) {
            // Bind parameters and execute query
            $stmt->bind_param('s', $username);
            $stmt->execute();
            // Get the result
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $role = intval($row['role'] ?? 0);
                if (!in_array($role, [1, 9], true)) {
                    return ['result' => false, 'message' => 'Only administrator and cashier accounts can access the web application.'];
                }
                $stored_hashed_password = $row['password'];
                if (password_verify($password, $stored_hashed_password)) {
                    session_regenerate_id(true);
                    foreach ($row as $key => $value) {
                        if ($key != 'passwors' && !is_numeric($key)) {
                            $_SESSION['login_' . $key] = $value;
                        }
                        $_SESSION['is_login'] = true;
                    }
                    return ['result' => true, 'message' => 'login successful'];
                } else {
                    return ['result' => false, 'message' => 'Password incorrect'];
                }
            } else {
                return ['result' => false, 'message' => 'No user found with the given username'];
            }
        } else {
            return ['result' => false, 'message' => 'Error preparing statement'];
        }
    }

    function login2()
    {
        extract($_POST);

        $qry = $this->db->query("SELECT * FROM users where username = '" . $email . "' and password = '" . md5($password) . "' ");

        if ($qry->num_rows > 0) {
            foreach ($qry->fetch_array() as $key => $value) {
                if ($key != 'passwors' && !is_numeric($key)) {
                    $_SESSION['login_' . $key] = $value;
                }
            }

            return 1;
        } else {
            return 3;
        }
    }

    function logout()
    {
        session_destroy();

        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }

        header("location:login.php");
    }

    function logout2()
    {
        session_destroy();

        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }

        header("location:../index.php");
    }


    function signup()
    {
        extract($_POST);

        $data = " name = '$name' ";

        $data .= ", contact = '$contact' ";

        $data .= ", address = '$address' ";

        $data .= ", username = '$email' ";

        $data .= ", password = '" . md5($password) . "' ";

        $data .= ", type = 3";

        $chk = $this->db->query("SELECT * FROM users where username = '$email' ")->num_rows;

        if ($chk > 0) {
            return 2;

            exit();
        }

        $save = $this->db->query("INSERT INTO users set " . $data);

        if ($save) {
            $qry = $this->db->query("SELECT * FROM users where username = '" . $email . "' and password = '" . md5($password) . "' ");

            if ($qry->num_rows > 0) {
                foreach ($qry->fetch_array() as $key => $value) {
                    if ($key != 'passwors' && !is_numeric($key)) {
                        $_SESSION['login_' . $key] = $value;
                    }
                }
            }

            return 1;
        }
    }



    function calculate_payrollOld()
    {
        extract($_POST);
        $this->db->query("DELETE FROM payroll_items where payroll_id=" . $id);
        $pay = $this->db->query("SELECT * FROM payroll where id = " . $id)->fetch_array();
        $employee = $this->db->query("SELECT * FROM employee WHERE status = 1 ");
        $calc_days = abs(strtotime($pay['date_to'] . " 23:59:59")) - strtotime($pay['date_from'] . " 00:00:00 -1 day");
        $calc_days = floor($calc_days / (60 * 60 * 24));
            ($att = $this->db->query("SELECT * FROM attendance where date(datetime_log) between '" . $pay['date_from'] . "' and '" . $pay['date_to'] . "' order by UNIX_TIMESTAMP(datetime_log) asc  ")) or die($this->db->error);
        while ($row = $att->fetch_array()) {
            $date = date("Y-m-d", strtotime($row['datetime_log']));
            if ($row['log_type'] == 1) {
                if (!isset($attendance[$row['employee_id'] . "_" . $date]['log'][$row['log_type']])) {
                    $attendance[$row['employee_id'] . "_" . $date]['log'][$row['log_type']] = $row['datetime_log'];
                }
            } else {
                $attendance[$row['employee_id'] . "_" . $date]['log'][$row['log_type']] = $row['datetime_log'];
            }
        }
        $deductions = $this->db->query("SELECT * FROM employee_deductions where (`type` = '" . $pay['type'] . "' or (date(effective_date) between '" . $pay['date_from'] . "' and '" . $pay['date_from'] . "' ) ) ");
        $allowances = $this->db->query("SELECT * FROM employee_allowances where (`type` = '" . $pay['type'] . "' or (date(effective_date) between '" . $pay['date_from'] . "' and '" . $pay['date_from'] . "' ) ) ");
        while ($row = $deductions->fetch_assoc()) {
            $ded[$row['employee_id']][] = ['did' => $row['deduction_id'], "amount" => $row['amount']];
        }
        while ($row = $allowances->fetch_assoc()) {
            $allow[$row['employee_id']][] = ['aid' => $row['allowance_id'], "amount" => $row['amount']];
        }

        while ($row = $employee->fetch_assoc()) {
            $am_in = $row['time_in'];
            $am_out = $row['time_out'];
            $salary = $row['salary'];
            $time_in = $row['time_in'];
            $time_out = $row['time_out'];
            $daily_hours_worked = abs(strtotime($time_out) - strtotime($time_in)) / 3600 - 1;
            $min = $salary / $daily_hours_worked / 60;
            $daily_hours_worked_min = $daily_hours_worked * 60;
            $absent = 0;
            $undertime = 0;
            $late = 0;
            $dp = 22 / $pay['type'];
            $present = 0;
            $net = 0;
            $allow_amount = 0;
            $ded_amount = 0;
            $contribute_amount = 0;
            $time_logs = 0;

            for ($i = 0; $i < $calc_days; $i++) {
                $dd = date("Y-m-d", strtotime($pay['date_from'] . " +" . $i . " days"));
                if (isset($attendance[$row['id'] . "_" . $dd]['log'])) {
                    $count = count($attendance[$row['id'] . "_" . $dd]['log']);
                }
                if (isset($attendance[$row['id'] . "_" . $dd]['log'][1]) && isset($attendance[$row['id'] . "_" . $dd]['log'][4])) {
                    $attendance_morning = strtotime($attendance[$row['id'] . "_" . $dd]['log'][1]);
                    $attendance_morning = date('H:i', $attendance_morning);
                    $attendance_afternoon = strtotime($attendance[$row['id'] . "_" . $dd]['log'][4]);
                    $attendance_afternoon = date('H:i', $attendance_afternoon);

                    $hours_worked = abs(strtotime($attendance_afternoon) - strtotime($attendance_morning)) / 3600 - 1;

                    $undertime_in_minutes = 0;

                    if (floatval($daily_hours_worked) > floatval($hours_worked)) {
                        //$daily_hours_worked_min = $daily_hours_worked * 60;

                        $hours_worked_min = $hours_worked * 60;

                        $undertime_in_minutes = $daily_hours_worked_min - $hours_worked_min;
                    }

                    $late_in_minutes = 0;

                    if (strtotime($am_in) < strtotime($attendance_morning)) {
                        $late_in_minutes = strtotime($attendance_morning) - strtotime($am_in);

                        $late_in_minutes = $late_in_minutes / 60;
                    }
                    $att_mn = abs(strtotime($attendance[$row['id'] . "_" . $dd]['log'][4])) - strtotime($attendance[$row['id'] . "_" . $dd]['log'][1]);
                    $att_mn = floor($att_mn / 60);
                    if ($att_mn > $daily_hours_worked_min) {
                        $att_mn = $daily_hours_worked_min;
                    }
                    $net += $att_mn * $min;
                    $late += $min * $late_in_minutes;
                    $undertime += $min * $undertime_in_minutes;
                    $present += 1;
                }
            }

            $ded_arr = [];
            $all_arr = [];
            if (isset($allow[$row['id']])) {
                foreach ($allow[$row['id']] as $arow) {
                    $all_arr[] = $arow;
                    $net += $arow['amount'];
                    $allow_amount += $arow['amount'];
                }
            }

            if (isset($ded[$row['id']])) {
                foreach ($ded[$row['id']] as $drow) {
                    $ded_arr[] = $drow;
                    $net -= $drow['amount'];
                    $ded_amount += $drow['amount'];
                }
            }

            $contributionList = [];
            $contributions = $this->db->query("SELECT * FROM employee_contributions WHERE employee_id='" . $row['id'] . "'  AND payroll_type='" . $pay['type'] . "'  ");
            while ($row_cont = $contributions->fetch_assoc()) {
                $contributionList[$row_cont['employee_id']][] = ['cid' => $row_cont['contribution_id'], "amount" => $row_cont['amount']];
                $net -= $row_cont['amount'];
                $contribute_amount += $row_cont['amount'];
            }
            $timeLogsList = [];
            $timelogquery = $this->db->query("SELECT * FROM time_logs WHERE employee_id='" . $row['id'] . "'  ");
            while ($row_logs = $timelogquery->fetch_assoc()) {
                $time_log_min = $row_logs['total_hours'] * 60 * $min;
                $timeLogsList[$row_logs['employee_id']][] = ['tid' => $row_logs['id'], "total_hours" => $row_logs['total_hours'], "amount" => $time_log_min, 'rate' => $min];
                $net += $time_log_min;
                $time_logs += $time_log_min;
            }
            $net = $net - $late;
            $absent = $dp - $present;
            $data = " payroll_id = '" . $pay['id'] . "' ";
            $data .= ", employee_id = '" . $row['id'] . "' ";
            $data .= ", absent = '$absent' ";
            $data .= ", present = '$present' ";
            $data .= ", late = '$late' ";
            $data .= ", under_time = '$undertime' ";
            $data .= ", salary = '$salary' ";
            $data .= ", allowance_amount = '$allow_amount' ";
            $data .= ", contribute_amount = '$contribute_amount' ";
            $data .= ", deduction_amount = '$ded_amount' ";
            $data .= ", time_log_amount = '$time_logs' ";
            $data .= ", time_logs = '" . json_encode($timeLogsList) . "' ";
            $data .= ", allowances = '" . json_encode($all_arr) . "' ";
            $data .= ", deductions = '" . json_encode($ded_arr) . "' ";
            $data .= ", contributions = '" . json_encode($contributionList) . "' ";
            $data .= ", net = '$net' "; // var_dump($data);
            $save[] = $this->db->query("INSERT INTO payroll_items set " . $data);
        }

        if (isset($save)) {
            $this->db->query("UPDATE payroll set status = 1 where id = " . $pay['id']);

            return 1;
        }
    }


    function save_settings()
    {
        extract($_POST);
        $data = " name = '" . str_replace("'", "&#x2019;", $name) . "' ";
        $data .= ", email = '$email' ";
        $data .= ", contact = '$contact' ";
        $data .= ", about_content = '" . htmlentities(str_replace("'", "&#x2019;", $about)) . "' ";
        if ($_FILES['img']['tmp_name'] != '') {
            $fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
            $move = move_uploaded_file($_FILES['img']['tmp_name'], 'assets/img/' . $fname);
            $data .= ", cover_img = '$fname' ";
        }

        $chk = $this->db->query("SELECT * FROM system_settings");
        if ($chk->num_rows > 0) {
            $save = $this->db->query("UPDATE system_settings set " . $data);
        } else {
            $save = $this->db->query("INSERT INTO system_settings set " . $data);
        }
        if ($save) {
            $query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
            foreach ($query as $key => $value) {
                if (!is_numeric($key)) {
                    $_SESSION['setting_' . $key] = $value;
                }
            }
            return 1;
        }
    }

    function save_employee()
    {
        extract($_POST);
        $status = isset($_POST['status']) ? 1 : 0;
        $isAutoDeduct = isset($_POST['isAutoDeduct']) ? 1 : 0;
        $weekly_payroll = isset($_POST['weekly_payroll']) ? 1 : 0;
        $age = $_POST['age'] ?? '';
        $address = $_POST['address'] ?? '';
        $contact_number = $_POST['contact_number'] ?? '';

        // Calculate deductions
        $sss = ($weekly_payroll === 1) ? $this->getSSSWeeklyDeduction($basic_pay) : $this->getSSSMonthlyDeduction($basic_pay);
        $phic = ($weekly_payroll === 1) ? $this->calculatePhilHealthWeekly($basic_pay) : $this->calculatePhilHealth($basic_pay);
        $hdmf = 0; // Default value

        // Start transaction
        $this->db->begin_transaction();

        try {
            if (empty($id)) {
                $employee_code = mt_rand(100000000000, 999999999999);
                // Generate unique employee number
                do {
                    $e_num = date('Y') . '-' . mt_rand(1, 99999);

                    // FIXED: Use prepared statement for checking employee number
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM employee WHERE employee_no = ?");
                    $stmt->bind_param("s", $e_num);
                    $stmt->execute();
                    $stmt->bind_result($chk);
                    $stmt->fetch();
                    $stmt->close();
                } while ($chk > 0);

                // Insert new employee
                $query = "INSERT INTO employee 
                (employee_no, employee_code, firstname, middlename, lastname, position_id, salary, basic_pay, status, ot_rate, isAutoDeduct, weekly_payroll, clasification_id, sss_fund, allowance_rate, bday, ext, age, address, contact_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $types = str_repeat('s', 20);
                $stmt->bind_param($types, $e_num, $employee_code, $firstname, $middlename, $lastname, $position_id, $salary, $basic_pay, $status, $ot_rate, $isAutoDeduct, $weekly_payroll, $clasification_id, $sss_fund, $allowance_rate, $bday, $ext, $age, $address, $contact_number);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $employee_id = $this->db->insert_id; // Get newly inserted employee ID
                } else {
                    throw new Exception("Failed to insert employee.");
                }

                // Insert only the configured employee contributions: SSS and PAG-IBIG.
                $contributions = [
                    ['id' => 1, 'amount' => $sss],
                    ['id' => 2, 'amount' => $phic]
                ];

                $query = "INSERT INTO employee_contributions (employee_id, contribution_id, amount, payroll_type) VALUES (?, ?, ?, ?)";

                $stmt = $this->db->prepare($query);

                foreach ($contributions as $contribution) {

                    $stmt->bind_param("ssss", $employee_id, $contribution['id'], $contribution['amount'], $payroll_type);
                    $payroll_type = 1;
                    $stmt->execute();
                    if ($stmt->affected_rows <= 0) {
                        throw new Exception("Failed to insert contribution.");
                    }
                }
                $this->db->commit();
                return $employee_id;
            } else {
                // Update existing employee
                $query = "UPDATE employee SET 
                employee_code=?, firstname=?, middlename=?, lastname=?, position_id=?, salary=?, basic_pay=?, status=?, ot_rate=?, isAutoDeduct=?, weekly_payroll=?, clasification_id=?, sss_fund=?, allowance_rate=?, bday=?, ext=?, age=?, address=?, contact_number=?
                WHERE id=?";
                $stmt = $this->db->prepare($query);
                $types = str_repeat('s', 20);
                $stmt->bind_param($types, $employee_code, $firstname, $middlename, $lastname, $position_id, $salary, $basic_pay, $status, $ot_rate, $isAutoDeduct, $weekly_payroll, $clasification_id, $sss_fund, $allowance_rate, $bday, $ext, $age, $address, $contact_number, $id);
                $stmt->execute();

                $this->db->commit();
                return 'updated';
            }
        } catch (Exception $e) {
            $this->db->rollback(); // Rollback transaction on error
            error_log("Error in save_employee(): " . $e->getMessage()); // Log error
            return 0; // Error occurred
        }
    }





    function save_employee_contribution()
    {

        $type = '';
        $$type = $_POST['type'] ?? '';
        $value = $_POST['value'];
        $id = $_POST['id'];
        // Sanitize inputs
        $id = intval($id);
        $type2 =  $$type;
        $data = "$type2='$value' ";
        $save = $this->db->query("UPDATE employee set " . $data . " where id=" . $id);
        if ($save) {
            return 1;
        }
    }

    function delete_employee()
    {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM employee where id = " . $id);
        if ($delete) {
            return 1;
        }
    }

    function save_department()
    {
        extract($_POST);

        $data = " name='$name' ";

        if (empty($id)) {
            $save = $this->db->query("INSERT INTO department set " . $data);

            if ($save) {
                return 1;
            }
        } else {
            $save = $this->db->query("UPDATE department set " . $data . " where id=" . $id);

            if ($save) {
                return 2;
            }
        }
    }

    function delete_department()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM department where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function save_position()
    {
        extract($_POST);

        $data = " name='$name' ";

        // $data .= ", department_id = '$department_id' ";

        if (empty($id)) {
            $this->db->query("INSERT INTO position set " . $data);
            return 1;
        } else {
            $this->db->query("UPDATE position set " . $data . " where id=" . $id);
            return 2;
        }
    }

    function delete_position()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM position where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function save_allowances()
    {
        extract($_POST);

        $data = " allowance='$allowance' ";

        $data .= ", description = '$description' ";

        if (empty($id)) {
            $save = $this->db->query("INSERT INTO allowances set " . $data);
        } else {
            $save = $this->db->query("UPDATE allowances set " . $data . " where id=" . $id);
        }

        if ($save) {
            return 1;
        }
    }

    function delete_allowances()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM allowances where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function save_employee_allowance()
    {
        extract($_POST);

        foreach ($allowance_id as $k => $v) {
            $data = " employee_id='$employee_id' ";

            $data .= ", allowance_id = '$allowance_id[$k]' ";

            $data .= ", type = '$type[$k]' ";

            $data .= ", amount = '$amount[$k]' ";

            $data .= ", effective_date = '$effective_date[$k]' ";

            $save[] = $this->db->query("INSERT INTO employee_allowances set " . $data);
        }

        if (isset($save)) {
            return 1;
        }
    }

    function delete_employee_allowance()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM employee_allowances where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function delete_employee_contribution()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM employee_contributions where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function save_deductions()
    {
        extract($_POST);

        $data = " deduction='$deduction' ";

        $data .= ", description = '$description' ";

        if (empty($id)) {
            $save = $this->db->query("INSERT INTO deductions set " . $data);
        } else {
            $save = $this->db->query("UPDATE deductions set " . $data . " where id=" . $id);
        }

        if ($save) {
            return 1;
        }
    }

    function delete_deductions()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM deductions where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function save_employee_deduction()
    {
        extract($_POST);

        foreach ($deduction_id as $k => $v) {
            $data = " employee_id='$employee_id' ";
            $data .= ", deduction_id = '$deduction_id[$k]' ";
            $data .= ", amount = '$amount[$k]' ";
            //$data .=", effective_date = '$effective_date[$k]' ";
            $save[] = $this->db->query("INSERT INTO employee_deductions set " . $data);
        }

        if (isset($save)) {
            return 1;
        }
    }

    function delete_employee_deduction()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM employee_deductions where id = " . $id);

        if ($delete) {
            return 1;
        }
    }



    function delete_employee_attendance()
    {
        extract($_POST);
        $date = explode('_', $id);
        $dt = str_replace('"', "", $date[1]);
        $date_data = str_replace('"', "", $date[0]);
        $date_data = (int) $date_data;
        $delete = $this->db->query("DELETE FROM attendance where employee_id = '" . $date_data . "' and date(datetime_log) ='$dt' ");
        if ($delete) {
            return 1;
        }
    }

    function delete_employee_attendance_single()
    {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM attendance where id = $id ");
        if ($delete) {
            return 1;
        }
    }


    function delete_payroll()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM payroll where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function delete_dtr()
    {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $login_role = isset($_SESSION['login_role']) ? intval($_SESSION['login_role']) : 0;
        $branch_id = isset($_SESSION['login_branch_id']) ? intval($_SESSION['login_branch_id']) : 0;

        if ($id <= 0) {
            return ['result' => false, 'message' => 'Invalid DTR ID'];
        }

        $stmt = $this->db->prepare("SELECT branch_id, status FROM dtr WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return ['result' => false, 'message' => 'Failed to validate DTR'];
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            return ['result' => false, 'message' => 'DTR not found'];
        }

        $dtr_status = intval($row['status']);
        if (!in_array($dtr_status, [1, 2], true)) {
            return ['result' => false, 'message' => 'This DTR cannot be deleted.'];
        }

        if ($dtr_status === 2 && !in_array($login_role, [1, 10], true)) {
            return ['result' => false, 'message' => 'Only an administrator or owner can delete an approved DTR.'];
        }

        if (!in_array($login_role, [1, 10], true) && $branch_id > 0 && intval($row['branch_id']) !== $branch_id) {
            return ['result' => false, 'message' => 'You do not have permission to delete this upload.'];
        }

        $this->db->begin_transaction();
        try {
            $stmtDelDetails = $this->db->prepare("DELETE FROM dtr_details WHERE ddtr_id = ?");
            if (!$stmtDelDetails) {
                throw new Exception($this->db->error);
            }
            $stmtDelDetails->bind_param('i', $id);
            if (!$stmtDelDetails->execute()) {
                throw new Exception($stmtDelDetails->error);
            }

            $stmtDelete = $this->db->prepare("DELETE FROM dtr WHERE id = ?");
            if (!$stmtDelete) {
                throw new Exception($this->db->error);
            }
            $stmtDelete->bind_param('i', $id);
            if (!$stmtDelete->execute()) {
                throw new Exception($stmtDelete->error);
            }

            $this->db->commit();
            return ['result' => true, 'message' => 'deleted'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }


    function save_contribution()
    {
        extract($_POST);

        // Validate and sanitize inputs
        $id = intval($id); // Ensure ID is an integer
        $amount = floatval($amount); // Ensure amount is a valid number

        if ($id <= 0 || $amount < 0) {
            return 0; // Invalid data
        }

        // Use prepared statements to prevent SQL injection
        $stmt = $this->db->prepare("UPDATE employee_contributions SET amount = ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $id); // "di" means double, integer

        if ($stmt->execute()) {
            return 1; // Success
        } else {
            return 0; // Failure
        }
    }



    function save_time_logs()
    {
        extract($_POST);

        $hours_worked = abs(strtotime($end_date) - strtotime($start_date)) / 3600;

        $total_hours = number_format((float) $hours_worked, 2, '.', '');

        // $start_date1 = date('Y-m-d hh:mm', strtotime($start_date));

        // $end_date1 = date('Y-m-d hh:mm', strtotime($end_date));

        $start_date = DateTime::createFromFormat('Y-m-d h:i:s A', $start_date);
        $end_date = DateTime::createFromFormat('Y-m-d h:i:s A', $end_date);

        $start = $start_date->format('Y-m-d H:i:s');
        $end = $end_date->format('Y-m-d H:i:s');

        $data = "employee_id = '$employee_id' ";

        $data .= ", start_date = '$start' ";

        $data .= ", end_date = '$end' ";

        $data .= ", total_hours = '$total_hours' ";

        $data .= ", memo = '$memo' ";
        $save = $this->db->query("INSERT INTO time_logs set " . $data);

        if (isset($save)) {
            return 1;
        }
    }

    function delete_employee_timelogs()
    {
        extract($_POST);

        $delete = $this->db->query("DELETE FROM time_logs where id = " . $id);

        if ($delete) {
            return 1;
        }
    }

    function gel_all_employee()
    {
        $list = [];
        $query = $this->db->query("SELECT * FROM employee ");
        while ($row = $query->fetch_assoc()) {
            $list[] = $row;
        }
        return $list;
    }

    function filter_attendance()
    {
        extract($_POST);
        $_SESSION['attendance_from'] = $from;
        $_SESSION['attendance_to'] = $to;
        return 1;
    }

    function save_user()
    {
        try {
            // Enable MySQLi exceptions for try/catch
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            // Gather POST data safely
            $name         = isset($_POST['name']) ? $_POST['name'] : '';
            $username     = isset($_POST['username']) ? $_POST['username'] : '';
            $password     = isset($_POST['password']) ? $_POST['password'] : '';
            $role         = isset($_POST['role']) ? $_POST['role'] : '';
            $site_id      = isset($_POST['site_id']) ? $_POST['site_id'] : '';
            $branch_id    = isset($_POST['branch_id']) ? $_POST['branch_id'] : '';
            $employer_id  = 1;
            $id           = isset($_POST['id']) ? $_POST['id'] : '';

            // Sanitize inputs
            $name        = mysqli_real_escape_string($this->db, $name);
            $username    = mysqli_real_escape_string($this->db, $username);
            $role        = mysqli_real_escape_string($this->db, $role);
            $site_id     = mysqli_real_escape_string($this->db, $site_id);
            $branch_id   = mysqli_real_escape_string($this->db, $branch_id);
            $employer_id = mysqli_real_escape_string($this->db, $employer_id);
            $id          = mysqli_real_escape_string($this->db, $id);

            // Handle password hashing and query part
            $password_sql = '';

            if (empty($id)) {
                // New user
                if (empty($password)) {
                    return ['result' => false, 'message' => 'Password is required for new users.'];
                }
                $password = password_hash(mysqli_real_escape_string($this->db, $password), PASSWORD_BCRYPT);
                $password_sql = ", password = '$password'";
            } else {
                // Existing user — update password only if provided
                if (!empty($password)) {
                    $password = password_hash(mysqli_real_escape_string($this->db, $password), PASSWORD_BCRYPT);
                    $password_sql = ", password = '$password'";
                }
            }

            // Check duplicate username only for new users
            if (empty($id)) {
                $check_username = $this->db->query("SELECT id FROM users WHERE username = '$username' LIMIT 1");
                if ($check_username->num_rows > 0) {
                    return ['result' => false, 'message' => 'Username already exists!'];
                }
            }

            // Build data string
            $data = "
            name = '$name',
            username = '$username',
            role = '$role'
            $password_sql
        ";

            // Employer is optional (field may be hidden in the form)
            if (!empty($employer_id)) {
                $data .= ", employer_id = '$employer_id'";
            }

            // Branch assignment
            if ($role == '5' || $role == '6' || $role == '9') {
                if (!empty($branch_id)) {
                    $data .= ", branch_id = '$branch_id'";
                } else {
                    $data .= ", branch_id = NULL";
                }
            } elseif ($role == '10') {
                if (!empty($branch_id) && $branch_id !== '0') {
                    $data .= ", branch_id = '$branch_id'";
                } else {
                    $data .= ", branch_id = NULL";
                }
            } else {
                $data .= ", branch_id = NULL";
            }

            // Insert or update user
            if (empty($id)) {
                $save = $this->db->query("INSERT INTO users SET $data");
                $user_id = $this->db->insert_id;
            } else {
                $save = $this->db->query("UPDATE users SET $data WHERE id = '$id'");
                $user_id = $id;
            }

            // Success response
            if ($save) {
                return [
                    'result' => true,
                    'message' => empty($id) ? 'User created successfully!' : 'User updated successfully!'
                ];
            }
        } catch (mysqli_sql_exception $e) {
            // Database errors (e.g., constraint violations, SQL syntax issues)
            return [
                'result' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            // Other unexpected PHP errors
            return [
                'result' => false,
                'message' => 'Unexpected error: ' . $e->getMessage()
            ];
        }
    }



    function localSync()
    {
        $result = [
            'employees' => [],

        ];

        // Query the department table
        $query = $this->db->query("SELECT * FROM department");
        while ($row = $query->fetch_assoc()) {
            $result['departments'][] = $row;
        }

        // Query the position table
        $query = $this->db->query("SELECT * FROM position");
        while ($row = $query->fetch_assoc()) {
            $result['positions'][] = $row;
        }

        // Query the employee table
        $query = $this->db->query("SELECT e.id,e.department_id,e.position_id, e.employee_no, e.firstname, e.middlename, e.lastname, e.salary, e.ot_rate, e.status, e.weekly_payroll, d.name as department, p.name as position FROM employee e 
        LEFT JOIN department d ON e.department_id = d.id 
        LEFT JOIN position p ON e.position_id = p.id "); //WHERE e.id = 27
        while ($row = $query->fetch_assoc()) {
            $result['employees'][] = $row;
        }


        // Return the structured result
        return $result;
    }

    function loginMobile1()
    {
        extract($_POST);
        // Prepare the SQL statement with parameters
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        if ($stmt) {
            // Bind parameters and execute query
            $stmt->bind_param('s', $username);
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            // Check if a row was found
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password correct, return user ID or other identifier
                    return $user['id']; // Assuming 'id' is the primary key of the users table
                } else {
                    // Password incorrect
                    return false;
                }
            } else {
                // No user found with the given username
                return false;
            }

            // Close the statement
            $stmt->close();
        } else {
            // Error preparing statement
            return false;
        }
    }

    function mobile_get_branches()
    {
        $result = $this->db->query("SELECT id, branch_code, branch_name FROM branches WHERE status = 1 ORDER BY branch_name ASC");
        $branches = [];
        while ($row = $result->fetch_assoc()) {
            $branches[] = $row;
        }
        return ['result' => true, 'data' => $branches];
    }

    // ── Mobile POS: fetch active products for a branch ──
    function mobile_pos_products()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $branch_id = isset($input['branch_id']) ? intval($input['branch_id']) : 0;

            $where = "status = 1";
            if ($branch_id > 0) {
                $where .= " AND branch_id = $branch_id";
            }
            $res = $this->db->query("SELECT id, product_code, product_name, unit_price, quantity_on_hand, unit, image
                                     FROM products WHERE $where ORDER BY product_name ASC");
            $products = [];
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
            return ['result' => true, 'products' => $products];
        } catch (Exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Mobile POS: recent sales for a branch ──
    function mobile_pos_sales()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $branch_id = isset($input['branch_id']) ? intval($input['branch_id']) : 0;
            $from = isset($input['from']) ? $this->db->real_escape_string($input['from']) : '';
            $to   = isset($input['to'])   ? $this->db->real_escape_string($input['to'])   : '';

            $where = "1";
            if ($branch_id > 0) {
                $where .= " AND branch_id = $branch_id";
            }
            if ($from !== '') {
                $where .= " AND created_at >= '$from 00:00:00'";
            }
            if ($to !== '') {
                $where .= " AND created_at <= '$to 23:59:59'";
            }
            $res = $this->db->query("SELECT id, invoice_no, subtotal, discount, total, payment, change_due, created_at
                                     FROM pos_sales WHERE $where ORDER BY created_at DESC LIMIT 200");
            $sales = [];
            while ($row = $res->fetch_assoc()) {
                $sales[] = $row;
            }
            return ['result' => true, 'sales' => $sales];
        } catch (Exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Web admin: sale + line items (reads $_POST) ──
    function get_pos_sale_details()
    {
        $sale_id = isset($_POST['sale_id']) ? intval($_POST['sale_id']) : 0;
        if ($sale_id <= 0) return ['result' => false, 'message' => 'Invalid sale.'];

        $sale = $this->db->query("SELECT s.*, b.branch_name, b.branch_code, u.name AS cashier_name
                                  FROM pos_sales s
                                  LEFT JOIN branches b ON b.id = s.branch_id
                                  LEFT JOIN users u ON u.id = s.cashier_id
                                  WHERE s.id = $sale_id")->fetch_assoc();
        if (!$sale) return ['result' => false, 'message' => 'Sale not found.'];

        $res = $this->db->query("SELECT product_name, price, qty, line_total
                                 FROM pos_sale_items WHERE sale_id = $sale_id ORDER BY id ASC");
        $items = [];
        while ($row = $res->fetch_assoc()) $items[] = $row;

        return ['result' => true, 'sale' => $sale, 'items' => $items];
    }

    // ── Mobile POS: one sale with its line items ──
    function mobile_pos_sale_details()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $sale_id = isset($input['sale_id']) ? intval($input['sale_id']) : 0;
            if ($sale_id <= 0) {
                return ['result' => false, 'message' => 'Invalid sale.'];
            }

            $sale = $this->db->query("SELECT * FROM pos_sales WHERE id = $sale_id")->fetch_assoc();
            if (!$sale) {
                return ['result' => false, 'message' => 'Sale not found.'];
            }

            $res = $this->db->query("SELECT product_id, product_name, price, qty, line_total
                                     FROM pos_sale_items WHERE sale_id = $sale_id ORDER BY id ASC");
            $items = [];
            while ($row = $res->fetch_assoc()) {
                $items[] = $row;
            }

            return ['result' => true, 'sale' => $sale, 'items' => $items];
        } catch (Exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    // ── Mobile POS: quotations ──
    function mobile_pos_quotations()
    {
        try {
            $res = $this->db->query("SELECT id, quotation_no, subtotal, discount, total, created_at
                                     FROM pos_quotations ORDER BY created_at DESC LIMIT 200");
            $quotations = [];
            while ($row = $res->fetch_assoc()) $quotations[] = $row;
            return ['result' => true, 'quotations' => $quotations];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_save_quotation()
    {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || empty($input['items'])) return ['result' => false, 'message' => 'No items in quotation.'];

            $discount = max(0, floatval($input['discount'] ?? 0));
            $items = $input['items'];
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += max(0, floatval($item['price'] ?? 0)) * max(0, floatval($item['qty'] ?? 0));
            }
            if ($subtotal <= 0) return ['result' => false, 'message' => 'Quotation total must be greater than zero.'];
            $discount = min($discount, $subtotal);
            $total = $subtotal - $discount;
            $quotation_no = 'QUO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $this->db->begin_transaction();
            $stmt = $this->db->prepare("INSERT INTO pos_quotations
                (quotation_no, branch_id, subtotal, discount, total)
                VALUES (?, 1, ?, ?, ?)");
            $stmt->bind_param('sddd', $quotation_no, $subtotal, $discount, $total);
            $stmt->execute();
            $quotation_id = $this->db->insert_id;
            $stmt->close();

            $itemStmt = $this->db->prepare("INSERT INTO pos_quotation_items
                (quotation_id, product_id, product_name, description, price, qty, line_total)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $product_id = intval($item['product_id'] ?? 0);
                $product_name = trim($item['product_name'] ?? '');
                $description = trim($item['description'] ?? '');
                $price = max(0, floatval($item['price'] ?? 0));
                $qty = max(0, floatval($item['qty'] ?? 0));
                $line_total = $price * $qty;
                $itemStmt->bind_param('iissddd', $quotation_id, $product_id, $product_name, $description, $price, $qty, $line_total);
                $itemStmt->execute();
            }
            $itemStmt->close();
            $this->db->commit();
            return ['result' => true, 'message' => 'Quotation saved successfully.', 'quotation_no' => $quotation_no];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_quotation_details()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $quotation_id = intval($input['quotation_id'] ?? 0);
            if ($quotation_id <= 0) return ['result' => false, 'message' => 'Invalid quotation.'];

            $stmt = $this->db->prepare('SELECT * FROM pos_quotations WHERE id = ?');
            $stmt->bind_param('i', $quotation_id);
            $stmt->execute();
            $quotation = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$quotation) return ['result' => false, 'message' => 'Quotation not found.'];

            $stmt = $this->db->prepare('SELECT product_id, product_name, description, price, qty, line_total FROM pos_quotation_items WHERE quotation_id = ? ORDER BY id ASC');
            $stmt->bind_param('i', $quotation_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];
            while ($row = $result->fetch_assoc()) $items[] = $row;
            $stmt->close();
            return ['result' => true, 'quotation' => $quotation, 'items' => $items];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ── Mobile POS: save a sale (header + items), decrement stock ──
    function mobile_pos_save_sale()
    {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || empty($input['items'])) {
                return ['result' => false, 'message' => 'No items in cart.'];
            }

            $branch_id  = intval($input['branch_id'] ?? 0);
            $cashier_id = intval($input['cashier_id'] ?? 0);
            $discount   = floatval($input['discount'] ?? 0);
            $payment    = floatval($input['payment'] ?? 0);
            $items      = $input['items'];

            // Compute subtotal from items (server-side, don't trust client total)
            $subtotal = 0;
            foreach ($items as $it) {
                $subtotal += floatval($it['price']) * floatval($it['qty']);
            }
            if ($discount < 0) $discount = 0;
            if ($discount > $subtotal) $discount = $subtotal;
            $total = $subtotal - $discount;
            $change = $payment > 0 ? max(0, $payment - $total) : 0;

            $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $this->db->begin_transaction();

            $stmt = $this->db->prepare("INSERT INTO pos_sales
                (invoice_no, branch_id, cashier_id, subtotal, discount, total, payment, change_due)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('siiddddd', $invoice_no, $branch_id, $cashier_id, $subtotal, $discount, $total, $payment, $change);
            $stmt->execute();
            $sale_id = $this->db->insert_id;
            $stmt->close();

            $itemStmt = $this->db->prepare("INSERT INTO pos_sale_items
                (sale_id, product_id, product_name, price, qty, line_total)
                VALUES (?, ?, ?, ?, ?, ?)");
            // ensure quantity does not go below zero using GREATEST
            $stockStmt = $this->db->prepare("UPDATE products SET quantity_on_hand = GREATEST(quantity_on_hand - ?, 0) WHERE id = ?");

            foreach ($items as $it) {
                $pid   = intval($it['product_id']);
                $pname = $it['product_name'];
                $price = floatval($it['price']);
                $qty   = floatval($it['qty']);
                $line  = $price * $qty;

                $itemStmt->bind_param('iisddd', $sale_id, $pid, $pname, $price, $qty, $line);
                $itemStmt->execute();

                $stockStmt->bind_param('di', $qty, $pid);
                $stockStmt->execute();
            }
            $itemStmt->close();
            $stockStmt->close();
            // prepare updated stocks map to return to client
            $pids = array_map(function($it){ return intval($it['product_id']); }, $items);
            $pids = array_unique($pids);
            $updated_stocks = [];
            if (count($pids) > 0) {
                $ids_list = implode(',', array_map('intval', $pids));
                $res2 = $this->db->query("SELECT id, quantity_on_hand FROM products WHERE id IN ($ids_list)");
                while ($r = $res2->fetch_assoc()) {
                    $updated_stocks[intval($r['id'])] = floatval($r['quantity_on_hand']);
                }
            }

            $this->db->commit();

            return [
                'result'        => true,
                'message'       => 'Sale completed.',
                'invoice_no'    => $invoice_no,
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'total'         => $total,
                'change'        => $change,
                'updated_stocks'=> $updated_stocks,
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_update_product_stock()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $branch_id = isset($input['branch_id']) ? intval($input['branch_id']) : 0;
            $product_id = isset($input['product_id']) ? intval($input['product_id']) : 0;
            $quantity = isset($input['quantity']) ? floatval($input['quantity']) : 0;

            if ($product_id <= 0 || $quantity <= 0) {
                return ['result' => false, 'message' => 'Invalid product or quantity.'];
            }

            $stmt = $this->db->prepare("UPDATE products SET quantity_on_hand = quantity_on_hand + ? WHERE id = ?");
            if (!$stmt) {
                return ['result' => false, 'message' => 'Failed to prepare stock update.'];
            }
            $stmt->bind_param('di', $quantity, $product_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected === 0) {
                return ['result' => false, 'message' => 'No product updated.'];
            }

            return ['result' => true, 'message' => 'Stock updated successfully.'];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_save_damage()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $branch_id = intval($input['branch_id'] ?? 0);
            $cashier_id = intval($input['cashier_id'] ?? 0);
            $product_id = intval($input['product_id'] ?? 0);
            $item_name = trim($input['item_name'] ?? '');
            $quantity = floatval($input['quantity'] ?? 0);
            $description = trim($input['description'] ?? '');

            if ($branch_id <= 0 || $cashier_id <= 0) {
                return ['result' => false, 'message' => 'Invalid branch or cashier.'];
            }
            if ($item_name === '' || $quantity <= 0) {
                return ['result' => false, 'message' => 'Invalid damage item data.'];
            }

            $columnCheck = $this->db->query("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'damage_items' AND column_name = 'product_id'");
            if ($columnCheck) {
                $col = $columnCheck->fetch_assoc();
                if (intval($col['cnt']) === 0) {
                    $this->db->query("ALTER TABLE damage_items ADD COLUMN product_id INT NULL DEFAULT NULL AFTER damage_code");
                }
            }

            $this->db->begin_transaction();

            if ($product_id > 0) {
                $stockStmt = $this->db->prepare("UPDATE products SET quantity_on_hand = GREATEST(quantity_on_hand - ?, 0) WHERE id = ?");
                if (!$stockStmt) {
                    $this->db->rollback();
                    return ['result' => false, 'message' => 'Failed to prepare stock deduction.'];
                }
                $stockStmt->bind_param('di', $quantity, $product_id);
                $stockStmt->execute();
                if ($stockStmt->affected_rows === 0) {
                    $stockStmt->close();
                    $this->db->rollback();
                    return ['result' => false, 'message' => 'Product not found or insufficient stock.'];
                }
                $stockStmt->close();
            }

            $damage_code = 'DMG-' . date('YmdHis') . '-' . rand(100, 999);
            $stmt = $this->db->prepare("INSERT INTO damage_items (damage_code, product_id, item_name, quantity, description, branch_id, reported_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            if (!$stmt) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to prepare damage item insert.'];
            }

            $stmt->bind_param('sisdiii', $damage_code, $product_id, $item_name, $quantity, $description, $branch_id, $cashier_id);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                $this->db->rollback();
                return ['result' => false, 'message' => 'Error: ' . $error];
            }

            $damage_id = $this->db->insert_id;
            $stmt->close();
            $this->db->commit();
            return [
                'result' => true,
                'message' => 'Damage item recorded and inventory adjusted.',
                'damage_id' => $damage_id,
            ];
        } catch (Exception $e) {
            if ($this->db->errno === 0) {
                $this->db->rollback();
            }
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_delete_damage()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $damage_id = intval($input['damage_id'] ?? 0);

            if ($damage_id <= 0) {
                return ['result' => false, 'message' => 'Invalid damage ID.'];
            }

            $stmt = $this->db->prepare('SELECT product_id, quantity FROM damage_items WHERE id = ?');
            if (!$stmt) {
                return ['result' => false, 'message' => 'Failed to prepare damage lookup.'];
            }
            $stmt->bind_param('i', $damage_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                $stmt->close();
                return ['result' => false, 'message' => 'Failed to fetch damage item.'];
            }

            $damage = $result->fetch_assoc();
            $stmt->close();

            if (!$damage) {
                return ['result' => false, 'message' => 'Damage item not found.'];
            }

            $product_id = intval($damage['product_id'] ?? 0);
            $quantity = floatval($damage['quantity'] ?? 0);

            $this->db->begin_transaction();

            if ($product_id > 0 && $quantity > 0) {
                $stockStmt = $this->db->prepare('UPDATE products SET quantity_on_hand = quantity_on_hand + ? WHERE id = ?');
                if (!$stockStmt) {
                    $this->db->rollback();
                    return ['result' => false, 'message' => 'Failed to prepare stock restoration.'];
                }
                $stockStmt->bind_param('di', $quantity, $product_id);
                $stockStmt->execute();
                $stockStmt->close();
            }

            $deleteStmt = $this->db->prepare('DELETE FROM damage_items WHERE id = ?');
            if (!$deleteStmt) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to prepare damage delete.'];
            }
            $deleteStmt->bind_param('i', $damage_id);
            if (!$deleteStmt->execute()) {
                $error = $deleteStmt->error;
                $deleteStmt->close();
                $this->db->rollback();
                return ['result' => false, 'message' => 'Error: ' . $error];
            }
            $deleteStmt->close();

            $this->db->commit();
            return ['result' => true, 'message' => 'Damage item deleted successfully.'];
        } catch (Exception $e) {
            if ($this->db->errno === 0) {
                $this->db->rollback();
            }
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_save_owner_requisition()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $item_name = trim($input['item_name'] ?? '');
            $quantity = floatval($input['quantity'] ?? 0);
            $amount_paid = floatval($input['amount_paid'] ?? 0);
            $branch_id = intval($input['branch_id'] ?? 0);
            $description = trim($input['description'] ?? '');

            if ($item_name === '' || $quantity <= 0 || $amount_paid < 0) {
                return ['result' => false, 'message' => 'Invalid requisition data.'];
            }

            $requisition_code = 'REQ-' . date('YmdHis') . '-' . rand(100, 999);
            $stmt = $this->db->prepare("INSERT INTO owner_requisitions (requisition_code, item_name, quantity, amount_paid, branch_id, description, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            if (!$stmt) {
                return ['result' => false, 'message' => 'Failed to prepare requisition insert.'];
            }
            $stmt->bind_param('ssddis', $requisition_code, $item_name, $quantity, $amount_paid, $branch_id, $description);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                return ['result' => false, 'message' => 'Error: ' . $error];
            }

            $requisition_id = $this->db->insert_id;
            $stmt->close();
            return ['result' => true, 'message' => 'Requisition saved successfully.', 'requisition_id' => $requisition_id];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_update_owner_requisition_payment()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $requisition_id = intval($input['requisition_id'] ?? 0);
            $amount_paid = floatval($input['amount_paid'] ?? 0);

            if ($requisition_id <= 0 || $amount_paid < 0) {
                return ['result' => false, 'message' => 'Invalid payment data.'];
            }

            $stmt = $this->db->prepare('UPDATE owner_requisitions SET amount_paid = ? WHERE id = ?');
            if (!$stmt) {
                return ['result' => false, 'message' => 'Failed to prepare payment update.'];
            }
            $stmt->bind_param('di', $amount_paid, $requisition_id);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                return ['result' => false, 'message' => 'Error: ' . $error];
            }

            $stmt->close();
            return ['result' => true, 'message' => 'Payment saved successfully.'];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_delete_owner_requisition()
    {
        try {
            if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true && intval($_SESSION['login_role'] ?? 0) !== 9) {
                return ['result' => false, 'message' => 'Only cashier accounts can delete requisitions.'];
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $requisition_id = intval($input['requisition_id'] ?? 0);

            if ($requisition_id <= 0) {
                return ['result' => false, 'message' => 'Invalid requisition ID.'];
            }

            $stmt = $this->db->prepare('DELETE FROM owner_requisitions WHERE id = ?');
            if (!$stmt) {
                return ['result' => false, 'message' => 'Failed to prepare requisition delete.'];
            }
            $stmt->bind_param('i', $requisition_id);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                return ['result' => false, 'message' => 'Error: ' . $error];
            }
            $stmt->close();

            return ['result' => true, 'message' => 'Requisition deleted successfully.'];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_approve_damage()
    {
        try {
            if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true && intval($_SESSION['login_role'] ?? 0) !== 9) {
                return ['result' => false, 'message' => 'Only cashier accounts can approve damage items.'];
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $damage_id = intval($input['damage_id'] ?? 0);
            if ($damage_id <= 0) {
                return ['result' => false, 'message' => 'Invalid damage item ID.'];
            }

            $this->db->begin_transaction();
            $stmt = $this->db->prepare('SELECT status FROM damage_items WHERE id = ? FOR UPDATE');
            if (!$stmt) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to prepare damage item lookup.'];
            }
            $stmt->bind_param('i', $damage_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $damage = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$damage) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Damage item not found.'];
            }
            if (($damage['status'] ?? '') !== 'Pending') {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Only pending damage items can be approved.'];
            }

            $update = $this->db->prepare("UPDATE damage_items SET status = 'Approved' WHERE id = ? AND status = 'Pending'");
            if (!$update) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to prepare damage approval.'];
            }
            $update->bind_param('i', $damage_id);
            if (!$update->execute()) {
                $update->close();
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to approve damage item.'];
            }
            $update->close();
            $this->db->commit();

            return ['result' => true, 'message' => 'Damage item approved successfully.'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => 'Unable to approve damage item.'];
        }
    }

    function mobile_pos_update_owner_requisition_status()
    {
        try {
            if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true && intval($_SESSION['login_role'] ?? 0) !== 9) {
                return ['result' => false, 'message' => 'Only cashier accounts can approve requisitions.'];
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $requisition_id = intval($input['requisition_id'] ?? 0);
            $status = trim($input['status'] ?? '');

            if ($requisition_id <= 0 || !in_array($status, ['Approved', 'Rejected'], true)) {
                return ['result' => false, 'message' => 'Invalid requisition status update.'];
            }

            $this->db->begin_transaction();

            $stmt = $this->db->prepare("SELECT item_name, quantity, branch_id, status
                FROM owner_requisitions WHERE id = ? FOR UPDATE");
            if (!$stmt) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to prepare requisition lookup.'];
            }
            $stmt->bind_param('i', $requisition_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) {
                $stmt->close();
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to fetch requisition.'];
            }

            $requisition = $result->fetch_assoc();
            $stmt->close();

            if (!$requisition) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Requisition not found.'];
            }

            if (($requisition['status'] ?? '') !== 'Pending') {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Only pending requisitions can be updated.'];
            }

            $item_name = trim($requisition['item_name'] ?? '');
            $quantity = floatval($requisition['quantity'] ?? 0);
            $branch_id = intval($requisition['branch_id'] ?? 0);

            if ($status === 'Approved' && $item_name !== '' && $quantity > 0 && $branch_id > 0) {
                $product = $this->db->query("SELECT id, quantity_on_hand FROM products WHERE product_name = '" . $this->db->real_escape_string($item_name) . "' AND branch_id = $branch_id AND status = 1 LIMIT 1");
                if ($product && $product->num_rows > 0) {
                    $productData = $product->fetch_assoc();
                    $productId = intval($productData['id']);
                    $currentQty = floatval($productData['quantity_on_hand'] ?? 0);
                    $newQty = max($currentQty - $quantity, 0);

                    $stockStmt = $this->db->prepare('UPDATE products SET quantity_on_hand = ? WHERE id = ?');
                    if (!$stockStmt) {
                        $this->db->rollback();
                        return ['result' => false, 'message' => 'Failed to prepare inventory update.'];
                    }
                    $stockStmt->bind_param('di', $newQty, $productId);
                    if (!$stockStmt->execute()) {
                        $error = $stockStmt->error;
                        $stockStmt->close();
                        $this->db->rollback();
                        return ['result' => false, 'message' => 'Error: ' . $error];
                    }
                    $stockStmt->close();
                }
            }

            $updateStmt = $this->db->prepare("UPDATE owner_requisitions
                SET status = ? WHERE id = ? AND status = 'Pending'");
            if (!$updateStmt) {
                $this->db->rollback();
                return ['result' => false, 'message' => 'Failed to prepare status update.'];
            }
            $updateStmt->bind_param('si', $status, $requisition_id);
            if (!$updateStmt->execute()) {
                $error = $updateStmt->error;
                $updateStmt->close();
                $this->db->rollback();
                return ['result' => false, 'message' => 'Error: ' . $error];
            }
            $updateStmt->close();

            $this->db->commit();
            return ['result' => true, 'message' => 'Requisition status updated successfully.'];
        } catch (Exception $e) {
            if ($this->db->errno === 0) {
                $this->db->rollback();
            }
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function mobile_pos_update_product_price()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $product_id = isset($input['product_id']) ? intval($input['product_id']) : 0;
            $unit_price = isset($input['unit_price']) ? floatval($input['unit_price']) : null;

            if ($product_id <= 0 || $unit_price === null || $unit_price < 0) {
                return ['result' => false, 'message' => 'Invalid product or price.'];
            }

            $stmt = $this->db->prepare("UPDATE products SET unit_price = ? WHERE id = ?");
            if (!$stmt) {
                return ['result' => false, 'message' => 'Failed to prepare price update.' ];
            }
            $stmt->bind_param('di', $unit_price, $product_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected === 0) {
                return ['result' => false, 'message' => 'No product updated.'];
            }

            return ['result' => true, 'message' => 'Price updated successfully.'];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    function loginMobile()
    {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $status = 1;
            $inputJSON = file_get_contents('php://input');
            $input = json_decode($inputJSON, true);

            if ($input === null || !isset($input['username']) || !isset($input['password'])) {
                return ['result' => false, 'message' => 'Invalid data'];
            }

            $username = $input['username'];
            $password = $input['password'];

            // Fetch active user
            $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE username = ? AND users.status = ?
        ");
            $stmt->bind_param('ss', $username, $status);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows !== 1) {
                return ['result' => false, 'message' => 'No user found with the given username'];
            }

            $user = $result->fetch_assoc();
            $stored_hashed_password = $user['password'];

            if (!password_verify($password, $stored_hashed_password)) {
                return ['result' => false, 'message' => 'Password incorrect'];
            }

            $role = intval($user['role']);

            // Cashier (9) / Secretary (8) use a branch, not assigned sites
            if ($role === 8 || $role === 9) {
                $branch = null;
                if (!empty($user['branch_id'])) {
                    $branch_id = intval($user['branch_id']);
                    $branch = $this->db->query("SELECT * FROM branches WHERE id = '$branch_id'")->fetch_assoc();
                }

                // Cashier must have a branch; Secretary may operate without one
                if ($role === 9 && empty($branch)) {
                    return ['result' => false, 'message' => 'No branch assigned to you.'];
                }

                return [
                    'result' => true,
                    'user'   => $user,
                    'branch' => $branch,
                    'sites'  => [],
                ];
            }

            // Owner (10) has access to all branches and does not require active sites
            if ($role === 10) {
                return [
                    'result' => true,
                    'user'   => $user,
                    'branch' => null,
                    'sites'  => [],
                ];
            }

            // Timekeepers and PICs are assigned to a branch.
            $branch_id = intval($user['branch_id'] ?? 0);
            $qry_sites = $this->db->query("
            SELECT id, branch_code AS site_code, branch_name AS site_name,
                   address AS site_address, status
            FROM branches
            WHERE id = '$branch_id' AND status = 1
        ");

            $sites = [];
            while ($site_row = $qry_sites->fetch_assoc()) {
                $sites[] = $site_row;
            }

            if (count($sites) === 0) {
                return ['result' => false, 'message' => 'No active branch assigned to you.'];
            }

            return [
                'result' => true,
                'user'   => $user,
                'sites'  => $sites,
            ];
        } catch (mysqli_sql_exception $e) {
            return ['result' => false, 'message' => 'Database error: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['result' => false, 'message' => 'Unexpected error: ' . $e->getMessage()];
        }
    }



    function save_employee_attendance_manual()
    {
        $post = $_POST;
        $date_from =  date("Y-m-d", strtotime($post['dtr']['date_from']));
        $date_to =  date("Y-m-d", strtotime($post['dtr']['date_to']));
        $timekeeper_id =  $post['dtr']['timekeeper_id'];
        $site_id =   $post['dtr']['site_id'];
        $device_id = $post['dtr']['device_id'];
        $file =  $post['dtr']['file'];
        $local_id = $post['dtr']['id'];
        $dtr_details = $post['dtr_details'];
        $qry = $this->db->query("SELECT * FROM users WHERE id = '$timekeeper_id' AND role = 5 ");
        $user_data = $qry->fetch_assoc();
        // $site_id = $user_data['site_id'];
        $employer_id = $user_data['employer_id'];
        $qry_exist = $this->db->query("SELECT * FROM dtr WHERE date_from = '$date_from' AND date_to = '$date_to' AND site_id = '$site_id'  LIMIT 1 ");
        if ($qry_exist->num_rows > 0) {
            return ['result' => false, 'message' => 'DTR date already exist'];
        }

        $qry_site = $this->db->query("SELECT id FROM branches WHERE id = '$site_id' AND status = 1");
        if ($qry_site->num_rows === 0) {
            return ['result' => false, 'message' => 'Branch is inactive'];
        }

        $qry_site_2 = $this->db->query("SELECT id FROM users WHERE id = '$timekeeper_id' AND branch_id = '$site_id'");
        if ($qry_site_2->num_rows === 0) {
            return ['result' => false, 'message' => "You're not currently assigned to this branch. Please log in again."];
        }

        // A user can now be assigned to one branch only.

        $this->db->begin_transaction();
        try {

            if ($qry->num_rows == 0) {
                throw new Exception('User not found');
            }
            $sql = "INSERT INTO dtr (local_id, date_from, date_to, cashier_id, site_id, device_id, file, uploaded_by, employer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sssssssss', $local_id, $date_from, $date_to, $timekeeper_id, $site_id, $device_id, $file, $_SESSION['login_id'], $employer_id);
            $stmt->execute();
            $ddtr_id = '';
            if ($stmt->affected_rows == 0) {
                throw new Exception('Failed to insert data');
            } else {
                $ddtr_id = $this->db->insert_id;
            }
            foreach ($dtr_details as $k) {
                $employee_id = $k['employee_id'];
                $attendance_type = $k['type'];
                $logs = $k['logs'];
                $hours = $k['hours'] > 8 ? 8 : $k['hours'];
                $overtime = $k['ot'];
                $date_time = $k['date_time'];
                $code = $k['code'];
                $qry_bio = $this->db->query("SELECT * FROM employee_bio  WHERE employee_id = '$employee_id' AND site_id = '$site_id' AND device_id = '$device_id'
                 LIMIT 1 ");
                if ($qry_bio->num_rows == 0) {
                    $sql2 = "INSERT INTO employee_bio (employee_id, device_id, site_id, code) VALUES (?, ?, ?, ?)";
                    $stmtbio = $this->db->prepare($sql2);
                    $stmtbio->bind_param('ssss', $employee_id, $device_id, $site_id, $code);
                    try {
                        $stmtbio->execute();
                    } catch (Exception $e) {
                        throw new Exception('Failed to insert data');
                    }
                }

                    $sql2 = "INSERT INTO dtr_details (ddtr_id, employee_id, date_time, work_hours, logs, attendance_type, overtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->bind_param('sssssss', $ddtr_id, $employee_id, $date_time, $hours, $logs, $attendance_type, $overtime);
                try {
                    $stmt2->execute();
                } catch (Exception $e) {
                    throw new Exception('Failed to insert data');
                }
            }
            $this->db->commit();
            return ['result' => true, 'message' => 'Data inserted successfully', 'id' => $this->db->insert_id]; //

        } catch (Exception $e) {
            $this->db->rollback(); // Rollback on errors
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    function save_employee_attendance_mobile()
    {
        $post = json_decode(file_get_contents('php://input'), true);
        $date_from =  date("Y-m-d", strtotime($post['dtr']['date_from']));
        $date_to =  date("Y-m-d", strtotime($post['dtr']['date_to']));
        $timekeeper_id =  $post['timekeeper_id'];
        $branch_id     = isset($post['branch_id']) ? intval($post['branch_id']) : 1;

        $device_id = $post['dtr']['device_id'];
        $file =  $post['dtr']['file'];
        $local_id = $post['dtr']['id'];
        $dtr_details = $post['dtr_details'];
        $ptype = $post['dtr']['weekly_payroll'];
        $qry = $this->db->query("SELECT id FROM users WHERE id = '$timekeeper_id' AND role IN (5,9) ");

        $qry_exist = $this->db->query("SELECT * FROM dtr WHERE date_from = '$date_from' AND date_to = '$date_to' AND ptype='$ptype' AND timekeeper_id='$timekeeper_id' LIMIT 1 ");
        if ($qry_exist->num_rows > 0) {
            return ['result' => false, 'message' => 'DTR date already exist'];
        }

        $this->db->begin_transaction();
        try {
            if ($qry->num_rows == 0) {
                throw new Exception('User not found');
            }

            $stmt = $this->db->prepare("INSERT INTO dtr (local_id, date_from, date_to, timekeeper_id, branch_id, device_id, file, uploaded_by, ptype) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) throw new Exception('DTR prepare failed: ' . $this->db->error);
            $stmt->bind_param('sssssssss', $local_id, $date_from, $date_to, $timekeeper_id, $branch_id, $device_id, $file, $timekeeper_id, $ptype);
            if (!$stmt->execute()) throw new Exception('DTR insert failed: ' . $stmt->error);
            if ($stmt->affected_rows == 0) throw new Exception('DTR insert affected 0 rows');
            $ddtr_id = $stmt->insert_id;

            if (empty($dtr_details)) {
                throw new Exception('No DTR details received');
            }

            foreach ($dtr_details as $k) {
                $employee_id    = $k['employee_id'];
                $attendance_type = $k['type'];
                $logs           = is_array($k['logs']) ? json_encode($k['logs']) : $k['logs'];
                $hours          = $k['hours'] > 8 ? 8 : $k['hours'];
                $overtime       = $k['ot'];
                $notes          = $k['notes'] ?? '';
                $date_time      = $k['date_time'];
                $code           = $k['code'];

                // upsert employee_bio
                $qry_bio = $this->db->query("SELECT id FROM employee_bio WHERE employee_id='$employee_id' AND device_id='$device_id' LIMIT 1");
                if ($qry_bio->num_rows == 0) {
                    $stmtbio = $this->db->prepare("INSERT INTO employee_bio (employee_id, device_id, site_id, code) VALUES (?, ?, ?, ?)");
                    if ($stmtbio) {
                        $stmtbio->bind_param('ssss', $employee_id, $device_id, $branch_id, $code);
                        $stmtbio->execute();
                    }
                }

                $stmt2 = $this->db->prepare("INSERT INTO dtr_details (ddtr_id, employee_id, date_time, work_hours, logs, attendance_type, overtime, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt2) throw new Exception('DTR_details prepare failed: ' . $this->db->error);
                $stmt2->bind_param('ssssssss', $ddtr_id, $employee_id, $date_time, $hours, $logs, $attendance_type, $overtime, $notes);
                if (!$stmt2->execute()) throw new Exception('DTR_details insert failed for employee ' . $employee_id . ': ' . $stmt2->error);
            }

            $this->db->commit();
            return ['result' => true, 'message' => 'Data inserted successfully', 'id' => $ddtr_id];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    function upload_biometric_dtr()
    {
        $user_id = isset($_SESSION['login_id']) ? intval($_SESSION['login_id']) : 0;
        $login_role = isset($_SESSION['login_role']) ? intval($_SESSION['login_role']) : 0;
        $branch_id = isset($_SESSION['login_branch_id']) ? intval($_SESSION['login_branch_id']) : 0;

        if ($user_id <= 0) {
            return ['result' => false, 'message' => 'Please login again.'];
        }

        if (!in_array($login_role, [8], true)) {
            return ['result' => false, 'message' => 'You are not allowed to upload biometric attendance.'];
        }

        if (!isset($_FILES['fileBiometric']) || $_FILES['fileBiometric']['error'] !== UPLOAD_ERR_OK) {
            return ['result' => false, 'message' => 'No biometric file was uploaded or the upload failed.'];
        }

        $file = $_FILES['fileBiometric'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'dat') {
            return ['result' => false, 'message' => 'Only .dat files are accepted.'];
        }

        $content = @file_get_contents($file['tmp_name']);
        if ($content === false) {
            return ['result' => false, 'message' => 'Unable to read the uploaded file.'];
        }

        $content = trim($content);
        if ($content === '') {
            return ['result' => false, 'message' => 'The uploaded file is empty.'];
        }

        $lines = preg_split('/\r\n|\r|\n/', $content);
        $parsed = [];
        $invalid_count = 0;
        $unknown_codes = [];
        $imported_count = 0;
        $date_from = null;
        $date_to = null;

        $file_branch_id = null;
        $file_device_id = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/[\t,]+/', $line);
            if (count($parts) < 3) {
                $invalid_count++;
                continue;
            }

            $code = trim($parts[0]);
            $date_time_str = trim($parts[1]);
            // Standard biometric DAT files store verify mode in column 3 and
            // attendance state (0 = In, 1 = Out) in column 4.
            $is_standard_dat = count($parts) >= 4 && in_array(trim($parts[3]), ['0', '1'], true);
            $attendance_type = $is_standard_dat
                ? trim($parts[3])
                : trim($parts[2]);

            if (!$is_standard_dat && $file_branch_id === null && count($parts) >= 4 && ctype_digit(trim($parts[3]))) {
                $file_branch_id = trim($parts[3]);
            }
            if (!$is_standard_dat && $file_device_id === null && count($parts) >= 5 && trim($parts[4]) !== '') {
                $file_device_id = trim($parts[4]);
            }

            if ($code === '' || $date_time_str === '') {
                $invalid_count++;
                continue;
            }

            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date_time_str);
            if (!$dateTime) {
                $timestamp = strtotime($date_time_str);
                if ($timestamp === false) {
                    $invalid_count++;
                    continue;
                }
                $dateTime = new DateTime();
                $dateTime->setTimestamp($timestamp);
            }

            if ($attendance_type === '0') {
                $attendance_type = 'In';
            } elseif ($attendance_type === '1') {
                $attendance_type = 'Out';
            }

            $parsed[] = [
                'code' => $code,
                'date_time' => $dateTime,
                'attendance_type' => $attendance_type,
                'file_branch_id' => $file_branch_id,
                'file_device_id' => $file_device_id,
            ];

            $ymd = $dateTime->format('Y-m-d');
            if ($date_from === null || $ymd < $date_from) {
                $date_from = $ymd;
            }
            if ($date_to === null || $ymd > $date_to) {
                $date_to = $ymd;
            }
        }

        if (empty($parsed)) {
            return ['result' => false, 'message' => 'No valid biometric log lines were found in the uploaded file.'];
        }

        if ($login_role === 9 && $branch_id <= 0) {
            return ['result' => false, 'message' => 'Your account is not assigned to a branch.'];
        }

        if ($file_branch_id !== null && ctype_digit($file_branch_id)) {
            $branch_id = intval($file_branch_id);
        } elseif ($branch_id <= 0) {
            foreach ($parsed as $row) {
                if (!empty($row['file_branch_id']) && ctype_digit($row['file_branch_id'])) {
                    $branch_id = intval($row['file_branch_id']);
                    break;
                }
            }
        }

        $branch_id = $branch_id > 0 ? $branch_id : 0;
        $local_id = $branch_id;
        $uploaded_file = 'data:text/plain;base64,' . base64_encode($content);
        $device_id = isset($_POST['device_id']) ? trim($_POST['device_id']) : '';
        if ($file_device_id !== null && $file_device_id !== '') {
            $device_id = $file_device_id;
        } elseif ($device_id === '') {
            foreach ($parsed as $row) {
                if (!empty($row['file_device_id'])) {
                    $device_id = trim($row['file_device_id']);
                    break;
                }
            }
        }
        $device_id = $device_id === '' ? '0' : $device_id;

        $this->db->begin_transaction();
        try {
            $check_dup = $this->db->prepare("SELECT id FROM dtr WHERE date_from = ? AND date_to = ? AND branch_id = ? AND device_id = ? LIMIT 1");
            if ($check_dup) {
                $check_dup->bind_param('ssis', $date_from, $date_to, $branch_id, $device_id);
                $check_dup->execute();
                $result_dup = $check_dup->get_result();
                if ($result_dup && $result_dup->num_rows > 0) {
                    throw new Exception('A biometric upload with this date range and device already exists.');
                }
            }

            $stmt = $this->db->prepare("INSERT INTO dtr (local_id, date_from, date_to, timekeeper_id, branch_id, device_id, file, uploaded_by, approved_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)");
            if (!$stmt) {
                throw new Exception('Failed to prepare DTR insert: ' . $this->db->error);
            }
            $status = 1;
            $stmt->bind_param('sssissssi', $local_id, $date_from, $date_to, $user_id, $branch_id, $device_id, $uploaded_file, $user_id, $status);
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert DTR: ' . $stmt->error);
            }
            $ddtr_id = $stmt->insert_id;

            $insert_details = $this->db->prepare("INSERT INTO dtr_details (ddtr_id, employee_id, date_time, work_hours, logs, attendance_type, overtime, undertime, late, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$insert_details) {
                throw new Exception('Failed to prepare DTR details insert: ' . $this->db->error);
            }

            $detail_keyset = [];
            $employee_bio_query = $this->db->prepare("SELECT employee_id, device_id, site_id FROM employee_bio WHERE TRIM(code) = ? LIMIT 1");

            foreach ($parsed as $row) {
                $code_param = trim($row['code']);
                $result_bio = null;

                if ($employee_bio_query) {
                    $employee_bio_query->bind_param('s', $code_param);
                    if (!$employee_bio_query->execute()) {
                        throw new Exception('Failed to query employee bio: ' . $employee_bio_query->error);
                    }
                    $result_bio = $employee_bio_query->get_result();
                }

                if (($result_bio === null || $result_bio->num_rows === 0) && $branch_id > 0) {
                    $fallback = $this->db->prepare("SELECT employee_id, device_id, site_id FROM employee_bio WHERE TRIM(code) = ? AND site_id = ? LIMIT 1");
                    if ($fallback) {
                        $fallback->bind_param('si', $code_param, $branch_id);
                        $fallback->execute();
                        $result_bio = $fallback->get_result();
                    }
                }

                $bio = null;
                if ($result_bio && $result_bio->num_rows > 0) {
                    $bio = $result_bio->fetch_assoc();
                }

                $is_employee_number = preg_match('/^[\d-]+$/', $code_param);
                $is_numeric_id = preg_match('/^[0-9]+$/', $code_param);

                if ($bio !== null) {
                    $employee_id = intval($bio['employee_id']);
                    $emp_check = $this->db->prepare("SELECT id FROM employee WHERE id = ? LIMIT 1");
                    $emp_check->bind_param('i', $employee_id);
                    $emp_check->execute();
                    $emp_check_result = $emp_check->get_result();
                    if ($emp_check_result->num_rows === 0) {
                        $bio = null;
                    }
                }

                if ($bio === null && $is_employee_number) {
                    $direct_emp_stmt = $this->db->prepare("SELECT id, id as employee_id FROM employee WHERE employee_no = ? LIMIT 1");
                    if ($direct_emp_stmt) {
                        $emp_no_param = $code_param;
                        $direct_emp_stmt->bind_param('s', $emp_no_param);
                        $direct_emp_stmt->execute();
                        $result_direct_emp = $direct_emp_stmt->get_result();
                        if ($result_direct_emp && $result_direct_emp->num_rows > 0) {
                            $bio = $result_direct_emp->fetch_assoc();
                        } else {
                            $normalized_emp_no = ltrim($code_param, '0');
                            if ($normalized_emp_no === '') {
                                $normalized_emp_no = '0';
                            }
                            if ($normalized_emp_no !== $code_param) {
                                $direct_emp_stmt = $this->db->prepare("SELECT id, id as employee_id FROM employee WHERE employee_no = ? LIMIT 1");
                                if ($direct_emp_stmt) {
                                    $direct_emp_stmt->bind_param('s', $normalized_emp_no);
                                    $direct_emp_stmt->execute();
                                    $result_direct_emp = $direct_emp_stmt->get_result();
                                    if ($result_direct_emp && $result_direct_emp->num_rows > 0) {
                                        $bio = $result_direct_emp->fetch_assoc();
                                    }
                                }
                            }
                        }
                    }
                }

                if ($bio === null && $is_numeric_id) {
                    $direct_emp_stmt = $this->db->prepare("SELECT id, id as employee_id FROM employee WHERE id = ? LIMIT 1");
                    if ($direct_emp_stmt) {
                        $emp_id_param = intval($code_param);
                        $direct_emp_stmt->bind_param('i', $emp_id_param);
                        $direct_emp_stmt->execute();
                        $result_direct_emp = $direct_emp_stmt->get_result();
                        if ($result_direct_emp && $result_direct_emp->num_rows > 0) {
                            $bio = $result_direct_emp->fetch_assoc();
                        }
                    }
                }

                if ($bio === null) {
                    $unknown_codes[] = $row['code'];
                    continue;
                }

                $employee_id = intval($bio['employee_id']);

                // Persist fallback matches so the same biometric code is not
                // reported as unmatched on the next upload.
                if ($branch_id > 0 || ($device_id !== '0' && $device_id !== '')) {
                    $map_site_id = $branch_id > 0 ? $branch_id : intval($bio['site_id'] ?? 0);
                    $map_device_id = $device_id !== '0' && $device_id !== ''
                        ? $device_id
                        : (string)($bio['device_id'] ?? '0');
                    $map = $this->db->prepare(
                        "SELECT id FROM employee_bio WHERE employee_id = ? AND TRIM(code) = ? AND site_id = ? LIMIT 1"
                    );
                    if ($map) {
                        $map->bind_param('isi', $employee_id, $code_param, $map_site_id);
                        $map->execute();
                        $map_result = $map->get_result();
                        if (!$map_result || $map_result->num_rows === 0) {
                            $insert_map = $this->db->prepare(
                                "INSERT INTO employee_bio (employee_id, device_id, site_id, code) VALUES (?, ?, ?, ?)"
                            );
                            if ($insert_map) {
                                $insert_map->bind_param('isis', $employee_id, $map_device_id, $map_site_id, $code_param);
                                $insert_map->execute();
                            }
                        }
                    }
                }
                
                if ($device_id === '0' || $device_id === '') {
                    $device_id = $bio['device_id'];
                }

                $date_key = $employee_id . '|' . $row['date_time']->format('Y-m-d H:i:s') . '|' . strtolower($row['attendance_type']);
                if (isset($detail_keyset[$date_key])) {
                    continue;
                }
                $detail_keyset[$date_key] = true;

                $date_time_value = $row['date_time']->format('Y-m-d');
                $logs = json_encode([[ 'dateTime' => $row['date_time']->format('Y-m-d H:i:s'), 'type' => 'bio' ]]);
                $work_hours = 0;
                $overtime = 0.0;
                $undertime = 0.0;
                $late = 0.0;
                $detail_status = 0;

                $insert_details->bind_param(
                    'iisdssdddi',
                    $ddtr_id,
                    $employee_id,
                    $date_time_value,
                    $work_hours,
                    $logs,
                    $attendance_type,
                    $overtime,
                    $undertime,
                    $late,
                    $detail_status
                );

                if (!$insert_details->execute()) {
                    throw new Exception('Failed to insert DTR detail: ' . $insert_details->error);
                }
                $imported_count++;
            }

            if ($imported_count === 0) {
                throw new Exception('No valid biometric employee codes were found in the uploaded file.');
            }

            if ($device_id !== '0' && $device_id !== '') {
                $update_device = $this->db->prepare("UPDATE dtr SET device_id = ? WHERE id = ?");
                if ($update_device) {
                    $update_device->bind_param('si', $device_id, $ddtr_id);
                    $update_device->execute();
                }
            }

            $unique_unknown_codes = array_values(array_unique($unknown_codes));
            $skipped_count = $invalid_count + count($unique_unknown_codes);

            try {
                $audit = $this->db->prepare("INSERT INTO pos_audit_log (table_name, record_id, action, old_data, new_data, user_id) VALUES (?, ?, ?, ?, ?, ?)");
                if ($audit) {
                    $table_name = 'dtr';
                    $action = 'Biometric upload';
                    $old_data = null;
                    $new_data = json_encode(['uploaded_by' => $user_id, 'branch_id' => $branch_id, 'date_from' => $date_from, 'date_to' => $date_to, 'imported' => $imported_count]);
                    $audit->bind_param('sisssi', $table_name, $ddtr_id, $action, $old_data, $new_data, $user_id);
                    $audit->execute();
                }
            } catch (Exception $ignored) {
            }

            $this->db->commit();

            return [
                'result' => true,
                'message' => 'Biometric upload completed successfully.',
                'id' => $ddtr_id,
                'imported_count' => $imported_count,
                'skipped_count' => $skipped_count,
                'unknown_codes' => $unique_unknown_codes,
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }


    ////https://chatgpt.com/c/67bd10fb-c7f8-800f-9ed4-c429be8e50fe
    function getSSSMonthlyDeduction($monthly_salary)
    {
        // Define the 2025 SSS Contribution Table (MSC brackets and EE share)
        $sss_brackets = [
            ["range" => [5000, 5499.99], "monthly_employee" => 250],
            ["range" => [5500, 5999.99], "monthly_employee" => 275],
            ["range" => [6000, 6499.99], "monthly_employee" => 300],
            ["range" => [6500, 6999.99], "monthly_employee" => 325],
            ["range" => [7000, 7499.99], "monthly_employee" => 350],
            ["range" => [7500, 7999.99], "monthly_employee" => 375],
            ["range" => [8000, 8499.99], "monthly_employee" => 400],
            ["range" => [8500, 8999.99], "monthly_employee" => 425],
            ["range" => [9000, 9499.99], "monthly_employee" => 450],
            ["range" => [9500, 9999.99], "monthly_employee" => 475],
            ["range" => [10000, 10499.99], "monthly_employee" => 500],
            ["range" => [10500, 10999.99], "monthly_employee" => 525],
            ["range" => [11000, 11499.99], "monthly_employee" => 550],
            ["range" => [11500, 11999.99], "monthly_employee" => 575],
            ["range" => [12000, 12499.99], "monthly_employee" => 600],
            ["range" => [12500, 12999.99], "monthly_employee" => 625],
            ["range" => [13000, 13499.99], "monthly_employee" => 650],
            ["range" => [13500, 13999.99], "monthly_employee" => 675],
            ["range" => [14000, 14499.99], "monthly_employee" => 700],
            ["range" => [14500, 14999.99], "monthly_employee" => 725],
            ["range" => [15000, 15499.99], "monthly_employee" => 750],
            ["range" => [15500, 15999.99], "monthly_employee" => 775],
            ["range" => [16000, 16499.99], "monthly_employee" => 800],
            ["range" => [16500, 16999.99], "monthly_employee" => 825],
            ["range" => [17000, 17499.99], "monthly_employee" => 850],
            ["range" => [17500, 17999.99], "monthly_employee" => 875],
            ["range" => [18000, 18499.99], "monthly_employee" => 900],
            ["range" => [18500, 18999.99], "monthly_employee" => 925],
            ["range" => [19000, 19499.99], "monthly_employee" => 950],
            ["range" => [19500, 19999.99], "monthly_employee" => 975],
            ["range" => [20000, 20499.99], "monthly_employee" => 1000],
            ["range" => [20500, 20999.99], "monthly_employee" => 1025],
            ["range" => [21000, 21499.99], "monthly_employee" => 1050],
            ["range" => [21500, 21999.99], "monthly_employee" => 1075],
            ["range" => [22000, 22499.99], "monthly_employee" => 1100],
            ["range" => [22500, 22999.99], "monthly_employee" => 1125],
            ["range" => [23000, 23499.99], "monthly_employee" => 1150],
            ["range" => [23500, 23999.99], "monthly_employee" => 1175],
            ["range" => [24000, 24499.99], "monthly_employee" => 1200],
            ["range" => [24500, 24999.99], "monthly_employee" => 1225],
            ["range" => [25000, 25499.99], "monthly_employee" => 1250],
            ["range" => [25500, 25999.99], "monthly_employee" => 1275],
            ["range" => [26000, 26499.99], "monthly_employee" => 1300],
            ["range" => [26500, 26999.99], "monthly_employee" => 1325],
            ["range" => [27000, 27499.99], "monthly_employee" => 1350],
            ["range" => [27500, 27999.99], "monthly_employee" => 1375],
            ["range" => [28000, 28499.99], "monthly_employee" => 1400],
            ["range" => [28500, 28999.99], "monthly_employee" => 1425],
            ["range" => [29000, 29499.99], "monthly_employee" => 1450],
            ["range" => [29500, 29999.99], "monthly_employee" => 1475],
            ["range" => [30000, 34999.99], "monthly_employee" => 1500],
            ["range" => [35000, PHP_INT_MAX], "monthly_employee" => 1750] // Maximum MSC
        ];

        // Find the appropriate bracket
        foreach ($sss_brackets as $bracket) {
            if ($monthly_salary >= $bracket["range"][0] && $monthly_salary <= $bracket["range"][1]) {
                return $bracket["monthly_employee"];
            }
        }

        // Default return 0 if no bracket is matched
        return 0;
    }

    function calculatePhilHealth($monthly_salary)
    {
        // Minimum and Maximum Salary Brackets
        $min_salary = 12000;
        $max_salary = 50000;
        $rate = 0.05; // 5% PhilHealth rate
        $max_contribution = 1250; // Max contribution cap at ₱50,000

        // If salary is below the minimum, apply the lowest contribution
        if ($monthly_salary <= $min_salary) {
            return 300; // ₱12,000 salary = ₱300 PhilHealth
        }

        // If salary is above the maximum, apply the highest contribution
        if ($monthly_salary >= $max_salary) {
            return $max_contribution;
        }

        // Compute PhilHealth Contribution: (Salary × 5%) ÷ 2 (shared by employer & employee)
        $contribution = ($monthly_salary * $rate) / 2;

        return round($contribution, 2);
    }


    function getSSSWeeklyDeduction($weekly_salary)
    {
        // Define the updated 2025 MSC brackets and employee contributions
        $sss_brackets = [
            ["range" => [7800, 8499.99], "monthly_employee" => 400],
            ["range" => [8500, 8999.99], "monthly_employee" => 425],
            ["range" => [9000, 9499.99], "monthly_employee" => 450],
            ["range" => [9500, 9999.99], "monthly_employee" => 475],
            ["range" => [10000, 10499.99], "monthly_employee" => 500],
            ["range" => [10500, 10999.99], "monthly_employee" => 525],
            ["range" => [11000, 11499.99], "monthly_employee" => 550],
            ["range" => [11500, 11999.99], "monthly_employee" => 575],
            ["range" => [12000, PHP_INT_MAX], "monthly_employee" => 600]
        ];

        // Convert weekly salary to monthly equivalent (assuming 4.33 weeks in a month)
        $monthly_salary = $weekly_salary; //* 4.33

        // Find the appropriate bracket
        foreach ($sss_brackets as $bracket) {
            if ($monthly_salary >= $bracket["range"][0] && $monthly_salary <= $bracket["range"][1]) {
                // Convert monthly employee share to weekly
                return round($bracket["monthly_employee"] / 4.33, 2);
            }
        }

        // Default return 0 if no bracket is matched
        return 0;
    }

    function calculatePhilHealthWeekly($monthly_salary)
    {
        // Define 2024 PhilHealth Rates
        $rate = 0.05; // 5% total contribution
        $employee_share = 0.025; // 2.5% EE share
        $employer_share = 0.025; // 2.5% ER share
        $min_salary = 10000; // Minimum salary for PhilHealth
        $max_salary = 100000; // Maximum salary cap for PhilHealth

        // Apply salary limits
        if ($monthly_salary < $min_salary) {
            $monthly_salary = $min_salary; // Apply minimum base salary
        } elseif ($monthly_salary > $max_salary) {
            $monthly_salary = $max_salary; // Apply maximum base salary
        }

        // Compute Contributions
        $total_contribution = $monthly_salary * $rate; // 5% of salary
        $ee_contribution = $total_contribution * $employee_share / $rate; // 2.5%
        $er_contribution = $total_contribution * $employer_share / $rate; // 2.5%
        return $total_contribution;
        // Return results as an array
        // return [
        //     'total' => round($total_contribution, 2),
        //     'ee' => round($ee_contribution, 2),
        //     'er' => round($er_contribution, 2)
        // ];
    }

    //  calcute tax https://chatgpt.com/c/67c55173-83e0-800f-b6a2-58fa42f159db
    function calculate_payroll()
    {
        try {
        $id = $this->db->real_escape_string($_POST['id'] ?? 0);
        $type = isset($_POST['type']) ? $this->db->real_escape_string($_POST['type']) : '';
        $recalculate = isset($type) ? true : false;

        $payResult = $this->db->query("SELECT * FROM payroll WHERE id = " . (int)$id);
        if (!$payResult || $payResult->num_rows === 0) {
            return ['result' => false, 'message' => 'Payroll not found (id=' . $id . ')'];
        }
        $pay = $payResult->fetch_array();

        $this->db->begin_transaction();
        $site_ids_string = $pay['site_ids'];
        $weekly_payroll =  $pay['type'] == 5 ? 0 : 1;
        $site_ids = json_decode($site_ids_string, true);
        if (empty($site_ids)) {
            return ['result' => false, 'message' => 'No branch assigned to this payroll.'];
        }
        $commaSeparatedSites = implode(',', array_map('intval', $site_ids));
        $settings = json_decode($pay['settings'], true);

        if ($recalculate) {
            $this->db->query("DELETE FROM payroll_items where payroll_id = " . $id);
            $this->db->query("DELETE FROM loan_history where payroll_id = " . $id);
            $this->save_payroll_history($id, 3);
        } else {
            $this->save_payroll_history($id, 2);
        }


        try {
            // Construct the SQL query with the site IDs directly included
            $sql = "SELECT dtr_details.*, employee.salary, employee.allowance_rate, employee.sss_fund, employee.basic_pay, employee.ot_rate, employee.isAutoDeduct, employee.loan_id, employee.loan_deduction, employee.loan, dtr.branch_id
                FROM dtr_details
                INNER JOIN dtr ON dtr.id = dtr_details.ddtr_id
                INNER JOIN employee ON dtr_details.employee_id = employee.id
                WHERE date(dtr_details.date_time) BETWEEN ? AND ? AND dtr.status = 2
                AND dtr.branch_id IN ($commaSeparatedSites) AND employee.weekly_payroll=$weekly_payroll";

            $stmt = $this->db->prepare($sql);
            // Bind the date parameters only
            $date_from = date("Y-m-d", strtotime($pay['date_from']));
            $date_to = date("Y-m-d", strtotime($pay['date_to']));
            $stmt->bind_param("ss", $date_from, $date_to);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $grouped_data = [];
                $ipresent = 0;
                $employeeCount = [];
                foreach ($result as $row) {
                    $employee_id = $row["employee_id"];
                    $isAutoDeduct = $row["isAutoDeduct"];
                    $sss_fund = $row["sss_fund"];
                    $allowance_rate = $row["allowance_rate"];
                    $site_id = $row['branch_id'];
                    // Check if the employee_id already exists in the count array
                    if (isset($employeeCount[$employee_id])) {
                        // If it exists, increment the count
                        $employeeCount[$employee_id]++;
                    } else {
                        // If it doesn't exist, initialize the count to 1
                        $employeeCount[$employee_id] = 1;
                    }

                    // Cap hours at 8 (1 day)
                    $work_hours = floor($row["work_hours"]) >= 8 ? 8 : $row["work_hours"];

                    // Convert to days using your special rules
                    if ($work_hours == 8) {
                        $days = 1;
                    } elseif ($work_hours == 4.5625) {
                        $days = 0.5625;
                    } else {
                        $days = $work_hours / 8;
                    }

                    $under_time = max(0, 8 - $work_hours);
                    $per_day = $row['salary'];
                    $basic_pay = $row['basic_pay'];
                    $per_hour = $per_day / 8;
                    $minutesPerDay = 8 * 60;
                    $per_minute = round($per_day / $minutesPerDay, 2);
                    $salary = $work_hours * $per_hour;
                    // var_dump($employee_id .' ====' . $per_day);
                    // If the id is not already a key in the array, initialize the work_hours and pay
                    if (!array_key_exists($employee_id, $grouped_data)) {
                        $grouped_data[$employee_id] = [
                            "total_hours" => 0,
                            "salary" => 0,
                            "present" => 0,
                            "per_minute" => 0,
                            "overtime" => 0,
                            "late_in_minutes" => 0,
                            "undertime" => 0,
                        ];
                        $ipresent++;
                    }
                    $grouped_data[$employee_id]["under_time"] += $under_time;

                    // Add the work hours and pay to the total for the current employee
                    $grouped_data[$employee_id]["total_hours"] += $work_hours;
                    $grouped_data[$employee_id]["salary"] += $salary;
                    $grouped_data[$employee_id]["basic_pay"] = $row['basic_pay'];
                    $grouped_data[$employee_id]["ot_rate"] = $row['ot_rate'];
                    $grouped_data[$employee_id]["sss_fund"] = $row["sss_fund"];
                    $grouped_data[$employee_id]["per_minute"] = $per_minute;
                    $grouped_data[$employee_id]["per_day"] = $per_day;
                    $grouped_data[$employee_id]["present"] += $days;
                    $grouped_data[$employee_id]["overtime"] += $row['overtime'];
                    $grouped_data[$employee_id]["late_in_minutes"] += $row['late'];
                    $grouped_data[$employee_id]["undertime"] += $row['undertime'];
                    $grouped_data[$employee_id]["isAutoDeduct"]  =  $isAutoDeduct;
                    $grouped_data[$employee_id]["site_id"]  = $site_id;
                    $grouped_data[$employee_id]["sss_fund"]  = $sss_fund;
                    $grouped_data[$employee_id]["allowance_amount"]  = $allowance_rate;
                    $grouped_data[$employee_id]["date_time"]  = $row['date_time'];
                }
                foreach ($grouped_data as $employee_id => $data) {
                    $last_attendance = $data['date_time'];
                    $sql2 = "SELECT dtr_details.*, dtr.branch_id
                            FROM dtr_details
                            INNER JOIN dtr ON dtr.id = dtr_details.ddtr_id
                            INNER JOIN employee ON dtr_details.employee_id = employee.id
                            WHERE date(dtr_details.date_time) BETWEEN ? AND ? AND dtr.status = 2 AND dtr.branch_id NOT IN ($commaSeparatedSites)
                            AND employee.weekly_payroll=$weekly_payroll AND dtr_details.employee_id = $employee_id ORDER BY dtr_details.date_time DESC
                            ";
                    $stmt2 = $this->db->prepare($sql2);
                    $stmt2->bind_param("ss", $date_from, $date_to);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    $data__details = [];
                    if ($result2->num_rows > 0) {
                        foreach ($result2 as $row2) {
                            $work_hours2 = floor($row2["work_hours"]) >= 8 ? 8 : $row2["work_hours"];
                            $data__details[] = [
                                "site_id" => $row2["branch_id"],
                                "date_time" => $row2["date_time"],
                                "work_hours" => $work_hours2,
                                "overtime" => $row2["overtime"],
                                "undertime" => $row2["undertime"],
                                "present" => $row2["present"],
                                "late" => $row2["late"],
                            ];
                        }
                        // compare attendance last and other cluster
                        $date1 = strtotime($last_attendance);
                        $date2 = strtotime($data__details[0]["date_time"]);
                        if ($date2 < $date1) {
                            $date1 = strtotime($last_attendance);
                            $date2 = strtotime($data__details[0]["date_time"]);
                            foreach ($data__details as $data__detail) {
                                $data['total_hours'] += $data__detail['work_hours'];
                                $data['overtime'] += $data__detail['overtime'];
                                $data['undertime'] += $data__detail['undertime'];
                                $data['late_in_minutes'] += $data__detail['late'];
                                $data['present'] += $data__detail['work_hours'] / 8;
                            }
                        } else {
                            continue;
                        }
                    }



                    $contribute_amount = 0;
                    // get deductions 
                    $deduction_amount =  0;
                    $deductions = [];
                    $contributions = [];
                    $loans = [];
                    $loans = [];
                    $refunds = [];
                    foreach ($settings as $setting) {
                        if ($setting['type'] == 1) {
                            // Benefit contributions are controlled by the employee's Auto Deductions switch.
                            if ((int) ($data['isAutoDeduct'] ?? 0) !== 1) {
                                continue;
                            }
                            $contibution_id = $setting['id'];
                            $query = "SELECT * FROM employee_contributions WHERE employee_id = ? AND contribution_id = ? ";
                            $stmt = $this->db->prepare($query);
                            $stmt->bind_param("is", $employee_id,  $contibution_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                //check if auto deduct and sss
                                // if ($row['contribution_id'] === 1 &&  $data['isAutoDeduct']) {
                                //     if ($weekly_payroll === 1) {
                                //         $sss_amount = $this->getSSSWeeklyDeduction($data['basic_pay']);
                                //     } else {
                                //         $sss_amount = $this->getSSSMonthlyDeduction($data['basic_pay']);
                                //     }
                                //     $contribute_amount += $sss_amount;
                                //     $contributions[] = ["amount" => $sss_amount, "contribution_id" => 1];
                                // } else {
                                //     $contribute_amount += $row['amount'];
                                //     $contributions[] = ["amount" => $row['amount'], "contribution_id" => $row['contribution_id']];
                                // }
                                $contribute_amount += $row['amount'];
                                $contributions[] = ["amount" => $row['amount'], "contribution_id" => (int)  $row['contribution_id']];
                            }
                        }
                        if ($setting['type'] == 2) {
                            $deduction_id = $setting['id'];
                            $query = "SELECT * FROM employee_deductions
                                      WHERE employee_id = ? AND deduction_id = ?
                                      AND (effective_date IS NULL OR date(effective_date) <= ?)
                                      ORDER BY effective_date DESC, id DESC";
                            $stmt = $this->db->prepare($query);
                            $stmt->bind_param("iis", $employee_id, $deduction_id, $date_to);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $deduction_amount += $row['amount'];
                                $deductions[] = ["amount" => $row['amount'], "deduction_id" => (int)  $row['deduction_id'], "type" => 1];
                            }
                        }

                        if ($setting['type'] == 3) {
                            $clt_id = (int)  $setting['id'];
                            $query = "SELECT * FROM loans
                                      WHERE employee_id = ? AND loan_type = ?
                                      AND loan_status = 0 AND loan_balance > 0
                                      ORDER BY loan_date ASC, loan_id ASC";
                            $stmt = $this->db->prepare($query);
                            $stmt->bind_param("is", $employee_id, $clt_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $balance = (float) $row['loan_balance'];
                                $damount = (float)  $row['damount'];
                                if ($balance < $damount) {
                                    $damount = $balance;
                                }
                                $deduction_amount += $damount;
                                $loans[] = [
                                    "amount" => $damount,
                                    "loan_id" => (int) $row['loan_id'],
                                    "deduction_id" => $row['loan_type'],
                                    "type" => 2
                                ];
                            }
                        }

                        if ($setting['type'] == 4) {
                            $refunds[] = ["amount" => 0, "refund_id" => (int)  $setting['id']];
                        }
                    }
                    $contributions = json_encode($contributions);
                    $deductions = json_encode($deductions);
                    $loans = json_encode($loans);
                    $refunds = json_encode($refunds);
                    $payroll_id = $id;
                    // inser loan table
                    $salary = $data['salary'];
                    $total_hours = $data['total_hours'];
                    $under_time = $data['under_time'];
                    $late = $data['late_in_minutes'];
                    $present = $data['present'];
                    $sss_fund = $data['sss_fund'];
                    $per_minute = number_format($data['per_minute'], 2);
                    $per_day = $data['per_day'];
                    $ot_rate = $data['ot_rate'];
                    $ssite_id = $data['site_id'];
                    $basic_pay = $data['basic_pay'];
                    $ot = $data['overtime'];
                    $allowance_amount = $data['allowance_amount'];

                    $sql2 = "INSERT INTO payroll_items 
                    (payroll_id, employee_id, salary, allowance_amount, contribute_amount, 
                     deduction_amount, deductions, contributions, total_hours, 
                     per_day, under_time, late, present, ot_rate, per_minute, ot, site_id, loans,basic_pay,sss_fund,refunds) 
                 VALUES (?, ?, ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt2 = $this->db->prepare($sql2);
                    if (!$stmt2) {
                        throw new Exception('Failed to prepare statement: ' . $this->db->error);
                    }

                    $stmt2->bind_param(
                        'sssssssssssssssssssss',
                        $payroll_id,
                        $employee_id,
                        $salary,
                        $allowance_amount,
                        $contribute_amount,
                        $deduction_amount,
                        $deductions,
                        $contributions,
                        $total_hours,
                        $per_day,
                        $under_time,
                        $late,
                        $present,
                        $ot_rate,
                        $per_minute,
                        $ot,
                        $ssite_id,
                        $loans,
                        $basic_pay,
                        $sss_fund,
                        $refunds
                    );

                    try {
                        if (!$stmt2->execute()) {
                            throw new Exception('Failed to execute statement: ' . $stmt2->error);
                        }
                    } catch (Exception $e) {
                        error_log($e->getMessage()); // Logs error for debugging
                        throw new Exception('Failed to insert data: ' . $e->getMessage());
                    } finally {
                        // $stmt2->close(); // Ensure the statement is closed to free resources
                    }

                    $query_update = "UPDATE payroll SET status = ? WHERE id = ?";
                    $stmt3 = $this->db->prepare($query_update);
                    if ($stmt3 === false) {
                        throw new Exception('Failed to prepare the statement: ' . $this->db->error);
                    }
                    $status = 1;
                    $id = $pay['id'];
                    $stmt3->bind_param("ii", $status, $id);
                    try {
                        $stmt3->execute();
                    } catch (Exception $e) {
                        throw new Exception('Failed to update data: ' . $e->getMessage());
                    }
                }
                $this->db->commit();
                return ['result' => true, 'message' => 'save'];
            } else {
                return ['result' => false, 'message' => 'Calculation failed: No DTR records found.'];
            }
        } catch (\Throwable $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => 'DB error: ' . $e->getMessage() . ' (line ' . $e->getLine() . ')'];
        }
        } catch (\Throwable $e) {
            return ['result' => false, 'message' => 'Calculate error: ' . $e->getMessage() . ' (line ' . $e->getLine() . ')'];
        }
    }

    function update_status_user()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
        if ($id > 0 && in_array($status, [1, 2], true)) {
            $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
            if (!$stmt) {
                return ['result' => false, 'message' => 'Unable to prepare status update.'];
            }
            $stmt->bind_param('si', $status, $id);
            if ($stmt->execute()) {
                $stmt->close();
                return ['result' => true, 'message' => 'updated'];
            } else {
                $error = $stmt->error;
                $stmt->close();
                return ['result' => false, 'message' => $error];
            }
        } else {
            return ['result' => false, 'message' => 'Invalid user status parameters.'];
        }
    }

    function update_status_dtr()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $approved_by = $_SESSION['login_id'];
        $status = 2;
        if ($id) {
            $stmt = $this->db->prepare("UPDATE dtr SET status = ?, approved_by = ? WHERE id = ?");
            $stmt->bind_param('ssi', $status, $approved_by, $id);
            if ($stmt->execute()) {
                return ['result' => true, 'message' => 'updated'];
            } else {
                return ['result' => false, 'message' => $stmt->error];
            }
        } else {
            return ['result' => false, 'message' => 'Invalid parameters'];
        }
    }



    function save_payroll()
    {
        $pid       = null;
        $id        = $_POST['id'] ?? '';
        $p2        = $this->db->real_escape_string($_POST['p2'] ?? 'no');
        $date_from = date("Y-m-d", strtotime($_POST['date_from'] ?? ''));
        $date_to   = date("Y-m-d", strtotime($_POST['date_to'] ?? ''));
        $type      = (int) ($_POST['type'] ?? 0);
        $branch_id = (int) ($_POST['employer_id'] ?? 0);
        if ($branch_id === 0) {
            $branchList = [];
            $result = $this->db->query("SELECT id FROM branches WHERE status = 1 ORDER BY branch_name ASC");
            while ($row = $result->fetch_assoc()) {
                $branchList[] = (int) $row['id'];
            }
            $site_ids = $this->db->real_escape_string(json_encode($branchList));
        } else {
            $site_ids = $this->db->real_escape_string(json_encode([$branch_id]));
        }

        $data  = " date_from='$date_from' ";
        $data .= ", date_to='$date_to' ";
        $data .= ", type='$type' ";
        $data .= ", site_ids='$site_ids' ";
        $data .= ", category=0 ";
        $data .= ", p2='$p2' ";

        if (empty($id)) {
            do {
                $ref_no = date('Y') . '-' . mt_rand(1, 9999);
                $chk = $this->db->query("SELECT id FROM payroll WHERE ref_no='$ref_no'")->num_rows;
            } while ($chk > 0);

            $data .= ", ref_no='$ref_no' ";
            $save = $this->db->query("INSERT INTO payroll SET " . $data);
            if ($save) {
                $pid = $this->db->insert_id;
                $this->save_payroll_history($pid, 1);
            }
        } else {
            $id   = (int) $id;
            $save = $this->db->query("UPDATE payroll SET " . $data . " WHERE id=$id");
        }

        if ($save) {
            return ['result' => true, 'message' => 'Payroll saved.', 'id' => $pid];
        }
        return ['result' => false, 'message' => $this->db->error];
    }

    function get_sites()
    {
        // Assuming you have a valid DB connection in $this->db
        $employer_id = $_POST['employer_id'];
        $date_from = date("Y-m-d", strtotime($_POST['date_from']));
        $date_to = date("Y-m-d", strtotime($_POST['date_to']));

        $filter_query = "";
        $disabled = false;
        $sites = $this->db->query("
            SELECT 
                        branches.id, branches.branch_code AS site_code,
                        branches.branch_name AS site_name, branches.address AS site_address,
                        users.name, 
                         dtr.date_from,
                          dtr.date_to
                    FROM users
                    INNER JOIN branches
                        ON branches.id = users.branch_id
                    INNER JOIN dtr
                        ON dtr.site_id = branches.id
                    WHERE users.role = 5
                    AND branches.status = 1
                    AND dtr.date_from BETWEEN '$date_from' AND '$date_to'
                    AND dtr.status = 2
                    $filter_query
                    GROUP BY branches.id
        ");



        // Start outputting the table with Bootstrap classes
        echo '<div class="container mt-5">';
        echo '<table class="table table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">Select</th>';
        echo '<th scope="col">Site</th>';
        echo '<th scope="col">Cashier</th>';
        echo '<th scope="col">Approved DTR</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Loop through the rows and create table rows with checkboxes
        while ($row = $sites->fetch_assoc()) {
            echo '<tr>';
            echo '<td class="text-center"><input type="checkbox" name="site_ids[]" value="' . $row['id'] . '"' . ($disabled ? ' onclick="return false;" checked ' : '') . '></td>';
            echo '<td><b><span class="text-primary">(' . htmlspecialchars($row['site_code']) . ')</span>' . htmlspecialchars($row['site_name']) . '</b><p>' . htmlspecialchars($row['site_address']) . '</p></td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td>'
                . date("F d, Y", strtotime($row['date_from']))
                . ' - '
                . date("F d, Y", strtotime($row['date_to']))
                . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    function save_payroll_settings()
    {
        $settings = [];
        $count = 0;
        $contributions = $_POST['contributions'];
        foreach ($contributions as $i =>  $k) {
            $settings[$count]["id"] = $k;
            $settings[$count]["type"] = 1;
            $count++;
        }

        $loans = $_POST['loans'];
        foreach ($loans as $i =>  $k) {
            $settings[$count]["id"] = $k;
            $settings[$count]["type"] = 3;
            $count++;
        }

        $deductions = $_POST['deductions'];
        foreach ($deductions as $i =>  $k) {
            $settings[$count]["id"] = $k;
            $settings[$count]["type"] = 2;
            $count++;
        }

        $refunds = $_POST['refunds'];
        foreach ($refunds as $i =>  $k) {
            $settings[$count]["id"] = $k;
            $settings[$count]["type"] = 4;
            $count++;
        }



        $id = $_POST['id'];
        $settings_json =  json_encode($settings);
        $stmt = $this->db->prepare("UPDATE payroll SET settings = ? WHERE id = ?");
        $stmt->bind_param('si', $settings_json, $id);
        if ($stmt->execute()) {
            return ['result' => true, 'message' => 'updated'];
        } else {
            return ['result' => false, 'message' => $stmt->error];
        }
    }

    function delete_dtr_logs()
    {
        extract($_POST);
            $delete = $this->db->query("DELETE FROM dtr_details where id = " . $id);
        if ($delete) {
            return ['result' => true, 'message' => 'deleted'];
        } else {
            return ['result' => false, 'message' => 'Error while deleting'];
        }
    }

    function save_employee_attendance222()
    {
        extract($_POST);
        foreach ($employee_id as $k => $v) {
            $datetime_log[$k] = date("Y-m-d H:i", strtotime($datetime_log[$k]));
            $data = " employee_id='$employee_id[$k]' ";
            $data .= ", log_type = '$log_type[$k]' ";
            $data .= ", datetime_log = '$datetime_log[$k]' ";
            $save[] = $this->db->query("INSERT INTO attendance set " . $data);
        }
        if (isset($save)) {
            return 1;
        }
    }

    function save_employee_attendance()
    {
        $this->db->begin_transaction();
        try {
            $id = $_POST['id'];
            $employee_id = $_POST['employee_id'];
            $date_time = $_POST['date_time'];
            $datetime_log = $_POST['datetime_log'];
            $query = "SELECT  * FROM dtr_details
        WHERE ddtr_id = ? AND employee_id = ? AND date_time = ? ";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iis", $id, $employee_id, $date_time);
            $stmt->execute();
            $result = $stmt->get_result();
            $details = $result->fetch_assoc();
            if (isset($details)) {
                $new_logs = [];
                $logs = json_decode($details['logs'], true);
                foreach ($datetime_log as $k => $log) {
                    $new_logs[$k]['dateTime'] =  $date_time . ' ' . $log;
                    $new_logs[$k]['type'] =  'manual';
                }
                $updated_logs =  array_merge($logs, $new_logs);
                $query_update = "UPDATE dtr_details SET logs = ? WHERE id = ?";
                $stmt3 = $this->db->prepare($query_update);
                if ($stmt3 === false) {
                    throw new Exception('Failed to prepare the statement: ' . $this->db->error);
                }
                $updated_logs_json = json_encode($updated_logs);
                $details_id = (int) $details['id'];
                $stmt3->bind_param("si", $updated_logs_json, $details_id);
                try {
                    $stmt3->execute();
                } catch (Exception $e) {
                    throw new Exception('Failed to update data: ' . $e->getMessage());
                }
            } else {
                $hours = 0;
                $overtime = 0;
                $attendance_type = 'manual';
                $new_logs = [];
                foreach ($datetime_log as $k => $log) {
                    $new_logs[$k]['dateTime'] =  $date_time . ' ' . $log;
                    $new_logs[$k]['type'] =  'manual';
                }
                $logs = json_encode($new_logs);
                $sql2 = "INSERT INTO dtr_details (ddtr_id, employee_id, date_time, work_hours, logs, attendance_type, overtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->bind_param('sssssss', $id, $employee_id, $date_time, $hours, $logs, $attendance_type, $overtime);
                try {
                    $stmt2->execute();
                } catch (Exception $e) {
                    throw new Exception('Failed to insert data');
                }
            }
            $this->db->commit();
            return ['result' => true, 'message' => 'save'];
        } catch (mysqli_sql_exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
        return ['result' => false, 'message' => 'save'];
    }

    function update_dtr_logs()
    {
        $this->db->begin_transaction();
        $id = $_POST['id'];
        if (isset($_POST['work_hours'])) {
            try {
                $query_update = "UPDATE dtr_details SET work_hours = ? WHERE id = ?";
                $stmt3 = $this->db->prepare($query_update);
                if ($stmt3 === false) {
                    throw new Exception('Failed to prepare the statement: ' . $this->db->error);
                }
                $stmt3->bind_param("si", $_POST['work_hours'], $id);
                try {
                    $stmt3->execute();
                } catch (Exception $e) {
                    throw new Exception('Failed to update data: ' . $e->getMessage());
                }
                $this->db->commit();
                return ['result' => true, 'message' => 'save'];
            } catch (mysqli_sql_exception $e) {
                return ['result' => false, 'message' => $e->getMessage()];
            }
            return ['result' => false, 'message' => 'save'];
        }

        if (isset($_POST['overtime'])) {
            try {
                $query_update = "UPDATE dtr_details SET overtime = ? WHERE id = ?";
                $stmt3 = $this->db->prepare($query_update);
                if ($stmt3 === false) {
                    throw new Exception('Failed to prepare the statement: ' . $this->db->error);
                }
                $stmt3->bind_param("si", $_POST['overtime'], $id);
                try {
                    $stmt3->execute();
                } catch (Exception $e) {
                    throw new Exception('Failed to update data: ' . $e->getMessage());
                }
                $this->db->commit();
                return ['result' => true, 'message' => 'save'];
            } catch (mysqli_sql_exception $e) {
                return ['result' => false, 'message' => $e->getMessage()];
            }
            return ['result' => false, 'message' => 'save'];
        }

        if (isset($_POST['undertime'])) {
            try {
                $query_update = "UPDATE dtr_details SET undertime = ? WHERE id = ?";
                $stmt3 = $this->db->prepare($query_update);
                if ($stmt3 === false) {
                    throw new Exception('Failed to prepare the statement: ' . $this->db->error);
                }
                $stmt3->bind_param("si", $_POST['undertime'], $id);
                try {
                    $stmt3->execute();
                } catch (Exception $e) {
                    throw new Exception('Failed to update data: ' . $e->getMessage());
                }
                $this->db->commit();
                return ['result' => true, 'message' => 'save'];
            } catch (mysqli_sql_exception $e) {
                return ['result' => false, 'message' => $e->getMessage()];
            }
            return ['result' => false, 'message' => 'save'];
        }

        if (isset($_POST['late'])) {
            try {
                $query_update = "UPDATE dtr_details SET late = ? WHERE id = ?";
                $stmt3 = $this->db->prepare($query_update);
                if ($stmt3 === false) {
                    throw new Exception('Failed to prepare the statement: ' . $this->db->error);
                }
                $stmt3->bind_param("si", $_POST['late'], $id);
                try {
                    $stmt3->execute();
                } catch (Exception $e) {
                    throw new Exception('Failed to update data: ' . $e->getMessage());
                }
                $this->db->commit();
                return ['result' => true, 'message' => 'save'];
            } catch (mysqli_sql_exception $e) {
                return ['result' => false, 'message' => $e->getMessage()];
            }
            return ['result' => false, 'message' => 'save'];
        }
    }



    function updateContributionAmount($contricutions, $dd_id, $value, $id)
    {
        foreach ($contricutions as &$contribution) {
            if ($contribution[$id] === $dd_id) {
                $contribution['amount'] = $value;
                return $contricutions; // Return updated array immediately
            }
        }
        return $contricutions; // Return original array if no match is found
    }

    function update_payroll_item()
    {
        $this->db->begin_transaction();
        $payroll_r = [];
        $id = $_POST['id'];
        $value = $_POST['value'];
        $field = $_POST['type'];
        $dd_id = (int) $_POST['dd_id'];
        $query = "SELECT loan_history.*, payroll.ref_no, payroll.date_from, payroll.date_to, payroll_items.employee_id FROM loan_history 
        INNER JOIN payroll ON  loan_history.payroll_id = payroll.id 
        INNER JOIN payroll_items ON  payroll_items.payroll_id = payroll.id
        WHERE loan_id = ?";
        $type = 4;
        $field2 = $dd_id;
        $value2 = $value;
        try {
            if (isset($dd_id)) {
                $query = "SELECT  contributions, deductions, loans, refunds, payroll.id AS payroll_id, employee_id FROM payroll_items INNER JOIN payroll ON  payroll_items.payroll_id = payroll.id WHERE  payroll_items.id = ?";
                $stmt =  $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $payroll_r = $result->fetch_assoc();

                if ($field === 'contribution') {
                    $contricutions = json_decode($payroll_r['contributions'], true);
                    $updatedContributions = $this->updateContributionAmount($contricutions, $dd_id, $value, 'contribution_id');
                    var_dump($updatedContributions) or die();
                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'contribution_id' => (int) $dd_id,
                            'amount' =>  (float) $value
                        ];
                        $new_contributions =  array_merge($contricutions, $array);
                        $value = json_encode($new_contributions);
                        $field = "contributions";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "contributions";
                    }
                    $type = 7;
                }

                if ($field === 'deduction') {
                    $deductions = json_decode(($payroll_r['deductions']), true);
                    $updatedContributions = $this->updateContributionAmount($deductions, $dd_id, $value, 'deduction_id');

                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'deduction_id' => (int) $dd_id,
                            'amount' => (float) $value
                        ];
                        $new_deductions =  array_merge($deductions, $array);
                        $value = json_encode($new_deductions);
                        $field = "deductions";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "deductions";
                    }
                    $type = 8;
                }

                if ($field === 'loan') {
                    $deductions = json_decode(($payroll_r['loans']), true);
                    $updatedContributions = $this->updateContributionAmount($deductions, $dd_id, $value, 'deduction_id');
                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'deduction_id' => (int) $dd_id,
                            'amount' => (float) $value
                        ];
                        $new_deductions =  array_merge($deductions, $array);
                        $value = json_encode($new_deductions);
                        $field = "loans";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "loans";
                    }
                    $type = 9;
                }

                if ($field === 'refund') {
                    $deductions = json_decode(($payroll_r['refunds']), true);
                    $updatedContributions = $this->updateContributionAmount($deductions, $dd_id, $value, 'refund_id');
                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'refund_id' => (int) $dd_id,
                            'amount' => (float)  $value
                        ];
                        $new_deductions =  array_merge($deductions, $array);
                        $value = json_encode($new_deductions);
                        $field = "refunds";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "refunds";
                    }
                    $type = 10;
                }
            }
            $query_update = "UPDATE payroll_items SET $field = ? WHERE id = ?";

            $stmt3 = $this->db->prepare($query_update);
            if ($stmt3 === false) {
                throw new Exception('Failed to prepare the statement: ' . $this->db->error);
            }
            $stmt3->bind_param("si", $value, $id);
            try {
                $stmt3->execute();
            } catch (Exception $e) {
                throw new Exception('Failed to update data: ' . $e->getMessage());
            }

            $this->save_payroll_history($payroll_r['payroll_id'], $type, [["value" => $value, "field" => $field, "employee_id" => $payroll_r['employee_id']]],  $field2, $value2);
            $this->db->commit();
            return ['result' => true, 'message' => 'save'];
        } catch (mysqli_sql_exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
        return ['result' => false, 'message' => 'save'];
    }

    function update_payroll_item_new()
    {
        $items = $_POST['items'];

        try {
            $this->db->begin_transaction();

            foreach ($items as $item) {
                $id = $item['id'];              // payroll_item ID
                $value = (float) $item['value'];
                $field = $item['type'];
                $dd_id = (int) $item['dd_id'];

                // Save the payroll item
                $this->save_new_payroll_item($id, $value, $field, $dd_id);

                // Handle per_day type
                if ($field === 'per_day') {
                    // First get the employee_id from payroll_items table
                    $getEmployeeQuery = "SELECT employee_id FROM payroll_items WHERE id = ?";
                    $stmt = $this->db->prepare($getEmployeeQuery);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $employee_id = $row['employee_id'];

                        // Calculate monthly salary (assuming 22 working days)
                        $salary = $value;

                        // Update the employee's salary in the database
                        $updateQuery = "UPDATE employee SET salary = ? WHERE id = ?";
                        $stmt = $this->db->prepare($updateQuery);
                        $stmt->bind_param("di", $salary, $employee_id);
                        $stmt->execute();
                    }
                }
            }

            $this->db->commit();
            return ['result' => true, 'message' => 'save'];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['result' => false, 'message' => 'Error updating payroll items: ' . $e->getMessage()];
        }
    }

    function save_new_payroll_item($id, $value, $field, $dd_id)
    {
        $this->db->begin_transaction();
        $payroll_r = [];
        $query = "SELECT loan_history.*, payroll.ref_no, payroll.date_from, payroll.date_to, payroll_items.employee_id FROM loan_history 
        INNER JOIN payroll ON  loan_history.payroll_id = payroll.id 
        INNER JOIN payroll_items ON  payroll_items.payroll_id = payroll.id
        WHERE loan_id = ?";
        $type = 4;
        $field2 =   $dd_id;
        $value2 = $value;
        try {
            if (isset($dd_id)) {
                $query = "SELECT  contributions, deductions, loans, refunds, payroll.id AS payroll_id, employee_id FROM payroll_items INNER JOIN payroll ON  payroll_items.payroll_id = payroll.id WHERE  payroll_items.id = ?";
                $stmt =  $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $payroll_r = $result->fetch_assoc();

                if ($field === 'contribution') {
                    $contricutions = json_decode($payroll_r['contributions'], true);
                    $updatedContributions = $this->updateContributionAmount($contricutions, $dd_id, $value, 'contribution_id');
                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'contribution_id' => (int) $dd_id,
                            'amount' =>  (float) $value
                        ];
                        $new_contributions =  array_merge($contricutions, $array);
                        $value = json_encode($new_contributions);
                        $field = "contributions";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "contributions";
                    }
                    $type = 7;
                }

                if ($field === 'deduction') {
                    $deductions = json_decode(($payroll_r['deductions']), true);
                    $updatedContributions = $this->updateContributionAmount($deductions, $dd_id, $value, 'deduction_id');

                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'deduction_id' => (int) $dd_id,
                            'amount' => (float) $value
                        ];
                        $new_deductions =  array_merge($deductions, $array);
                        $value = json_encode($new_deductions);
                        $field = "deductions";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "deductions";
                    }
                    $type = 8;
                }

                if ($field === 'loan') {
                    $deductions = json_decode(($payroll_r['loans']), true);
                    $updatedContributions = $this->updateContributionAmount($deductions, $dd_id, $value, 'deduction_id');
                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'deduction_id' => (int) $dd_id,
                            'amount' => (float) $value
                        ];
                        $new_deductions =  array_merge($deductions, $array);
                        $value = json_encode($new_deductions);
                        $field = "loans";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "loans";
                    }
                    $type = 9;
                }

                if ($field === 'refund') {
                    $deductions = json_decode(($payroll_r['refunds']), true);
                    $updatedContributions = $this->updateContributionAmount($deductions, $dd_id, $value, 'refund_id');
                    if (count($updatedContributions) === 0) {
                        $array[] = (object) [
                            'refund_id' => (int) $dd_id,
                            'amount' => (float)  $value
                        ];
                        $new_deductions =  array_merge($deductions, $array);
                        $value = json_encode($new_deductions);
                        $field = "refunds";
                    } else {
                        $value = json_encode($updatedContributions);
                        $field = "refunds";
                    }
                    $type = 10;
                }
            }
            $query_update = "UPDATE payroll_items SET $field = ? WHERE id = ?";

            $stmt3 = $this->db->prepare($query_update);
            if ($stmt3 === false) {
                throw new Exception('Failed to prepare the statement: ' . $this->db->error);
            }
            $stmt3->bind_param("si", $value, $id);
            try {
                $stmt3->execute();
            } catch (Exception $e) {
                throw new Exception('Failed to update data: ' . $e->getMessage());
            }
            $this->save_payroll_history($payroll_r['payroll_id'], $type, [["value" => $value, "field" => $field, "employee_id" => $payroll_r['employee_id']]],  $field2, $value2);
            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    function save_payroll_amount()
    {
        $ids = $_POST['id'];
        $nets = $_POST['net'];
        foreach ($ids as $index =>  $k) {
            $id = $k;
            $net =  $nets[$index];

            $query_update = "UPDATE payroll_items SET net = ? WHERE id = ?";
            $stmt3 = $this->db->prepare($query_update);
            if ($stmt3 === false) {
                throw new Exception('Failed to prepare the statement: ' . $this->db->error);
            }
            $stmt3->bind_param("si", $net, $id);
            try {
                $stmt3->execute();
            } catch (Exception $e) {
            }
        }

        $this->db->commit();
    }

    function isLock()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = $_POST['isLock'];
        $this->save_payroll_history($id, 5, $status == 0  ? 'Lock' : 'Unlock');
        if ($id) {
            $stmt = $this->db->prepare("UPDATE payroll SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $status, $id);
            if ($stmt->execute()) {
                return ['result' => true, 'message' => 'updated'];
            } else {
                return ['result' => false, 'message' => $stmt->error];
            }
        } else {
            return ['result' => false, 'message' => 'Invalid parameters'];
        }
    }

    function saveLogs()
    {
        define('UPLOAD_DIR', 'uploads/');
        $post = json_decode(file_get_contents('php://input'), true);
        $company = $post['company'];
        $name = $post['name'];
        $site_id = $post['site_id'];
        $image = $post['image'];
        $date_visited = $post['date_visited'];
        $date = new DateTime($date_visited);
        $date->setTimezone(new DateTimeZone('Asia/Manila'));
        $formattedDate = $date->format('Y-m-d H:i:s');
        $image_data = base64_decode($image);
        $filename =  uniqid() . '.jpg';
        $this->db->begin_transaction();
        $file_path = UPLOAD_DIR . $filename;
        try {
            $sql2 = "INSERT INTO visitors_logs (site_id, image, name, company, date_visited) VALUES (?, ?, ?, ?, ?)";
            $stmtbio = $this->db->prepare($sql2);
            $stmtbio->bind_param('sssss', $site_id, $filename, $name, $company, $formattedDate);
            try {
                $stmtbio->execute();
                file_put_contents($file_path, $image_data);
            } catch (Exception $e) {
                throw new Exception('Failed to insert data');
            }
            $this->db->commit();
            return ['result' => true, 'message' => 'Data inserted successfully'];
        } catch (Exception $e) {
            $this->db->rollback(); // Rollback on errors
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }


    function save_employee_loan()
    {
        $id = (int) ($_POST['id'] ?? 0);
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $loan_type = (int) ($_POST['loan_type'] ?? 0);
        $loan_date = trim((string) ($_POST['loan_date'] ?? ''));
        $loan_amount = (float) ($_POST['loan_amount'] ?? 0);
        $loan_balance = (float) ($_POST['loan_balance'] ?? 0);
        $damount = (float) ($_POST['damount'] ?? 0);
        $loan_status = isset($_POST['loan_status']) ? 1 : 0;

        if ($employee_id <= 0 || $loan_type <= 0 || $loan_date === '' || $loan_amount < 0 || $loan_balance < 0 || $damount < 0) {
            return 0;
        }

        if ($id <= 0) {
            $stmt = $this->db->prepare("INSERT INTO loans (employee_id, loan_date, loan_amount, loan_status, loan_type, loan_balance, damount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) return 0;
            $stmt->bind_param('isdiddd', $employee_id, $loan_date, $loan_amount, $loan_status, $loan_type, $loan_balance, $damount);
        } else {
            $stmt = $this->db->prepare("UPDATE loans SET employee_id=?, loan_date=?, loan_amount=?, loan_status=?, loan_type=?, loan_balance=?, damount=? WHERE loan_id=?");
            if (!$stmt) return 0;
            $stmt->bind_param('isdidddi', $employee_id, $loan_date, $loan_amount, $loan_status, $loan_type, $loan_balance, $damount, $id);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return 0;
        }
        $stmt->close();
        return $id > 0 ? 2 : 1;
    }

    function active_employee_loan()
    {
        extract($_POST);
        $data = " loan_id=$loan_id ";
        $data .= ", loan_deduction='$loan_deduction' ";
        $data .= ", loan='$loan' ";
        $this->db->query("UPDATE employee set " . $data . " where id=" . $id);
        return 1;
    }

    function update_payroll_status()
    {
        extract($_POST);

        // Start Transaction
        $this->db->begin_transaction();

        try {
            $sql = "SELECT * FROM payroll_items WHERE payroll_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                foreach ($result as $row) {
                    $loans = json_decode($row['loans'], true);
                    if (!is_array($loans)) {
                        $loans = [];
                    }
                    $employee_id = $row['employee_id'];

                    foreach ($loans as $loan_d) {
                        $loan_query = "SELECT * FROM loans WHERE loan_type = ? AND employee_id = ?";
                        $loan_stmt = $this->db->prepare($loan_query);
                        $loan_stmt->bind_param("ii", $loan_d['deduction_id'], $row['employee_id']);
                        $loan_stmt->execute();
                        $loan_list = $loan_stmt->get_result()->fetch_array();

                        if ($loan_list) {
                            $loan_id = $loan_list['loan_id'];
                            $amount = $loan_d['amount'];
                            $current_bal = $loan_list['loan_balance'];
                            if ($current_bal < $amount) {
                                $amount = $current_bal;
                            }
                            $new_bal = max(0, $current_bal - $amount);
                            $payroll_id = $id;

                            // Update loan status if fully paid
                            if ($new_bal <= 0) {
                                $loan_status_query = "UPDATE loans SET loan_status = 1, loan_balance = 0 WHERE loan_id = ?";
                                $loan_status_stmt = $this->db->prepare($loan_status_query);

                                if (!$loan_status_stmt) {
                                    die("Query preparation failed: " . $this->db->error);
                                }

                                $loan_status_stmt->bind_param("i", $loan_id);

                                if (!$loan_status_stmt->execute()) {
                                    die("Execution failed: " . $loan_status_stmt->error);
                                }

                                $loan_status_stmt->close();
                            } else {
                                $loan_status_query = "UPDATE loans SET loan_balance = ? WHERE loan_id = ?";
                                $loan_status_stmt = $this->db->prepare($loan_status_query);

                                if (!$loan_status_stmt) {
                                    die("Query preparation failed: " . $this->db->error);
                                }

                                $loan_status_stmt->bind_param("di", $new_bal, $loan_id); // "d" for double (float), "i" for integer
                                $loan_status_stmt->execute();

                                $loan_status_stmt->close();
                            }

                            // Insert into loan history
                            $loan_history_query = "INSERT INTO loan_history (loan_id, amount, current_bal, new_bal, payroll_id, employee_id) VALUES (?, ?, ?, ?, ?, ?)";
                            $loan_history_stmt = $this->db->prepare($loan_history_query);
                            $loan_history_stmt->bind_param("idddii", $loan_id, $amount, $current_bal, $new_bal, $payroll_id, $employee_id);
                            $loan_history_stmt->execute();
                        }
                    }
                }

                // Update payroll status
                $payroll_update_query = "UPDATE payroll SET status = ? WHERE id = ?";
                $payroll_stmt = $this->db->prepare($payroll_update_query);
                $payroll_stmt->bind_param("si", $status, $id);
                $payroll_stmt->execute();
                $this->save_payroll_history($id, 5, "Lock");
            }

            // Commit Transaction
            $this->db->commit();
            return 1;
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollback();
            return 0;
        }
    }

    function loan_history_details()
    {
        $loan_id = $_POST['id'] ?? null;

        if ($loan_id) {
            // Prepare SQL query to fetch records
            $query = "SELECT loan_history.*, payroll.ref_no, payroll.date_from, payroll.date_to FROM loan_history INNER JOIN payroll ON  loan_history.payroll_id = payroll.id WHERE loan_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $loan_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Fetch data as an associative array
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            // Return JSON response
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "Invalid loan_id"]);
        }
    }

    function payroll_history_details()
    {
        $payroll_id = $_POST['id'] ?? null;

        if ($payroll_id) {
            // Prepare SQL query to fetch records
            $query = "SELECT payroll_logs.*, users.name FROM payroll_logs INNER JOIN users ON  payroll_logs.user_id =  users.id WHERE payroll_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $payroll_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            // Return JSON response
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "Invalid payroll_id"]);
        }
    }

    function save_payroll_history($payroll_id, $type = 1, $other = [],  $field2 = null, $value2 = 0)
    {

        $user_id = $_SESSION['login_id'];
        $details = "No Details";
        if ($type === 1) {
            $details = 'New Payroll Created';
        }

        if ($type === 2) {
            $details = 'Payroll Calculated';
        }

        if ($type === 3) {
            $details = 'Payroll Re-calculated';
        }
        if ($type === 4) {
            $employee_id = $other[0]['employee_id'];
            $field = $other[0]['field'];
            $value = $other[0]['value'];
            $query = "SELECT  firstname,lastname  FROM employee WHERE  id = ?";
            $stmt =  $this->db->prepare($query);
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $emp = $result->fetch_assoc();
            $files_types = ['present' => 'No. of Days', 'per_day' => 'Basic Rate', 'allowance_amount' => 'Allowance', 'ot' => "Overtime", 'ot_rate' => "Overtime Rate", 'under_time' => "Undertime", "other_deduction" => "Other Deduction", 'late' => 'Late', 'absent' => 'Absent', 'legal_holiday' => 'Legal Holiday', 'sunday_duty' => "Sunday Duty", "special_holiday" => 'Special Holiday', "sss_fund" => "SSS PROVIDENT FUND", "jei_advances" => "JEI ADVANCE", "jcc_advances" => "JCC ADVANCES", "tax" => "Tax", 'allowance_days' => "Allowance No. dys"];
            $details = "Employee: " . $emp['lastname'] . ", " . $emp['firstname'] . " & Field: {$files_types[$field]} & Value: $value";
        }

        if ($type === 5) {
            $details = (is_array($other) ? implode(', ', array_map('strval', $other)) : (string) $other) . ' Payroll';
        }

        if ($type === 7) {
            $employee_id = $other[0]['employee_id'];
            $field = $other[0]['field'];
            $value = $other[0]['value'];
            $query2 = "SELECT  contribution FROM contributions WHERE  id = ?";
            $stmt2 =  $this->db->prepare($query2);
            $stmt2->bind_param("i", $field2);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $emp22 = $result2->fetch_assoc();


            $query = "SELECT  firstname,lastname  FROM employee WHERE  id = ?";
            $stmt =  $this->db->prepare($query);
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $emp = $result->fetch_assoc();

            $details = "Employee: " . $emp['lastname'] . ", " . $emp['firstname'] . " & Field: CONTRIBUTION {$emp22['contribution']} & Value: $value2";
        }

        if ($type === 8) {
            $employee_id = $other[0]['employee_id'];
            $field = $other[0]['field'];
            $value = $other[0]['value'];
            $query2 = "SELECT  deduction FROM deductions WHERE  id = ?";
            $stmt2 =  $this->db->prepare($query2);
            $stmt2->bind_param("i", $field2);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $emp22 = $result2->fetch_assoc();


            $query = "SELECT  firstname,lastname  FROM employee WHERE  id = ?";
            $stmt =  $this->db->prepare($query);
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $emp = $result->fetch_assoc();

            $details = "Employee: " . $emp['lastname'] . ", " . $emp['firstname'] . " & Field: DEDUCTION  {$emp22['deduction']} & Value: $value2";
        }

        if ($type === 9) {
            $employee_id = $other[0]['employee_id'];
            $field = $other[0]['field'];
            $value = $other[0]['value'];
            $query2 = "SELECT  loan_type FROM contribution_loan_types WHERE  clt_id = ?";
            $stmt2 =  $this->db->prepare($query2);
            $stmt2->bind_param("i", $field2);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $emp22 = $result2->fetch_assoc();


            $query = "SELECT  firstname,lastname  FROM employee WHERE  id = ?";
            $stmt =  $this->db->prepare($query);
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $emp = $result->fetch_assoc();

            $details = "Employee: " . $emp['lastname'] . ", " . $emp['firstname'] . " & Field: {$emp22['loan_type']} & Value: $value2";
        }

        if ($type === 10) {
            $employee_id = $other[0]['employee_id'];
            $field = $other[0]['field'];
            $value = $other[0]['value'];
            $query2 = "SELECT  refunds FROM refunds WHERE  id = ?";
            $stmt2 =  $this->db->prepare($query2);
            $stmt2->bind_param("i", $field2);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $emp22 = $result2->fetch_assoc();


            $query = "SELECT  firstname,lastname  FROM employee WHERE  id = ?";
            $stmt =  $this->db->prepare($query);
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $emp = $result->fetch_assoc();

            $details = "Employee: " . $emp['lastname'] . ", " . $emp['firstname'] . " & Field: REFUND {$emp22['refunds']} & Value: $value2";
        }





        $data = " payroll_id='$payroll_id' ";
        $data .= ", user_id = '$user_id' ";
        $data .= ", details = '$details' ";
        // var_dump("INSERT INTO payroll_logs set " . $data);
        $save = $this->db->query("INSERT INTO payroll_logs set " . $data);
    }

    function update_payroll_print()
    {
        if (isset($_POST['id'], $_POST['prepared_by'], $_POST['prepared_by_role'], $_POST['verified_by'], $_POST['verified_by_role'], $_POST['approved_by'], $_POST['approved_by_role'])) {
            $id = $_POST['id'];
            $prepared_by = $_POST['prepared_by'];
            $prepared_by_role = $_POST['prepared_by_role'];
            $verified_by = $_POST['verified_by'];
            $verified_by_role = $_POST['verified_by_role'];
            $approved_by = $_POST['approved_by'];
            $approved_by_role = $_POST['approved_by_role'];

            $stmt = $this->db->prepare("UPDATE payroll SET prepared_by = ?, prepared_by_role = ?, verified_by = ?, verified_by_role = ?, approved_by = ?, approved_by_role = ? WHERE id = ?");

            if ($stmt) {
                $stmt->bind_param('ssssssi', $prepared_by, $prepared_by_role, $verified_by, $verified_by_role, $approved_by, $approved_by_role, $id);

                if ($stmt->execute()) {
                    return ['result' => true, 'message' => 'updated'];
                } else {
                    return ['result' => false, 'message' => $stmt->error];
                }
            } else {
                return ['result' => false, 'message' => 'Statement preparation failed'];
            }
        } else {
            return ['result' => false, 'message' => 'Missing required fields'];
        }
    }

    function save_refunds()
    {
        extract($_POST);

        $data = " refunds='$refunds' ";

        // $data .= ", department_id = '$department_id' ";

        if (empty($id)) {
            $this->db->query("INSERT INTO refunds set " . $data);
            return 1;
        } else {
            $this->db->query("UPDATE refunds set " . $data . " where id=" . $id);
            return 2;
        }
    }

    function import_employeeOLD()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $allowedExt = ['xls', 'xlsx', 'csv'];
        $fileExt = pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION);

        if (!in_array($fileExt, $allowedExt)) {
            die("Invalid file type. Only Excel files are allowed.");
        }

        $file = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        if (count($data) > 1) { // Ensure there is more than just the header
            array_shift($data); // Remove header row
        }
        $this->db->begin_transaction();
        $stmtCheckPosition = $this->db->prepare("SELECT id FROM position WHERE LOWER(name) = LOWER(?)");
        $stmtInsertPosition = $this->db->prepare("INSERT INTO position (name) VALUES (?)");
        $stmtInsert =  $this->db->prepare("INSERT INTO employee 
        (employee_no, employee_code, firstname, middlename, lastname, position_id, salary, basic_pay, status, ot_rate, isAutoDeduct, weekly_payroll, clasification_id, sss_fund, allowance_rate, sss_no, ph_no, hdmf_no, tin_no, ext, bday) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,? ,? ,?, ? , ? )");

        try {


            foreach ($data as $row) {
                $employee_code = mt_rand(100000000000, 999999999999);
                $status = 1;
                $e_num = date('Y') . '-' . mt_rand(1, 99999);
                $clasification_id = isset($row[0]) ? intval(trim($row[0])) : 1;
                $firstname = trim($row[1]);
                $lastname = trim($row[3]);
                if (empty($firstname) || empty($lastname)) {
                    continue;
                }
                $middlename = trim($row[2]);
                $ext = "";
                $position_name =  trim($row[4]);
                $basic_pay = floatval(preg_replace('/[^0-9.]/', '', $row[5]));
                $salary = floatval(preg_replace('/[^0-9.]/', '', $row[6]));
                $ot_rate = floatval(preg_replace('/[^0-9.]/', '', $row[7]));
                $allowance_rate = floatval(preg_replace('/[^0-9.]/', '', $row[8]));
                $sss_fund = floatval(preg_replace('/[^0-9.]/', '', $row[9]));
                $weekly_payroll = intval($row[10]);
                $isAutoDeduct = intval($row[11]);
                $sss = floatval(preg_replace('/[^0-9.]/', '', $row[12]));;
                $sss_loan = floatval(preg_replace('/[^0-9.]/', '', $row[13]));
                $phic = floatval(preg_replace('/[^0-9.]/', '', $row[14]));
                $hdmf = floatval(preg_replace('/[^0-9.]/', '', $row[15]));
                $hdmf_loan = floatval(preg_replace('/[^0-9.]/', '', $row[16]));
                $bday = trim($row[17]);
                $ph_no = trim($row[18]);
                $hdmf_no = trim($row[19]);
                $sss_no = trim($row[20]);
                $ppe = floatval(preg_replace('/[^0-9.]/', '', $row[21]));
                $cash_bond = floatval(preg_replace('/[^0-9.]/', '', $row[22]));
                $penalty = floatval(preg_replace('/[^0-9.]/', '', $row[23]));
                $cash_advance = floatval(preg_replace('/[^0-9.]/', '', $row[24]));
                $tin_no = "";


                // 🔹 CHECK IF EMPLOYEE EXISTS (case-sensitive)
                $stmtCheckEmployee = $this->db->prepare("SELECT id FROM employee WHERE  LOWER(firstname) = LOWER(?)  AND  LOWER(lastname) = LOWER(?)  AND  LOWER(middlename) = LOWER(?) ");
                $stmtCheckEmployee->bind_param("sss", $firstname, $lastname, $middlename);
                $stmtCheckEmployee->execute();
                $stmtCheckEmployee->store_result();

                if ($stmtCheckEmployee->num_rows > 0) {
                    echo "Skipping duplicate employee: $firstname $lastname $middlename \n"; // Debugging message
                    $stmtCheckEmployee->free_result();
                    continue; // Skip this row and move to the next
                }
                $stmtCheckEmployee->free_result();

                // 🔹 CHECK IF POSITION EXISTS (case-insensitive)
                $position_id = null; // Reset before each check
                $stmtCheckPosition->bind_param("s", $position_name);
                $stmtCheckPosition->execute();
                $stmtCheckPosition->store_result(); // Ensure previous results don’t interfere

                if ($stmtCheckPosition->num_rows > 0) {
                    $stmtCheckPosition->bind_result($position_id);
                    $stmtCheckPosition->fetch();
                } else {
                    // 🔹 INSERT NEW POSITION
                    $stmtInsertPosition->bind_param("s", $position_name);
                    $stmtInsertPosition->execute();
                    $position_id = $this->db->insert_id; // Get new position ID
                }
                $stmtCheckPosition->free_result(); // Free result set to avoid conflicts
                $stmtInsert->bind_param("sssssssssssssssssssss", $e_num, $employee_code, $firstname, $middlename, $lastname, $position_id, $salary, $basic_pay, $status, $ot_rate, $isAutoDeduct, $weekly_payroll, $clasification_id, $sss_fund, $allowance_rate, $sss_no, $ph_no, $hdmf_no, $tin_no, $ext, $bday);
                $stmtInsert->execute();
                if ($stmtInsert->affected_rows > 0) {
                    $employee_id =  $this->db->insert_id;
                    // Insert only the configured employee contributions: SSS and PAG-IBIG.
                    $contributions = [
                        ['id' => 1, 'amount' => $sss],
                        ['id' => 2, 'amount' => $phic]
                    ];
                    $query = "INSERT INTO employee_contributions (employee_id, contribution_id, amount, payroll_type) VALUES (?, ?, ?, ?)";
                    $stmt = $this->db->prepare($query);
                    foreach ($contributions as $contribution) {
                        $stmt->bind_param("ssss", $employee_id, $contribution['id'], $contribution['amount'], $payroll_type);
                        $payroll_type = 1;
                        $stmt->execute();
                        if ($stmt->affected_rows <= 0) {
                            throw new Exception("Failed to insert contribution.");
                        }
                    }

                    // loans
                    if ($sss_loan > 0) {
                        $loan_status = 0;
                        $current_date = date('Y-m-d');
                        $data = " employee_id=$employee_id ";
                        $data .= ", loan_date='$current_date' ";
                        $data .= ", loan_amount = $sss_loan ";
                        $data .= ", loan_status = $loan_status ";
                        $data .= ", loan_type = 1 ";
                        $data .= ", loan_balance = $sss_loan ";
                        $data .= ", damount = $sss_loan ";
                        $this->db->query("INSERT INTO loans SET " . $data);
                    }

                    if ($hdmf_loan > 0) {
                        $loan_status = 0;
                        $current_date = date('Y-m-d');
                        $data = " employee_id=$employee_id ";
                        $data .= ", loan_date='$current_date' ";
                        $data .= ", loan_amount = $hdmf_loan ";
                        $data .= ", loan_status = $loan_status ";
                        $data .= ", loan_type = 2 ";
                        $data .= ", loan_balance = $hdmf_loan ";
                        $data .= ", damount = $hdmf_loan ";
                        $this->db->query("INSERT INTO loans SET " . $data);
                    }

                    //cash bond
                    if ($cash_bond > 0) {
                        $data = " employee_id='$employee_id' ";
                        $data .= ", deduction_id = 1 ";
                        $data .= ", amount = $cash_bond ";
                        $this->db->query("INSERT INTO employee_deductions set " . $data);
                    }


                    //ppe
                    if ($ppe > 0) {
                        $data = " employee_id='$employee_id' ";
                        $data .= ", deduction_id = 2 ";
                        $data .= ", amount = $ppe ";
                        $this->db->query("INSERT INTO employee_deductions set " . $data);
                    }

                    //penalty
                    if ($penalty > 0) {
                        $data = " employee_id='$employee_id' ";
                        $data .= ", deduction_id = 3 ";
                        $data .= ", amount = $penalty ";
                        $this->db->query("INSERT INTO employee_deductions set " . $data);
                    }


                    //ca
                    if ($cash_advance > 0) {
                        $data = " employee_id='$employee_id' ";
                        $data .= ", deduction_id = 4 ";
                        $data .= ", amount = $cash_advance ";
                        $this->db->query("INSERT INTO employee_deductions set " . $data);
                    }
                } else {
                    throw new Exception("Failed to insert employee: " . $stmtInsert->error);
                }
            }

            $this->db->commit();
            echo "saved";
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $this->db->rollback();
            echo "Error: " . $e->getMessage();
        }

        $stmtInsert->close();
    }

    function import_employee()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $allowedExt = ['xls', 'xlsx', 'csv'];
        $fileExt = pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION);

        if (!in_array($fileExt, $allowedExt)) {
            die("Invalid file type. Only Excel files are allowed.");
        }

        $file = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        if (count($data) > 1) {
            array_shift($data); // Remove header row
        }

        $this->db->begin_transaction();
        $stmtCheckPosition  = $this->db->prepare("SELECT id FROM position WHERE LOWER(name) = LOWER(?)");
        $stmtInsertPosition = $this->db->prepare("INSERT INTO position (name) VALUES (?)");
        $stmtInsert = $this->db->prepare("INSERT INTO employee
    (employee_no, employee_code, firstname, middlename, lastname, position_id, salary, basic_pay, status, ot_rate, isAutoDeduct, weekly_payroll, clasification_id, sss_fund, allowance_rate, sss_no, ph_no, hdmf_no, tin_no, ext, bday)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtUpdate = $this->db->prepare("UPDATE employee SET
    position_id=?, salary=?, basic_pay=?, ot_rate=?, isAutoDeduct=?, weekly_payroll=?, clasification_id=?, sss_fund=?, allowance_rate=?, sss_no=?, ph_no=?, hdmf_no=?, bday=?, employee_no=?, employee_code=?, ext=?
    WHERE id=?");

        $stmtUpdateContrib = $this->db->prepare("UPDATE employee_contributions SET amount=? WHERE employee_id=? AND contribution_id=?");

        try {
            $insertCount = 0;
            $updateCount = 0;

            foreach ($data as $row) {
                $employee_code = mt_rand(100000000000, 999999999999);
                $status = 1;
                $e_num = date('Y') . '-' . mt_rand(1, 99999);
                $clasification_id = 1;

                // Parse "LASTNAME, FIRSTNAME[ MIDDLENAME]" from a single cell
                $raw_name = trim($row[3]);
                if (strpos($raw_name, ',') !== false) {
                    [$last_part, $rest] = explode(',', $raw_name, 2);
                    $lastname   = trim($last_part);
                    $name_parts = preg_split('/\s+/', trim($rest), 2);
                    $firstname  = $name_parts[0] ?? '';
                    $middlename = $name_parts[1] ?? '';
                } else {
                    $lastname   = $raw_name;
                    $firstname  = trim($row[1]);
                    $middlename = trim($row[2]);
                }

                if (empty($firstname) || empty($lastname)) {
                    continue;
                }

                $ext = "";
                $position_name = trim($row[4]);
                $basic_pay = floatval(preg_replace('/[^0-9.]/', '', $row[10]));
                $salary = 0;
                $ot_rate = floatval(preg_replace('/[^0-9.]/', '', $row[12]));
                $allowance_rate = floatval(preg_replace('/[^0-9.]/', '', $row[11]));
                $sss_fund = 0;
                $weekly_payroll = 0;
                $isAutoDeduct = 0;
                $sss = 0;
                $sss_loan = 0;
                $phic = 0;
                $hdmf = 0;
                $hdmf_loan = 0;
                $bday = "";
                $ph_no = trim($row[5]);
                $hdmf_no = trim($row[9]);
                $sss_no = trim($row[7]);
                $ppe = 0;
                $cash_bond = 0;
                $penalty = 0;
                $cash_advance = 0;
                $tin_no = "";

                // 🔹 CHECK IF EMPLOYEE EXISTS
                $stmtCheckEmployee = $this->db->prepare("SELECT id FROM employee WHERE LOWER(firstname) = LOWER(?) AND LOWER(lastname) = LOWER(?) AND LOWER(middlename) = LOWER(?)");
                $stmtCheckEmployee->bind_param("sss", $firstname, $lastname, $middlename);
                $stmtCheckEmployee->execute();
                $stmtCheckEmployee->store_result();

                $employee_exists = false;
                $existing_employee_id = null;

                if ($stmtCheckEmployee->num_rows > 0) {
                    $stmtCheckEmployee->bind_result($existing_employee_id);
                    $stmtCheckEmployee->fetch();
                    $employee_exists = true;
                    echo "Updating existing employee: $firstname $lastname $middlename \n";
                }
                $stmtCheckEmployee->free_result();

                // 🔹 GET OR CREATE POSITION
                $position_id = null;
                $stmtCheckPosition->bind_param("s", $position_name);
                $stmtCheckPosition->execute();
                $stmtCheckPosition->store_result();

                if ($stmtCheckPosition->num_rows > 0) {
                    $stmtCheckPosition->bind_result($position_id);
                    $stmtCheckPosition->fetch();
                } else {
                    $stmtInsertPosition->bind_param("s", $position_name);
                    $stmtInsertPosition->execute();
                    $position_id = $this->db->insert_id;
                }
                $stmtCheckPosition->free_result();

                if ($employee_exists) {
                    // 🔹 UPDATE EXISTING EMPLOYEE
                    $stmtUpdate->bind_param(
                        "ssssssssssssssssi",
                        $position_id,
                        $salary,
                        $basic_pay,
                        $ot_rate,
                        $isAutoDeduct,
                        $weekly_payroll,
                        $clasification_id,
                        $sss_fund,
                        $allowance_rate,
                        $sss_no,
                        $ph_no,
                        $hdmf_no,
                        $bday,
                        $e_num,
                        $employee_code,
                        $ext,
                        $existing_employee_id
                    );
                    $stmtUpdate->execute();

                    if ($stmtUpdate->affected_rows >= 0) {
                        $updateCount++;
                        $employee_id = $existing_employee_id;

                        // Update contributions
                        $contributions = [
                            ['id' => 1, 'amount' => $sss],
                            ['id' => 2, 'amount' => $phic]
                        ];

                        foreach ($contributions as $contribution) {
                            $stmtUpdateContrib->bind_param("sss", $contribution['amount'], $employee_id, $contribution['id']);
                            $stmtUpdateContrib->execute();
                        }
                    }
                } else {
                    // 🔹 INSERT NEW EMPLOYEE
                    $stmtInsert->bind_param(
                        "sssssssssssssssssssss",
                        $e_num,
                        $employee_code,
                        $firstname,
                        $middlename,
                        $lastname,
                        $position_id,
                        $salary,
                        $basic_pay,
                        $status,
                        $ot_rate,
                        $isAutoDeduct,
                        $weekly_payroll,
                        $clasification_id,
                        $sss_fund,
                        $allowance_rate,
                        $sss_no,
                        $ph_no,
                        $hdmf_no,
                        $tin_no,
                        $ext,
                        $bday
                    );
                    $stmtInsert->execute();

                    if ($stmtInsert->affected_rows > 0) {
                        $insertCount++;
                        $employee_id = $this->db->insert_id;

                        // Insert contributions
                        $contributions = [
                            ['id' => 1, 'amount' => $sss],
                            ['id' => 2, 'amount' => $phic]
                        ];

                        $query = "INSERT INTO employee_contributions (employee_id, contribution_id, amount, payroll_type) VALUES (?, ?, ?, ?)";
                        $stmt = $this->db->prepare($query);

                        foreach ($contributions as $contribution) {
                            $payroll_type = 1;
                            $stmt->bind_param("ssss", $employee_id, $contribution['id'], $contribution['amount'], $payroll_type);
                            $stmt->execute();
                        }

                        // Insert loans and deductions for new employees only
                        if ($sss_loan > 0) {
                            $loan_status = 0;
                            $current_date = date('Y-m-d');
                            $data = " employee_id=$employee_id ";
                            $data .= ", loan_date='$current_date' ";
                            $data .= ", loan_amount = $sss_loan ";
                            $data .= ", loan_status = $loan_status ";
                            $data .= ", loan_type = 1 ";
                            $data .= ", loan_balance = $sss_loan ";
                            $data .= ", damount = $sss_loan ";
                            $this->db->query("INSERT INTO loans SET " . $data);
                        }

                        if ($hdmf_loan > 0) {
                            $loan_status = 0;
                            $current_date = date('Y-m-d');
                            $data = " employee_id=$employee_id ";
                            $data .= ", loan_date='$current_date' ";
                            $data .= ", loan_amount = $hdmf_loan ";
                            $data .= ", loan_status = $loan_status ";
                            $data .= ", loan_type = 2 ";
                            $data .= ", loan_balance = $hdmf_loan ";
                            $data .= ", damount = $hdmf_loan ";
                            $this->db->query("INSERT INTO loans SET " . $data);
                        }

                        if ($cash_bond > 0) {
                            $data = " employee_id='$employee_id' ";
                            $data .= ", deduction_id = 1 ";
                            $data .= ", amount = $cash_bond ";
                            $this->db->query("INSERT INTO employee_deductions SET " . $data);
                        }

                        if ($ppe > 0) {
                            $data = " employee_id='$employee_id' ";
                            $data .= ", deduction_id = 2 ";
                            $data .= ", amount = $ppe ";
                            $this->db->query("INSERT INTO employee_deductions SET " . $data);
                        }

                        if ($penalty > 0) {
                            $data = " employee_id='$employee_id' ";
                            $data .= ", deduction_id = 3 ";
                            $data .= ", amount = $penalty ";
                            $this->db->query("INSERT INTO employee_deductions SET " . $data);
                        }

                        if ($cash_advance > 0) {
                            $data = " employee_id='$employee_id' ";
                            $data .= ", deduction_id = 4 ";
                            $data .= ", amount = $cash_advance ";
                            $this->db->query("INSERT INTO employee_deductions SET " . $data);
                        }
                    } else {
                        throw new Exception("Failed to insert employee: " . $stmtInsert->error);
                    }
                }
            }

            $this->db->commit();
            echo "Import completed: $insertCount inserted, $updateCount updated";
        } catch (Exception $e) {
            $this->db->rollback();
            echo "Error: " . $e->getMessage();
        }

        $stmtInsert->close();
        $stmtUpdate->close();
        $stmtCheckPosition->close();
        $stmtInsertPosition->close();
        $stmtUpdateContrib->close();
    }

    // POS CRUD Operations
    function add_pos_branch() {
        extract($_POST);
        $branch_code = $this->db->real_escape_string($branch_code);
        $branch_name = $this->db->real_escape_string($branch_name);
        $city = $this->db->real_escape_string($city ?? '');
        $phone = $this->db->real_escape_string($phone ?? '');
        $email = $this->db->real_escape_string($email ?? '');
        $status = intval($status ?? 1);

        $check = $this->db->query("SELECT id FROM branches WHERE branch_code='$branch_code'");
        if ($check->num_rows > 0) return "Branch code already exists";

        $query = "INSERT INTO branches (branch_code, branch_name, city, phone, email, status)
                  VALUES ('$branch_code', '$branch_name', '$city', '$phone', '$email', $status)";
        if ($this->db->query($query)) {
            $this->log_cashier_notification(
                'Branch added',
                "Branch '$branch_name' was added by admin.",
            );
            return 1;
        }
        return "Error: " . $this->db->error;
    }

    function update_pos_branch() {
        extract($_POST);
        $id = intval($id);
        $branch_code = $this->db->real_escape_string($branch_code);
        $branch_name = $this->db->real_escape_string($branch_name);
        $city = $this->db->real_escape_string($city ?? '');
        $phone = $this->db->real_escape_string($phone ?? '');
        $email = $this->db->real_escape_string($email ?? '');
        $status = intval($status ?? 1);

        $query = "UPDATE branches SET branch_code='$branch_code', branch_name='$branch_name',
                  city='$city', phone='$phone', email='$email', status=$status WHERE id=$id";
        if ($this->db->query($query)) {
            $this->log_cashier_notification(
                'Branch updated',
                "Branch '$branch_name' was updated by admin.",
            );
            return 1;
        }
        return "Error: " . $this->db->error;
    }

    function add_pos_category() {
        extract($_POST);
        $category_code = $this->db->real_escape_string($category_code);
        $category_name = $this->db->real_escape_string($category_name);
        $description = $this->db->real_escape_string($description ?? '');
        $status = intval($status ?? 1);

        $check = $this->db->query("SELECT id FROM product_categories WHERE category_code='$category_code'");
        if ($check->num_rows > 0) return "Category code already exists";

        $query = "INSERT INTO product_categories (category_code, category_name, description, status)
                  VALUES ('$category_code', '$category_name', '$description', $status)";
        if ($this->db->query($query)) {
            $this->log_cashier_notification(
                'Category added',
                "Category '$category_name' was added by admin.",
            );
            return 1;
        }
        return "Error: " . $this->db->error;
    }

    function update_pos_category() {
        extract($_POST);
        $id = intval($id);
        $category_code = $this->db->real_escape_string($category_code);
        $category_name = $this->db->real_escape_string($category_name);
        $description = $this->db->real_escape_string($description ?? '');
        $status = intval($status ?? 1);

        $query = "UPDATE product_categories SET category_code='$category_code', category_name='$category_name',
                  description='$description', status=$status WHERE id=$id";
        if ($this->db->query($query)) {
            $this->log_cashier_notification(
                'Category updated',
                "Category '$category_name' was updated by admin.",
            );
            return 1;
        }
        return "Error: " . $this->db->error;
    }

    // Handle product image upload; returns filename or '' if none/failed
    private function upload_product_image() {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK || $_FILES['image']['tmp_name'] === '') {
            return '';
        }
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array(mime_content_type($_FILES['image']['tmp_name']), $allowed)) {
            return '';
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = 'prod_' . uniqid() . '_' . time() . '.' . strtolower($ext);
        if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/products/' . $fname)) {
            return $fname;
        }
        return '';
    }

    function add_pos_product() {
        $login_role = intval($_SESSION['login_role'] ?? 0);
        if (!in_array($login_role, [1, 9, 10], true)) {
            return 'You do not have permission to add products.';
        }

        $product_name = trim($_POST['product_name'] ?? '');
        $branch_id = intval($_POST['branch_id'] ?? 0);
        $quantity_on_hand = floatval($_POST['quantity_on_hand'] ?? 0);
        $unit_price = floatval($_POST['unit_price'] ?? 0);
        $description = $_POST['description'] ?? '';
        $status = intval($_POST['status'] ?? 1);
        $unit = trim($_POST['unit'] ?? '');
        $reorder_level = floatval($_POST['reorder_level'] ?? 10);
        $cost_price = floatval($_POST['cost_price'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $product_code = trim($_POST['product_code'] ?? '');
        $image = $this->db->real_escape_string($this->upload_product_image());

        if (empty($product_name)) {
            return 'Product name is required.';
        }
        if ($branch_id <= 0) {
            return 'Branch is required.';
        }
        if ($unit_price <= 0) {
            return 'Price is required.';
        }

        if ($category_id <= 0) {
            $categoryRow = $this->db->query("SELECT id FROM product_categories WHERE status=1 ORDER BY id LIMIT 1");
            if ($categoryRow && $categoryRow->num_rows > 0) {
                $category_id = intval($categoryRow->fetch_assoc()['id']);
            } else {
                $defaultCategoryCode = 'UNCAT' . time();
                $this->db->query("INSERT INTO product_categories (category_code, category_name, description, status) VALUES ('$defaultCategoryCode', 'Uncategorized', 'Default category', 1)");
                $category_id = intval($this->db->insert_id);
            }
        }

        if (empty($product_code)) {
            $baseCode = preg_replace('/[^A-Z0-9]/', '', strtoupper(substr($product_name, 0, 3)));
            if (empty($baseCode)) {
                $baseCode = 'PRD';
            }
            $product_code = $baseCode . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            while ($this->db->query("SELECT id FROM products WHERE product_code='$product_code'")->num_rows > 0) {
                $product_code = $baseCode . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        }

        $product_name = $this->db->real_escape_string($product_name);
        $product_code = $this->db->real_escape_string($product_code);
        $description = $this->db->real_escape_string($description);
        $unit = $this->db->real_escape_string($unit);

        $check = $this->db->query("SELECT id FROM products WHERE product_code='$product_code'");
        if ($check->num_rows > 0) return "Product code already exists";

        $query = "INSERT INTO products (product_code, product_name, category_id, branch_id, description,
                  quantity_on_hand, reorder_level, unit_price, cost_price, unit, image, status)
                  VALUES ('$product_code', '$product_name', $category_id, $branch_id, '$description',
                  $quantity_on_hand, $reorder_level, $unit_price, $cost_price, '$unit', '$image', $status)";
        if ($this->db->query($query)) {
            $notificationBranchId = $branch_id === 1 ? null : $branch_id;
            $branchLabel = $branch_id === 1 ? 'Main Branch' : "Branch ID $branch_id";
            $this->log_cashier_notification(
                'Product added',
                "Product '$product_name' was added by admin in $branchLabel.",
                null,
                $notificationBranchId,
            );
            return 1;
        }
        return "Error: " . $this->db->error;
    }

    function add_pos_product_stock() {
        $id = intval($_POST['id'] ?? 0);
        $quantity_to_add = floatval($_POST['quantity_to_add'] ?? 0);

        if ($id <= 0 || $quantity_to_add <= 0) {
            return 'Invalid stock quantity.';
        }

        $stmt = $this->db->prepare("UPDATE products SET quantity_on_hand = GREATEST(0, quantity_on_hand + ?) WHERE id = ?");
        if (!$stmt) {
            return 'Failed to prepare stock update.';
        }

        $stmt->bind_param('di', $quantity_to_add, $id);
        if ($stmt->execute()) {
            $stmt->close();
            $this->log_cashier_notification(
                'Product stock updated',
                "Product stock was increased by $quantity_to_add.",
            );
            return 1;
        }

        $error = $stmt->error;
        $stmt->close();
        return "Error: $error";
    }

    function update_pos_product() {
        extract($_POST);
        $id = intval($id);
        $product_code = $this->db->real_escape_string($product_code);
        $product_name = $this->db->real_escape_string($product_name);
        $category_id = intval($category_id);
        $branch_id = intval($branch_id);
        $description = $this->db->real_escape_string($description ?? '');
        $quantity_on_hand = floatval($quantity_on_hand ?? 0);
        $reorder_level = floatval($reorder_level ?? 10);
        $unit_price = floatval($unit_price);
        $cost_price = floatval($cost_price ?? 0);
        $unit = $this->db->real_escape_string($unit ?? '');
        $status = intval($status ?? 1);

        // Keep existing image unless a new one is uploaded
        $newImage = $this->upload_product_image();
        $image = $this->db->real_escape_string($newImage !== '' ? $newImage : ($current_image ?? ''));
        $imageSql = ", image='$image'";

        $query = "UPDATE products SET product_code='$product_code', product_name='$product_name',
                  category_id=$category_id, branch_id=$branch_id, description='$description',
                  quantity_on_hand=$quantity_on_hand, reorder_level=$reorder_level,
                  unit_price=$unit_price, cost_price=$cost_price, unit='$unit'$imageSql, status=$status WHERE id=$id";
        if ($this->db->query($query)) {
            $this->log_cashier_notification(
                'Product updated',
                "Product '$product_name' was updated by admin.",
                null,
                $branch_id,
            );
            return 1;
        }
        return "Error: " . $this->db->error;
    }
}
