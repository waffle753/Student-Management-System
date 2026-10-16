<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/CurriculumSubject.php";

header('Content-Type: application/json; charset=utf-8');

$course = trim($_GET['course'] ?? '');

if ($course === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Student course is required.']);
    exit;
}

$curriculumSubject = new CurriculumSubject();
$load = $curriculumSubject->getStudentLoad($course);

if ($load === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load the student curriculum.']);
    exit;
}

echo json_encode($load);