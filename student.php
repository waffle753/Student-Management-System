<?php
require_once __DIR__ . '/config/auth.php';
requireStudentLogin();
require_once __DIR__ . '/models/studentquery.php';
require_once __DIR__ . '/models/CurriculumSubject.php';

$studentModel = new Student();
$student = $studentModel->getStudentById((int) $_SESSION['student_id']);
if (!$student) {
    session_unset();
    session_destroy();
    header('Location: login.php?error=invalid');
    exit;
}

$studentView = ($_GET['view'] ?? 'profile') === 'load' ? 'load' : 'profile';
$curriculumSubjectModel = new CurriculumSubject();
$studentLoad = $studentView === 'load'
    ? $curriculumSubjectModel->getStudentLoad($student['Course'])
    : null;

$notice = null;
if (($_GET['status'] ?? '') === 'success') {
    $notice = ['success', 'Your personal information has been updated.'];
} elseif (($_GET['status'] ?? '') === 'error') {
    $notice = ['danger', 'Your changes could not be saved. Please review the information and try again.'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | Student Portal</title>
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <style>
        .brand-logo { display: inline-block; width: 2rem; text-align: center; }
        .account-menu { position: relative; }
        .account-menu summary { max-width: min(42vw, 260px); overflow-wrap: anywhere; cursor: pointer; list-style: none; }
        .account-menu summary::-webkit-details-marker { display: none; }
        .account-menu-panel { position: absolute; z-index: 1050; top: calc(100% + 8px); right: 0; width: 220px; color: #26383c; background: #fff; border: 1px solid #dce5e5; box-shadow: 0 8px 24px rgba(23, 60, 70, .14); }
        .profile-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; padding: 20px 24px; background: #f5f8f8; border-bottom: 1px solid #e2e9e9; }
        .summary-label { color: #687b80; font-size: .8rem; text-transform: uppercase; }
        .summary-value { display: block; margin-top: 4px; color: #173c46; font-weight: 700; overflow-wrap: anywhere; }
        .form-control { min-height: 44px; }
        .student-load-table { min-width: 920px; table-layout: fixed; border-color: #d4dce3; }
        .student-load-table th,
        .student-load-table td { padding: .7rem .8rem; vertical-align: middle; border-color: #d4dce3; }
        .student-load-table thead th { background: #e9edf1; color: #424b55; font-weight: 700; white-space: nowrap; }
        .student-load-table .load-code,
        .student-load-table .load-time,
        .student-load-table .load-days,
        .student-load-table .load-room { white-space: nowrap; }
        .student-load-table tfoot th,
        .student-load-table tfoot td { background: #f7f9fa; }
        @media (max-width: 640px) {
            .profile-summary { grid-template-columns: 1fr; gap: 12px; }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle sidebar">
                        <i class="fas fa-bars" aria-hidden="true"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link">Student Portal</span>
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

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="student.php" class="brand-link">
                <span class="brand-logo"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
                <span class="brand-text font-weight-light">Student Portal</span>
            </a>
            <div class="sidebar">
                <nav class="mt-3" aria-label="Student navigation">
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                        <li class="nav-header">STUDENT</li>
                        <li class="nav-item">
                            <a href="student.php" class="nav-link <?= $studentView === 'profile' ? 'active' : '' ?>" <?= $studentView === 'profile' ? 'aria-current="page"' : '' ?>>
                                <i class="nav-icon fas fa-id-card" aria-hidden="true"></i>
                                <p>Personal Information</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="student.php?view=load" class="nav-link <?= $studentView === 'load' ? 'active' : '' ?>" <?= $studentView === 'load' ? 'aria-current="page"' : '' ?>>
                                <i class="nav-icon fas fa-book-open" aria-hidden="true"></i>
                                <p>View Study Load</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <h1 class="h3"><?= $studentView === 'load' ? 'My Student Load' : 'My Personal Information' ?></h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <?php if ($notice): ?>
                        <div class="alert alert-<?= htmlspecialchars($notice[0]) ?>" role="status">
                            <?= htmlspecialchars($notice[1]) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($studentView === 'profile'): ?>
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h2 class="card-title">Student Record</h2>
                        </div>
                        <div class="profile-summary">
                            <div><span class="summary-label">Student number</span><span class="summary-value"><?= htmlspecialchars($student['Student_Number']) ?></span></div>
                            <div><span class="summary-label">Program</span><span class="summary-value"><?= htmlspecialchars($student['Course']) ?></span></div>
                            <div><span class="summary-label">School year</span><span class="summary-value"><?= htmlspecialchars($student['School_Year']) ?></span></div>
                        </div>
                        <form method="post" action="Controller/Student/update-profile.php">
                            <div class="card-body">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

                                <section class="mb-4">
                                    <h3 class="h5 mb-3">1. Personal Information</h3>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="first_name">First name</label>
                                            <input class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($student['First_Name'] ?? '') ?>" maxlength="100" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="middle_name">Middle name</label>
                                            <input class="form-control" id="middle_name" name="middle_name" value="<?= htmlspecialchars($student['Middle_Name'] ?? '') ?>" maxlength="100">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="last_name">Last name</label>
                                            <input class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($student['Last_Name'] ?? '') ?>" maxlength="100" required>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="date_of_birth">Date of birth</label>
                                            <input class="form-control" id="date_of_birth" name="date_of_birth" type="date" value="<?= htmlspecialchars($student['Date_Of_Birth'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="age">Age</label>
                                            <input class="form-control" id="age" type="text" value="" readonly>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="gender">Gender</label>
                                            <select class="form-control" id="gender" name="gender">
                                                <option value="">Select gender</option>
                                                <option value="Female" <?= ($student['Gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                                <option value="Male" <?= ($student['Gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                                <option value="Non-binary" <?= ($student['Gender'] ?? '') === 'Non-binary' ? 'selected' : '' ?>>Non-binary</option>
                                                <option value="Prefer not to say" <?= ($student['Gender'] ?? '') === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="civil_status">Civil status</label>
                                            <select class="form-control" id="civil_status" name="civil_status">
                                                <option value="">Select status</option>
                                                <option value="Single" <?= ($student['Civil_Status'] ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                                                <option value="Married" <?= ($student['Civil_Status'] ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
                                                <option value="Divorced" <?= ($student['Civil_Status'] ?? '') === 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                                                <option value="Widowed" <?= ($student['Civil_Status'] ?? '') === 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label for="house_unit_no">House / unit no.</label>
                                            <input class="form-control" id="house_unit_no" name="house_unit_no" value="<?= htmlspecialchars($student['House_Unit_No'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="street">Street</label>
                                            <input class="form-control" id="street" name="street" value="<?= htmlspecialchars($student['Street'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="barangay">Barangay</label>
                                            <input class="form-control" id="barangay" name="barangay" value="<?= htmlspecialchars($student['Barangay'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="city_municipality">City / municipality</label>
                                            <input class="form-control" id="city_municipality" name="city_municipality" value="<?= htmlspecialchars($student['City_Municipality'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="province">Province</label>
                                            <input class="form-control" id="province" name="province" value="<?= htmlspecialchars($student['Province'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="contact_number">Contact number</label>
                                            <input class="form-control" id="contact_number" name="contact_number" value="<?= htmlspecialchars($student['Contact_Number'] ?? '') ?>" maxlength="30">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="email">Email address</label>
                                            <input class="form-control" type="email" id="email" name="email" value="<?= htmlspecialchars($student['Email'] ?? '') ?>" maxlength="255" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="nationality">Nationality</label>
                                            <input class="form-control" id="nationality" name="nationality" value="<?= htmlspecialchars($student['Nationality'] ?? '') ?>" maxlength="100">
                                        </div>
                                    </div>
                                </section>

                                <section class="mb-4">
                                    <h3 class="h5 mb-3">2. Academic Information</h3>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label for="student_id">Student ID</label>
                                            <input class="form-control" id="student_id" name="student_id" value="<?= htmlspecialchars($student['Student_Number'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="program_course">Program / course</label>
                                            <input class="form-control" id="program_course" name="program_course" value="<?= htmlspecialchars($student['Course'] ?? '') ?>" maxlength="255">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="year_level">Year level</label>
                                            <input class="form-control" id="year_level" name="year_level" value="<?= htmlspecialchars($student['Year_Level'] ?? '') ?>" maxlength="50">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="academic_year">Academic year</label>
                                            <input class="form-control" id="academic_year" name="academic_year" value="<?= htmlspecialchars($student['School_Year'] ?? '') ?>" maxlength="255">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="department">Department</label>
                                            <input class="form-control" id="department" name="department" value="<?= htmlspecialchars($student['Department'] ?? '') ?>" maxlength="255">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="current_gpa">Current GPA / grade</label>
                                            <input class="form-control" id="current_gpa" name="current_gpa" value="<?= htmlspecialchars($student['Current_GPA'] ?? '') ?>" maxlength="20">
                                        </div>
                                    </div>
                                </section>

                                <section class="mb-4">
                                    <h3 class="h5 mb-3">3. Emergency Contact Information</h3>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="emergency_first_name">First name</label>
                                            <input class="form-control" id="emergency_first_name" name="emergency_first_name" value="<?= htmlspecialchars($student['Emergency_First_Name'] ?? '') ?>" maxlength="100">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="emergency_middle_name">Middle name</label>
                                            <input class="form-control" id="emergency_middle_name" name="emergency_middle_name" value="<?= htmlspecialchars($student['Emergency_Middle_Name'] ?? '') ?>" maxlength="100">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="emergency_last_name">Last name</label>
                                            <input class="form-control" id="emergency_last_name" name="emergency_last_name" value="<?= htmlspecialchars($student['Emergency_Last_Name'] ?? '') ?>" maxlength="100">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="emergency_contact_number">Contact number</label>
                                            <input class="form-control" id="emergency_contact_number" name="emergency_contact_number" value="<?= htmlspecialchars($student['Emergency_Contact_Number'] ?? '') ?>" maxlength="30">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="emergency_email">Email address</label>
                                            <input class="form-control" type="email" id="emergency_email" name="emergency_email" value="<?= htmlspecialchars($student['Emergency_Email'] ?? '') ?>" maxlength="255">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="emergency_house_unit_no">House / unit no.</label>
                                            <input class="form-control" id="emergency_house_unit_no" name="emergency_house_unit_no" value="<?= htmlspecialchars($student['Emergency_House_Unit_No'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label for="emergency_street">Street</label>
                                            <input class="form-control" id="emergency_street" name="emergency_street" value="<?= htmlspecialchars($student['Emergency_Street'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="emergency_barangay">Barangay</label>
                                            <input class="form-control" id="emergency_barangay" name="emergency_barangay" value="<?= htmlspecialchars($student['Emergency_Barangay'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="emergency_city_municipality">City / municipality</label>
                                            <input class="form-control" id="emergency_city_municipality" name="emergency_city_municipality" value="<?= htmlspecialchars($student['Emergency_City_Municipality'] ?? '') ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="emergency_province">Province</label>
                                            <input class="form-control" id="emergency_province" name="emergency_province" value="<?= htmlspecialchars($student['Emergency_Province'] ?? '') ?>">
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save mr-1" aria-hidden="true"></i>Save changes
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="card card-outline card-success">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                            <h2 class="card-title">Current Subjects</h2>
                            <span class="text-muted small mt-1 mt-sm-0">
                                <?= htmlspecialchars($student['Course']) ?>
                                <?php if (!empty($student['School_Year'])): ?>
                                    &middot; <?= htmlspecialchars($student['School_Year']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($studentLoad === false): ?>
                                <div class="alert alert-danger m-3 mb-0" role="alert">Unable to load your subjects right now.</div>
                            <?php elseif (empty($studentLoad['subjects'])): ?>
                                <div class="text-muted text-center py-5">No curriculum subjects are currently listed for your program.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0 student-load-table">
                                        <colgroup>
                                            <col style="width: 14%">
                                            <col style="width: 29%">
                                            <col style="width: 7%">
                                            <col style="width: 20%">
                                            <col style="width: 9%">
                                            <col style="width: 11%">
                                            <col style="width: 10%">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th scope="col" class="load-code">Subject Code</th>
                                                <th scope="col">Subject</th>
                                                <th scope="col" class="text-center">Units</th>
                                                <th scope="col">Time</th>
                                                <th scope="col" class="text-center">Days</th>
                                                <th scope="col" class="load-room">Room</th>
                                                <th scope="col">Instructor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($studentLoad['subjects'] as $subject): ?>
                                                <?php
                                                $subjectCode = trim((string) ($subject['SubjectCode'] ?? ''));
                                                $subjectName = trim((string) ($subject['SubjectName'] ?? ''));
                                                $scheduleTime = trim(
                                                    trim((string) ($subject['time_start'] ?? '')) . ' - ' . trim((string) ($subject['time_end'] ?? '')),
                                                    ' -'
                                                );
                                                $scheduleDays = preg_replace('/\s+/', ' ', str_replace(',', ' ', trim((string) ($subject['days'] ?? ''))));
                                                ?>
                                                <tr>
                                                    <td class="load-code"><?= htmlspecialchars($subjectCode) ?></td>
                                                    <td><?= htmlspecialchars($subjectName) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars((string) ($subject['Unit'] ?? 0)) ?></td>
                                                    <td class="load-time"><?= htmlspecialchars($scheduleTime) ?></td>
                                                    <td class="text-center load-days"><?= htmlspecialchars($scheduleDays) ?></td>
                                                    <td class="load-room"><?= htmlspecialchars(($subject['room'] ?? '') !== '' ? $subject['room'] : 'TBA') ?></td>
                                                    <td><?= htmlspecialchars(($subject['instructor'] ?? '') !== '' ? $subject['instructor'] : 'TBA') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th scope="row" colspan="2" class="text-right">Total Units:</th>
                                                <td class="text-center"><strong><?= htmlspecialchars((string) $studentLoad['total_units']) ?></strong></td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <script src="adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="adminlte/dist/js/adminlte.min.js"></script>
    <script>
        function calculateAge() {
            const birthDate = document.getElementById('date_of_birth').value;
            const ageInput = document.getElementById('age');

            if (!birthDate) {
                ageInput.value = '';
                return;
            }

            const birth = new Date(`${birthDate}T00:00:00`);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const monthDifference = today.getMonth() - birth.getMonth();

            if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birth.getDate())) {
                age -= 1;
            }

            ageInput.value = age >= 0 ? age : '';
        }

        document.getElementById('date_of_birth').addEventListener('input', calculateAge);
        calculateAge();
    </script>
</body>
</html>