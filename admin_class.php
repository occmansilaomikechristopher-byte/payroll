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

        include 'db_connect.php';

        $this->db = $conn;
    }

    function __destruct()
    {
        $this->db->close();

        ob_end_flush();
    }

    function save_cluster()
    {
        extract($_POST);
        $data = " cluster='$cluster' ";
        if (empty($id)) {
            $this->db->query("INSERT INTO clusters set " . $data);
            return 1;
        } else {
            $this->db->query("UPDATE clusters set " . $data . " where id=" . $id);
            return 2;
        }
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
                if ($row['role'] === 5) {
                    return ['result' => false, 'message' => 'No user found with the given username'];
                }
                $stored_hashed_password = $row['password'];
                if (password_verify($password, $stored_hashed_password)) {
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
        ($att = $this->db->query("SELECT * FROM attendance where date(datetime_log) between '" . $pay['date_from'] . "' and '" . $pay['date_to'] . "' order by UNIX_TIMESTAMP(datetime_log) asc  ")) or die(mysqli_error());
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
                (employee_no, employee_code, firstname, middlename, lastname, position_id, salary, basic_pay, status, ot_rate, isAutoDeduct, weekly_payroll, clasification_id, sss_fund, allowance_rate, bday, ext) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ?)";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("sssssssssssssssss", $e_num, $employee_code, $firstname, $middlename, $lastname, $position_id, $salary, $basic_pay, $status, $ot_rate, $isAutoDeduct, $weekly_payroll, $clasification_id, $sss_fund, $allowance_rate, $bday, $ext);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $employee_id = $this->db->insert_id; // Get newly inserted employee ID
                } else {
                    throw new Exception("Failed to insert employee.");
                }

                // Insert contributions for SSS, PHIC, HDMF
                $contributions = [
                    ['id' => 1, 'amount' => $sss],
                    ['id' => 2, 'amount' => $phic],
                    ['id' => 3, 'amount' => $hdmf]
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
                employee_code=?, firstname=?, middlename=?, lastname=?, position_id=?, salary=?, basic_pay=?, status=?, ot_rate=?, isAutoDeduct=?, weekly_payroll=?, clasification_id=? , sss_fund=?, allowance_rate = ? , bday = ?, ext = ?
                WHERE id=?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("sssssssssssssssss", $employee_code, $firstname, $middlename, $lastname, $position_id, $salary, $basic_pay, $status, $ot_rate, $isAutoDeduct, $weekly_payroll, $clasification_id, $sss_fund, $allowance_rate, $bday, $ext, $id);
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
        extract($_POST);

        $delete = $this->db->query("DELETE FROM DTR where id = " . $id);

        if ($delete) {
            return 1;
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

    function save_site()
    {
        try {
            // Ensure all expected keys exist
            $site_name     = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
            $site_address  = isset($_POST['site_address']) ? trim($_POST['site_address']) : '';
            $employer_id   = isset($_POST['employer_id']) ? $_POST['employer_id'] : '';
            $cluster_id    = isset($_POST['cluster_id']) ? $_POST['cluster_id'] : '';
            $site_code     = isset($_POST['site_code']) ? trim($_POST['site_code']) : '';
            $timekeeper_id = isset($_POST['timekeeper_id']) ? $_POST['timekeeper_id'] : '';
            $pic           = isset($_POST['pic']) ? trim($_POST['pic']) : '';
            $id            = isset($_POST['id']) ? $_POST['id'] : '';
            $status        = isset($_POST['status']) ? 1 : 0;

            // Sanitize inputs
            $site_name     = mysqli_real_escape_string($this->db, $site_name);
            $site_address  = mysqli_real_escape_string($this->db, $site_address);
            $employer_id   = mysqli_real_escape_string($this->db, $employer_id);
            $cluster_id    = mysqli_real_escape_string($this->db, $cluster_id);
            $site_code     = mysqli_real_escape_string($this->db, $site_code);
            $timekeeper_id = mysqli_real_escape_string($this->db, $timekeeper_id);
            $pic           = mysqli_real_escape_string($this->db, $pic);
            $id            = mysqli_real_escape_string($this->db, $id);

            // Basic validation
            if (empty($site_name) || empty($employer_id)) {
                return [
                    'status'  => "success",
                    'message' => 'Site name and employer are required.'
                ];
            }

            // Build SQL data string
            $data = "
            site_name = '$site_name',
            site_address = '$site_address',
            employer_id = '$employer_id',
            cluster_id = '$cluster_id',
            site_code = '$site_code',
            status = '$status',
            timekeeper_id = '$timekeeper_id'
        ";

            // Insert or update
            if (empty($id)) {
                $save = $this->db->query("INSERT INTO sites SET $data");
                if (!$save) {
                    throw new Exception("Insert failed: " . $this->db->error);
                }

                $new_id = $this->db->insert_id;
                if (!empty($timekeeper_id)) {
                    $this->db->query("UPDATE users SET site_id = '$new_id' WHERE id = '$timekeeper_id'");
                }

                return [
                    'status'  => "success",
                    'message' => 'Site created successfully.'
                ];
            } else {
                $save = $this->db->query("UPDATE sites SET $data WHERE id = '$id'");
                if (!$save) {
                    throw new Exception("Update failed: " . $this->db->error);
                }

                if (!empty($timekeeper_id)) {
                    $this->db->query("UPDATE users SET site_id = '$id' WHERE id = '$timekeeper_id'");
                }

                return [
                    'status'  => "success",
                    'message' => 'Site updated successfully.'
                ];
            }
        } catch (Exception $e) {
            // Catch and return any error message
            return [
                'status'  => "error",
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
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
            $employer_id  = isset($_POST['employer_id']) ? $_POST['employer_id'] : '';
            $id           = isset($_POST['id']) ? $_POST['id'] : '';

            // Sanitize inputs
            $name        = mysqli_real_escape_string($this->db, $name);
            $username    = mysqli_real_escape_string($this->db, $username);
            $role        = mysqli_real_escape_string($this->db, $role);
            $site_id     = mysqli_real_escape_string($this->db, $site_id);
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
            role = '$role',
            employer_id = '$employer_id'
            $password_sql
        ";

            if (!empty($site_id)) {
                $data .= ", site_id = '$site_id'";
            }

            // Insert or update user
            if (empty($id)) {
                $save = $this->db->query("INSERT INTO users SET $data");
                $user_id = $this->db->insert_id;
            } else {
                $save = $this->db->query("UPDATE users SET $data WHERE id = '$id'");
                $user_id = $id;
            }

            // Optional: update related site for timekeeper role
            if ($role == '5') {
                $this->db->query("UPDATE sites SET timekeeper_id = '$user_id' WHERE id = '$site_id'");
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
            SELECT users.*, employers.employer_name
            FROM users
            LEFT JOIN employers ON employers.id = users.employer_id
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

            // ✅ Only return ACTIVE sites assigned to this user
            $timekeeper_id = $user['id'];
            $qry_sites = $this->db->query("
            SELECT sites.*, clusters.cluster 
            FROM sites  
            LEFT JOIN clusters ON sites.cluster_id = clusters.id
            WHERE sites.timekeeper_id = '$timekeeper_id' AND sites.status = 1
        ");

            $sites = [];
            while ($site_row = $qry_sites->fetch_assoc()) {
                $sites[] = $site_row;
            }

            if (count($sites) === 0) {
                return ['result' => false, 'message' => 'No active sites assigned to you.'];
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
        $qry_exist = $this->db->query("SELECT * FROM DTR WHERE date_from = '$date_from' AND date_to = '$date_to' AND site_id = '$site_id'  LIMIT 1 ");
        if ($qry_exist->num_rows > 0) {
            return ['result' => false, 'message' => 'DTR date already exist'];
        }

        $qry_site = $this->db->query("SELECT * FROM sites WHERE id = '$site_id' AND  status = 1 ");
        if ($qry_site->num_rows === 0) {
            return ['result' => false, 'message' => 'Site is inactive'];
        }

        $qry_site_2 = $this->db->query("SELECT * FROM sites WHERE timekeeper_id = '$timekeeper_id' ");
        if ($qry_site_2->num_rows === 0) {
            return ['result' => false, 'message' => "You're not currently assigned to this site. Please log in again."];
        }

        $qry_site_2 = $this->db->query("SELECT COUNT(*) AS total_sites FROM sites WHERE timekeeper_id = '$timekeeper_id' AND status = 1");
        if ($qry_site_2->num_rows > 0) {
            $row_site = $qry_site_2->fetch_assoc();
            if ($row_site['total_sites'] > 1) {
                return ['result' => false, 'message' => "Too many sites are currently assigned. Please contact the administrator for assistance."];
            }
        }

        $this->db->begin_transaction();
        try {

            if ($qry->num_rows == 0) {
                throw new Exception('User not found');
            }
            $sql = "INSERT INTO DTR (local_id, date_from, date_to, timekeeper_id, site_id, device_id, file, uploaded_by, employer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";
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

                $sql2 = "INSERT INTO DTR_details (ddtr_id, employee_id, date_time, work_hours, logs, attendance_type, overtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
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
        $site_id =  $post['site_id'];

        $device_id = $post['dtr']['device_id'];
        $file =  $post['dtr']['file'];
        $local_id = $post['dtr']['id'];
        $dtr_details = $post['dtr_details'];
        $ptype = $post['dtr']['weekly_payroll']; //needd to fixed this from mobile
        $qry = $this->db->query("SELECT * FROM users WHERE id = '$timekeeper_id' AND role = 5 ");
        $user_data = $qry->fetch_assoc();
        // $site_id = $user_data['site_id'];
        $employer_id = $user_data['employer_id'];
        $qry_exist = $this->db->query("SELECT * FROM DTR WHERE date_from = '$date_from' AND date_to = '$date_to' AND site_id = '$site_id'   AND ptype='$ptype' LIMIT 1 ");
        if ($qry_exist->num_rows > 0) {
            return ['result' => false, 'message' => 'DTR date already exist'];
        }
        $qry_site = $this->db->query("SELECT * FROM sites WHERE id = '$site_id' AND  status = 1 ");
        if ($qry_site->num_rows === 0) {
            return ['result' => false, 'message' => 'Site is inactive'];
        }

        $qry_site_2 = $this->db->query("SELECT * FROM sites WHERE timekeeper_id = '$timekeeper_id' ");
        if ($qry_site_2->num_rows === 0) {
            return ['result' => false, 'message' => "You're not currently assigned to this site. Please log in again."];
        }

        // $qry_site_2 = $this->db->query("SELECT COUNT(*) AS total_sites FROM sites WHERE timekeeper_id = '$timekeeper_id' AND status = 1");
        // if ($qry_site_2->num_rows > 0) {
        //     $row_site = $qry_site_2->fetch_assoc();
        //     if ($row_site['total_sites'] > 1) {
        //         return ['result' => false, 'message' => "Too many sites are currently assigned. Please contact the administrator for assistance."];
        //     }
        // }

        $this->db->begin_transaction();
        try {
            if ($qry->num_rows == 0) {
                throw new Exception('User not found'); // Throw exception for rollback
            }

            $sql = "INSERT INTO DTR (local_id, date_from, date_to, timekeeper_id, site_id, device_id, file, uploaded_by, employer_id, ptype ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ssssssssss', $local_id, $date_from, $date_to, $timekeeper_id, $site_id, $device_id, $file,  $timekeeper_id, $employer_id, $ptype);
            $stmt->execute();
            $ddtr_id = '';
            if ($stmt->affected_rows == 0) {
                throw new Exception('Failed to insert data'); // Throw exception for rollback
            } else {
                $ddtr_id = $this->db->insert_id; // Assuming $this->db is your connection object
            }

            foreach ($dtr_details as $k) {
                $employee_id = $k['employee_id'];
                $attendance_type = $k['type'];
                $logs = $k['logs'];
                $hours = $k['hours']  > 8 ? 8 : $k['hours'];
                $overtime = $k['ot'];
                $notes = $k['notes'];
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
                $sql2 = "INSERT INTO DTR_details (ddtr_id, employee_id, date_time, work_hours, logs, attendance_type, overtime, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->bind_param('ssssssss', $ddtr_id, $employee_id, $date_time, $hours, $logs, $attendance_type, $overtime, $notes);
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
        $id = $this->db->real_escape_string($_POST['id']);
        $type = isset($_POST['type']) ? $this->db->real_escape_string($_POST['type']) : '';
        $recalculate = isset($type) ? true : false;
        $pay = $this->db->query("SELECT * FROM payroll where id = " . $id)->fetch_array();
        $week = $this->db->real_escape_string($pay['type']);
        $this->db->begin_transaction(); // Start transaction
        $site_ids_string = $pay['site_ids'];
        $weekly_payroll =  $pay['type'] == 5 ? 0 : 1;
        $site_ids = json_decode($site_ids_string, true);
        $commaSeparatedSites = implode(',', $site_ids);
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
            $sql = "SELECT DTR_details.*, employee.salary, employee.allowance_rate, employee.sss_fund, employee.basic_pay, employee.ot_rate,employee.isAutoDeduct, employee.loan_id, employee.loan_deduction, employee.loan, DTR.site_id 
                FROM DTR_details 
                INNER JOIN DTR ON DTR.id = DTR_details.ddtr_id  
                INNER JOIN employee ON  DTR_details.employee_id = employee.id  
                WHERE date(DTR_details.date_time) BETWEEN ? AND ?  AND DTR.status = 2  
                AND DTR.site_id IN ($commaSeparatedSites) AND weekly_payroll=$weekly_payroll";

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
                    $site_id = $row['site_id'];
                    // Check if the employee_id already exists in the count array
                    if (isset($employeeCount[$employee_id])) {
                        // If it exists, increment the count
                        $employeeCount[$employee_id]++;
                    } else {
                        // If it doesn't exist, initialize the count to 1
                        $employeeCount[$employee_id] = 1;
                    }

                    // Cap hours at 8 (1 day)
                    // Cap hours at 8 (1 day)
                    $work_hours = floor($row["work_hours"]) >= 8 ? 8 : $row["work_hours"];

                    // Convert to days using your special rules
                    if ($work_hours == 8) {
                        $days = 1;
                    } else if ($work_hours == 4.5625) {
                        $days = 0.5625;
                    } else {
                        $days = $work_hours / 8;
                    }



                    // Convert to days using your special rules

                    $under_time = 0; // 8 - $work_hours
                    $grouped_data[$employee_id]["under_time"] += $under_time;

                    $per_day = $row['salary'];
                    $basic_pay = $row['basic_pay'];
                    $per_hour = $per_day / 8;
                    $minutesPerDay = 24 * 60;
                    $per_minute =  round($per_day / $minutesPerDay, 2);
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

                    // Add the work hours and pay to the total for the current employee
                    $grouped_data[$employee_id]["total_hours"] += $work_hours;
                    $grouped_data[$employee_id]["salary"] = $salary;
                    $grouped_data[$employee_id]["basic_pay"] = $row['basic_pay'];
                    $grouped_data[$employee_id]["ot_rate"] = $row['ot_rate'];
                    $grouped_data[$employee_id]["sss_fund"] = $row["sss_fund"];
                    $grouped_data[$employee_id]["per_minute"] = $per_minute;
                    $grouped_data[$employee_id]["per_day"] = $per_day;
                    $grouped_data[$employee_id]["present"] += $days;
                    $grouped_data[$employee_id]["overtime"] +=  $row['overtime'];
                    $grouped_data[$employee_id]["late_in_minutes"]  += $row['late'];
                    $grouped_data[$employee_id]["undertime"]  +=  $row['undertime'];
                    $grouped_data[$employee_id]["isAutoDeduct"]  =  $isAutoDeduct;
                    $grouped_data[$employee_id]["site_id"]  = $site_id;
                    $grouped_data[$employee_id]["sss_fund"]  = $sss_fund;
                    $grouped_data[$employee_id]["allowance_amount"]  = $allowance_rate;
                    $grouped_data[$employee_id]["date_time"]  = $row['date_time'];
                }
                foreach ($grouped_data as $employee_id => $data) {
                    $last_attendance = $data['date_time'];
                    $sql2 = "SELECT DTR_details.*, DTR.site_id
                            FROM DTR_details 
                            INNER JOIN DTR ON DTR.id = DTR_details.ddtr_id  
                            INNER JOIN employee ON  DTR_details.employee_id = employee.id  
                            WHERE date(DTR_details.date_time) BETWEEN ? AND ?  AND DTR.status = 2     AND DTR.site_id NOT IN ($commaSeparatedSites)
                            AND weekly_payroll=$weekly_payroll AND employee_id = $employee_id ORDER BY date_time DESC
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
                                "site_id" => $row2["site_id"],
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
                            $query = "SELECT * FROM employee_deductions WHERE employee_id = ?  AND deduction_id = ? ";
                            $stmt = $this->db->prepare($query);
                            $stmt->bind_param("is", $employee_id, $deduction_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $deduction_amount += $row['amount'];
                                $deductions[] = ["amount" => $row['amount'], "deduction_id" => (int)  $row['deduction_id'], "type" => 1];
                            }
                        }

                        if ($setting['type'] == 3) {
                            $clt_id = (int)  $setting['id'];
                            $query = "SELECT * FROM loans WHERE employee_id = ?  AND loan_type = ? ";
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
                                $loans[] = [
                                    "amount" => $damount,
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
        } catch (mysqli_sql_exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
        return ['result' => false, 'message' => 'save'];
    }

    function update_status_user()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = isset($_POST['status']) ? $this->db->real_escape_string($_POST['status']) : '';
        if ($id) {
            $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
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

    function update_status_dtr()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $approved_by = $_SESSION['login_id'];
        $status = 2;
        if ($id) {
            $stmt = $this->db->prepare("UPDATE DTR SET status = ?, approved_by = ? WHERE id = ?");
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

        $pid = null;
        $site_ids = $_POST['site_ids'];
        $decodedQueryString = urldecode($site_ids);
        parse_str($decodedQueryString, $resultArray);
        if (count($resultArray["site_ids"])  === 0) {
            return ['result' => false, 'message' => 'No site selected'];
        }
        $jsonString = json_encode($resultArray["site_ids"]);

        $decodedQueryString2 = urldecode($_POST['form_data']);
        // Parse the query string into an associative array
        parse_str($decodedQueryString2, $resultArray2);
        $id = $resultArray2["id"];
        $p2 = $resultArray2["p2"];
        $date_from = $resultArray2["date_from"];
        $date_to = $resultArray2["date_to"];
        $type = $resultArray2["type"];
        $employer_id = $resultArray2["employer_id"];
        $category = $resultArray2["category_id"];
        //$deferential = $resultArray2["deferential"];
        $deferential = isset($deferential) ? 2 : 1;
        $data = " date_from='$date_from' ";
        $data .= ", date_to = '$date_to' ";
        $data .= ", type = '$type' ";
        // $data .= ", deferential = '$deferential' ";
        $data .= ", site_ids = '$jsonString' ";
        $data .= ", employer_id = '$employer_id' ";
        $data .= ", category = '$category' ";
        $data .= ", p2 = '$p2' ";
        if (empty($id)) {
            $i = 1;
            while ($i == 1) {
                $ref_no = date('Y') . '-' . mt_rand(1, 9999);

                $chk = $this->db->query("SELECT * FROM payroll where ref_no = '$ref_no' ")->num_rows;

                if ($chk <= 0) {
                    $i = 0;
                }
            }
            $data .= ", ref_no='$ref_no' ";
            $save = $this->db->query("INSERT INTO payroll set " . $data);
            $pid = $this->db->insert_id;
            $this->save_payroll_history($this->db->insert_id, 1);
        } else {
            $save = $this->db->query("UPDATE payroll set " . $data . " where id=" . $id);
        }
        if ($save) {
            return ['result' => true, 'message' => 'save', 'id' =>  $pid];
        }
    }

    function get_sites()
    {
        // Assuming you have a valid DB connection in $this->db
        $employer_id = $_POST['employer_id'];
        $category_id = $_POST['category_id'];
        $date_from = date("Y-m-d", strtotime($_POST['date_from']));
        $date_to = date("Y-m-d", strtotime($_POST['date_to']));

        $filter_query = "";
        $disabled = false;
        if ($category_id != 0) {
            $disabled = true;
            $filter_query = "AND sites.cluster_id  = $category_id ";
        }
        $sites = $this->db->query("
            SELECT 
                        sites.*, 
                        users.name, 
                        clusters.cluster,
                         DTR.date_from,
                          DTR.date_to
                    FROM users
                    INNER JOIN sites 
                        ON users.id = sites.timekeeper_id
                    LEFT JOIN clusters 
                        ON clusters.id = sites.cluster_id
                    INNER JOIN DTR 
                        ON DTR.site_id = sites.id
                    WHERE users.role = 5
                    AND sites.status = 1
                    AND sites.employer_id = '$employer_id'
                    AND DTR.date_from BETWEEN '$date_from' AND '$date_to'
                    AND DTR.status = 2 
                    $filter_query
                    GROUP BY sites.id
        ");



        // Start outputting the table with Bootstrap classes
        echo '<div class="container mt-5">';
        echo '<table class="table table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">Select</th>';
        echo '<th scope="col">Site</th>';
        echo '<th scope="col">Cluster</th>';
        echo '<th scope="col">Timekeeper</th>';
        echo '<th scope="col">Approved DTR</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Loop through the rows and create table rows with checkboxes
        while ($row = $sites->fetch_assoc()) {
            echo '<tr>';
            echo '<td class="text-center"><input type="checkbox" name="site_ids[]" value="' . $row['id'] . '"' . ($disabled ? ' onclick="return false;" checked ' : '') . '></td>';
            echo '<td><b><span class="text-primary">(' . htmlspecialchars($row['site_code']) . ')</span>' . htmlspecialchars($row['site_name']) . '</b><p>' . htmlspecialchars($row['site_address']) . '</p></td>';
            echo '<td>' . htmlspecialchars($row['cluster']) . '</td>';
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
        $delete = $this->db->query("DELETE FROM DTR_details where id = " . $id);
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
            $query = "SELECT  * FROM DTR_details
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
                $query_update = "UPDATE DTR_details SET logs = ? WHERE id = ?";
                $stmt3 = $this->db->prepare($query_update);
                if ($stmt3 === false) {
                    throw new Exception('Failed to prepare the statement: ' . $this->db->error);
                }
                $stmt3->bind_param("si", json_encode($updated_logs), $details['id']);
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
                $sql2 = "INSERT INTO DTR_details (ddtr_id, employee_id, date_time, work_hours, logs, attendance_type, overtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
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
                $query_update = "UPDATE DTR_details SET work_hours = ? WHERE id = ?";
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
                $query_update = "UPDATE DTR_details SET overtime = ? WHERE id = ?";
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
                $query_update = "UPDATE DTR_details SET undertime = ? WHERE id = ?";
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
                $query_update = "UPDATE DTR_details SET late = ? WHERE id = ?";
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
        extract($_POST);
        $loan_status = isset($_POST['loan_status']) ? 1 : 0;
        $data = " employee_id=$employee_id ";
        $data .= ", loan_date='$loan_date' ";
        $data .= ", loan_amount = $loan_amount ";
        $data .= ", loan_status = $loan_status ";
        $data .= ", loan_type = $loan_type ";
        $data .= ", loan_balance = $loan_balance ";
        $data .= ", damount = $damount ";
        if (empty($id)) {
            $save = $this->db->query("INSERT INTO loans SET " . $data);
            return 1;
        } else {
            $this->db->query("UPDATE loans set " . $data . " where loan_id=" . $id);
            return 2;
        }
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
                            $new_bal = $current_bal - $amount;
                            $payroll_id = $id;
                            if ($current_bal < $amount) {
                                $amount = $current_bal;
                            }

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

                                if ($loan_status_stmt->affected_rows > 0) {
                                    echo "Loan status updated successfully.";
                                } else {
                                    echo "No rows were updated. Loan ID might not exist.";
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

                                if ($loan_status_stmt->affected_rows > 0) {
                                    echo "Loan balance updated successfully.";
                                } else {
                                    echo "No rows were updated. Loan ID might not exist.";
                                }

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
            $details = $other . ' Payroll';
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
                    // Insert contributions for SSS, PHIC, HDMF
                    $contributions = [
                        ['id' => 1, 'amount' => $sss],
                        ['id' => 2, 'amount' => $phic],
                        ['id' => 3, 'amount' => $hdmf]
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
}
