<?php

require_once __DIR__ . '/../../config/auth.php';
if (isStudentUser()) {
    requireStudentLogin();
} else {
    requireStaffLogin();
}
require_once __DIR__ . '/../../models/studentquery.php';
require_once __DIR__ . '/../../models/Programs.php';
require_once __DIR__ . '/../../models/Semester.php';
require_once __DIR__ . '/../../models/Section.php';
require_once __DIR__ . '/../../models/Department.php';

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
    $studentNumber = (string) $student['Student_Number'];
    $firstName = isStudentUser() ? (string) $student['First_Name'] : $value('first_name');
    $middleName = isStudentUser() ? (string) ($student['Middle_Name'] ?? '') : $value('middle_name');
    $lastName = isStudentUser() ? (string) $student['Last_Name'] : $value('last_name');
    $email = $value('email');
    $nationality = $value('nationality');
    $nationalityOptions = require __DIR__ . '/../../config/nationalities.php';
    $dateOfBirth = $value('date_of_birth');
    $gender = $value('gender');
    $civilStatus = $value('civil_status');
    $birthDate = $dateOfBirth !== ''
        ? DateTime::createFromFormat('!Y-m-d', $dateOfBirth)
        : null;
    $age = Student::calculateAge($dateOfBirth);

    if (
        !preg_match('/^(?:[0-9]{10}|[0-9]{12})$/', $studentNumber) ||
        !preg_match($namePattern, $firstName) ||
        ($middleName !== '' && !preg_match($namePattern, $middleName)) ||
        !preg_match($namePattern, $lastName) ||
        ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) ||
        ($nationality !== '' &&
            !in_array($nationality, $nationalityOptions, true) &&
            $nationality !== (string) ($student['Nationality'] ?? '')) ||
        !$birthDate || $birthDate->format('Y-m-d') !== $dateOfBirth || $age === null || $age < 18 ||
        !in_array($gender, ['Female', 'Male'], true) ||
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
        'Nationality' => $nationality,
    ];
}

if ($category === 'academic' || $combinedStudentUpdate) {
    if (isStudentUser()) {
        $course = (string) ($student['Course'] ?? '');
        $yearLevel = (string) ($student['Year_Level'] ?? '');
        $sectionId = !empty($student['Section_ID']) ? (int) $student['Section_ID'] : null;
        $schoolYear = (string) ($student['School_Year'] ?? '');
        $semesterId = !empty($student['Semester_ID']) ? (int) $student['Semester_ID'] : null;
        $department = (string) ($student['Department'] ?? '');
    } else {
        $course = $value('course');
        $yearLevel = $value('year_level');
        $sectionIdValue = $value('section_id');
        if ($sectionIdValue !== '' && (!ctype_digit($sectionIdValue) || (int) $sectionIdValue <= 0)) {
            redirectCategoryStatus('error', $category);
        }
        $sectionId = $sectionIdValue !== '' ? (int) $sectionIdValue : null;
        $schoolYear = $value('school_year');
        $semesterIdValue = $value('semester_id');
        if ($semesterIdValue !== '' && !ctype_digit($semesterIdValue)) {
            redirectCategoryStatus('error', $category);
        }
        $semesterId = $semesterIdValue !== '' ? (int) $semesterIdValue : null;
        $department = $value('department');
        if ($department !== '' && !(new Department())->departmentNameExists($department)) {
            redirectCategoryStatus('error', $category);
        }
    }
    $currentGpa = $value('current_gpa');

    $semesterModel = new Semester();
    $semester = isStudentUser() ? (string) ($student['Semester'] ?? '') : '';
    $semesterRecord = null;
    if ($semesterId !== null) {
        $semesterRecord = $semesterModel->getSemesterById($semesterId);
        if ($semesterRecord === false || !$semesterRecord) {
            redirectCategoryStatus('error', $category);
        }
        $semester = (string) $semesterRecord['semester'];
    }

    $programModel = new Program();
    $programs = $programModel->getAllPrograms();
    $validCourses = [];
    if ($programs) {
        while ($program = $programs->fetch_assoc()) {
            $validCourses[] = $program['CourseName'];
        }
    }

    $academicIsEmpty = $course === '' && $yearLevel === '' && $schoolYear === '' && $semesterId === null && $sectionId === null && $department === '' && $currentGpa === '';
    $academicIsValid = $course !== '' &&
        in_array($yearLevel, ['1', '2', '3', '4', '5'], true) &&
        in_array($course, $validCourses, true);
    if ((!$combinedStudentUpdate && !$academicIsValid) || ($combinedStudentUpdate && !$academicIsEmpty && !$academicIsValid)) {
        redirectCategoryStatus('error', $category);
    }
    if ($sectionId !== null) {
        $sectionMatches = (new Section())->sectionMatchesStudent($sectionId, $course, $yearLevel);
        if ($sectionMatches !== true) {
            redirectCategoryStatus('error', $category);
        }
    }

    $fieldsByCategory['academic'] = [
        'Course' => $course,
        'Year_Level' => $yearLevel,
        'School_Year' => $schoolYear,
        'Semester' => $semester,
        'Semester_ID' => $semesterId,
        'Section_ID' => $sectionId,
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
