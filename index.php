<?php
require_once __DIR__ . "/config/auth.php";

if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}

if (isStudentUser()) {
    header("Location: student.php");
    exit;
}

include "models/studentquery.php";
include "models/School_Year.php";
include "models/Programs.php";

$student = new Student();
$schoolYearModel = new School_Year();
$programModel = new Program();
$schoolYearOptions = $schoolYearModel->getSchoolYearOptions();
$courseOptions = [];
$programs = $programModel->getAllPrograms();

if ($programs) {
    while ($program = $programs->fetch_assoc()) {
        $courseOptions[] = [
            'code' => $program['CourseCode'],
            'name' => $program['CourseName']
        ];
    }
}

$validCourseOptions = array_fill_keys(array_column($courseOptions, 'name'), true);

$page = $_GET['page'] ?? 'students';

// Handle Add Student
if (isset($_POST['save'])) {

    $student_no = trim($_POST['student_no']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $civilStatus = trim($_POST['civil_status'] ?? '');
    $houseUnitNo = trim($_POST['house_unit_no'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $cityMunicipality = trim($_POST['city_municipality'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email']);
    $nationality = trim($_POST['nationality'] ?? '');
    $course = trim($_POST['course']);
    $yearLevel = trim($_POST['year_level'] ?? '');
    $school_year = trim($_POST['school_year']);
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

    if (
        !empty($fname) &&
        !empty($lname) &&
        !empty($course) &&
        !empty($school_year) &&
        !empty($email) &&
        isset($validCourseOptions[$course])
    ) {
        if ($student->studentExists($student_no)) {
            header("Location: index.php?duplicate=1");
            exit;
        }

        if ($student->addStudent(
            $student_no,
            $fname,
            $mname,
            $lname,
            $course,
            $school_year,
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
            $yearLevel,
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
            header("Location: index.php?success=1");
            exit;
        }
    }
}

$result = $student->getAllStudents();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Student Management System</title>

    <!-- AdminLTE -->
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<style>
    body {
        background: linear-gradient(180deg, #f4f7fb 0%, #eef3f9 100%);
    }

    .main-sidebar,
    .main-header {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .account-menu {
        position: relative;
    }

    .account-menu summary {
        max-width: min(42vw, 260px);
        overflow-wrap: anywhere;
        cursor: pointer;
        list-style: none;
    }

    .account-menu summary::-webkit-details-marker {
        display: none;
    }

    .account-menu-panel {
        position: absolute;
        z-index: 1050;
        top: calc(100% + 8px);
        right: 0;
        width: 220px;
        color: #26383c;
        background: #fff;
        border: 1px solid #dce5e5;
        box-shadow: 0 8px 24px rgba(23, 60, 70, 0.14);
    }

    .card {
        border: 1px solid rgba(13, 110, 253, 0.08);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .card-header {
        border-bottom: none;
        padding: 1rem 1.25rem;
        font-weight: 600;
    }

    .card-body {
        padding: 1.25rem;
    }

    .row {
        margin-bottom: 1rem;
    }

    .row>[class*="col-"] {
        padding-left: 12px;
        padding-right: 12px;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .form-control,
    .custom-select,
    select,
    input,
    textarea {
        border-radius: 10px !important;
        border: 1px solid #dfe7f1 !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .custom-select:focus,
    select:focus,
    input:focus,
    textarea:focus {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.12) !important;
    }

    .btn {
        border-radius: 10px;
        font-weight: 600;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .action-btn {
        width: 80px;
        margin: 0 4px;
        border-radius: 8px;
    }

    .d-flex.justify-content-center.align-items-center {
        gap: 6px;
    }

    .modern-data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }

    .modern-data-table thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #edf2f8 100%);
        color: #1f2937;
        font-size: 15px;
        font-weight: 800;
        border-bottom: 1px solid #dfe7f1;
        border-right: 1px solid #e5e7eb;
        padding: 0.9rem 0.8rem;
        vertical-align: middle;
    }

    .modern-data-table thead th:last-child {
        border-right: none;
    }

    .modern-data-table tbody td {
        padding: 0.85rem 0.8rem;
        vertical-align: middle;
        color: #374151;
        background: #ffffff;
        border-top: 1px solid #edf2f7;
        border-right: 1px solid #edf2f7;
        font-weight: 600;
    }

    .modern-data-table tbody td:last-child {
        border-right: none;
    }

    .modern-data-table tbody tr {
        transition: background-color 0.18s ease, transform 0.15s ease;
    }

    .modern-data-table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.04);
    }

    .modal-content {
        border: none;
        border-radius: 18px;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
    }

    .modal-header {
        border-bottom: none;
        padding: 1rem 1.25rem;
    }

    .modal-footer {
        border-top: none;
        padding: 1rem 1.25rem 1.25rem;
    }

    /* Change font for the student table */
    #studentTable {
        font-size: 15px;
        min-width: 1100px;
        width: 100% !important;
    }

    #studentTable thead th {
        font-size: 16px;
        font-weight: 700;
        color: #212529;
        background-color: #f8f9fa;
    }

    #studentTable tbody td {
        font-size: 15px;
        font-weight: 500;
        color: #212529;
    }

    #studentTable .student-course-cell {
        white-space: normal;
        overflow-wrap: break-word;
        word-break: normal;
    }

    #studentTable .student-actions-cell {
        width: 235px;
        white-space: nowrap;
    }

    #studentTable .student-actions-cell .action-btn {
        width: auto !important;
        min-width: 0;
        padding-left: 0.55rem;
        padding-right: 0.55rem;
        flex: 0 0 auto;
    }

    #studentTable .student-actions-cell>div {
        display: flex;
        flex-wrap: nowrap;
        gap: 4px;
        width: max-content;
    }

    .student-table-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    .student-load-table {
        min-width: 900px;
        margin-bottom: 0;
    }

    .student-load-table th {
        background: #f8f9fa;
        color: #212529;
        font-weight: 700;
        white-space: nowrap;
    }

    .student-load-table td,
    .student-load-table th {
        border-color: #dfe7f1;
        vertical-align: middle;
    }

    #studentTable tbody td {
        overflow-wrap: break-word;
        word-break: normal;
    }

    .nav-sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.08);
    }

    .nav-sidebar .nav-link.active {
        background: rgba(255, 255, 255, 0.12);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    }

    .brand-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 68px;
        padding: 0.75rem 0.85rem;
        text-decoration: none;
    }

    .brand-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        color: #ffffff;
        font-size: 1.15rem;
        line-height: 1;
        flex-shrink: 0;
    }

    .brand-link .brand-text {
        margin-left: 10px;
        color: #ffffff;
        font-size: 0.95rem;
        white-space: nowrap;
        letter-spacing: 0.01em;
    }

    .sidebar-collapse .brand-link {
        justify-content: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .sidebar-collapse .brand-link .brand-text {
        display: none;
    }

    .sidebar-collapse .brand-link .brand-logo {
        margin: 0;
    }
</style>

<body class="hold-transition sidebar-mini">

    <div class="wrapper">

        <!-- Navbar -->

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">

            <ul class="navbar-nav">

                <li class="nav-item">

                    <a class="nav-link" data-widget="pushmenu" href="#">

                        <i class="fas fa-bars"></i>

                    </a>

                </li>

                <li class="nav-item d-none d-sm-inline-block">

                    <a href="index.php" class="nav-link">

                        Student Management System

                    </a>

                </li>

            </ul>

            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item">
                    <details class="account-menu">
                        <summary class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user-circle mr-1" aria-hidden="true"></i>
                            <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
                            <span class="badge badge-light ml-1"><?= htmlspecialchars($_SESSION['role_name'] ?? '') ?></span>
                        </summary>
                        <div class="account-menu-panel">
                            <div class="px-3 py-2 border-bottom">
                                <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>
                                <div class="text-muted small">Role: <?= htmlspecialchars($_SESSION['role_name'] ?? '') ?></div>
                            </div>
                            <form method="post" action="logout.php" class="mb-0 p-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                                <button type="submit" class="btn btn-outline-secondary btn-sm btn-block">
                                    <i class="fas fa-sign-out-alt mr-1" aria-hidden="true"></i>Sign out
                                </button>
                            </form>
                        </div>
                    </details>
                </li>
            </ul>

        </nav>


        <!-- Sidebar -->

        <aside class="main-sidebar sidebar-dark-primary elevation-4">

            <a href="index.php" class="brand-link">

                <span class="brand-logo">
                    <i class="fas fa-graduation-cap"></i>
                </span>

                <span class="brand-text font-weight-light">

                    Student Management System

                </span>

            </a>


            <div class="sidebar">

                <nav class="mt-3">

                    <ul class="nav nav-pills nav-sidebar flex-column">

                        <!-- Students -->

                        <li class="nav-item">

                            <a href="index.php" class="nav-link <?= $page === 'students' ? 'active' : '' ?>">

                                <i class="nav-icon fas fa-user-graduate"></i>

                                <p>Students</p>

                            </a>

                        </li>


                        <!-- Administrator -->

                        <li class="nav-item">

                            <a href="#" class="nav-link" onclick="toggleAdministrator(event)">

                                <i class="nav-icon fas fa-user-cog"></i>

                                <p>

                                    Maintenance

                                    <i class="right fas fa-angle-left"></i>

                                </p>

                            </a>


                            <ul id="administratorMenu" class="nav nav-treeview" style="display: block;">

                                <li class="nav-item">
                                    <a href="index.php?page=user"
                                        class="nav-link <?= $page === 'user' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>User</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=instructor"
                                        class="nav-link <?= $page === 'instructor' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Instructor</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=programs"
                                        class="nav-link <?= $page === 'programs' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Programs</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=role"
                                        class="nav-link <?= $page === 'role' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Role</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=schoolyear"
                                        class="nav-link <?= $page === 'schoolyear' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>School Year</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=semester"
                                        class="nav-link <?= $page === 'semester' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Semester</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=subject"
                                        class="nav-link <?= $page === 'subject' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Subject</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=curriculum_subject"
                                        class="nav-link <?= $page === 'curriculum_subject' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Curriculum Subject</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="index.php?page=class_schedule"
                                        class="nav-link <?= $page === 'class_schedule' ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Class Schedule</p>
                                    </a>
                                </li>

                            </ul>

                        </li>

                    </ul>

                </nav>

            </div>

        </aside>


        <!-- Content -->

        <div class="content-wrapper">

            <?php if ($page === 'user'): ?>

                <?php include "view/user.php"; ?>

            <?php elseif ($page === 'instructor'): ?>

                <?php include "view/instructor.php"; ?>

            <?php elseif ($page === 'programs'): ?>

                <?php include "view/programs.php"; ?>

            <?php elseif ($page === 'role'): ?>

                <?php include "view/role.php"; ?>

            <?php elseif ($page === 'schoolyear'): ?>

                <?php include "view/school_year.php"; ?>

            <?php elseif ($page === 'semester'): ?>

                <?php include "view/semester.php"; ?>

            <?php elseif ($page === 'subject'): ?>

                <?php include "view/subject.php"; ?>

            <?php elseif ($page === 'curriculum_subject'): ?>

                <?php include "view/curriculum_subject.php"; ?>

            <?php elseif ($page === 'class_schedule'): ?>

                <?php include "view/class_schedule.php"; ?>

            <?php else: ?>


                <!-- STUDENT PAGE -->

                <section class="content-header">

                    <div class="container-fluid">

                        <h2>Student Registration</h2>

                    </div>

                </section>


                <section class="content">

                    <div class="container-fluid">


                        <?php if (isset($_GET['success']) || isset($_GET['deleted']) || isset($_GET['updated'])) { ?>

                            <script>

                                Swal.fire({

                                    title: "Success",

                                    text: "Student processed successfully.",

                                    icon: "success",

                                    confirmButtonText: "OK",

                                    confirmButtonColor: "#3085d6",

                                    draggable: false

                                }).then(() => {

                                    window.history.replaceState(
                                        {},
                                        document.title,
                                        "index.php"
                                    );

                                });

                            </script>

                        <?php } ?>


                        <?php if (isset($_GET['duplicate'])) { ?>

                            <script>

                                Swal.fire({

                                    title: "Error",

                                    text: "This student already exists.",

                                    icon: "error",

                                    confirmButtonText: "OK",

                                    confirmButtonColor: "#3085d6",

                                    draggable: false

                                }).then(() => {

                                    window.history.replaceState(
                                        {},
                                        document.title,
                                        "index.php"
                                    );

                                });

                            </script>

                        <?php } ?>


                        <!-- Registration Form -->

                        <div class="card card-primary student-entry-card">

                            <div class="card-header bg-primary text-white">

                                <h3 class="card-title">

                                    <i class="fas fa-user-plus mr-2"></i>
                                    Add New Student

                                </h3>

                            </div>


                            <form method="POST">

                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-md-3">
                                            <label>Student No.</label>
                                            <input type="text" name="student_no" id="add_student_no" class="form-control"
                                                maxlength="10" placeholder="Enter Student No." required>
                                        </div>

                                        <div class="col-md-3">
                                            <label>First Name</label>
                                            <input type="text" id="add_fname" name="fname" class="form-control"
                                                placeholder="Enter First Name" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Middle Name</label>
                                            <input type="text" id="add_mname" name="mname" class="form-control"
                                                placeholder="Enter Middle Name">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Last Name</label>
                                            <input type="text" id="add_lname" name="lname" class="form-control"
                                                placeholder="Enter Last Name" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Date of Birth</label>
                                            <input type="date" name="date_of_birth" class="form-control">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Gender</label>
                                            <select name="gender" class="form-control">
                                                <option value="">Select Gender</option>
                                                <option value="Female">Female</option>
                                                <option value="Male">Male</option>
                                                <option value="Non-binary">Non-binary</option>
                                                <option value="Prefer not to say">Prefer not to say</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Civil Status</label>
                                            <select name="civil_status" class="form-control">
                                                <option value="">Select Status</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Nationality</label>
                                            <input type="text" name="nationality" class="form-control" placeholder="Enter Nationality">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Contact Number</label>
                                            <input type="text" name="contact_number" class="form-control" maxlength="30" placeholder="Enter Contact Number">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Email</label>
                                            <input type="email" id="add_email" name="email" placeholder="Enter Email" class="form-control" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label>House / Unit No.</label>
                                            <input type="text" name="house_unit_no" class="form-control" placeholder="Enter House or Unit No.">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Street</label>
                                            <input type="text" name="street" class="form-control" placeholder="Enter Street">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Barangay</label>
                                            <input type="text" name="barangay" class="form-control" placeholder="Enter Barangay">
                                        </div>

                                        <div class="col-md-3">
                                            <label>City / Municipality</label>
                                            <input type="text" name="city_municipality" class="form-control" placeholder="Enter City or Municipality">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Province</label>
                                            <input type="text" name="province" class="form-control" placeholder="Enter Province">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>Program / Course</label>
                                            <select name="course" class="form-control" required>
                                                <option value="">Select Course</option>
                                                <?php foreach ($courseOptions as $courseOption): ?>
                                                    <option value="<?= htmlspecialchars($courseOption['name']) ?>">
                                                        <?= htmlspecialchars($courseOption['code'] . ' - ' . $courseOption['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Year Level</label>
                                            <input type="text" name="year_level" class="form-control" placeholder="Enter Year Level">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Academic Year</label>
                                            <select name="school_year" class="form-control" required>
                                                <?php if (empty($schoolYearOptions)): ?>
                                                    <option value="">No school year available</option>
                                                <?php else: ?>
                                                    <option value="">Select School Year</option>
                                                    <?php foreach ($schoolYearOptions as $year): ?>
                                                        <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Department</label>
                                            <input type="text" name="department" class="form-control" placeholder="Enter Department">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Current GPA / Grade</label>
                                            <input type="text" name="current_gpa" class="form-control" maxlength="20" placeholder="Enter GPA or Grade">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>Emergency First Name</label>
                                            <input type="text" name="emergency_first_name" class="form-control" placeholder="Enter First Name">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency Middle Name</label>
                                            <input type="text" name="emergency_middle_name" class="form-control" placeholder="Enter Middle Name">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency Last Name</label>
                                            <input type="text" name="emergency_last_name" class="form-control" placeholder="Enter Last Name">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency Contact Number</label>
                                            <input type="text" name="emergency_contact_number" class="form-control" maxlength="30" placeholder="Enter Contact Number">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency Email</label>
                                            <input type="email" name="emergency_email" class="form-control" placeholder="Enter Email">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency House / Unit No.</label>
                                            <input type="text" name="emergency_house_unit_no" class="form-control" placeholder="Enter House or Unit No.">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency Street</label>
                                            <input type="text" name="emergency_street" class="form-control" placeholder="Enter Street">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency Barangay</label>
                                            <input type="text" name="emergency_barangay" class="form-control" placeholder="Enter Barangay">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency City / Municipality</label>
                                            <input type="text" name="emergency_city_municipality" class="form-control" placeholder="Enter City or Municipality">
                                        </div>

                                        <div class="col-md-3">
                                            <label>Emergency Province</label>
                                            <input type="text" name="emergency_province" class="form-control" placeholder="Enter Province">
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer d-flex align-items-center">

                                    <button type="submit" name="save" class="btn btn-primary">

                                        <i class="fas fa-save"></i>

                                        Save Student

                                    </button>


                                    <button type="reset" class="btn btn-secondary ml-2">

                                        Clear

                                    </button>

                                </div>

                            </form>

                        </div>


                        <!-- Student Records -->

                        <div class="card">

                            <div class="card-header bg-primary text-white">

                                <h3 class="card-title">

                                    <i class="fas fa-user-graduate mr-2"></i>
                                    Student Records

                                </h3>

                            </div>


                            <div class="card-body">

                                <div class="table-responsive student-table-wrapper">

                                    <table id="studentTable" class="table modern-data-table">

                                        <thead>

                                            <tr>

                                                <th>ID</th>

                                                <th>Student No.</th>

                                                <th>First Name</th>

                                                <th>Middle Name</th>

                                                <th>Last Name</th>

                                                <th>Course</th>

                                                <th>School Year</th>

                                                <th>Email</th>

                                                <th width="180">Actions</th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php while ($row = $result->fetch_assoc()) { ?>

                                                <tr>

                                                    <td>
                                                        <?= $row['ID']; ?>
                                                    </td>


                                                    <td>
                                                        <?= htmlspecialchars($row['Student_Number']); ?>
                                                    </td>


                                                    <td>
                                                        <?= htmlspecialchars($row['First_Name']); ?>
                                                    </td>


                                                    <td>
                                                        <?= htmlspecialchars($row['Middle_Name']); ?>
                                                    </td>


                                                    <td>
                                                        <?= htmlspecialchars($row['Last_Name']); ?>
                                                    </td>


                                                    <td class="student-course-cell">
                                                        <?= htmlspecialchars($row['Course']); ?>
                                                    </td>


                                                    <td>
                                                        <?= htmlspecialchars($row['School_Year']); ?>
                                                    </td>


                                                    <td>
                                                        <?= htmlspecialchars($row['Email']); ?>
                                                    </td>


                                                    <td class="text-center student-actions-cell">

                                                        <div class="d-flex justify-content-center align-items-center">


                                                            <!-- Edit -->

                                                            <button class="btn btn-info btn-sm action-btn mr-1 viewBtn"
                                                                data-id="<?= $row['ID']; ?>"
                                                                data-studentno="<?= htmlspecialchars($row['Student_Number']); ?>"
                                                                data-fname="<?= htmlspecialchars($row['First_Name']); ?>"
                                                                data-mname="<?= htmlspecialchars($row['Middle_Name']); ?>"
                                                                data-lname="<?= htmlspecialchars($row['Last_Name']); ?>"
                                                                data-course="<?= htmlspecialchars($row['Course']); ?>"
                                                                data-schoolyear="<?= htmlspecialchars($row['School_Year']); ?>"
                                                                data-email="<?= htmlspecialchars($row['Email']); ?>"
                                                                data-toggle="modal" data-target="#viewModal">

                                                                <i class="fas fa-edit"></i>

                                                                Edit

                                                            </button>


                                                            <!-- View -->

                                                            <button class="btn btn-secondary btn-sm action-btn mr-1 infoBtn"
                                                                data-id="<?= $row['ID']; ?>"
                                                                data-studentno="<?= htmlspecialchars($row['Student_Number']); ?>"
                                                                data-fname="<?= htmlspecialchars($row['First_Name']); ?>"
                                                                data-mname="<?= htmlspecialchars($row['Middle_Name']); ?>"
                                                                data-lname="<?= htmlspecialchars($row['Last_Name']); ?>"
                                                                data-course="<?= htmlspecialchars($row['Course']); ?>"
                                                                data-schoolyear="<?= htmlspecialchars($row['School_Year']); ?>"
                                                                data-email="<?= htmlspecialchars($row['Email']); ?>"
                                                                data-toggle="modal" data-target="#infoModal">

                                                                <i class="fas fa-eye"></i>

                                                                View

                                                            </button>


                                                            <!-- Delete -->

                                                            <a href="delete.php?id=<?= $row['ID']; ?>"
                                                                class="btn btn-danger btn-sm action-btn deleteBtn">

                                                                <i class="fas fa-trash"></i>

                                                                Delete

                                                            </a>

                                                        </div>

                                                    </td>

                                                </tr>

                                            <?php } ?>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>


            <?php endif; ?>

        </div>


        <!-- Footer -->

        <footer class="main-footer text-center">

            Student Management System

        </footer>

    </div>


    <!-- View Student Modal -->

    <div class="modal fade" id="infoModal">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-secondary">

                    <h4 class="modal-title">

                        Student Information

                    </h4>


                    <button type="button" class="close" data-dismiss="modal">

                        <span>&times;</span>

                    </button>

                </div>


                <div class="modal-body">


                    <div class="form-group">

                        <label>Student No.</label>

                        <input type="text" class="form-control" id="view_studentno" readonly>

                    </div>


                    <div class="form-group">

                        <label>First Name</label>

                        <input type="text" class="form-control" id="view_fname" readonly>

                    </div>


                    <div class="form-group">

                        <label>Middle Name</label>

                        <input type="text" class="form-control" id="view_mname" readonly>

                    </div>


                    <div class="form-group">

                        <label>Last Name</label>

                        <input type="text" class="form-control" id="view_lname" readonly>

                    </div>


                    <div class="form-group">

                        <label>Course</label>

                        <input type="text" class="form-control" id="view_course" readonly>

                    </div>


                    <div class="form-group">

                        <label>School Year</label>

                        <input type="text" class="form-control" id="view_schoolyear" readonly>

                    </div>


                    <div class="form-group">

                        <label>Email</label>

                        <input type="email" class="form-control" id="view_email" readonly>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="viewStudentLoadBtn">
                        <i class="fas fa-book-open"></i>
                        View Load
                    </button>

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">

                        Close

                    </button>

                </div>

            </div>

        </div>

    </div>


    <!-- View Student Load Modal -->

    <div class="modal fade" id="studentLoadModal">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header bg-secondary text-white">

                    <h4 class="modal-title"><i class="fas fa-book-open mr-2"></i>Student Load</h4>

                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm student-load-table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject</th>
                                    <th>Units</th>
                                    <th>Time</th>
                                    <th>Days</th>
                                    <th>Room</th>
                                    <th>Instructor</th>
                                </tr>
                            </thead>
                            <tbody id="studentLoadBody">
                                <tr><td colspan="7" class="text-center text-muted">Select View Load to load the student's subjects.</td></tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" id="studentLoadDate" class="text-right font-weight-bold">Total Units:</td>
                                    <td id="studentLoadTotal" class="font-weight-bold">0</td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>

            </div>

        </div>

    </div>


    <!-- Edit Student Modal -->

    <div class="modal fade" id="viewModal">

        <div class="modal-dialog">

            <div class="modal-content">

                <form action="update.php" method="POST">


                    <div class="modal-header bg-primary">

                        <h4 class="modal-title">

                            Student Information

                        </h4>


                        <button type="button" class="close" data-dismiss="modal">

                            <span>&times;</span>

                        </button>

                    </div>


                    <div class="modal-body">


                        <input type="hidden" name="id" id="studentID">


                        <div class="form-group">

                            <label>Student No.</label>

                            <input type="text" class="form-control" name="student_no" id="update_student_no"
                                maxlength="10" required>

                        </div>


                        <div class="form-group">

                            <label>First Name</label>

                            <input type="text" class="form-control" name="fname" id="fname" required>

                        </div>


                        <div class="form-group">

                            <label>Middle Name</label>

                            <input type="text" class="form-control" name="mname" id="mname">

                        </div>


                        <div class="form-group">

                            <label>Last Name</label>

                            <input type="text" class="form-control" name="lname" id="lname" required>

                        </div>


                        <div class="form-group">

                            <label>Course</label>

                            <select class="form-control" name="course" id="course" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courseOptions as $courseOption): ?>
                                    <option value="<?= htmlspecialchars($courseOption['name']) ?>">
                                        <?= htmlspecialchars($courseOption['code'] . ' - ' . $courseOption['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>


                        <div class="form-group">

                            <label>School Year</label>

                            <select class="form-control" name="school_year" id="school_year" required>
                                <?php if (empty($schoolYearOptions)): ?>
                                    <option value="">No school year available</option>
                                <?php else: ?>
                                    <option value="">Select School Year</option>
                                    <?php foreach ($schoolYearOptions as $year): ?>
                                        <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                        </div>


                        <div class="form-group">

                            <label>Email</label>

                            <input type="email" class="form-control" name="email" id="update_email" required>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="submit" id="updateBtn" class="btn btn-success" disabled>

                            <i class="fas fa-save"></i>

                            Update

                        </button>


                        <button type="button" class="btn btn-secondary" data-dismiss="modal">

                            Close

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <!-- jQuery -->

    <script src="adminlte/plugins/jquery/jquery.min.js"></script>

    <!-- Bootstrap -->

    <script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- AdminLTE -->

    <script src="adminlte/dist/js/adminlte.min.js"></script>


    <!-- DataTables -->

    <script src="adminlte/plugins/datatables/jquery.dataTables.min.js"></script>

    <script src="adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

    <script src="adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>

    <script src="adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>


    <script>

        $(document).ready(function () {

            // Initialize DataTable

            var table = $('#studentTable').DataTable({

                responsive: false,

                autoWidth: false,

                scrollX: false,

                pageLength: 10,

                dom: 't<"bottom"ip>'

            });



            // Fix table when browser is resized

            $(window).on('resize', function () {

                table.columns.adjust().draw();

            });


            // Fix table after sidebar collapse/expand

            $('[data-widget="pushmenu"]').on('click', function () {

                setTimeout(function () {

                    table.columns.adjust().draw();

                }, 350);

            });

        });


        // View Information Modal

        $(document).ready(function () {

            function setEditOriginals(modalSelector, fieldSelectors, buttonSelector) {
                const modal = $(modalSelector);
                const button = $(buttonSelector);
                const originalValues = {};

                fieldSelectors.forEach(function (fieldSelector) {
                    originalValues[fieldSelector] = modal.find(fieldSelector).val();
                });

                button[0].originalValues = originalValues;
                button.prop('disabled', true);
            }

            function trackEditChanges(modalSelector, fieldSelectors, buttonSelector) {
                const button = $(buttonSelector);

                $(document).on('input change', fieldSelectors.join(', '), function () {
                    const originalValues = button[0].originalValues || {};
                    const changed = fieldSelectors.some(function (fieldSelector) {
                        return $(fieldSelector).val() !== originalValues[fieldSelector];
                    });

                    button.prop('disabled', !changed);
                });
            }

            trackEditChanges('#programEditModal', ['#edit_program_code', '#edit_program_name'], '#updateProgramBtn');
            trackEditChanges('#instructorEditModal', ['#edit_instructor_employee_id', '#edit_instructor_firstname', '#edit_instructor_lastname'], '#updateInstructorBtn');
            trackEditChanges('#roleEditModal', ['#edit_role_name'], '#updateRoleBtn');
            trackEditChanges('#schoolYearEditModal', ['#edit_schoolyear_value', '#edit_schoolyear_active'], '#updateSchoolYearBtn');
            trackEditChanges('#semesterEditModal', ['#edit_semester_code', '#edit_semester_name'], '#updateSemesterBtn');
            trackEditChanges('#subjectEditModal', ['#edit_subject_code', '#edit_subject_name'], '#updateSubjectBtn');

            function escapeHtml(value) {
                return $('<div>').text(value == null ? '' : value).html();
            }

            $('.infoBtn').click(function () {

                $('#viewStudentLoadBtn').data('course', $(this).data('course'));

                $('#view_studentno').val(
                    $(this).data('studentno')
                );

                $('#view_fname').val(
                    $(this).data('fname')
                );

                $('#view_mname').val(
                    $(this).data('mname')
                );

                $('#view_lname').val(
                    $(this).data('lname')
                );

                $('#view_course').val(
                    $(this).data('course')
                );

                $('#view_schoolyear').val(
                    $(this).data('schoolyear')
                );

                $('#view_email').val(
                    $(this).data('email')
                );

            });

            $('#viewStudentLoadBtn').click(function () {
                const studentCourse = $(this).data('course');
                $('#infoModal').modal('hide');
                $('#studentLoadModal').modal('show');
                $('#studentLoadBody').html('<tr><td colspan="7" class="text-center text-muted">Loading student load...</td></tr>');
                $('#studentLoadTotal').text('0');
                $('#studentLoadDate').text('Total Units:');

                fetch(`Controller/Student/load.php?course=${encodeURIComponent(studentCourse)}`)
                    .then((response) => response.json())
                    .then((load) => {
                        if (load.error) {
                            throw new Error(load.error);
                        }

                        if (!load.subjects || load.subjects.length === 0) {
                            $('#studentLoadBody').html('<tr><td colspan="7" class="text-center text-muted">No curriculum subjects found for this program.</td></tr>');
                        } else {
                            const rows = load.subjects.map((subject) => {
                                const time = subject.time_start && subject.time_end
                                    ? `${subject.time_start} - ${subject.time_end}`
                                    : '';
                                const days = String(subject.days || '').replace(/,/g, ' ');
                                return `<tr>
                                    <td>${escapeHtml(subject.SubjectCode)}</td>
                                    <td>${escapeHtml(subject.SubjectName)}</td>
                                    <td>${escapeHtml(subject.Unit)}</td>
                                    <td>${escapeHtml(time)}</td>
                                    <td>${escapeHtml(days)}</td>
                                    <td>${escapeHtml(subject.room)}</td>
                                    <td>${escapeHtml(subject.instructor)}</td>
                                </tr>`;
                            }).join('');
                            $('#studentLoadBody').html(rows);
                        }

                        $('#studentLoadTotal').text(load.total_units || 0);
                    })
                    .catch(() => {
                        $('#studentLoadBody').html('<tr><td colspan="7" class="text-center text-danger">Unable to load the student subjects.</td></tr>');
                    });
            });


            // Student Number Validation

            function validateStudentNumber(input) {

                // Numbers only

                input.value = input.value.replace(/[^0-9]/g, '');


                // Maximum 10 digits

                if (input.value.length > 10) {

                    input.value =
                        input.value.substring(0, 10);

                }


                if (input.value.length == 0) {

                    input.setCustomValidity(
                        "Student Number is required."
                    );

                }
                else if (input.value.length != 10) {

                    input.setCustomValidity(
                        "Student Number must be exactly 10 digits."
                    );

                }
                else {

                    input.setCustomValidity("");

                }

            }


            $("#add_student_no").on("input", function () {

                validateStudentNumber(this);

            });


            $("#update_student_no").on("input", function () {

                validateStudentNumber(this);

            });


            // Name Validation

            function validateName(input) {

                // Allow letters, spaces, periods and hyphens only

                input.value =
                    input.value.replace(
                        /[^a-zA-Z.\-\s]/g,
                        ''
                    );


                if (input.value.trim() == "") {

                    input.setCustomValidity(
                        "This field is required."
                    );

                }
                else {

                    input.setCustomValidity("");

                }

            }


            // Email Validation

            function validateEmail(input) {

                let emailPattern =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


                if (input.value.trim() == "") {

                    input.setCustomValidity(
                        "Email is required."
                    );

                }
                else if (!emailPattern.test(input.value)) {

                    input.setCustomValidity(
                        "Please enter a valid email address."
                    );

                }
                else {

                    input.setCustomValidity("");

                }

            }


            $("#add_email").on("input", function () {

                validateEmail(this);

            });


            $("#update_email").on("input", function () {

                validateEmail(this);

            });


            $("#add_fname, #add_mname, #add_lname").on(
                "input",
                function () {

                    validateName(this);

                }
            );


            $("#fname, #mname, #lname").on(
                "input",
                function () {

                    validateName(this);

                }
            );


            // Update Modal

            let originalData = {};


            $('.viewBtn').click(function () {

                $('#studentID').val(
                    $(this).data('id')
                );

                $('#update_student_no').val(
                    $(this).data('studentno')
                );

                $('#fname').val(
                    $(this).data('fname')
                );

                $('#mname').val(
                    $(this).data('mname')
                );

                $('#lname').val(
                    $(this).data('lname')
                );

                $('#course').val(
                    $(this).data('course')
                );

                $('#school_year').val(
                    $(this).data('schoolyear')
                );

                $('#update_email').val(
                    $(this).data('email')
                );


                originalData = {

                    student_no:
                        $('#update_student_no').val(),

                    fname:
                        $('#fname').val(),

                    mname:
                        $('#mname').val(),

                    lname:
                        $('#lname').val(),

                    course:
                        $('#course').val(),

                    school_year:
                        $('#school_year').val(),

                    email:
                        $('#update_email').val()

                };


                $('#updateBtn').prop(
                    'disabled',
                    true
                );

            });


            $('#update_student_no, #fname, #mname, #lname, #course, #school_year, #update_email')
                .on(
                    'keyup change',
                    function () {

                        let changed =

                            $('#update_student_no').val()
                            !== originalData.student_no ||

                            $('#fname').val()
                            !== originalData.fname ||

                            $('#mname').val()
                            !== originalData.mname ||

                            $('#lname').val()
                            !== originalData.lname ||

                            $('#course').val()
                            !== originalData.course ||

                            $('#school_year').val()
                            !== originalData.school_year ||

                            $('#update_email').val()
                            !== originalData.email;


                        $('#updateBtn').prop(
                            'disabled',
                            !changed
                        );

                    }
                );


            // Add Form Validation

            $('form:not([action])').submit(function (e) {

                let student_no =
                    $('#add_student_no').val().trim();

                let fname =
                    $('#add_fname').val().trim();

                let mname =
                    $('#add_mname').val().trim();

                let lname =
                    $('#add_lname').val().trim();

                let email =
                    $('#add_email').val().trim();

                const validCourses = <?= json_encode(array_column($courseOptions, 'name')) ?>;

                const selectedCourse = $('#add_student_no').closest('form').find('select[name="course"]').val();

                if (!validCourses.includes(selectedCourse)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Course',
                        text: 'Please select a valid course from the list.'
                    });
                    return false;
                }

                if (!/^\d{10}$/.test(student_no)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Student Number',

                        text:
                            'Student Number must contain exactly 10 digits and numbers only.'

                    });

                    return false;

                }


                if (!/^[A-Za-z.\-\s]+$/.test(fname)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid First Name',

                        text:
                            'First Name must contain letters only.'

                    });

                    return false;

                }


                if (
                    mname !== "" &&
                    !/^[A-Za-z.\-\s]+$/.test(mname)
                ) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Middle Name',

                        text:
                            'Middle Name must contain letters only.'

                    });

                    return false;

                }


                if (!/^[A-Za-z.\-\s]+$/.test(lname)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Last Name',

                        text:
                            'Last Name must contain letters only.'

                    });

                    return false;

                }


                let emailPattern =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


                if (!emailPattern.test(email)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Email',

                        text:
                            'Please enter a valid email address (example: student@gmail.com).'

                    });

                    return false;

                }

            });


            // Update Form Validation

            $('form[action="update.php"]').submit(function (e) {

                let student_no =
                    $('#update_student_no').val().trim();

                let fname =
                    $('#fname').val().trim();

                let lname =
                    $('#lname').val().trim();

                let course =
                    $('#course').val().trim();

                let school_year =
                    $('#school_year').val().trim();

                let email =
                    $('#update_email').val().trim();

                const validCourses = <?= json_encode(array_column($courseOptions, 'name')) ?>;

                const selectedCourse = $('#course').val();

                if (!validCourses.includes(selectedCourse)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Course',
                        text: 'Please select a valid course from the list.'
                    });
                    return false;
                }

                if (

                    student_no == "" ||

                    fname == "" ||

                    lname == "" ||

                    course == "" ||

                    school_year == "" ||

                    email == ""

                ) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Validation Error',

                        text:
                            'All required fields must be filled.'

                    });

                    return false;

                }


                // Student Number

                if (!/^\d{10}$/.test(student_no)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Student Number',

                        text:
                            'Student Number must contain exactly 10 digits and numbers only.'

                    });

                    return false;

                }


                // First Name

                if (!/^[A-Za-z.\-\s]+$/.test(fname)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid First Name',

                        text:
                            'First Name must contain letters only.'

                    });

                    return false;

                }


                // Last Name

                if (!/^[A-Za-z.\-\s]+$/.test(lname)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Last Name',

                        text:
                            'Last Name must contain letters only.'

                    });

                    return false;

                }


                // Email

                let emailPattern =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


                if (!emailPattern.test(email)) {

                    e.preventDefault();

                    Swal.fire({

                        icon: 'error',

                        title: 'Invalid Email',

                        text:
                            'Please enter a valid email address.'

                    });

                    return false;

                }

            });


            // Delete Confirmation for Student

            $('.deleteBtn').click(function (e) {

                e.preventDefault();

                let url = $(this).attr('href');

                Swal.fire({

                    title: 'Delete Student?',

                    text: "You won't be able to undo this!",

                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonColor: '#d33',

                    cancelButtonColor: '#3085d6',

                    confirmButtonText: 'Yes, delete it!',

                    cancelButtonText: 'Cancel'

                }).then((result) => {

                    if (result.isConfirmed) {

                        window.location.href = url;

                    }

                });

            });

            // SweetAlert confirms for maintenance delete actions

            $('.delete-user, .delete-instructor, .delete-program, .delete-role, .delete-semester, .delete-subject, .delete-curriculum-subject, .delete-class-schedule').on('click', function (e) {

                e.preventDefault();

                let url = $(this).attr('href');

                Swal.fire({

                    title: 'Delete Record?',

                    text: 'This action cannot be undone.',

                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonColor: '#d33',

                    cancelButtonColor: '#3085d6',

                    confirmButtonText: 'Yes, delete it!',

                    cancelButtonText: 'Cancel'

                }).then((result) => {

                    if (result.isConfirmed) {

                        window.location.href = url;

                    }

                });

            });

            // SweetAlert confirms for school year status changes and deletes

            $('.status-form select').each(function () {
                $(this).data('previous-value', this.value);
            });

            $('.status-form select').on('change', function (e) {
                e.preventDefault();

                const select = this;
                const form = $(this).closest('form')[0];
                const previousValue = $(this).data('previous-value');

                Swal.fire({
                    title: 'Change School Year Status?',
                    text: 'This will update the current school year status.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, update it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(select).data('previous-value', select.value);
                        form.submit();
                    } else {
                        select.value = previousValue;
                    }
                });
            });

            $('.edit-schoolyear').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const currentYear = $(this).data('school-year');
                const activeValue = $(this).closest('tr').find('select[name="active"]').val() || 'no';

                $('#edit_schoolyear_id').val(id);
                $('#edit_schoolyear_value').val(currentYear);
                $('#edit_schoolyear_active').val(activeValue);

                setEditOriginals('#schoolYearEditModal', ['#edit_schoolyear_value', '#edit_schoolyear_active'], '#updateSchoolYearBtn');

                $('#schoolYearEditModal').modal('show');
            });

            $('.edit-semester').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const currentSemesterId = $(this).data('semester-id');
                const currentSemester = $(this).data('semester');

                $('#edit_semester_id').val(id);
                $('#edit_semester_code').val(currentSemesterId);
                $('#edit_semester_name').val(currentSemester);

                setEditOriginals('#semesterEditModal', ['#edit_semester_code', '#edit_semester_name'], '#updateSemesterBtn');

                $('#semesterEditModal').modal('show');
            });

            $('.view-semester').on('click', function (e) {
                e.preventDefault();

                $('#view_semester_id').val($(this).data('id'));
                $('#view_semester_code').val($(this).data('semester-id'));
                $('#view_semester_name').val($(this).data('semester'));

                $('#semesterViewModal').modal('show');
            });

            $('.view-program').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const courseCode = $(this).data('course-code');
                const courseName = $(this).data('course-name');

                $('#view_program_id').val(id);
                $('#view_program_code').val(courseCode);
                $('#view_program_name').val(courseName);

                $('#programViewModal').modal('show');
            });

            $('.edit-program').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const courseCode = $(this).data('course-code');
                const courseName = $(this).data('course-name');

                $('#edit_program_id').val(id);
                $('#edit_program_code').val(courseCode);
                $('#edit_program_name').val(courseName);

                setEditOriginals('#programEditModal', ['#edit_program_code', '#edit_program_name'], '#updateProgramBtn');

                $('#programEditModal').modal('show');
            });

            $('.view-role').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id') || $(this).data('role-id');
                const roleName = $(this).data('role-name');

                $('#view_role_id').val(id);
                $('#view_role_name').val(roleName);

                $('#roleViewModal').modal('show');
            });

            $('.edit-role').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id') || $(this).data('role-id');
                const roleName = $(this).data('role-name');

                $('#edit_role_id').val(id);
                $('#edit_role_name').val(roleName);

                setEditOriginals('#roleEditModal', ['#edit_role_name'], '#updateRoleBtn');

                $('#roleEditModal').modal('show');
            });

            $('.view-instructor').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const employeeId = $(this).data('employee-id');
                const firstName = $(this).data('firstname');
                const lastName = $(this).data('lastname');

                $('#view_instructor_id').val(id);
                $('#view_instructor_employee_id').val(employeeId);
                $('#view_instructor_firstname').val(firstName);
                $('#view_instructor_lastname').val(lastName);

                $('#instructorViewModal').modal('show');
            });

            $('.edit-instructor').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const employeeId = $(this).data('employee-id');
                const firstName = $(this).data('firstname');
                const lastName = $(this).data('lastname');

                $('#edit_instructor_id').val(id);
                $('#edit_instructor_employee_id').val(employeeId);
                $('#edit_instructor_firstname').val(firstName);
                $('#edit_instructor_lastname').val(lastName);

                setEditOriginals('#instructorEditModal', ['#edit_instructor_employee_id', '#edit_instructor_firstname', '#edit_instructor_lastname'], '#updateInstructorBtn');

                $('#instructorEditModal').modal('show');
            });

            $('.view-subject').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const subjectCode = $(this).data('subject-code');
                const subjectName = $(this).data('subject-name');
                const unit = $(this).data('unit');

                $('#view_subject_id').val(id);
                $('#view_subject_code').val(subjectCode);
                $('#view_subject_name').val(subjectName);
                $('#view_subject_unit').val(unit);

                $('#subjectViewModal').modal('show');
            });

            $('.edit-subject').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('id');
                const subjectCode = $(this).data('subject-code');
                const subjectName = $(this).data('subject-name');
                const unit = $(this).data('unit');

                $('#edit_subject_id').val(id);
                $('#edit_subject_code').val(subjectCode);
                $('#edit_subject_name').val(subjectName);
                $('#edit_subject_unit').val(unit);

                setEditOriginals('#subjectEditModal', ['#edit_subject_code', '#edit_subject_name', '#edit_subject_unit'], '#updateSubjectBtn');

                $('#subjectEditModal').modal('show');
            });

            $('.save-program-form').on('submit', function (e) {
                e.preventDefault();

                const form = this;

                Swal.fire({
                    title: 'Save Program?',
                    text: 'This will add the new program record.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, save it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            $(document).on('click', '.delete-program', function (e) {
                e.preventDefault();

                const url = $(this).attr('href');

                Swal.fire({
                    title: 'Delete Program?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });

            $('.delete-schoolyear').closest('form').on('submit', function (e) {

                e.preventDefault();

                const form = this;

                Swal.fire({

                    title: 'Delete School Year?',

                    text: 'This action cannot be undone.',

                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonColor: '#d33',

                    cancelButtonColor: '#3085d6',

                    confirmButtonText: 'Yes, delete it!',

                    cancelButtonText: 'Cancel'

                }).then((result) => {

                    if (result.isConfirmed) {

                        form.submit();

                    }

                });

            });

        });


        function toggleAdministrator(event) {

            event.preventDefault();


            const menu =
                document.getElementById(
                    "administratorMenu"
                );


            if (menu.style.display === "none") {

                menu.style.display = "block";

            }
            else {

                menu.style.display = "none";

            }

        }

    </script>

</body>

</html>