<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/Department.php";
include_once __DIR__ . "/../config/pagination.php";

$departmentModel = new Department();
$departments = $departmentModel->getAllDepartments();
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $departments ? $departments->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($departments) {
    $departments->data_seek($offset);
}

function departmentValue($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid">
    <h2 class="mb-2">Department Management</h2>

    <?php if (isset($_GET['status'])): ?>
        <script>
            const departmentAlertStatus = <?= json_encode($_GET['status'] ?? 'error') ?>;
            const departmentAlertTitle = <?= json_encode($_GET['title'] ?? 'Operation Result') ?>;
            const departmentAlertMessage = <?= json_encode($_GET['message'] ?? '') ?>;
            Swal.fire({
                title: departmentAlertTitle,
                text: departmentAlertMessage,
                icon: departmentAlertStatus === 'success' ? 'success' : 'error',
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
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-building mr-2"></i>Add Department</h3>
        </div>
        <form method="POST" action="Controller/Department/create.php">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="department_code">Department Code</label>
                            <input type="text" id="department_code" name="department_code" class="form-control"
                                maxlength="45" placeholder="Enter department code" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="department_name">Department Name</label>
                            <input type="text" id="department_name" name="department_name" class="form-control"
                                maxlength="255" placeholder="Enter department name" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Department</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-building mr-2"></i>Department Records</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Department Code</th>
                            <th>Department Name</th>
                            <th style="width: 250px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($departments && $departments->num_rows > 0): ?>
                            <?php while ($row = $departments->fetch_assoc()): ?>
                                <tr>
                                    <td><?= departmentValue($row['department_id']) ?></td>
                                    <td><?= departmentValue($row['department_code']) ?></td>
                                    <td><?= departmentValue($row['department_name']) ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center" style="gap: 6px;">
                                            <button type="button" class="btn btn-secondary btn-sm view-department"
                                                data-id="<?= departmentValue($row['department_id']) ?>"
                                                data-code="<?= departmentValue($row['department_code']) ?>"
                                                data-name="<?= departmentValue($row['department_name']) ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm edit-department"
                                                data-id="<?= departmentValue($row['department_id']) ?>"
                                                data-code="<?= departmentValue($row['department_code']) ?>"
                                                data-name="<?= departmentValue($row['department_name']) ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="Controller/Department/delete.php?id=<?= urlencode((string) $row['department_id']) ?>"
                                                class="btn btn-danger btn-sm delete-department">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No departments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'departments'); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="departmentViewModal" tabindex="-1" role="dialog" aria-labelledby="departmentViewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="departmentViewModalLabel">Department Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group"><label>Department ID</label><input id="view_department_id" class="form-control" readonly></div>
                <div class="form-group"><label>Department Code</label><input id="view_department_code" class="form-control" readonly></div>
                <div class="form-group"><label>Department Name</label><input id="view_department_name" class="form-control" readonly></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="departmentEditModal" tabindex="-1" role="dialog" aria-labelledby="departmentEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/Department/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="departmentEditModalLabel">Edit Department</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_department_id">
                    <div class="form-group">
                        <label for="edit_department_code">Department Code</label>
                        <input type="text" id="edit_department_code" name="department_code" class="form-control" maxlength="45" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_department_name">Department Name</label>
                        <input type="text" id="edit_department_name" name="department_name" class="form-control" maxlength="255" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('.view-department').on('click', function () {
        $('#view_department_id').val($(this).data('id'));
        $('#view_department_code').val($(this).data('code'));
        $('#view_department_name').val($(this).data('name'));
        $('#departmentViewModal').modal('show');
    });

    $('.edit-department').on('click', function () {
        $('#edit_department_id').val($(this).data('id'));
        $('#edit_department_code').val($(this).data('code'));
        $('#edit_department_name').val($(this).data('name'));
        $('#departmentEditModal').modal('show');
    });
</script>
