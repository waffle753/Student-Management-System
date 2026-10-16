<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/user.php";

if (!isset($_GET['id'])) {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Request&message=Please+select+a+user+to+delete.");
    exit;
}

$user = new User();
$id = intval($_GET['id']);

if ($id <= 0) {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Record&message=The+selected+user+is+invalid.");
    exit;
}

if ($user->deleteUser($id)) {
    header("Location: ../../index.php?page=user&status=success&title=Success&message=User+deleted+successfully.");
    exit;
}

header("Location: ../../index.php?page=user&status=error&title=Delete+Failed&message=The+user+could+not+be+deleted.");
exit;