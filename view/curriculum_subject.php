<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/CurriculumSubject.php";
include_once __DIR__ . "/../models/Subject.php";
include_once __DIR__ . "/../models/Programs.php";
include_once __DIR__ . "/../config/pagination.php";

$curriculumSubjectModel = new CurriculumSubject();
$subjectModel = new Subject();
$programModel = new Program();
$curriculumSubjects = $curriculumSubjectModel->getAllCurriculumSubjects();
$subjects = $subjectModel->getAllSubjects();
$programs = $programModel->getAllPrograms();
$subjectOptions = [];
$programOptions = [];

if ($subjects) {
    while ($subject = $subjects->fetch_assoc()) {
        $subjectOptions[] = $subject;
    }
}
if ($programs) {
    while ($program = $programs->fetch_assoc()) {
        $programOptions[] = $program;
    }
}

$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $curriculumSubjects ? $curriculumSubjects->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($curriculumSubjects) {
    $curriculumSubjects->data_seek($offset);
}

function curriculumSubjectValue($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function curriculumSubjectLabel($code, $name)
{
    return curriculumSubjectValue(trim($code . ' - ' . $name, ' -'));
}

?>

<div class="container-fluid">
    <h2 class="mb-4">Curriculum Subject Management</h2>

    <?php if (isset($_GET['status'])): ?>
        <script>
            const curriculumSubjectAlertStatus = <?= json_encode($_GET['status'] ?? 'error') ?>;
            const curriculumSubjectAlertTitle = <?= json_encode($_GET['title'] ?? 'Operation Result') ?>;
            const curriculumSubjectAlertMessage = <?= json_encode($_GET['message'] ?? '') ?>;
            Swal.fire({
                title: curriculumSubjectAlertTitle,
                text: curriculumSubjectAlertMessage,
                icon: curriculumSubjectAlertStatus === 'success' ? 'success' : 'error',
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
        <div class="card-header bg-primary text-white"><h3 class="card-title"><i class="fas fa-book-open mr-2"></i>Add Curriculum Subject</h3></div>
        <form method="POST" action="Controller/CurriculumSubject/create.php">
            <div class="card-body"><div class="row">
                <div class="col-md-6"><div class="form-group"><label>Subject</label><select name="subject_id" class="form-control" required><option value="">Select Subject</option><?php foreach ($subjectOptions as $subject): ?><option value="<?= curriculumSubjectValue($subject['id'] ?? $subject['ID']) ?>"><?= curriculumSubjectLabel($subject['SubjectCode'] ?? '', $subject['SubjectName'] ?? '') ?></option><?php endforeach; ?></select></div></div>
                <div class="col-md-6"><div class="form-group"><label>Program</label><select name="program_id" class="form-control" required><option value="">Select Program</option><?php foreach ($programOptions as $program): ?><option value="<?= curriculumSubjectValue($program['id']) ?>"><?= curriculumSubjectLabel($program['CourseCode'] ?? '', $program['CourseName'] ?? '') ?></option><?php endforeach; ?></select></div></div>
            </div></div>
            <div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Curriculum Subject</button> <button type="reset" class="btn btn-secondary">Clear</button></div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white"><h3 class="card-title"><i class="fas fa-book-open mr-2"></i>Curriculum Subject Records</h3></div>
        <div class="card-body"><div class="table-responsive"><table class="table modern-data-table">
            <thead><tr><th>ID</th><th>Subject</th><th>Program</th><th style="width: 220px;">Actions</th></tr></thead>
            <tbody>
            <?php if ($curriculumSubjects && $curriculumSubjects->num_rows > 0): ?>
                <?php while ($row = $curriculumSubjects->fetch_assoc()): ?>
                    <tr>
                        <td><?= curriculumSubjectValue($row['id']) ?></td>
                        <td><?= curriculumSubjectLabel($row['SubjectCode'] ?? '', $row['SubjectName'] ?? 'Subject unavailable') ?></td>
                        <td><?= curriculumSubjectLabel($row['CourseCode'] ?? '', $row['CourseName'] ?? 'Program unavailable') ?></td>
                        <td><div class="d-flex justify-content-center align-items-center" style="gap: 6px;"><button type="button" class="btn btn-secondary btn-sm action-btn view-curriculum-subject" data-record='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-eye"></i> View</button><button type="button" class="btn btn-info btn-sm action-btn edit-curriculum-subject" data-record='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-edit"></i> Edit</button><a href="Controller/CurriculumSubject/delete.php?id=<?= urlencode((string) $row['id']) ?>" class="btn btn-danger btn-sm action-btn delete-curriculum-subject"><i class="fas fa-trash"></i> Delete</a></div></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?><tr><td colspan="4" class="text-center text-muted py-4">No curriculum subjects found.</td></tr><?php endif; ?>
            </tbody>
        </table><?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'curriculum subjects'); ?></div></div>
    </div>
</div>

<div class="modal fade" id="curriculumSubjectViewModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header bg-secondary text-white"><h5 class="modal-title">Curriculum Subject Information</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><div class="form-group"><label>ID</label><input id="view_curriculum_subject_id" class="form-control" readonly></div><div class="form-group"><label>Subject</label><input id="view_curriculum_subject_subject" class="form-control" readonly></div><div class="form-group"><label>Program</label><input id="view_curriculum_subject_program" class="form-control" readonly></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div></div></div></div>

<div class="modal fade" id="curriculumSubjectEditModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><form method="POST" action="Controller/CurriculumSubject/update.php"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Edit Curriculum Subject</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><input type="hidden" name="id" id="edit_curriculum_subject_id"><div class="form-group"><label>Subject</label><select name="subject_id" id="edit_curriculum_subject_subject" class="form-control" required><option value="">Select Subject</option><?php foreach ($subjectOptions as $subject): ?><option value="<?= curriculumSubjectValue($subject['id'] ?? $subject['ID']) ?>"><?= curriculumSubjectLabel($subject['SubjectCode'] ?? '', $subject['SubjectName'] ?? '') ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Program</label><select name="program_id" id="edit_curriculum_subject_program" class="form-control" required><option value="">Select Program</option><?php foreach ($programOptions as $program): ?><option value="<?= curriculumSubjectValue($program['id']) ?>"><?= curriculumSubjectLabel($program['CourseCode'] ?? '', $program['CourseName'] ?? '') ?></option><?php endforeach; ?></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div></div>

<script>
(function () {
    function label(record, codeKey, nameKey, fallback) {
        const value = `${record[codeKey] ?? ''} - ${record[nameKey] ?? ''}`.replace(/^\s*-\s*|\s*-\s*$/g, '').trim();
        return value || fallback;
    }

    document.querySelectorAll('.view-curriculum-subject').forEach((button) => button.addEventListener('click', () => {
        const record = JSON.parse(button.dataset.record);
        $('#view_curriculum_subject_id').val(record.id);
        $('#view_curriculum_subject_subject').val(label(record, 'SubjectCode', 'SubjectName', 'Subject unavailable'));
        $('#view_curriculum_subject_program').val(label(record, 'CourseCode', 'CourseName', 'Program unavailable'));
        $('#curriculumSubjectViewModal').modal('show');
    }));

    document.querySelectorAll('.edit-curriculum-subject').forEach((button) => button.addEventListener('click', () => {
        const record = JSON.parse(button.dataset.record);
        $('#edit_curriculum_subject_id').val(record.id);
        $('#edit_curriculum_subject_subject').val(record.subject_id);
        $('#edit_curriculum_subject_program').val(record.program_id);
        $('#curriculumSubjectEditModal').modal('show');
    }));
})();
</script>
