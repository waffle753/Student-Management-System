<?php
require_once __DIR__ . "/../config/auth.php";
include_once __DIR__ . "/../models/Department.php";
include_once __DIR__ . "/../models/Programs.php";
include_once __DIR__ . "/../models/ClassSchedule.php";
requireStaffLogin();
requireRolePermission('teacher');

$teacherInstructorId = (int) ($_SESSION['instructor_id'] ?? 0);
$teacherStudents = (new Student())->getStudentsForInstructor($teacherInstructorId);
if (!$teacherStudents) {
    die("Unable to load students assigned to the teacher.");
}
$teacherStudentRows = $teacherStudents->fetch_all(MYSQLI_ASSOC);
$teacherStudentCount = count($teacherStudentRows);
$teacherSchedules = (new ClassSchedule())->getSchedulesForInstructor($teacherInstructorId);
if (!$teacherSchedules) {
    die("Unable to load subjects assigned to the teacher.");
}
$teacherScheduleRows = $teacherSchedules->fetch_all(MYSQLI_ASSOC);
$teacherClassCount = count($teacherScheduleRows);
$todayScheduleDay = [
    'Mon' => 'M',
    'Tue' => 'T',
    'Wed' => 'W',
    'Thu' => 'Th',
    'Fri' => 'F',
    'Sat' => 'S',
    'Sun' => 'Su',
][date('D')];
$teacherTodayClassCount = 0;
foreach ($teacherScheduleRows as $scheduleRow) {
    $scheduledDays = array_map('trim', explode(',', (string) ($scheduleRow['days'] ?? '')));
    if (in_array($todayScheduleDay, $scheduledDays, true)) {
        $teacherTodayClassCount++;
    }
}
$teacherStudentsOnly = ($_GET['page'] ?? '') === 'teacher_students';
$teacherClassesOnly = ($_GET['page'] ?? '') === 'teacher_classes';
$teacherGradesOnly = ($_GET['page'] ?? '') === 'teacher_grades';
$teacherFilterOptions = [
    'department' => [],
    'program' => [],
    'year' => [],
];
$teacherSectionOptions = [];
$departmentResult = (new Department())->getAllDepartments();
if (!$departmentResult) {
    die("Unable to load departments for student filters.");
}
while ($department = $departmentResult->fetch_assoc()) {
    $departmentName = (string) $department['department_name'];
    if ($departmentName !== '') {
        $teacherFilterOptions['department'][$departmentName] = $departmentName;
    }
}

$programResult = (new Program())->getAllPrograms();
if (!$programResult) {
    die("Unable to load programs for student filters.");
}
while ($program = $programResult->fetch_assoc()) {
    $programName = (string) $program['CourseName'];
    if ($programName !== '') {
        $teacherFilterOptions['program'][$programName] = $programName;
    }
}

foreach ($teacherStudentRows as $studentRow) {
    $programName = (string) ($studentRow['Program_Name'] ?? '');
    $sectionName = (string) ($studentRow['Section_Name'] ?? '');
    if ($programName !== '' && $sectionName !== '') {
        $teacherSectionOptions[$programName][$sectionName] = $sectionName;
    }

    foreach ([
        'year' => (string) ($studentRow['Year_Level'] ?? ''),
    ] as $filter => $value) {
        if ($value !== '') {
            $teacherFilterOptions[$filter][$value] = $value;
        }
    }
}
foreach ($teacherFilterOptions as &$options) {
    natcasesort($options);
}
unset($options);
foreach ($teacherSectionOptions as &$sections) {
    natcasesort($sections);
}
unset($sections);
?>

