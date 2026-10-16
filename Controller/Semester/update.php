<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Semester.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=semester");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$semester_id = trim($_POST['semester_id'] ?? '');
$semester_name = trim($_POST['semester'] ?? '');

// Validate
if (!$id || $id <= 0 || $semester_id === '' || $semester_name === '') {
    header("Location: ../../index.php?page=semester&error=empty");
    exit;
}

if (!preg_match('/^[A-Za-z0-9-]+$/', $semester_id)) {
    header("Location: ../../index.php?page=semester&error=invalid");
    exit;
}

$semesterModel = new Semester();

// Update
if ($semesterModel->updateSemester(
    $id,
    $semester_id,
    $semester_name
)) {

    header("Location: ../../index.php?page=semester&success=updated");
    exit;
}

header("Location: ../../index.php?page=semester&error=update");
exit;