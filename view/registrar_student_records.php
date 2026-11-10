<?php
$registrarStudentName = trim(implode(' ', array_filter([
    $registrarStudent['First_Name'] ?? '',
    $registrarStudent['Middle_Name'] ?? '',
    $registrarStudent['Last_Name'] ?? '',
], static fn($part) => trim((string) $part) !== '')));
?>

<section class="content-header">
    <div class="container-fluid">
        <h1 class="h3"><?= htmlspecialchars($registrarRecordType === 'grades' ? 'Student Grades' : 'Enrolled Subjects', ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">
            <?= htmlspecialchars($registrarStudentName, ENT_QUOTES, 'UTF-8') ?>
            (<?= htmlspecialchars((string) ($registrarStudent['Student_Number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
            &mdash; <?= htmlspecialchars((string) ($registrarStudent['Course'] ?? 'Program not specified'), ENT_QUOTES, 'UTF-8') ?>,
            Year <?= htmlspecialchars((string) ($registrarStudent['Year_Level'] ?? 'Not specified'), ENT_QUOTES, 'UTF-8') ?>,
            <?= htmlspecialchars((string) ($registrarStudent['Section_Name'] ?? 'Section not specified'), ENT_QUOTES, 'UTF-8') ?>
        </p>
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
                                    <td><?= htmlspecialchars((string) ($gradeRow['grade'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
