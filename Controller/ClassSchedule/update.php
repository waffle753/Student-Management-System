<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/ClassSchedule.php";

$redirect = "../../index.php?page=class_schedule";
$allowedDays = ['M', 'T', 'W', 'Th', 'F', 'S', 'Su'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirect}&status=error&title=Invalid+Request&message=Please+use+the+class+schedule+form.");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$subjectId = (int) ($_POST['subject_id'] ?? 0);
$timeStart = trim($_POST['time_start'] ?? '');
$timeEnd = trim($_POST['time_end'] ?? '');
$days = $_POST['days'] ?? [];
$room = trim($_POST['room'] ?? '');
$instructorId = (int) ($_POST['instructor_id'] ?? 0);
$days = array_values(array_intersect($allowedDays, is_array($days) ? $days : []));
$daysValue = implode(',', $days);

if ($id <= 0 || $subjectId <= 0 || $instructorId <= 0 || $timeStart === '' || $timeEnd === '' || $daysValue === '' || $room === '') {
    header("Location: {$redirect}&status=error&title=Required+Field&message=Please+complete+all+class+schedule+fields.");
    exit;
}

if ($timeStart >= $timeEnd) {
    header("Location: {$redirect}&status=error&title=Invalid+Time&message=End+time+must+be+later+than+start+time.");
    exit;
}

$schedule = new ClassSchedule();

if ($schedule->updateClassSchedule($id, $subjectId, $timeStart, $timeEnd, $daysValue, $room, $instructorId)) {
    header("Location: {$redirect}&status=success&title=Success&message=Class+schedule+updated+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Update+Failed&message=The+class+schedule+could+not+be+updated.");
exit;
