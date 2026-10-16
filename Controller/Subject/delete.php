<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Subject.php";

if (!isset($_GET['id'])) {
    header("Location: ../../index.php?page=subject&status=error&title=Invalid+Request&message=Please+select+a+subject+to+delete.");
    exit;
}

$subject = new Subject();
$id = intval($_GET['id']);

if ($id <= 0) {
    header("Location: ../../index.php?page=subject&status=error&title=Invalid+Record&message=The+selected+subject+is+invalid.");
    exit;
}

if ($subject->deleteSubject($id)) {
    header("Location: ../../index.php?page=subject&status=success&title=Success&message=Subject+deleted+successfully.");
    exit;
}

header("Location: ../../index.php?page=subject&status=error&title=Delete+Failed&message=The+subject+could+not+be+deleted.");
exit;
?>