<style>
    .teacher-dashboard {
        padding: 1.5rem;
    }

    .teacher-dashboard-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .teacher-dashboard-header h1 {
        margin-bottom: 0.35rem;
        color: #1f2937;
        font-size: 1.65rem;
        font-weight: 700;
    }

    .teacher-dashboard-header p {
        margin-bottom: 0;
        color: #6b7280;
    }

    .teacher-stat-card {
        display: flex;
        align-items: center;
        min-height: 132px;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }

    .teacher-stat-icon {
        display: grid;
        width: 52px;
        height: 52px;
        margin-right: 1rem;
        place-items: center;
        border-radius: 14px;
        background: #edf5ff;
        color: #0d6efd;
        font-size: 1.25rem;
    }

    .teacher-stat-label {
        margin-bottom: 0.35rem;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .teacher-stat-value {
        margin: 0;
        color: #1f2937;
        font-size: 1.65rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .teacher-panel {
        height: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        scroll-margin-top: 1rem;
    }

    .teacher-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #edf2f7;
    }

    .teacher-panel-header h2 {
        margin: 0;
        color: #1f2937;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .teacher-panel-body {
        padding: 1.25rem;
    }

    .teacher-table {
        margin: 0;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .teacher-table thead th {
        padding: 0.85rem 1rem;
        border: 0;
        border-bottom: 1px solid #dfe7f1;
        background: #007bff;
        color: #fff;
        font-weight: 700;
    }

    .teacher-table tbody td {
        padding: 0.85rem 1rem;
        border-top: 1px solid #edf2f7;
        color: #374151;
        font-weight: 600;
        vertical-align: middle;
    }

    #my-student .teacher-table th,
    #my-student .teacher-table td {
        border: 1px solid #dfe7f1;
    }

    .teacher-empty-state {
        padding: 1.75rem 1rem;
        color: #6b7280;
        text-align: center;
    }

    .teacher-empty-state i {
        display: block;
        margin-bottom: 0.6rem;
        color: #94a3b8;
        font-size: 1.5rem;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #my-classes .teacher-panel-header,
        #my-classes .teacher-panel-header h2,
        #my-classes .teacher-panel-body table,
        #my-classes .teacher-panel-body table * {
            visibility: visible !important;
        }

        #my-classes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            border: 0;
            box-shadow: none;
        }

        #my-classes .teacher-panel-header,
        #my-classes .teacher-panel-body {
            padding: 0;
            border: 0;
        }

        #my-classes .teacher-print-button {
            display: none !important;
        }

        #my-classes .teacher-panel-body > :not(.table-responsive),
        #my-classes .table-responsive > :not(table) {
            display: none !important;
        }

        #my-classes .teacher-table thead th {
            display: table-cell !important;
            visibility: visible !important;
            color: #000 !important;
            background: #e9ecef !important;
            border: 1px solid #6c757d !important;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        #my-classes .teacher-table thead {
            display: table-header-group !important;
            visibility: visible !important;
        }
    }

    @media (max-width: 575.98px) {
        .teacher-dashboard {
            padding: 1rem;
        }

        .teacher-dashboard-header {
            display: block;
        }
    }
</style>

