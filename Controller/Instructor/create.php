<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Instructor.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=instructor&status=error&title=Invalid+Request&message=Please+use+the+instructor+form.");
    exit;
}

$Employee_ID = trim($_POST['Employee_ID'] ?? '');
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');

if ($Employee_ID === '' || $firstname === '' || $lastname === '') {
    header("Location: ../../index.php?page=instructor&status=error&title=Required+Field&message=Employee+ID,+first+name,+and+last+name+are+required.");
    exit;
}

if (!preg_match('/^\d{6}$/', $Employee_ID)) {
    header("Location: ../../index.php?page=instructor&status=error&title=Invalid+Employee+ID&message=Employee+ID+must+be+exactly+6+digits.");
    exit;
}

if (!preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúÜü\s\'.-]+$/u', $firstname) || !preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúÜü\s\'.-]+$/u', $lastname)) {
    header("Location: ../../index.php?page=instructor&status=error&title=Invalid+Name&message=First+and+last+name+cannot+contain+numbers.");
    exit;
}

$instructor = new Instructor();

if ($instructor->instructorExists($Employee_ID)) {
    header("Location: ../../index.php?page=instructor&status=error&title=Duplicate+Employee+ID&message=This+employee+ID+already+exists.");
    exit;
}

if ($instructor->addInstructor($Employee_ID, $firstname, $lastname)) {
    header("Location: ../../index.php?page=instructor&status=success&title=Success&message=Instructor+created+successfully.");
    exit;
}

header("Location: ../../index.php?page=instructor&status=error&title=Save+Failed&message=The+instructor+could+not+be+created.");
exit;
