<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/CurriculumSubject.php";

$redirect = "../../index.php?page=curriculum_subject";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirect}&status=error&title=Invalid+Request&message=Please+use+the+curriculum+subject+form.");
    exit;
}

$subjectId = (int) ($_POST['subject_id'] ?? 0);
$programId = (int) ($_POST['program_id'] ?? 0);

if ($subjectId <= 0 || $programId <= 0) {
    header("Location: {$redirect}&status=error&title=Required+Field&message=Please+select+a+subject+and+program.");
    exit;
}

$curriculumSubject = new CurriculumSubject();

if ($curriculumSubject->addCurriculumSubject($subjectId, $programId)) {
    header("Location: {$redirect}&status=success&title=Success&message=Curriculum+subject+created+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Save+Failed&message=The+curriculum+subject+could+not+be+created.");
exit;
