<?php
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_SESSION['login_role'] = 1;
$_SESSION['login_branch_id'] = 0;

ob_start();
include 'dtr-server.php';
$output = ob_get_clean();
file_put_contents('temp_dtr_response_output.txt', $output);
echo $output;
?>