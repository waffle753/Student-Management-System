<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/user.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Request&message=Please+use+the+reset+password+form.");
    exit;
}

$user = new User();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$defaultPassword = '123456';

if ($id <= 0) {
    header("Location: ../../index.php?page=user&status=error&title=Required+Field&message=User+is+required.");
    exit;
}

$hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

if ($user->updatePassword($id, $hashedPassword)) {
    header("Location: ../../index.php?page=user&status=success&title=Success&message=Password+reset+to+default+123456.");
    exit;
}

header("Location: ../../index.php?page=user&status=error&title=Reset+Failed&message=The+password+could+not+be+reset.");
exit;
