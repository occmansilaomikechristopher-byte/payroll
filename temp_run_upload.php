<?php
session_start();
$_SESSION['login_id'] = 1;
$_SESSION['login_role'] = 9;
$_SESSION['login_branch_id'] = 31;

$_FILES['fileBiometric'] = [
    'name' => 'sample_biometric_current.dat',
    'type' => 'application/octet-stream',
    'tmp_name' => __DIR__ . '/sample_biometric_current.dat',
    'error' => UPLOAD_ERR_OK,
    'size' => filesize(__DIR__ . '/sample_biometric_current.dat'),
];

require_once __DIR__ . '/admin_class.php';
$crud = new Action();
$result = $crud->upload_biometric_dtr();
var_dump($result);
?>