<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/CurriculumSubject.php";

$redirect = "../../index.php?page=curriculum_subject";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirect}&status=error&title=Invalid+Request&message=Please+use+the+curriculum+subject+form.");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$subjectId = (int) ($_POST['subject_id'] ?? 0);
$programId = (int) ($_POST['program_id'] ?? 0);
$yearLevel = trim($_POST['year_level'] ?? '');
$semesterId = (int) ($_POST['semester_id'] ?? 0);

if ($id <= 0 || $subjectId <= 0 || $programId <= 0 || !in_array($yearLevel, ['1', '2', '3', '4', '5'], true) || $semesterId <= 0) {
    header("Location: {$redirect}&status=error&title=Required+Field&message=Please+select+a+subject,+program,+year,+and+semester.");
    exit;
}

$curriculumSubject = new CurriculumSubject();
$validReferences = $curriculumSubject->curriculumReferencesExist($subjectId, $programId, $semesterId);
if ($validReferences === null) {
    header("Location: {$redirect}&status=error&title=Validation+Failed&message=The+selected+subject,+program,+and+semester+could+not+be+validated.");
    exit;
}
if (!$validReferences) {
    header("Location: {$redirect}&status=error&title=Invalid+Selection&message=Select+an+existing+subject,+program,+and+semester.");
    exit;
}

$existingEntry = $curriculumSubject->curriculumEntryExists($subjectId, $programId, $yearLevel, $semesterId, $id);
if ($existingEntry === null) {
    header("Location: {$redirect}&status=error&title=Validation+Failed&message=The+curriculum+assignment+could+not+be+validated.");
    exit;
}
if ($existingEntry) {
    header("Location: {$redirect}&status=error&title=Duplicate+Assignment&message=This+subject+is+already+assigned+to+that+program,+year,+and+semester.");
    exit;
}

if ($curriculumSubject->updateCurriculumSubject($id, $subjectId, $programId, $yearLevel, $semesterId)) {
    header("Location: {$redirect}&status=success&title=Success&message=Curriculum+subject+updated+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&title=Update+Failed&message=The+curriculum+subject+could+not+be+updated.");
exit;
