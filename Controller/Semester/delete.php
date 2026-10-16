<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Semester.php";

if (!isset($_GET['id'])) {
    header("Location: ../../index.php?page=semester&error=invalid");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: ../../index.php?page=semester&error=invalid");
    exit;
}

$semesterModel = new Semester();

if ($semesterModel->deleteSemester($id)) {

    header("Location: ../../index.php?page=semester&success=deleted");
    exit;
}

header("Location: ../../index.php?page=semester&error=delete");
exit;