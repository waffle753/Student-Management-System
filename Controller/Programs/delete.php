<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Programs.php";

if (isset($_GET['id'])) {

    $program = new Program();

    $id = intval($_GET['id']);

    if ($program->deleteProgram($id)) {
        header("Location: ../../index.php?page=programs&deleted=1");
        exit;
    }

    header("Location: ../../index.php?page=programs&deleted=0");
    exit;
}