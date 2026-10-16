<?php
require_once __DIR__ . "/config/auth.php";
requireStaffLogin();
include "models/studentquery.php";

if (isset($_GET['id'])) {

    $student = new Student();

    $student->deleteStudent($_GET['id']);

    header("Location: index.php?deleted=1");
    exit;
}
?>