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

$role = new Role();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
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
    $id <= 0 || $rolename === '' || strlen($rolename) > 100 || !$validPermissions
) {
    header("Location: ../../index.php?page=role&status=error&title=Required+Field&message=Role+name+is+required.");
    exit;
}

$permissions = array_values(array_unique($permissions));
if ($role->roleExists($rolename, $id)) {
    header("Location: ../../index.php?page=role&status=error&title=Duplicate+Role&message=This+role+already+exists.");
    exit;
}

if ($role->updateRole($id, $rolename, $permissions)) {
    header("Location: ../../index.php?page=role&status=success&title=Success&message=Role+updated+successfully.");
    exit;
}

header("Location: ../../index.php?page=role&status=error&title=Update+Failed&message=The+role+could+not+be+updated.");
exit;