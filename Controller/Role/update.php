<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/role.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=role&status=error&title=Invalid+Request&message=Please+use+the+role+form.");
    exit;
}

$role = new Role();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$rolename = trim($_POST['rolename'] ?? '');

if ($id <= 0 || $rolename === '') {
    header("Location: ../../index.php?page=role&status=error&title=Required+Field&message=Role+name+is+required.");
    exit;
}

if ($role->roleExists($rolename, $id)) {
    header("Location: ../../index.php?page=role&status=error&title=Duplicate+Role&message=This+role+already+exists.");
    exit;
}

if ($role->updateRole($id, $rolename)) {
    header("Location: ../../index.php?page=role&status=success&title=Success&message=Role+updated+successfully.");
    exit;
}

header("Location: ../../index.php?page=role&status=error&title=Update+Failed&message=The+role+could+not+be+updated.");
exit;