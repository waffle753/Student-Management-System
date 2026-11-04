<?php

require_once __DIR__ . '/../../config/auth.php';
requireStudentLogin();
require_once __DIR__ . '/../../models/studentquery.php';
require_once __DIR__ . '/../../models/Semester.php';
require_once __DIR__ . '/../../models/Section.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    header('Location: ../../student.php?status=error');
    exit;
}

$firstName = trim($_POST['first_name'] ?? '');
$middleName = trim($_POST['middle_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$dateOfBirth = trim($_POST['date_of_birth'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$civilStatus = trim($_POST['civil_status'] ?? '');
$houseUnitNo = trim($_POST['house_unit_no'] ?? '');
$street = trim($_POST['street'] ?? '');
$barangay = trim($_POST['barangay'] ?? '');
$cityMunicipality = trim($_POST['city_municipality'] ?? '');
$province = trim($_POST['province'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');
$nationalityOptions = require __DIR__ . '/../../config/nationalities.php';
$programCourse = trim($_POST['program_course'] ?? '');
$yearLevel = trim($_POST['year_level'] ?? '');
$academicYear = trim($_POST['academic_year'] ?? '');
$semesterIdValue = trim($_POST['semester_id'] ?? '');
$semesterId = $semesterIdValue !== '' && ctype_digit($semesterIdValue) ? (int) $semesterIdValue : null;
$sectionIdValue = trim($_POST['section_id'] ?? '');
$sectionId = $sectionIdValue !== '' && ctype_digit($sectionIdValue) ? (int) $sectionIdValue : null;
$department = trim($_POST['department'] ?? '');
$currentGpa = trim($_POST['current_gpa'] ?? '');
$emergencyFirstName = trim($_POST['emergency_first_name'] ?? '');
$emergencyMiddleName = trim($_POST['emergency_middle_name'] ?? '');
$emergencyLastName = trim($_POST['emergency_last_name'] ?? '');
$emergencyContactNumber = trim($_POST['emergency_contact_number'] ?? '');
$emergencyHouseUnitNo = trim($_POST['emergency_house_unit_no'] ?? '');
$emergencyStreet = trim($_POST['emergency_street'] ?? '');
$emergencyBarangay = trim($_POST['emergency_barangay'] ?? '');
$emergencyCityMunicipality = trim($_POST['emergency_city_municipality'] ?? '');
$emergencyProvince = trim($_POST['emergency_province'] ?? '');
$emergencyEmail = trim($_POST['emergency_email'] ?? '');
$namePattern = "/^[A-Za-zÀ-ÖØ-öø-ÿ\\s'.-]+$/u";

if (
    $firstName === '' || $lastName === '' || $email === '' ||
    !preg_match($namePattern, $firstName) ||
    ($middleName !== '' && !preg_match($namePattern, $middleName)) ||
    !preg_match($namePattern, $lastName) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    ($nationality !== '' && !in_array($nationality, $nationalityOptions, true)) ||
    !in_array($gender, ['Female', 'Male'], true) ||
    ($yearLevel !== '' && !in_array($yearLevel, ['1', '2', '3', '4', '5'], true)) ||
    ($semesterIdValue !== '' && $semesterId === null) ||
    ($sectionIdValue !== '' && ($sectionId === null || $sectionId <= 0)) ||
    ($emergencyFirstName !== '' && !preg_match($namePattern, $emergencyFirstName)) ||
    ($emergencyMiddleName !== '' && !preg_match($namePattern, $emergencyMiddleName)) ||
    ($emergencyLastName !== '' && !preg_match($namePattern, $emergencyLastName)) ||
    ($emergencyEmail !== '' && !filter_var($emergencyEmail, FILTER_VALIDATE_EMAIL))
) {
    header('Location: ../../student.php?status=error');
    exit;
}

$sectionMatches = $sectionId !== null
    ? (new Section())->sectionMatchesStudent($sectionId, $programCourse, $yearLevel)
    : true;
if ($sectionMatches !== true) {
    header('Location: ../../student.php?status=error');
    exit;
}

$semesterModel = new Semester();
$semesterRecord = null;
if ($semesterId !== null) {
    $semesterRecord = $semesterModel->getSemesterById($semesterId);
    if ($semesterRecord === false || !$semesterRecord) {
        header('Location: ../../student.php?status=error');
        exit;
    }
}
$semester = (string) ($semesterRecord['semester'] ?? '');

$studentModel = new Student();
if ($studentModel->updateStudentProfile(
    (int) $_SESSION['student_id'],
    $firstName,
    $middleName,
    $lastName,
    $email,
    $dateOfBirth,
    $gender,
    $civilStatus,
    $houseUnitNo,
    $street,
    $barangay,
    $cityMunicipality,
    $province,
    $contactNumber,
    $nationality,
    $programCourse,
    $yearLevel,
    $academicYear,
    $semester,
    $semesterId,
    $sectionId,
    $department,
    $currentGpa,
    $emergencyFirstName,
    $emergencyMiddleName,
    $emergencyLastName,
    $emergencyContactNumber,
    $emergencyHouseUnitNo,
    $emergencyStreet,
    $emergencyBarangay,
    $emergencyCityMunicipality,
    $emergencyProvince,
    $emergencyEmail
)) {
    header('Location: ../../student.php?status=success');
    exit;
}

header('Location: ../../student.php?status=error');
exit;