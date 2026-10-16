<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/CurriculumSubject.php";

$redirect = "../../index.php?page=curriculum_subject";
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: {$redirect}&status=error&title=Invalid+Record&message=The+selected+curriculum+subject+is+invalid.");
    exit;
}

$curriculumSubject = new CurriculumSubject();

if ($curriculumSubject->deleteCurriculumSubject($id)) {
    header("Location: {$redirect}&status=success&title=Success&message=Curriculum+subject+deleted+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Delete+Failed&message=The+curriculum+subject+could+not+be+deleted.");
exit;
