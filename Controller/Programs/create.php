<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Programs.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=programs");
    exit;
}

$CourseCode = trim($_POST['CourseCode'] ?? '');
$CourseName = trim($_POST['CourseName'] ?? '');

if ($CourseCode === '' || $CourseName === '') {
    header("Location: ../../index.php?page=programs&error=empty");
    exit;
}

$program = new Program();

if ($program->programExists($CourseCode)) {
    header("Location: ../../index.php?page=programs&error=duplicate");
    exit;
}

if ($program->addProgram($CourseCode, $CourseName)) {
    header("Location: ../../index.php?page=programs&success=created");
    exit;
}

header("Location: ../../index.php?page=programs&error=save");
exit;
