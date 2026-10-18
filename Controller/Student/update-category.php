<?php

require_once __DIR__ . '/../../config/auth.php';
if (isStudentUser()) {
    requireStudentLogin();
} else {
    requireStaffLogin();
}
require_once __DIR__ . '/../../models/studentquery.php';
require_once __DIR__ . '/../../models/Programs.php';

function redirectCategoryStatus($status, $category = '')
{
    if (isStudentUser()) {
        $destination = 'student.php';
        $query = [
            'status' => $status === 'saved' ? 'success' : 'error',
            'category' => $category,
        ];
    } else {
        $destination = 'index.php';
        $query = ['category_' . $status => $category];
    }
    header('Location: ../../' . $destination . '?' . http_build_query($query));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    redirectCategoryStatus('error');
}

$studentId = isStudentUser()
    ? (int) $_SESSION['student_id']
    : filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$category = is_string($_POST['category'] ?? null) ? $_POST['category'] : '';
$combinedStudentUpdate = isStudentUser() && $category === 'all';
$studentModel = new Student();
$student = $studentId ? $studentModel->getStudentById($studentId) : null;

if (!$student) {
    redirectCategoryStatus('error', $category);
}

$value = static function ($key) use ($category) {
    $input = $_POST[$key] ?? '';
    if (!is_string($input)) {
        redirectCategoryStatus('error', $category);
    }
    return trim($input);
};
$namePattern = "/^[A-Za-zÀ-ÖØ-öø-ÿ\\s'.-]+$/u";
$fieldsByCategory = [];

if ($category === 'personal' || $combinedStudentUpdate) {
    $studentNumber = isStudentUser()
        ? (string) $student['Student_Number']
        : $value('student_no');
    $firstName = $value('first_name');
    $middleName = $value('middle_name');
    $lastName = $value('last_name');
    $email = $value('email');
    $dateOfBirth = $value('date_of_birth');
    $gender = $value('gender');
    $civilStatus = $value('civil_status');
    $birthDate = $dateOfBirth !== ''
        ? DateTime::createFromFormat('!Y-m-d', $dateOfBirth)
        : null;

    if (
        !preg_match('/^[0-9]{10}$/', $studentNumber) ||
        !preg_match($namePattern, $firstName) ||
        ($middleName !== '' && !preg_match($namePattern, $middleName)) ||
        !preg_match($namePattern, $lastName) ||
        ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) ||
        ($dateOfBirth !== '' && (!$birthDate || $birthDate->format('Y-m-d') !== $dateOfBirth)) ||
        !in_array($gender, ['', 'Female', 'Male', 'Non-binary', 'Prefer not to say'], true) ||
        !in_array($civilStatus, ['', 'Single', 'Married', 'Divorced', 'Widowed'], true)
    ) {
        redirectCategoryStatus('error', $category);
    }

    if ($studentModel->studentExists($studentNumber, $studentId)) {
        redirectCategoryStatus('error', $category);
    }

    $fieldsByCategory['personal'] = [
        'Student_Number' => $studentNumber,
        'First_Name' => $firstName,
        'Middle_Name' => $middleName,
        'Last_Name' => $lastName,
        'Date_Of_Birth' => $dateOfBirth !== '' ? $dateOfBirth : null,
        'Gender' => $gender,
        'Civil_Status' => $civilStatus,
        'House_Unit_No' => $value('house_unit_no'),
        'Street' => $value('street'),
        'Barangay' => $value('barangay'),
        'City_Municipality' => $value('city_municipality'),
        'Province' => $value('province'),
        'Contact_Number' => $value('contact_number'),
        'Email' => $email,
        'Nationality' => $value('nationality'),
    ];
}

if ($category === 'academic' || $combinedStudentUpdate) {
    $course = $value(isStudentUser() ? 'program_course' : 'course');
    $yearLevel = $value('year_level');
    $schoolYear = $value(isStudentUser() ? 'academic_year' : 'school_year');
    $department = $value('department');
    $currentGpa = $value('current_gpa');

    $programModel = new Program();
    $programs = $programModel->getAllPrograms();
    $validCourses = [];
    if ($programs) {
        while ($program = $programs->fetch_assoc()) {
            $validCourses[] = $program['CourseName'];
        }
    }

    $academicIsEmpty = $course === '' && $yearLevel === '' && $schoolYear === '' && $department === '' && $currentGpa === '';
    $academicIsValid = $course !== '' && $yearLevel !== '' && in_array($course, $validCourses, true);
    if ((!$combinedStudentUpdate && !$academicIsValid) || ($combinedStudentUpdate && !$academicIsEmpty && !$academicIsValid)) {
        redirectCategoryStatus('error', $category);
    }

    $fieldsByCategory['academic'] = [
        'Course' => $course,
        'Year_Level' => $yearLevel,
        'School_Year' => $schoolYear,
        'Department' => $department,
        'Current_GPA' => $currentGpa,
    ];
}

if ($category === 'emergency' || $combinedStudentUpdate) {
    $emergencyEmail = $value('emergency_email');
    $emergencyNames = [
        $value('emergency_first_name'),
        $value('emergency_middle_name'),
        $value('emergency_last_name'),
    ];

    if (
        ($emergencyEmail !== '' && !filter_var($emergencyEmail, FILTER_VALIDATE_EMAIL)) ||
        array_filter($emergencyNames, static function ($name) use ($namePattern) {
            return $name !== '' && !preg_match($namePattern, $name);
        })
    ) {
        redirectCategoryStatus('error', $category);
    }

    $fieldsByCategory['emergency'] = [
        'Emergency_First_Name' => $emergencyNames[0],
        'Emergency_Middle_Name' => $emergencyNames[1],
        'Emergency_Last_Name' => $emergencyNames[2],
        'Emergency_Contact_Number' => $value('emergency_contact_number'),
        'Emergency_House_Unit_No' => $value('emergency_house_unit_no'),
        'Emergency_Street' => $value('emergency_street'),
        'Emergency_Barangay' => $value('emergency_barangay'),
        'Emergency_City_Municipality' => $value('emergency_city_municipality'),
        'Emergency_Province' => $value('emergency_province'),
        'Emergency_Email' => $emergencyEmail,
    ];
}

if ($combinedStudentUpdate) {
    global $conn;
    if (!$conn->begin_transaction()) {
        redirectCategoryStatus('error', $category);
    }

    $updated = true;
    foreach ($fieldsByCategory as $categoryName => $fields) {
        if (!$studentModel->updateStudentCategory($studentId, $categoryName, $fields)) {
            $updated = false;
            break;
        }
    }

    if ($updated) {
        $updated = $conn->commit();
        if (!$updated) {
            $conn->rollback();
        }
    } else {
        $conn->rollback();
    }
} elseif (isset($fieldsByCategory[$category])) {
    $updated = $studentModel->updateStudentCategory($studentId, $category, $fieldsByCategory[$category]);
} else {
    $updated = false;
}

if (!$updated) {
    redirectCategoryStatus('error', $category);
}

redirectCategoryStatus('saved', $category);