<main class="teacher-dashboard">
    <header class="teacher-dashboard-header">
        <div>
            <h1><?= $teacherStudentsOnly ? 'My Students' : ($teacherClassesOnly ? 'My Classes' : ($teacherGradesOnly ? 'Grades' : 'Dashboard')) ?></h1>
            <p>
                <?php if ($teacherStudentsOnly): ?>
                    Students assigned to your classes.
                <?php elseif ($teacherClassesOnly): ?>
                    Subjects assigned to your classes.
                <?php elseif ($teacherGradesOnly): ?>
                    Grade management.
                <?php else: ?>
                    Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Teacher') ?>. Here's your academic overview.
                <?php endif; ?>
            </p>
        </div>
        <span class="badge badge-light p-2"><?= htmlspecialchars(date('l, F j, Y')) ?></span>
    </header>

    <?php if (!$teacherStudentsOnly && !$teacherClassesOnly && !$teacherGradesOnly): ?>
    <section class="row" aria-label="Dashboard summary">
        <div class="col-xl-4 col-md-6 mb-3">
            <article class="teacher-stat-card">
                <div class="teacher-stat-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
                <div>
                    <div class="teacher-stat-label">Students</div>
                    <p class="teacher-stat-value"><?= number_format($teacherStudentCount) ?></p>
                </div>
            </article>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <article class="teacher-stat-card">
                <div class="teacher-stat-icon"><i class="fas fa-chalkboard" aria-hidden="true"></i></div>
                <div>
                    <div class="teacher-stat-label">My Classes</div>
                    <p class="teacher-stat-value"><?= number_format($teacherClassCount) ?></p>
                </div>
            </article>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <article class="teacher-stat-card">
                <div class="teacher-stat-icon"><i class="fas fa-calendar-day" aria-hidden="true"></i></div>
                <div>
                    <div class="teacher-stat-label">Today's Classes</div>
                    <p class="teacher-stat-value"><?= number_format($teacherTodayClassCount) ?></p>
                </div>
            </article>
        </div>
    </section>
    <?php endif; ?>

    <section class="row">
        <?php if ($teacherStudentsOnly): ?>
        <div class="col-12 mb-3">
            <article class="teacher-panel" id="my-student">
                <header class="teacher-panel-header">
                    <h2><i class="fas fa-users mr-2 text-primary" aria-hidden="true"></i>My Students</h2>
                </header>
                <div class="teacher-panel-body">
                    <div class="row teacher-student-filters" aria-label="Filter students">
                        <?php foreach ([
                            'department' => 'Department',
                            'program' => 'Program',
                            'section' => 'Section',
                            'year' => 'Year',
                        ] as $filter => $label): ?>
                            <div class="col-xl-3 col-md-6">
                                <div class="form-group">
                                    <label for="teacher_student_filter_<?= htmlspecialchars($filter) ?>"><?= htmlspecialchars($label) ?></label>
                                    <select class="form-control teacher-student-filter" id="teacher_student_filter_<?= htmlspecialchars($filter) ?>" data-filter="<?= htmlspecialchars($filter) ?>">
                                        <option value="">All <?= htmlspecialchars([
                                            'department' => 'Departments',
                                            'program' => 'Programs',
                                            'section' => 'Sections',
                                            'year' => 'Years',
                                        ][$filter]) ?></option>
                                        <?php if ($filter === 'section'): ?>
                                            <?php $sectionOptionIndex = 0; ?>
                                            <?php foreach ($teacherSectionOptions as $programName => $sections): ?>
                                                <?php foreach ($sections as $sectionName): ?>
                                                    <option value="<?= $sectionOptionIndex++ ?>" data-section="<?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8') ?>" data-program="<?= htmlspecialchars($programName, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($sectionName) ?></option>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <?php foreach ($teacherFilterOptions[$filter] as $option): ?>
                                                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-12 mb-3">
                            <button type="button" class="btn btn-primary" id="teacherApplyStudentFilters">
                                <i class="fas fa-filter" aria-hidden="true"></i> Filter
                            </button>
                            <button type="button" class="btn btn-secondary" id="teacherResetStudentFilters">
                                Reset
                            </button>
                            <button type="button" class="btn btn-success" id="teacherExportStudentsCsv">
                                <i class="fas fa-file-csv" aria-hidden="true"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table teacher-table">
                            <thead>
                                <tr>
                                    <th scope="col">Student ID</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Middle Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col">Program</th>
                                    <th scope="col">Section</th>
                                    <th scope="col">Year</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($teacherStudentCount > 0): ?>
                                    <?php foreach ($teacherStudentRows as $studentRow): ?>
                                        <tr
                                            data-department="<?= htmlspecialchars((string) ($studentRow['Department'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            data-program="<?= htmlspecialchars((string) ($studentRow['Program_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            data-section="<?= htmlspecialchars((string) ($studentRow['Section_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            data-year="<?= htmlspecialchars((string) ($studentRow['Year_Level'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <td><?= htmlspecialchars((string) $studentRow['Student_Number']) ?></td>
                                            <td><?= htmlspecialchars((string) $studentRow['First_Name']) ?></td>
                                            <td><?= htmlspecialchars((string) ($studentRow['Middle_Name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) $studentRow['Last_Name']) ?></td>
                                            <td><?= htmlspecialchars((string) ($studentRow['Program_Name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($studentRow['Section_Name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($studentRow['Year_Level'] ?? '')) ?></td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-secondary btn-sm teacher-view-student"
                                                    data-toggle="modal"
                                                    data-target="#teacherStudentViewModal"
                                                    data-student-id="<?= htmlspecialchars((string) $studentRow['Student_Number'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-first-name="<?= htmlspecialchars((string) $studentRow['First_Name'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-middle-name="<?= htmlspecialchars((string) ($studentRow['Middle_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-last-name="<?= htmlspecialchars((string) $studentRow['Last_Name'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-program="<?= htmlspecialchars((string) ($studentRow['Program_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-section="<?= htmlspecialchars((string) ($studentRow['Section_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-year="<?= htmlspecialchars((string) ($studentRow['Year_Level'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fas fa-eye" aria-hidden="true"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="teacher-no-filter-results" hidden>
                                        <td colspan="8" class="teacher-empty-state">No students match the selected filters.</td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="teacher-empty-state">No students are assigned to your classes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </div>
        <?php endif; ?>
        <?php if ($teacherClassesOnly): ?>
        <div class="col-12 mb-3">
            <article class="teacher-panel" id="my-classes">
                <header class="teacher-panel-header">
                    <h2><i class="fas fa-calendar-alt mr-2 text-primary" aria-hidden="true"></i>My Classes</h2>
                    <button type="button" class="btn btn-primary btn-sm teacher-print-button" id="teacherPrintClasses">
                        <i class="fas fa-print" aria-hidden="true"></i> Print
                    </button>
                </header>
                <div class="teacher-panel-body">
                    <?php if ($teacherScheduleRows): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0 teacher-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Subject Code</th>
                                        <th scope="col">Subject</th>
                                        <th scope="col">Program</th>
                                        <th scope="col">Year</th>
                                        <th scope="col">Semester</th>
                                        <th scope="col">Section</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Days</th>
                                        <th scope="col">Room</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teacherScheduleRows as $scheduleRow): ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) ($scheduleRow['SubjectCode'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($scheduleRow['SubjectName'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($scheduleRow['Program_Name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($scheduleRow['Year_Level'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($scheduleRow['Semester_Name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($scheduleRow['Section_Name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars(substr((string) $scheduleRow['time_start'], 0, 5) . ' - ' . substr((string) $scheduleRow['time_end'], 0, 5)) ?></td>
                                            <td><?= htmlspecialchars(str_replace(',', ', ', (string) ($scheduleRow['days'] ?? ''))) ?></td>
                                            <td><?= htmlspecialchars((string) ($scheduleRow['room'] ?? '')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="teacher-empty-state">No subjects are assigned to your classes yet.</div>
                    <?php endif; ?>
                </div>
            </article>
        </div>
        <?php endif; ?>
        <?php if ($teacherGradesOnly): ?>
        <div class="col-12 mb-3">
            <article class="teacher-panel" id="grades">
                <header class="teacher-panel-header">
                    <h2><i class="fas fa-star mr-2 text-primary" aria-hidden="true"></i>Grades</h2>
                </header>
                <div class="teacher-panel-body teacher-empty-state">
                    Grade management is not available yet.
                </div>
            </article>
        </div>
        <?php endif; ?>
    </section>
</main>

<div class="modal fade" id="teacherStudentViewModal" tabindex="-1" role="dialog" aria-labelledby="teacherStudentViewTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="teacherStudentViewTitle">Student Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="teacher_view_student_id">Student ID</label>
                    <input type="text" class="form-control" id="teacher_view_student_id" readonly>
                </div>
                <div class="form-group">
                    <label for="teacher_view_first_name">First Name</label>
                    <input type="text" class="form-control" id="teacher_view_first_name" readonly>
                </div>
                <div class="form-group">
                    <label for="teacher_view_middle_name">Middle Name</label>
                    <input type="text" class="form-control" id="teacher_view_middle_name" readonly>
                </div>
                <div class="form-group">
                    <label for="teacher_view_last_name">Last Name</label>
                    <input type="text" class="form-control" id="teacher_view_last_name" readonly>
                </div>
                <div class="form-group">
                    <label for="teacher_view_program">Program</label>
                    <input type="text" class="form-control" id="teacher_view_program" readonly>
                </div>
                <div class="form-group">
                    <label for="teacher_view_section">Section</label>
                    <input type="text" class="form-control" id="teacher_view_section" readonly>
                </div>
                <div class="form-group mb-0">
                    <label for="teacher_view_year">Year</label>
                    <input type="text" class="form-control" id="teacher_view_year" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#teacherPrintClasses').on('click', function () {
        window.print();
    });

    $('#teacher_student_filter_program').on('change', function () {
        const selectedProgram = this.value;
        const $sectionFilter = $('#teacher_student_filter_section');
        const selectedSection = $sectionFilter.val();
        let selectedSectionIsAvailable = selectedSection === '';

        $sectionFilter.find('option').each(function () {
            if (!this.value) {
                this.hidden = false;
                return;
            }

            this.hidden = selectedProgram !== '' && this.dataset.program !== selectedProgram;
            if (this.value === selectedSection && !this.hidden) {
                selectedSectionIsAvailable = true;
            }
        });

        if (!selectedSectionIsAvailable) {
            $sectionFilter.val('');
        }
    });

    function applyTeacherStudentFilters() {
        const filters = {};
        $('.teacher-student-filter').each(function () {
            const filter = this.dataset.filter;
            const selectedOption = this.options[this.selectedIndex];
            filters[filter] = filter === 'section'
                ? (selectedOption.dataset.section || '')
                : this.value;
        });

        let visibleCount = 0;
        $('#my-student tbody tr[data-department]').each(function () {
            const row = this;
            const matches = Object.keys(filters).every(function (filter) {
                return !filters[filter] || row.dataset[filter] === filters[filter];
            });
            row.hidden = !matches;
            if (matches) {
                visibleCount += 1;
            }
        });

        $('.teacher-no-filter-results').prop('hidden', visibleCount > 0);
    }

    $('#teacherApplyStudentFilters').on('click', applyTeacherStudentFilters);
    $('#teacherResetStudentFilters').on('click', function () {
        $('.teacher-student-filter').val('');
        $('#teacher_student_filter_program').trigger('change');
        applyTeacherStudentFilters();
    });

    $('#teacherExportStudentsCsv').on('click', function () {
        applyTeacherStudentFilters();

        const headers = ['Student ID', 'First Name', 'Middle Name', 'Last Name', 'Program', 'Section', 'Year'];
        const csvEscape = function (value) {
            let text = String(value).trim();
            if (/^[=+\-@\t\r]/.test(text)) {
                text = "'" + text;
            }
            return '"' + text.replace(/"/g, '""') + '"';
        };
        const rows = [headers.map(csvEscape).join(',')];

        $('#my-student tbody tr[data-department]').each(function () {
            if (this.hidden) {
                return;
            }

            const values = Array.from(this.cells)
                .slice(0, headers.length)
                .map(function (cell) { return cell.textContent; });
            rows.push(values.map(csvEscape).join(','));
        });

        const csv = '\uFEFF' + rows.join('\r\n');
        const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        const link = document.createElement('a');
        link.href = url;
        link.download = 'my-students.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    });

    $(document).on('click', '.teacher-view-student', function () {
        const student = this.dataset;
        $('#teacher_view_student_id').val(student.studentId || '');
        $('#teacher_view_first_name').val(student.firstName || '');
        $('#teacher_view_middle_name').val(student.middleName || '');
        $('#teacher_view_last_name').val(student.lastName || '');
        $('#teacher_view_program').val(student.program || '');
        $('#teacher_view_section').val(student.section || '');
        $('#teacher_view_year').val(student.year || '');
    });
});
</script>
