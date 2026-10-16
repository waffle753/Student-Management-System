<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

require_once "../../models/School_Year.php";

$schoolYear = new School_Year();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update'])) {
    header("Location: ../../index.php?page=schoolyear&status=error&title=Invalid+Request&message=Please+use+the+school+year+form.");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$school_year = trim($_POST['school_year'] ?? '');
$active = $_POST['active'] ?? 'no';

if (!$id || $id <= 0) {
    header("Location: ../../index.php?page=schoolyear&status=error&title=Invalid+Record&message=The+selected+school+year+is+invalid.");
    exit;
}

if ($school_year === '') {
    header("Location: ../../index.php?page=schoolyear&status=error&title=Required+Field&message=School+year+is+required.");
    exit;
}

if (!preg_match('/^\d{4}-\d{4}$/', $school_year)) {
    header("Location: ../../index.php?page=schoolyear&status=error&title=Invalid+School+Year&message=Use+the+format+YYYY-YYYY.");
    exit;
}

$years = explode('-', $school_year);
$startYear = (int) $years[0];
$endYear = (int) $years[1];

if ($endYear !== $startYear + 1) {
    header("Location: ../../index.php?page=schoolyear&status=error&title=Invalid+School+Year&message=The+ending+year+must+be+one+year+after+the+starting+year.");
    exit;
}

$active = ($active === 'yes') ? 'yes' : 'no';

if ($schoolYear->update($id, $school_year, $active)) {
    header("Location: ../../index.php?page=schoolyear&status=success&title=Success&message=School+year+updated+successfully.");
    exit;
}

header("Location: ../../index.php?page=schoolyear&status=error&title=Duplicate+Record&message=This+school+year+already+exists+or+could+not+be+updated.");
exit;