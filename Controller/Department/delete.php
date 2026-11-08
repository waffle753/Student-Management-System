<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Department.php";

$redirect = "../../index.php?page=department";
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    header("Location: {$redirect}&status=error&title=Invalid+Record&message=Select+a+valid+department+to+delete.");
    exit;
}

$department = new Department();
if ($department->deleteDepartment($id)) {
    header("Location: {$redirect}&status=success&title=Success&message=Department+deleted+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Delete+Failed&message=The+department+could+not+be+deleted.");
exit;
