<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/ClassSchedule.php";
include_once __DIR__ . "/../models/Instructor.php";
include_once __DIR__ . "/../config/pagination.php";

$scheduleModel = new ClassSchedule();
$instructorModel = new Instructor();
$schedules = $scheduleModel->getAllClassSchedules();
$subjects = $scheduleModel->getCurriculumSubjects();
$instructors = $instructorModel->getAllInstructors();
$subjectOptions = [];
$instructorOptions = [];

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

$dayOptions = ['M' => 'M - Monday', 'T' => 'T - Tuesday', 'W' => 'W - Wednesday', 'Th' => 'Th - Thursday', 'F' => 'F - Friday', 'S' => 'S - Saturday', 'Su' => 'Su - Sunday'];

?>

<style>
    .class-schedule-table { min-width: 900px; }
    .class-schedule-table th, .class-schedule-table td { white-space: nowrap; }
    .days-select { min-height: 122px; }
</style>

<div class="container-fluid">
    <h2 class="mb-4">Class Schedule Management</h2>

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
                    <div class="col-md-4"><div class="form-group"><label>Subject</label><select name="subject_id" class="form-control" required><option value="">Select Subject</option><?php foreach ($subjectOptions as $subject): ?><option value="<?= classScheduleValue($subject['ID'] ?? $subject['id']) ?>"><?= classScheduleValue(($subject['SubjectCode'] ?? '') . ' - ' . ($subject['SubjectName'] ?? '')) ?></option><?php endforeach; ?></select></div></div>
                    <div class="col-md-2"><div class="form-group"><label>Time Start</label><input type="time" name="time_start" class="form-control" required></div></div>
                    <div class="col-md-2"><div class="form-group"><label>Time End</label><input type="time" name="time_end" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Instructor</label><select name="instructor_id" class="form-control" required><option value="">Select Instructor</option><?php foreach ($instructorOptions as $instructor): ?><option value="<?= classScheduleValue($instructor['id']) ?>"><?= classScheduleValue(($instructor['firstname'] ?? '') . ' ' . ($instructor['lastname'] ?? '')) ?></option><?php endforeach; ?></select></div></div>
                </div>
                <div class="row mb-0">
                    <div class="col-md-4"><div class="form-group"><label>Days</label><select name="days[]" class="form-control days-select" multiple required><?php foreach ($dayOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select><small class="form-text text-muted">Use Ctrl-click to select multiple days.</small></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Room</label><input type="text" name="room" class="form-control" placeholder="Enter room" required></div></div>
                </div>
            </div>
            <div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Schedule</button> <button type="reset" class="btn btn-secondary">Clear</button></div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white"><h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Class Schedule Records</h3></div>
        <div class="card-body"><div class="table-responsive">
            <table class="table modern-data-table class-schedule-table">
                <thead><tr><th>ID</th><th>Subject</th><th>Time</th><th>Days</th><th>Room</th><th>Instructor</th><th style="width: 220px;">Actions</th></tr></thead>
                <tbody>
                <?php if ($schedules && $schedules->num_rows > 0): ?>
                    <?php while ($row = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td><?= classScheduleValue($row['id']) ?></td>
                            <td><?= classScheduleValue(($row['SubjectCode'] ?? '') . ' - ' . ($row['SubjectName'] ?? 'Subject unavailable')) ?></td>
                            <td><?= classScheduleValue(substr($row['time_start'], 0, 5) . ' - ' . substr($row['time_end'], 0, 5)) ?></td>
                            <td><?= classScheduleValue($row['days']) ?></td>
                            <td><?= classScheduleValue($row['room']) ?></td>
                            <td><?= classScheduleValue(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? 'Instructor unavailable')) ?></td>
                            <td><div class="d-flex justify-content-center align-items-center" style="gap: 6px;"><button type="button" class="btn btn-secondary btn-sm action-btn view-class-schedule" data-record='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-eye"></i> View</button><button type="button" class="btn btn-info btn-sm action-btn edit-class-schedule" data-record='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-edit"></i> Edit</button><a href="Controller/ClassSchedule/delete.php?id=<?= urlencode((string) $row['id']) ?>" class="btn btn-danger btn-sm action-btn delete-class-schedule"><i class="fas fa-trash"></i> Delete</a></div></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?><tr><td colspan="7" class="text-center text-muted py-4">No class schedules found.</td></tr><?php endif; ?>
                </tbody>
            </table>
            <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'class schedules'); ?>
        </div></div>
    </div>
</div>

<div class="modal fade" id="classScheduleViewModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header bg-secondary text-white"><h5 class="modal-title">Class Schedule Information</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body" id="classScheduleViewFields"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div></div></div></div>

<div class="modal fade" id="classScheduleEditModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><form method="POST" action="Controller/ClassSchedule/update.php"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Edit Class Schedule</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><input type="hidden" name="id" id="edit_class_schedule_id"><div class="form-group"><label>Subject</label><select name="subject_id" id="edit_class_schedule_subject" class="form-control" required><option value="">Select Subject</option><?php foreach ($subjectOptions as $subject): ?><option value="<?= classScheduleValue($subject['ID'] ?? $subject['id']) ?>"><?= classScheduleValue(($subject['SubjectCode'] ?? '') . ' - ' . ($subject['SubjectName'] ?? '')) ?></option><?php endforeach; ?></select></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Time Start</label><input type="time" name="time_start" id="edit_class_schedule_time_start" class="form-control" required></div></div><div class="col-md-6"><div class="form-group"><label>Time End</label><input type="time" name="time_end" id="edit_class_schedule_time_end" class="form-control" required></div></div></div><div class="form-group"><label>Days</label><select name="days[]" id="edit_class_schedule_days" class="form-control days-select" multiple required><?php foreach ($dayOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Room</label><input type="text" name="room" id="edit_class_schedule_room" class="form-control" required></div><div class="form-group"><label>Instructor</label><select name="instructor_id" id="edit_class_schedule_instructor" class="form-control" required><option value="">Select Instructor</option><?php foreach ($instructorOptions as $instructor): ?><option value="<?= classScheduleValue($instructor['id']) ?>"><?= classScheduleValue(($instructor['firstname'] ?? '') . ' ' . ($instructor['lastname'] ?? '')) ?></option><?php endforeach; ?></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>

<script>
(function () {
    const viewLabels = { id: 'ID', subject: 'Subject', time: 'Time', days: 'Days', room: 'Room', instructor: 'Instructor' };
    const escapeHtml = (value) => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    document.querySelectorAll('.view-class-schedule').forEach((button) => button.addEventListener('click', () => {
        const record = JSON.parse(button.dataset.record);
        const values = { id: record.id, subject: `${record.SubjectCode ?? ''} - ${record.SubjectName ?? 'Subject unavailable'}`, time: `${String(record.time_start ?? '').slice(0, 5)} - ${String(record.time_end ?? '').slice(0, 5)}`, days: record.days, room: record.room, instructor: `${record.firstname ?? ''} ${record.lastname ?? ''}` };
        document.getElementById('classScheduleViewFields').innerHTML = Object.keys(viewLabels).map((key) => `<div class="form-group"><label>${viewLabels[key]}</label><input class="form-control" value="${escapeHtml(values[key])}" readonly></div>`).join('');
        $('#classScheduleViewModal').modal('show');
    }));
    document.querySelectorAll('.edit-class-schedule').forEach((button) => button.addEventListener('click', () => {
        const record = JSON.parse(button.dataset.record);
        document.getElementById('edit_class_schedule_id').value = record.id;
        document.getElementById('edit_class_schedule_subject').value = record.subject_id;
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
