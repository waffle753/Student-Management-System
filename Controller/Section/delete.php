<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();
require_once __DIR__ . "/../../models/Section.php";

$redirect = "../../index.php?page=section";
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    header("Location: {$redirect}&status=error&message=Invalid+request.");
    exit;
}

$idInput = $_POST['section_id'] ?? '';
$idValue = is_string($idInput) ? $idInput : '';
$id = ctype_digit($idValue) ? (int) $idValue : 0;
if ($id <= 0) {
    header("Location: {$redirect}&status=error&message=Select+a+valid+section.");
    exit;
}

if ((new Section())->deleteSection($id)) {
    header("Location: {$redirect}&status=success&message=Section+deleted+successfully.");
    exit;
}

header("Location: {$redirect}&status=error&message=Section+could+not+be+deleted.");
exit;
