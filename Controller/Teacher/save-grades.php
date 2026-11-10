<?php

require_once __DIR__ . "/../../config/auth.php";
include_once __DIR__ . "/../../models/studentquery.php";
include_once __DIR__ . "/../../models/ClassSchedule.php";
include_once __DIR__ . "/../../models/Grade.php";

requireStaffLogin();
requireRolePermission('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid security token. Refresh the page and try again.');
}

$scheduleId = filter_var($_POST['schedule_id'] ?? null, FILTER_VALIDATE_INT);
$instructorId = (int) ($_SESSION['instructor_id'] ?? 0);
$submittedGrades = $_POST['grades'] ?? null;
if (!$scheduleId || $scheduleId < 1 || $instructorId < 1 || !is_array($submittedGrades)) {
    http_response_code(400);
    exit('Invalid grade submission.');
}

$scheduleResult = (new ClassSchedule())->getSchedulesForInstructor($instructorId);
$studentResult = (new Student())->getStudentsForInstructor($instructorId);
if (!$scheduleResult || !$studentResult) {
    http_response_code(500);
    exit('Unable to verify the assigned class and students.');
}

$scheduleRows = $scheduleResult->fetch_all(MYSQLI_ASSOC);
$studentRows = $studentResult->fetch_all(MYSQLI_ASSOC);
$assignedSchedule = null;
foreach ($scheduleRows as $scheduleRow) {
    if ((int) ($scheduleRow['id'] ?? 0) === $scheduleId) {
        $assignedSchedule = $scheduleRow;
        break;
    }
}
if ($assignedSchedule === null) {
    http_response_code(403);
    exit('You are not assigned to this class.');
}

$allowedStudentIds = [];
foreach ($studentRows as $studentRow) {
    if ((string) ($studentRow['Program_Name'] ?? '') === (string) ($assignedSchedule['Program_Name'] ?? '')
        && (string) ($studentRow['Year_Level'] ?? '') === (string) ($assignedSchedule['Year_Level'] ?? '')
        && (string) ($studentRow['Section_Name'] ?? '') === (string) ($assignedSchedule['Section_Name'] ?? '')) {
        $allowedStudentIds[(int) ($studentRow['ID'] ?? 0)] = true;
    }
}

$grades = [];
foreach ($submittedGrades as $studentId => $gradeValue) {
    $studentId = filter_var($studentId, FILTER_VALIDATE_INT);
    if ($studentId === false || $studentId < 1 || !is_scalar($gradeValue)) {
        http_response_code(400);
        exit('Invalid student or grade value.');
    }

    $gradeValue = trim((string) $gradeValue);
    if ($gradeValue === '') {
        continue;
    }
    if (!preg_match('/^\d(?:\.\d{1,2})?$/', $gradeValue)
        || (float) $gradeValue < 1
        || (float) $gradeValue > 5) {
        http_response_code(400);
        exit('Grades must be between 1.00 and 5.00, with no more than two decimal places.');
    }
    if (!isset($allowedStudentIds[$studentId])) {
        http_response_code(403);
        exit('A submitted student is not assigned to this class.');
    }

    $grades[$studentId] = (float) $gradeValue;
}

if (!$grades) {
    http_response_code(400);
    exit('Enter at least one grade before saving.');
}

if (!(new Grade())->saveScheduleGrades($scheduleId, $instructorId, $grades)) {
    http_response_code(500);
    exit('Unable to save grades. Please try again.');
}

header(
    'Location: ' . getApplicationBasePath()
    . '/index.php?page=teacher_grades&status=grades_saved&schedule_id=' . $scheduleId
);
exit;
