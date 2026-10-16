<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Subject.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=subject&status=error&title=Invalid+Request&message=Please+use+the+subject+form.");
    exit;
}

$SubjectCode = trim($_POST['SubjectCode'] ?? '');
$SubjectName = trim($_POST['SubjectName'] ?? '');
$Unit = filter_var($_POST['Unit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

if ($SubjectCode === '' || $SubjectName === '' || $Unit === false) {
    header("Location: ../../index.php?page=subject&status=error&title=Required+Field&message=Subject+code,+subject+name,+and+unit+are+required.");
    exit;
}

$subject = new Subject();

if ($subject->subjectExists($SubjectCode)) {
    header("Location: ../../index.php?page=subject&status=error&title=Duplicate+Subject&message=This+subject+code+already+exists.");
    exit;
}

if ($subject->addSubject($SubjectCode, $SubjectName, $Unit)) {
    header("Location: ../../index.php?page=subject&status=success&title=Success&message=Subject+created+successfully.");
    exit;
}

header("Location: ../../index.php?page=subject&status=error&title=Save+Failed&message=The+subject+could+not+be+created.");
exit;
