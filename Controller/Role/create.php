<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/role.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=role&status=error&title=Invalid+Request&message=Please+use+the+role+form.");
    exit;
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    header("Location: ../../index.php?page=role&status=error&title=Invalid+Request&message=Refresh+the+page+and+try+again.");
    exit;
}

$rolename = trim($_POST['rolename'] ?? '');
$permissions = $_POST['permissions'] ?? [];
$allowedPermissions = array_keys(Role::getPermissionOptions());
$validPermissions = is_array($permissions);
if ($validPermissions) {
    foreach ($permissions as $permission) {
        if (!is_string($permission) || !in_array($permission, $allowedPermissions, true)) {
            $validPermissions = false;
            break;
        }
    }
}

if (
    $rolename === '' || strlen($rolename) > 100 || !$validPermissions
) {
    header("Location: ../../index.php?page=role&status=error&title=Required+Field&message=Role+name+is+required.");
    exit;
}

$permissions = array_values(array_unique($permissions));
$role = new Role();

if ($role->roleExists($rolename)) {
    header("Location: ../../index.php?page=role&status=error&title=Duplicate+Role&message=This+role+already+exists.");
    exit;
}

if ($role->addRole($rolename, $permissions)) {
    header("Location: ../../index.php?page=role&status=success&title=Success&message=Role+created+successfully.");
    exit;
}

header("Location: ../../index.php?page=role&status=error&title=Save+Failed&message=The+role+could+not+be+created.");
exit;
