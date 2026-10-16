<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Semester.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=semester");
    exit;
}

$semester_id = trim($_POST['semester_id'] ?? '');
$semester_name = trim($_POST['semester'] ?? '');

if ($semester_id === '' || $semester_name === '') {
    header("Location: ../../index.php?page=semester&error=empty");
    exit;
}

if (!preg_match('/^[A-Za-z0-9-]+$/', $semester_id)) {
    header("Location: ../../index.php?page=semester&error=invalid");
    exit;
}

$semesterModel = new Semester();

// Check duplicate Semester ID
if ($semesterModel->semesterExists($semester_id)) {
    header("Location: ../../index.php?page=semester&error=duplicate");
    exit;
}

// Save semester
if ($semesterModel->addSemester($semester_id, $semester_name)) {
    header("Location: ../../index.php?page=semester&success=created");
    exit;
}

// Failed
header("Location: ../../index.php?page=semester&error=save");
exit;