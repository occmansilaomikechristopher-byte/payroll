<?php
include 'db_connect.php';

if ((int)($_SESSION['login_role'] ?? 0) === 9) {
    header('Location: home');
    exit;
}

$can_upload_biometric = in_array((int)($_SESSION['login_role'] ?? 0), [8], true);

$from_input = $_GET['from'] ?? '';
$to_input   = $_GET['to'] ?? '';
$from       = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_input) ? $from_input : '';
$to         = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_input) ? $to_input : '';
if ($from && $to && $from > $to) {
    list($from, $to) = [$to, $from];
}
?>
<style>
    .attendance-log-table th { background-color: #009688; color: white; }
    .attendance-log-table td { font-size: 13px; }
    .log-time-in { color: #28a745; font-weight: 600; }
    .log-time-out { color: #dc3545; font-weight: 600; }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0"><i class="ri-calendar-check-line me-2" style="color:#219688;"></i>Attendance</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Reports</a></li>
                                <li class="breadcrumb-item active">Attendance</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php if ($can_upload_biometric): ?>
                            <form method="get" action="?page=attendance" class="row gy-2 align-items-end">
                                <input type="hidden" name="page" value="attendance">
                                <div class="col-sm-6 col-lg-4">
                                    <div class="input-group">
                                        <span class="input-group-text" style="background:#eef0f8;border-color:#c5cde8;">
                                            <i class="ri-calendar-line" style="color:#009688;"></i>
                                        </span>
                                        <input type="date" name="from" id="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="input-group">
                                        <span class="input-group-text" style="background:#eef0f8;border-color:#c5cde8;">
                                            <i class="ri-calendar-line" style="color:#009688;"></i>
                                        </span>
                                        <input type="date" name="to" id="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ri-filter-3-line me-1"></i> Apply Date Range
                                    </button>
                                    <a href="?page=attendance" class="btn btn-outline-secondary btn-sm">
                                        <i class="ri-close-line me-1"></i> Clear
                                    </a>
                                </div>
                            </form>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" id="open-dtr-upload" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-dtr">
                                    <i class="ri-upload-2-line me-1"></i> Upload Biometric DAT
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-file-list-line me-2" style="color:#009688;"></i>Employee Logs
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover attendance-log-table" id="employeeLogsTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Employee No.</th>
                                            <th>Date</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $login_role = intval($_SESSION['login_role'] ?? 0);
                                        $branch_filter = intval($_SESSION['login_branch_id'] ?? 0);
                                        
                                        $where = "WHERE dd.status IN (0, 1)";
                                        if (!in_array($login_role, [1, 10], true) && $branch_filter > 0) {
                                            $where .= " AND d.branch_id = " . $branch_filter;
                                        }
                                        if ($from && $to) {
                                            $where .= " AND DATE(dd.date_time) BETWEEN '$from' AND '$to'";
                                        } elseif ($from) {
                                            $where .= " AND DATE(dd.date_time) >= '$from'";
                                        } elseif ($to) {
                                            $where .= " AND DATE(dd.date_time) <= '$to'";
                                        }
                                        
                                        $query = $conn->query("SELECT 
                                            e.id, 
                                            CONCAT(e.lastname, ', ', e.firstname, ' ', e.middlename) as employee_name,
                                            e.employee_no,
                                            dd.date_time as log_date,
                                            dd.logs,
                                            dd.attendance_type,
                                            dd.status,
                                            d.date_from,
                                            d.date_to
                                            FROM dtr_details dd
                                            LEFT JOIN employee e ON dd.employee_id = e.id
                                            LEFT JOIN dtr d ON dd.ddtr_id = d.id
                                            $where
                                            ORDER BY dd.date_time DESC, e.lastname ASC
                                            LIMIT 500");
                                        
                                        if ($query && $query->num_rows > 0) {
                                            $current_date = null;
                                            $logs_by_date = [];
                                            
                                            while ($row = $query->fetch_assoc()) {
                                                $date = date('Y-m-d', strtotime($row['log_date']));
                                                $emp_id = $row['id'];
                                                
                                                if (!isset($logs_by_date[$date])) {
                                                    $logs_by_date[$date] = [];
                                                }
                                                if (!isset($logs_by_date[$date][$emp_id])) {
                                                    $logs_by_date[$date][$emp_id] = [
                                                        'name' => $row['employee_name'],
                                                        'emp_no' => $row['employee_no'],
                                                        'date' => $date,
                                                        'date_from' => $row['date_from'],
                                                        'date_to' => $row['date_to'],
                                                        // store raw timestamps to compute earliest in / latest out
                                                        'time_in_ts' => null,
                                                        'time_out_ts' => null,
                                                        'time_in' => null,
                                                        'time_out' => null,
                                                        'status' => $row['status'],
                                                    ];
                                                }

                                                // Prefer the timestamp(s) inside the `logs` JSON (these include time); fall back to date-only value
                                                $logs_json = $row['logs'] ?? '';
                                                $found_any = false;
                                                if ($logs_json) {
                                                    $decoded_logs = json_decode($logs_json, true);
                                                    if (is_array($decoded_logs)) {
                                                        foreach ($decoded_logs as $lg) {
                                                            $dtstr = '';
                                                            if (isset($lg['dateTime'])) $dtstr = $lg['dateTime'];
                                                            elseif (isset($lg['date_time'])) $dtstr = $lg['date_time'];
                                                            if ($dtstr === '') continue;
                                                            $ts = strtotime($dtstr);
                                                            if ($ts === false) continue;
                                                            $found_any = true;
                                                            $att = strtolower(trim((string)$row['attendance_type']));
                                                            $is_in = ($att === 'in' || $att === '1' || strpos($att, 'in') !== false);
                                                            if ($is_in) {
                                                                if ($logs_by_date[$date][$emp_id]['time_in_ts'] === null || $ts < $logs_by_date[$date][$emp_id]['time_in_ts']) {
                                                                    $logs_by_date[$date][$emp_id]['time_in_ts'] = $ts;
                                                                }
                                                            } else {
                                                                if ($logs_by_date[$date][$emp_id]['time_out_ts'] === null || $ts > $logs_by_date[$date][$emp_id]['time_out_ts']) {
                                                                    $logs_by_date[$date][$emp_id]['time_out_ts'] = $ts;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                if (!$found_any) {
                                                    // fallback to the date-only column (treat as midnight)
                                                    $ts = strtotime($row['date'] ?? $row['date_time'] ?? $row['log_date']);
                                                    if ($ts !== false) {
                                                        $att = strtolower(trim((string)$row['attendance_type']));
                                                        $is_in = ($att === 'in' || $att === '1' || strpos($att, 'in') !== false);
                                                        if ($is_in) {
                                                            if ($logs_by_date[$date][$emp_id]['time_in_ts'] === null || $ts < $logs_by_date[$date][$emp_id]['time_in_ts']) {
                                                                $logs_by_date[$date][$emp_id]['time_in_ts'] = $ts;
                                                            }
                                                        } else {
                                                            if ($logs_by_date[$date][$emp_id]['time_out_ts'] === null || $ts > $logs_by_date[$date][$emp_id]['time_out_ts']) {
                                                                $logs_by_date[$date][$emp_id]['time_out_ts'] = $ts;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            // Finalize times (format timestamps) and display consolidated logs
                                            foreach ($logs_by_date as $date => $employees) {
                                                // compute formatted times from stored timestamps
                                                foreach ($employees as $eid => $ldata) {
                                                    if (!empty($ldata['time_in_ts'])) {
                                                        $logs_by_date[$date][$eid]['time_in'] = date('h:i A', $ldata['time_in_ts']);
                                                    }
                                                    if (!empty($ldata['time_out_ts'])) {
                                                        $logs_by_date[$date][$eid]['time_out'] = date('h:i A', $ldata['time_out_ts']);
                                                    }
                                                }

                                                foreach ($employees as $log) {
                                                    $time_in_class = $log['time_in'] ? 'log-time-in' : 'text-muted';
                                                    $time_out_class = $log['time_out'] ? 'log-time-out' : 'text-muted';
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($log['name'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($log['emp_no'] ?? '—') ?></td>
                                            <td>
                                                <div><?= date('M d, Y', strtotime($log['date'])) ?></div>
                                                <?php if (!empty($log['date_from']) && !empty($log['date_to'])): ?>
                                                    <small class="text-muted">Range: <?= date('M d, Y', strtotime($log['date_from'])) ?> - <?= date('M d, Y', strtotime($log['date_to'])) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="<?= $time_in_class ?>">
                                                <?= $log['time_in'] ?? '—' ?>
                                            </td>
                                            <td class="<?= $time_out_class ?>">
                                                <?= $log['time_out'] ?? '—' ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $record_status = intval($log['status'] ?? 0);
                                                if ($log['time_in'] && $log['time_out']): ?>
                                                    <span class="badge bg-success">Complete</span>
                                                <?php elseif ($log['time_in']): ?>
                                                    <span class="badge bg-warning">In Progress</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Incomplete</span>
                                                <?php endif; ?>
                                                <?php if ($record_status == 0): ?>
                                                    <span class="badge bg-info ms-1" title="Pending Approval">📋 Pending</span>
                                                <?php elseif ($record_status == 1): ?>
                                                    <span class="badge bg-success ms-1" title="Approved">✓ Approved</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                                }
                                            }
                                        } else {
                                            echo '<tr><td colspan="6" class="text-center text-muted">No attendance logs found</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($can_upload_biometric): ?>
    <?php include 'component/drt_form.php'; ?>
<?php endif; ?>

<script>
window.addEventListener('DOMContentLoaded', function() {
    // DTR upload modal is now handled by dtr.js
});
</script>