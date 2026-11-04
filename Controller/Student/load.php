<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/CurriculumSubject.php";

header('Content-Type: application/json; charset=utf-8');

$course = trim($_GET['course'] ?? '');
$yearLevel = trim($_GET['year_level'] ?? '');
$semester = trim($_GET['semester'] ?? '');
$sectionIdInput = $_GET['section_id'] ?? '';
$sectionIdValue = is_string($sectionIdInput) ? trim($sectionIdInput) : '';
if ($sectionIdValue === '' || !ctype_digit($sectionIdValue) || (int) $sectionIdValue <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'The student must have a valid assigned section to load schedules.']);
    exit;
}
$sectionId = (int) $sectionIdValue;
$semesterIdValue = trim($_GET['semester_id'] ?? '');
if ($semesterIdValue !== '' && !ctype_digit($semesterIdValue)) {
    http_response_code(400);
    echo json_encode(['error' => 'Semester selection is invalid.']);
    exit;
}
$semesterId = $semesterIdValue !== '' ? (int) $semesterIdValue : 0;

if ($course === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Student course is required.']);
    exit;
}

$curriculumSubject = new CurriculumSubject();
$load = $curriculumSubject->getStudentLoad($course, $yearLevel, $semester, $semesterId, $sectionId);

if ($load === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load the student curriculum.']);
    exit;
}

echo json_encode($load);