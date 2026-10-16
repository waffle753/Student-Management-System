<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Instructor.php";

if (!isset($_GET['id'])) {
    header("Location: ../../index.php?page=instructor&status=error&title=Invalid+Request&message=Please+select+an+instructor+to+delete.");
    exit;
}

$instructor = new Instructor();
$id = intval($_GET['id']);

if ($id <= 0) {
    header("Location: ../../index.php?page=instructor&status=error&title=Invalid+Record&message=The+selected+instructor+is+invalid.");
    exit;
}

if ($instructor->deleteInstructor($id)) {
    header("Location: ../../index.php?page=instructor&status=success&title=Success&message=Instructor+deleted+successfully.");
    exit;
}

header("Location: ../../index.php?page=instructor&status=error&title=Delete+Failed&message=The+instructor+could+not+be+deleted.");
exit;