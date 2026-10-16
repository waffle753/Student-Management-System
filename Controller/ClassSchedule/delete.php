<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/ClassSchedule.php";

$redirect = "../../index.php?page=class_schedule";
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: {$redirect}&status=error&title=Invalid+Record&message=The+selected+class+schedule+is+invalid.");
    exit;
}

$schedule = new ClassSchedule();

if ($schedule->deleteClassSchedule($id)) {
    header("Location: {$redirect}&status=success&title=Success&message=Class+schedule+deleted+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Delete+Failed&message=The+class+schedule+could+not+be+deleted.");
exit;
