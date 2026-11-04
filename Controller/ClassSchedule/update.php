<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/ClassSchedule.php";
include_once __DIR__ . "/../../models/Section.php";

$redirect = "../../index.php?page=class_schedule";
$allowedDays = ['M', 'T', 'W', 'Th', 'F', 'S', 'Su'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirect}&status=error&title=Invalid+Request&message=Please+use+the+class+schedule+form.");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$curriculumSubjectId = (int) ($_POST['curriculum_subject_id'] ?? 0);
$sectionIdValue = $_POST['section_id'] ?? '';
$sectionId = is_string($sectionIdValue) && ctype_digit($sectionIdValue) ? (int) $sectionIdValue : 0;
$timeStart = trim($_POST['time_start'] ?? '');
$timeEnd = trim($_POST['time_end'] ?? '');
$days = $_POST['days'] ?? [];
$room = trim($_POST['room'] ?? '');
$instructorId = (int) ($_POST['instructor_id'] ?? 0);
$days = array_values(array_intersect($allowedDays, is_array($days) ? $days : []));
$daysValue = implode(',', $days);

if ($id <= 0 || $curriculumSubjectId <= 0 || $sectionId <= 0 || $instructorId <= 0 || $timeStart === '' || $timeEnd === '' || $daysValue === '' || $room === '' || strlen($room) > 100) {
    header("Location: {$redirect}&status=error&title=Required+Field&message=Please+complete+all+class+schedule+fields.");
    exit;
}

if ($timeStart >= $timeEnd) {
    header("Location: {$redirect}&status=error&title=Invalid+Time&message=End+time+must+be+later+than+start+time.");
    exit;
}

$schedule = new ClassSchedule();
$curriculumSubject = $schedule->getCurriculumSubject($curriculumSubjectId);

if ($curriculumSubject === false) {
    header("Location: {$redirect}&status=error&title=Validation+Failed&message=The+curriculum+subject+could+not+be+validated.");
    exit;
}

if (!$curriculumSubject) {
    header("Location: {$redirect}&status=error&title=Invalid+Curriculum+Subject&message=Select+an+existing+curriculum+subject.");
    exit;
}
if (trim((string) ($curriculumSubject['year_level'] ?? '')) === '' || (int) ($curriculumSubject['semester_id'] ?? 0) <= 0) {
    header("Location: {$redirect}&status=error&title=Incomplete+Curriculum+Subject&message=Assign+the+curriculum+subject+to+a+year+and+semester+before+scheduling+it.");
    exit;
}

$sectionModel = new Section();
$sectionMatches = $sectionModel->sectionMatchesCurriculum($sectionId, (int) $curriculumSubject['program_id'], (string) $curriculumSubject['year_level']);
if ($sectionMatches !== true) {
    header("Location: {$redirect}&status=error&title=Invalid+Section&message=Select+a+section+assigned+to+the+curriculum+subject%27s+program+and+year+level.");
    exit;
}
$sectionRecord = $sectionModel->getSectionById($sectionId);
if (!$sectionRecord) {
    header("Location: {$redirect}&status=error&title=Invalid+Section&message=Select+an+existing+section.");
    exit;
}
$section = (string) $sectionRecord['section_name'];

$conflict = $schedule->hasScheduleConflict(
    $sectionId,
    (int) $curriculumSubject['semester_id'],
    $timeStart,
    $timeEnd,
    $daysValue,
    $id
);
if ($conflict === null) {
    header("Location: {$redirect}&status=error&title=Validation+Failed&message=Unable+to+check+section+schedule+conflicts.");
    exit;
}
if ($conflict) {
    header("Location: {$redirect}&status=error&title=Schedule+Conflict&message=This+section+already+has+a+schedule+at+an+overlapping+time+on+one+of+the+selected+days.");
    exit;
}

if ($schedule->updateClassSchedule($id, $curriculumSubjectId, (int) $curriculumSubject['subject_id'], (int) $curriculumSubject['program_id'], $sectionId, $section, $timeStart, $timeEnd, $daysValue, $room, $instructorId)) {
    header("Location: {$redirect}&status=success&title=Success&message=Class+schedule+updated+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Update+Failed&message=The+class+schedule+could+not+be+updated.");
exit;
