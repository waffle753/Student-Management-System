<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/ClassSchedule.php";
include_once __DIR__ . "/../models/Instructor.php";
include_once __DIR__ . "/../models/Section.php";
include_once __DIR__ . "/../config/pagination.php";

$scheduleModel = new ClassSchedule();
$instructorModel = new Instructor();
$schedules = $scheduleModel->getAllClassSchedules();
$subjects = $scheduleModel->getCurriculumSubjects();
$instructors = $instructorModel->getAllInstructors();
$sections = (new Section())->getAllSections();
$subjectOptions = [];
$instructorOptions = [];
$sectionOptions = [];

if ($subjects) {
    while ($subject = $subjects->fetch_assoc()) {
        $subjectOptions[] = $subject;
    }
}
if ($instructors) {
    while ($instructor = $instructors->fetch_assoc()) {
        $instructorOptions[] = $instructor;
    }
}
if ($sections) {
    while ($section = $sections->fetch_assoc()) {
        $sectionOptions[] = $section;
    }
}

$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $schedules ? $schedules->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($schedules) {
    $schedules->data_seek($offset);
}

function classScheduleValue($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function classScheduleCurriculumLabel($curriculumSubject)
{
    $subject = classScheduleSubjectLabel($curriculumSubject);
    $program = classScheduleProgramLabel($curriculumSubject);
    $year = classScheduleYearLabel($curriculumSubject['year_level'] ?? '');
    $semester = trim((string) ($curriculumSubject['SemesterName'] ?? 'Semester not set'));

    return implode(' | ', array_filter([$subject, $program, $year, $semester], static function ($value) {
        return $value !== '';
    }));
}

function classScheduleSubjectLabel($curriculumSubject)
{
    $code = trim((string) ($curriculumSubject['SubjectCode'] ?? ''));
    $name = trim((string) ($curriculumSubject['SubjectName'] ?? ''));

    return trim($code . ($code !== '' && $name !== '' ? ' - ' : '') . $name);
}

function classScheduleProgramLabel($curriculumSubject)
{
    $code = trim((string) ($curriculumSubject['CourseCode'] ?? ''));
    $name = trim((string) ($curriculumSubject['CourseName'] ?? ''));

    return trim($code . ($code !== '' && $name !== '' ? ' - ' : '') . $name);
}

function classScheduleYearLabel($yearLevel)
{
    $yearLabels = ['1' => '1st Year', '2' => '2nd Year', '3' => '3rd Year', '4' => '4th Year', '5' => '5th Year'];

    return $yearLabels[(string) $yearLevel] ?? 'Year not set';
}

$dayOptions = ['M' => 'M - Monday', 'T' => 'T - Tuesday', 'W' => 'W - Wednesday', 'Th' => 'Th - Thursday', 'F' => 'F - Friday', 'S' => 'S - Saturday', 'Su' => 'Su - Sunday'];

?>

<style>
    .class-schedule-table { min-width: 1300px; }
    .class-schedule-table th, .class-schedule-table td { white-space: nowrap; }
    .days-select { min-height: 122px; }
</style>

<div class="container-fluid">
    <h2 class="mb-2">Class Schedule Management</h2>

    <?php if (isset($_GET['status'])): ?>
        <script>
            const classScheduleAlertStatus = <?= json_encode($_GET['status'] ?? 'error') ?>;
            const classScheduleAlertTitle = <?= json_encode($_GET['title'] ?? 'Operation Result') ?>;
            const classScheduleAlertMessage = <?= json_encode($_GET['message'] ?? '') ?>;
            Swal.fire({
                title: classScheduleAlertTitle,
                text: classScheduleAlertMessage,
                icon: classScheduleAlertStatus === 'success' ? 'success' : 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                const url = new URL(window.location.href);
                ['status', 'title', 'message'].forEach((key) => url.searchParams.delete(key));
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white"><h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Add Class Schedule</h3></div>
        <form method="POST" action="Controller/ClassSchedule/create.php">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Curriculum Subject</label>
                            <select name="curriculum_subject_id" class="form-control schedule-curriculum-select" required>
                                <option value="">Select Curriculum Subject</option>
                                <?php foreach ($subjectOptions as $subject): ?>
                                    <option value="<?= classScheduleValue($subject['curriculum_subject_id']) ?>"
                                        data-program-id="<?= classScheduleValue($subject['program_id']) ?>"
                                        data-year-level="<?= classScheduleValue($subject['year_level']) ?>">
                                        <?= classScheduleValue(classScheduleCurriculumLabel($subject)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Section</label>
                            <select name="section_id" class="form-control schedule-section-select" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sectionOptions as $section): ?>
                                    <option value="<?= classScheduleValue($section['section_id']) ?>"
                                        data-program-id="<?= classScheduleValue($section['program_id']) ?>"
                                        data-year-level="<?= classScheduleValue($section['year_level']) ?>">
                                        <?= classScheduleValue($section['section_name'] . ' - ' . $section['CourseCode'] . ' - ' . classScheduleYearLabel($section['year_level'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Room</label>
                            <input type="text" name="room" class="form-control" maxlength="100" required>
                        </div>
                    </div>
                </div>
                <div class="row mb-0">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Time Start</label>
                            <input type="time" name="time_start" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Time End</label>
                            <input type="time" name="time_end" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Days</label>
                            <select name="days[]" class="form-control days-select" multiple required>
                                <?php foreach ($dayOptions as $value => $label): ?>
                                    <option value="<?= $value ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Use Ctrl-click to select multiple days.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Instructor</label>
                            <select name="instructor_id" class="form-control" required>
                                <option value="">Select Instructor</option>
                                <?php foreach ($instructorOptions as $instructor): ?>
                                    <option value="<?= classScheduleValue($instructor['id']) ?>">
                                        <?= classScheduleValue(($instructor['firstname'] ?? '') . ' ' . ($instructor['lastname'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Schedule</button> <button type="reset" class="btn btn-secondary">Clear</button></div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white"><h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Class Schedule Records</h3></div>
        <div class="card-body"><div class="table-responsive">
            <table class="table modern-data-table class-schedule-table">
                <thead><tr><th>ID</th><th>Subject</th><th>Program</th><th>Year Level</th><th>Semester</th><th>Section</th><th>Time</th><th>Days</th><th>Room</th><th>Instructor</th><th style="width: 220px;">Actions</th></tr></thead>
                <tbody>
                <?php if ($schedules && $schedules->num_rows > 0): ?>
                    <?php while ($row = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td><?= classScheduleValue($row['id']) ?></td>
                            <td><?= classScheduleValue(classScheduleSubjectLabel($row) ?: 'Subject unavailable') ?></td>
                            <td><?= classScheduleValue(classScheduleProgramLabel($row) ?: 'Program unavailable') ?></td>
                            <td><?= classScheduleValue(classScheduleYearLabel($row['year_level'] ?? '')) ?></td>
                            <td><?= classScheduleValue($row['SemesterName'] ?? 'Semester not set') ?></td>
                            <td><?= classScheduleValue(($row['managed_section_name'] ?? '') !== '' ? $row['managed_section_name'] : ($row['section'] ?? 'Section not set')) ?></td>
                            <td><?= classScheduleValue(substr($row['time_start'], 0, 5) . ' - ' . substr($row['time_end'], 0, 5)) ?></td>
                            <td><?= classScheduleValue($row['days']) ?></td>
                            <td><?= classScheduleValue($row['room']) ?></td>
                            <td><?= classScheduleValue(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? 'Instructor unavailable')) ?></td>
                            <td><div class="d-flex justify-content-center align-items-center" style="gap: 6px;"><button type="button" class="btn btn-secondary btn-sm action-btn view-class-schedule" data-record='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-eye"></i> View</button><button type="button" class="btn btn-info btn-sm action-btn edit-class-schedule" data-record='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-edit"></i> Edit</button><a href="Controller/ClassSchedule/delete.php?id=<?= urlencode((string) $row['id']) ?>" class="btn btn-danger btn-sm action-btn delete-class-schedule"><i class="fas fa-trash"></i> Delete</a></div></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?><tr><td colspan="8" class="text-center text-muted py-4">No class schedules found.</td></tr><?php endif; ?>
                </tbody>
            </table>
            <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'class schedules'); ?>
        </div></div>
    </div>
</div>

<div class="modal fade" id="classScheduleViewModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header bg-secondary text-white"><h5 class="modal-title">Class Schedule Information</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body" id="classScheduleViewFields"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div></div></div></div>

<div class="modal fade" id="classScheduleEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/ClassSchedule/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Class Schedule</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_class_schedule_id">
                    <div class="form-group">
                        <label>Curriculum Subject</label>
                        <select name="curriculum_subject_id" id="edit_class_schedule_curriculum_subject" class="form-control" required>
                            <option value="">Select Curriculum Subject</option>
                            <?php foreach ($subjectOptions as $subject): ?>
                                <option value="<?= classScheduleValue($subject['curriculum_subject_id']) ?>" data-program-id="<?= classScheduleValue($subject['program_id']) ?>" data-year-level="<?= classScheduleValue($subject['year_level']) ?>"><?= classScheduleValue(classScheduleCurriculumLabel($subject)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                        <div class="form-group">
                            <label>Section</label>
                            <select name="section_id" id="edit_class_schedule_section" class="form-control schedule-section-select" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sectionOptions as $section): ?>
                                    <option value="<?= classScheduleValue($section['section_id']) ?>" data-program-id="<?= classScheduleValue($section['program_id']) ?>" data-year-level="<?= classScheduleValue($section['year_level']) ?>">
                                        <?= classScheduleValue($section['section_name'] . ' - ' . $section['CourseCode'] . ' - ' . classScheduleYearLabel($section['year_level'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Time Start</label><input type="time" name="time_start" id="edit_class_schedule_time_start" class="form-control" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Time End</label><input type="time" name="time_end" id="edit_class_schedule_time_end" class="form-control" required></div></div>
                    </div>
                    <div class="form-group"><label>Days</label><select name="days[]" id="edit_class_schedule_days" class="form-control days-select" multiple required><?php foreach ($dayOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Room</label><input type="text" name="room" id="edit_class_schedule_room" class="form-control" maxlength="100" required></div>
                    <div class="form-group"><label>Instructor</label><select name="instructor_id" id="edit_class_schedule_instructor" class="form-control" required><option value="">Select Instructor</option><?php foreach ($instructorOptions as $instructor): ?><option value="<?= classScheduleValue($instructor['id']) ?>"><?= classScheduleValue(($instructor['firstname'] ?? '') . ' ' . ($instructor['lastname'] ?? '')) ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const viewLabels = { id: 'ID', subject: 'Subject', program: 'Program', year: 'Year Level', semester: 'Semester', section: 'Section', time: 'Time', days: 'Days', room: 'Room', instructor: 'Instructor' };
    const escapeHtml = (value) => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const filterScheduleSections = (form) => {
        const curriculumSelect = form.querySelector('.schedule-curriculum-select');
        const sectionSelect = form.querySelector('.schedule-section-select');
        const curriculum = curriculumSelect?.selectedOptions[0];
        const programId = curriculum?.dataset.programId ?? '';
        const yearLevel = curriculum?.dataset.yearLevel ?? '';

        Array.from(sectionSelect.options).forEach((option) => {
            if (!option.dataset.programId) {
                option.hidden = false;
                return;
            }
            option.hidden = programId === '' ||
                option.dataset.programId !== programId ||
                option.dataset.yearLevel !== yearLevel;
        });
        if (sectionSelect.selectedOptions.length && sectionSelect.selectedOptions[0].hidden) {
            sectionSelect.value = '';
        }
    };
    document.querySelectorAll('form').forEach((form) => {
        const curriculumSelect = form.querySelector('.schedule-curriculum-select');
        if (curriculumSelect) {
            curriculumSelect.addEventListener('change', () => filterScheduleSections(form));
            filterScheduleSections(form);
        }
    });
    document.querySelectorAll('.view-class-schedule').forEach((button) => button.addEventListener('click', () => {
        const record = JSON.parse(button.dataset.record);
        const values = { id: record.id, subject: `${record.SubjectCode ?? ''}${record.SubjectCode && record.SubjectName ? ' - ' : ''}${record.SubjectName ?? 'Subject unavailable'}`, program: `${record.CourseCode ?? ''}${record.CourseCode && record.CourseName ? ' - ' : ''}${record.CourseName ?? 'Program unavailable'}`, year: ({ 1: '1st Year', 2: '2nd Year', 3: '3rd Year', 4: '4th Year', 5: '5th Year' })[record.year_level] ?? 'Year not set', semester: record.SemesterName ?? 'Semester not set', section: record.managed_section_name || record.section || 'Section not set', time: `${String(record.time_start ?? '').slice(0, 5)} - ${String(record.time_end ?? '').slice(0, 5)}`, days: record.days, room: record.room, instructor: `${record.firstname ?? ''} ${record.lastname ?? ''}` };
        document.getElementById('classScheduleViewFields').innerHTML = Object.keys(viewLabels).map((key) => `<div class="form-group"><label>${viewLabels[key]}</label><input class="form-control" value="${escapeHtml(values[key])}" readonly></div>`).join('');
        $('#classScheduleViewModal').modal('show');
    }));
    document.querySelectorAll('.edit-class-schedule').forEach((button) => button.addEventListener('click', () => {
        const record = JSON.parse(button.dataset.record);
        document.getElementById('edit_class_schedule_id').value = record.id;
        document.getElementById('edit_class_schedule_curriculum_subject').value = record.curriculum_subject_id ?? '';
        document.getElementById('edit_class_schedule_section').value = record.section_id ?? '';
        filterScheduleSections(document.getElementById('edit_class_schedule_section').form);
        document.getElementById('edit_class_schedule_time_start').value = String(record.time_start ?? '').slice(0, 5);
        document.getElementById('edit_class_schedule_time_end').value = String(record.time_end ?? '').slice(0, 5);
        document.getElementById('edit_class_schedule_room').value = record.room ?? '';
        document.getElementById('edit_class_schedule_instructor').value = record.instructor_id;
        const selectedDays = String(record.days ?? '').split(',');
        Array.from(document.getElementById('edit_class_schedule_days').options).forEach((option) => { option.selected = selectedDays.includes(option.value); });
        $('#classScheduleEditModal').modal('show');
    }));
})();
</script>
