<?php

require_once __DIR__ . '/../../config/auth.php';
requireStaffLogin();

header('Content-Type: application/json; charset=utf-8');
$respond = static function ($status, array $payload) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
};

$roleName = strtolower(trim((string) ($_SESSION['role_name'] ?? '')));
if (strpos($roleName, 'admin') === false) {
    $respond(403, ['error' => 'Only administrators can edit student grades.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, ['error' => 'Method not allowed.']);
}
if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    $respond(403, ['error' => 'Invalid security token. Refresh the page and try again.']);
}

$studentId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$submittedGrades = $_POST['grades'] ?? null;
if ($studentId === false || $studentId === null || !is_array($submittedGrades) || $submittedGrades === []) {
    $respond(400, ['error' => 'Invalid grade submission.']);
}

require_once __DIR__ . '/../../models/studentquery.php';
require_once __DIR__ . '/../../models/Grade.php';
$student = (new Student())->getStudentById($studentId);
if (!$student) {
    $respond(404, ['error' => 'Student record not found.']);
}

$gradeResult = (new Grade())->getGradesForStudent($studentId);
if ($gradeResult === false) {
    $respond(500, ['error' => 'Unable to verify this student’s grades.']);
}
$allowedSchedules = [];
while ($gradeRow = $gradeResult->fetch_assoc()) {
    $allowedSchedules[(int) $gradeRow['class_schedule_id']] = true;
}

$grades = [];
foreach ($submittedGrades as $scheduleIdInput => $gradeInput) {
    $scheduleId = filter_var($scheduleIdInput, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if ($scheduleId === false || $scheduleId === null || !is_string($gradeInput)) {
        $respond(400, ['error' => 'Invalid subject or grade value.']);
    }

    $gradeInput = trim($gradeInput);
    if (!preg_match('/^\d(?:\.\d{1,2})?$/', $gradeInput)
        || (float) $gradeInput < 1
        || (float) $gradeInput > 5) {
        $respond(400, ['error' => 'Grades must be between 1.00 and 5.00, with no more than two decimal places.']);
    }
    if (!isset($allowedSchedules[$scheduleId])) {
        $respond(403, ['error' => 'A submitted subject does not belong to this student.']);
    }

    $grades[$scheduleId] = (float) $gradeInput;
}

if (!(new Grade())->updateStudentGrades($studentId, $grades)) {
    $respond(500, ['error' => 'Unable to update these grades. Please try again.']);
}

$respond(200, ['success' => true]);