<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Subject.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=subject&status=error&title=Invalid+Request&message=Please+use+the+subject+form.");
    exit;
}

$subject = new Subject();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$SubjectCode = trim($_POST['SubjectCode'] ?? '');
$SubjectName = trim($_POST['SubjectName'] ?? '');
$Unit = filter_var($_POST['Unit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

if ($id <= 0 || $SubjectCode === '' || $SubjectName === '' || $Unit === false) {
    header("Location: ../../index.php?page=subject&status=error&title=Required+Field&message=Subject+code,+subject+name,+and+unit+are+required.");
    exit;
}

if ($subject->subjectExists($SubjectCode, $id)) {
    header("Location: ../../index.php?page=subject&status=error&title=Duplicate+Subject&message=This+subject+code+already+exists.");
    exit;
}

if ($subject->updateSubject($id, $SubjectCode, $SubjectName, $Unit)) {
    header("Location: ../../index.php?page=subject&status=success&title=Success&message=Subject+updated+successfully.");
    exit;
}

header("Location: ../../index.php?page=subject&status=error&title=Update+Failed&message=The+subject+could+not+be+updated.");
exit;