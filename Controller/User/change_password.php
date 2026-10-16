<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/user.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Request&message=Please+use+the+password+form.");
    exit;
}

$user = new User();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$newPassword = trim($_POST['new_password'] ?? '');

if ($id <= 0 || $newPassword === '') {
    header("Location: ../../index.php?page=user&status=error&title=Required+Field&message=New+password+is+required.");
    exit;
}

if (strlen($newPassword) < 6) {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Password&message=Password+must+be+at+least+6+characters+long.");
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

if ($user->updatePassword($id, $hashedPassword)) {
    header("Location: ../../index.php?page=user&status=success&title=Success&message=Password+updated+successfully.");
    exit;
}

header("Location: ../../index.php?page=user&status=error&title=Update+Failed&message=The+password+could+not+be+updated.");
exit;
