<?php
$registrarStudentName = trim(implode(' ', array_filter([
    $registrarStudent['First_Name'] ?? '',
    $registrarStudent['Middle_Name'] ?? '',
    $registrarStudent['Last_Name'] ?? '',
], static fn($part) => trim((string) $part) !== '')));
?>

<style>
    .registrar-grade-print-value {
        display: none;
    }

    @media print {
        #registrarRecordModal .registrar-grade-input {
            display: none !important;
        }

        #registrarRecordModal .registrar-grade-print-value {
            display: inline !important;
        }
    }
</style>

<style>
    @media print {
        @page {
            margin: 12mm;
        }

        body * {
            visibility: hidden !important;
        }

        #registrarRecordModal,
        #registrarRecordModal * {
            visibility: visible !important;
        }

        #registrarRecordModal {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            display: block !important;
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            overflow: visible !important;
            opacity: 1 !important;
        }

        #registrarRecordModal .modal-dialog {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
        }

        #registrarRecordModal .modal-content {
            border: 0 !important;
            box-shadow: none !important;
        }

        #registrarRecordModal .modal-header,
        #registrarRecordModal .registrar-record-print-button,
        #registrarRecordModal .registrar-record-edit-controls,
        .modal-backdrop {
            display: none !important;
        }

        #registrarRecordModal .modal-body {
            max-height: none !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        #registrarRecordModal .table-responsive {
            overflow: visible !important;
        }
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="d-flex align-items-start justify-content-between flex-wrap">
            <div>
                <h1 class="h3"><?= htmlspecialchars($registrarRecordType === 'grades' ? 'Student Grades' : 'Enrolled Subjects', ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="text-muted mb-0">
                    <?= htmlspecialchars($registrarStudentName, ENT_QUOTES, 'UTF-8') ?>
                    (<?= htmlspecialchars((string) ($registrarStudent['Student_Number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                    &mdash; <?= htmlspecialchars((string) ($registrarStudent['Course'] ?? 'Program not specified'), ENT_QUOTES, 'UTF-8') ?>,
                    Year <?= htmlspecialchars((string) ($registrarStudent['Year_Level'] ?? 'Not specified'), ENT_QUOTES, 'UTF-8') ?>,
                    <?= htmlspecialchars((string) ($registrarStudent['Section_Name'] ?? 'Section not specified'), ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
            <button type="button" class="btn btn-primary btn-sm ml-auto registrar-record-print-button">
                <i class="fas fa-print mr-1" aria-hidden="true"></i>Print
            </button>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body table-responsive">
                <?php if ($registrarRecordType === 'subjects'): ?>
                    <?php $scheduledSubjects = $registrarSubjectLoad['subjects']; ?>
                    <?php if ($scheduledSubjects === []): ?>
                        <div class="alert alert-info mb-0">
                            No scheduled subjects were found for this student's current program, year level, semester, and section.
                        </div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject</th>
                                    <th>Units</th>
                                    <th>Semester</th>
                                    <th>Section</th>
                                    <th>Time</th>
                                    <th>Days</th>
                                    <th>Room</th>
                                    <th>Instructor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scheduledSubjects as $subjectRow): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($subjectRow['SubjectCode'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($subjectRow['SubjectName'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($subjectRow['Unit'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($subjectRow['semester_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($subjectRow['section'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?= htmlspecialchars((string) ($subjectRow['time_start'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            <?= !empty($subjectRow['time_end']) ? ' - ' . htmlspecialchars((string) $subjectRow['time_end'], ENT_QUOTES, 'UTF-8') : '' ?>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($subjectRow['days'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($subjectRow['room'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($subjectRow['instructor'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="text-muted mb-0">
                            Total units: <?= (int) ($registrarSubjectLoad['total_units'] ?? 0) ?>
                        </p>
                    <?php endif; ?>
                <?php elseif ($registrarGradeRows === []): ?>
                    <div class="alert alert-info mb-0">No grades have been posted for this student yet.</div>
                <?php else: ?>
                    <?php if ($isAdministratorRole): ?>
                        <form id="adminStudentGradesForm" action="Controller/Student/update-grades.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="student_id" value="<?= (int) $registrarStudent['ID'] ?>">
                    <?php endif; ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject</th>
                                <th>Program</th>
                                <th>Year Level</th>
                                <th>Semester</th>
                                <th>Section</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrarGradeRows as $gradeRow): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($gradeRow['SubjectCode'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($gradeRow['SubjectName'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($gradeRow['Program_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($gradeRow['Year_Level'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($gradeRow['Semester_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($gradeRow['Section_Name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if ($isAdministratorRole): ?>
                                            <?php $gradeValue = number_format((float) ($gradeRow['grade'] ?? 0), 2, '.', ''); ?>
                                            <input class="form-control form-control-sm registrar-grade-input" type="number"
                                                name="grades[<?= (int) $gradeRow['class_schedule_id'] ?>]"
                                                min="1" max="5" step="0.01" required
                                                value="<?= htmlspecialchars($gradeValue, ENT_QUOTES, 'UTF-8') ?>"
                                                aria-label="Grade for <?= htmlspecialchars((string) ($gradeRow['SubjectName'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <span class="registrar-grade-print-value"><?= htmlspecialchars($gradeValue, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php else: ?>
                                            <?= htmlspecialchars((string) ($gradeRow['grade'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($isAdministratorRole): ?>
                            <div class="registrar-record-edit-controls mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save mr-1" aria-hidden="true"></i>Save Grades
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
