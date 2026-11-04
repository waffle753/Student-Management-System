<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();
require_once __DIR__ . "/../models/Section.php";
require_once __DIR__ . "/../models/Programs.php";

$sectionModel = new Section();
$sections = $sectionModel->getAllSections();
$programResult = (new Program())->getAllPrograms();
$programOptions = [];
if ($programResult) {
    while ($program = $programResult->fetch_assoc()) {
        $programOptions[] = $program;
    }
}

function sectionViewValue($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid">
    <h2 class="mb-4">Section Maintenance</h2>

    <?php if (isset($_GET['status'])): ?>
        <script>
            Swal.fire({
                title: <?= json_encode($_GET['status'] === 'success' ? 'Success' : 'Error') ?>,
                text: <?= json_encode($_GET['message'] ?? '') ?>,
                icon: <?= json_encode($_GET['status'] === 'success' ? 'success' : 'error') ?>,
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-users mr-2"></i>Add Section</h3>
        </div>
        <form method="POST" action="Controller/Section/create.php">
            <input type="hidden" name="csrf_token" value="<?= sectionViewValue(getCsrfToken()) ?>">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="section_name">Section Name</label>
                        <select class="form-control" id="section_name" name="section_name" required>
                            <option value="">Select Section</option>
                            <?php foreach (range('A', 'J') as $sectionLetter): ?>
                                <option value="<?= $sectionLetter ?>"><?= $sectionLetter ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="section_program">Program</label>
                        <select class="form-control" id="section_program" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php foreach ($programOptions as $program): ?>
                                <option value="<?= sectionViewValue($program['id']) ?>">
                                    <?= sectionViewValue($program['CourseCode'] . ' - ' . $program['CourseName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="section_year_level">Year Level</label>
                        <select class="form-control" id="section_year_level" name="year_level" required>
                            <option value="">Select Year Level</option>
                            <?php foreach (['1' => '1st year', '2' => '2nd year', '3' => '3rd year', '4' => '4th year', '5' => '5th year'] as $yearValue => $yearLabel): ?>
                                <option value="<?= $yearValue ?>"><?= $yearLabel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Section</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-users mr-2"></i>Section Records</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table modern-data-table">
                    <thead>
                        <tr><th>ID</th><th>Section Name</th><th>Program</th><th>Year Level</th><th style="width: 210px;">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$sections): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Unable to load sections.</td></tr>
                        <?php elseif ($sections->num_rows === 0): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No sections found.</td></tr>
                        <?php else: ?>
                            <?php while ($row = $sections->fetch_assoc()): ?>
                                <tr>
                                    <td><?= sectionViewValue($row['section_id']) ?></td>
                                    <td><?= sectionViewValue($row['section_name']) ?></td>
                                    <td><?= sectionViewValue($row['CourseCode'] . ' - ' . $row['CourseName']) ?></td>
                                    <td><?= sectionViewValue(['1' => '1st year', '2' => '2nd year', '3' => '3rd year', '4' => '4th year', '5' => '5th year'][$row['year_level']] ?? $row['year_level']) ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center" style="gap: 6px;">
                                            <button type="button" class="btn btn-info btn-sm edit-section"
                                                data-id="<?= sectionViewValue($row['section_id']) ?>"
                                                data-name="<?= sectionViewValue($row['section_name']) ?>"
                                                data-program="<?= sectionViewValue($row['program_id']) ?>"
                                                data-year-level="<?= sectionViewValue($row['year_level']) ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" action="Controller/Section/delete.php" class="delete-section-form">
                                                <input type="hidden" name="csrf_token" value="<?= sectionViewValue(getCsrfToken()) ?>">
                                                <input type="hidden" name="section_id" value="<?= sectionViewValue($row['section_id']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sectionEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/Section/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Section</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= sectionViewValue(getCsrfToken()) ?>">
                    <input type="hidden" name="section_id" id="edit_section_id">
                    <div class="form-group">
                        <label for="edit_section_name">Section Name</label>
                        <select class="form-control" id="edit_section_name" name="section_name" required>
                            <?php foreach (range('A', 'J') as $sectionLetter): ?>
                                <option value="<?= $sectionLetter ?>"><?= $sectionLetter ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_section_program">Program</label>
                        <select class="form-control" id="edit_section_program" name="program_id" required>
                            <?php foreach ($programOptions as $program): ?>
                                <option value="<?= sectionViewValue($program['id']) ?>">
                                    <?= sectionViewValue($program['CourseCode'] . ' - ' . $program['CourseName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_section_year_level">Year Level</label>
                        <select class="form-control" id="edit_section_year_level" name="year_level" required>
                            <?php foreach (['1' => '1st year', '2' => '2nd year', '3' => '3rd year', '4' => '4th year', '5' => '5th year'] as $yearValue => $yearLabel): ?>
                                <option value="<?= $yearValue ?>"><?= $yearLabel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.edit-section').forEach((button) => button.addEventListener('click', () => {
        document.getElementById('edit_section_id').value = button.dataset.id;
        document.getElementById('edit_section_name').value = button.dataset.name;
        document.getElementById('edit_section_program').value = button.dataset.program;
        document.getElementById('edit_section_year_level').value = button.dataset.yearLevel;
        $('#sectionEditModal').modal('show');
    }));

    document.querySelectorAll('.delete-section-form').forEach((form) => form.addEventListener('submit', (event) => {
        if (!window.confirm('Are you sure you want to delete this section?')) {
            event.preventDefault();
        }
    }));
})();
</script>
