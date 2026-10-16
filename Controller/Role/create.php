<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/role.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=role&status=error&title=Invalid+Request&message=Please+use+the+role+form.");
    exit;
}

$rolename = trim($_POST['rolename'] ?? '');

if ($rolename === '') {
    header("Location: ../../index.php?page=role&status=error&title=Required+Field&message=Role+name+is+required.");
    exit;
}

$role = new Role();

if ($role->roleExists($rolename)) {
    header("Location: ../../index.php?page=role&status=error&title=Duplicate+Role&message=This+role+already+exists.");
    exit;
}

if ($role->addRole($rolename)) {
    header("Location: ../../index.php?page=role&status=success&title=Success&message=Role+created+successfully.");
    exit;
}

header("Location: ../../index.php?page=role&status=error&title=Save+Failed&message=The+role+could+not+be+created.");
exit;
