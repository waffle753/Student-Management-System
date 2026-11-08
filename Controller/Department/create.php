<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Department.php";

$redirect = "../../index.php?page=department";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirect}&status=error&title=Invalid+Request&message=Please+use+the+department+form.");
    exit;
}

$departmentCode = trim($_POST['department_code'] ?? '');
$departmentName = trim($_POST['department_name'] ?? '');
if ($departmentCode === '' || $departmentName === '' || strlen($departmentCode) > 45 || strlen($departmentName) > 255) {
    header("Location: {$redirect}&status=error&title=Required+Field&message=Department+code+and+name+are+required.");
    exit;
}

$department = new Department();
if ($department->departmentExists($departmentCode)) {
    header("Location: {$redirect}&status=error&title=Duplicate+Code&message=This+department+code+already+exists.");
    exit;
}

if ($department->addDepartment($departmentCode, $departmentName)) {
    header("Location: {$redirect}&status=success&title=Success&message=Department+created+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Save+Failed&message=The+department+could+not+be+created.");
exit;
