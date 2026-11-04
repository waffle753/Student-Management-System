<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();
require_once __DIR__ . "/../../models/Section.php";
require_once __DIR__ . "/../../models/Programs.php";

$redirect = "../../index.php?page=section";
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    header("Location: {$redirect}&status=error&message=Invalid+request.");
    exit;
}

$sectionNameInput = $_POST['section_name'] ?? '';
$programIdInput = $_POST['program_id'] ?? '';
$yearLevelInput = $_POST['year_level'] ?? '';
$sectionName = is_string($sectionNameInput) ? strtoupper(trim($sectionNameInput)) : '';
$programIdValue = is_string($programIdInput) ? $programIdInput : '';
$yearLevel = is_string($yearLevelInput) ? $yearLevelInput : '';
$programId = ctype_digit($programIdValue) ? (int) $programIdValue : 0;
if (!preg_match('/^[A-J]$/', $sectionName) || $programId <= 0 || !in_array($yearLevel, ['1', '2', '3', '4', '5'], true)) {
    header("Location: {$redirect}&status=error&message=Enter+a+section+letter+from+A+to+J,+select+a+program,+and+choose+a+year+level.");
    exit;
}

$program = (new Program())->getProgramById($programId);
if (!$program) {
    header("Location: {$redirect}&status=error&message=Select+an+existing+program.");
    exit;
}

$section = new Section();
$duplicate = $section->sectionExists($sectionName, $programId, $yearLevel);
if ($duplicate !== false) {
    $message = $duplicate ? "This+section+already+exists+for+the+selected+program+and+year+level." : "Unable+to+validate+the+section.";
    header("Location: {$redirect}&status=error&message={$message}");
    exit;
}

if ($section->addSection($sectionName, $programId, $yearLevel)) {
    header("Location: {$redirect}&status=success&message=Section+created+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&message=Section+could+not+be+created.");
exit;
