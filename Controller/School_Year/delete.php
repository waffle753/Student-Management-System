<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

require_once "../../models/School_Year.php";

$schoolYear = new School_Year();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=schoolyear&status=error&title=Invalid+Request&message=Please+use+the+school+year+form.");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: ../../index.php?page=schoolyear&status=error&title=Invalid+Record&message=The+selected+school+year+is+invalid.");
    exit;
}

if ($schoolYear->delete($id)) {
    header("Location: ../../index.php?page=schoolyear&status=success&title=Success&message=School+year+deleted+successfully.");
    exit;
}

header("Location: ../../index.php?page=schoolyear&status=error&title=Delete+Failed&message=The+school+year+could+not+be+deleted.");
exit;