<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/role.php";

if (!isset($_GET['id'])) {
    header("Location: ../../index.php?page=role&status=error&title=Invalid+Request&message=Please+select+a+role+to+delete.");
    exit;
}

$role = new Role();
$id = intval($_GET['id']);

if ($id <= 0) {
    header("Location: ../../index.php?page=role&status=error&title=Invalid+Record&message=The+selected+role+is+invalid.");
    exit;
}

if ($role->deleteRole($id)) {
    header("Location: ../../index.php?page=role&status=success&title=Success&message=Role+deleted+successfully.");
    exit;
}

header("Location: ../../index.php?page=role&status=error&title=Delete+Failed&message=The+role+could+not+be+deleted.");
exit;