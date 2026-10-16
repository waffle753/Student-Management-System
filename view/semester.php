<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/Semester.php";
include_once __DIR__ . "/../config/pagination.php";

$semesterModel = new Semester();
$semesters = $semesterModel->getAllSemesters();
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $semesters ? $semesters->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($semesters) {
    $semesters->data_seek($offset);
}

?>

<style>
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
    }
    .modern-data-table thead th:last-child { border-right: none; }
    .modern-data-table tbody td {
        padding: 0.85rem 0.8rem;
        vertical-align: middle;
        color: #374151;
        background: #ffffff;
        border-top: 1px solid #edf2f7;
        border-right: 1px solid #edf2f7;
        font-weight: 600;
    }
    .modern-data-table tbody td:last-child { border-right: none; }
    .modern-data-table tbody tr:hover { background-color: rgba(59, 130, 246, 0.04); }
</style>

<div class="container-fluid">
    <h2 class="mb-4">Semester Management</h2>

    <?php if (isset($_GET['success']) || isset($_GET['updated'])): ?>
        <script>
            Swal.fire({
                title: 'Success',
                text: 'Semester processed successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                draggable: false
            }).then(() => {
                window.history.replaceState({}, document.title, 'index.php?page=semester');
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <script>
            Swal.fire({
                title: 'Error',
                text: 'Semester could not be saved or updated.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                draggable: false
            }).then(() => {
                window.history.replaceState({}, document.title, 'index.php?page=semester');
            });
        </script>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Add New Semester</h3>
        </div>

        <form method="POST" action="Controller/Semester/create.php">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Semester ID</label>
                            <input type="text" name="semester_id" class="form-control" placeholder="Enter Semester ID" required>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Semester</label>
                            <input type="text" name="semester" class="form-control" placeholder="Enter Semester Name" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Semester</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Semester Records</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table modern-data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Semester ID</th>
                            <th>Semester</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($semesters && $semesters->num_rows > 0): ?>
                            <?php while ($row = $semesters->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['semester_id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['semester'] ?? '') ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <button type="button"
                                                    class="btn btn-secondary btn-sm action-btn view-semester"
                                                    data-id="<?= htmlspecialchars($row['id'] ?? '') ?>"
                                                    data-semester-id="<?= htmlspecialchars($row['semester_id'] ?? '') ?>"
                                                    data-semester="<?= htmlspecialchars($row['semester'] ?? '') ?>"
                                                    title="View">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </button>

                                            <button type="button"
                                                    class="btn btn-info btn-sm action-btn edit-semester"
                                                    data-id="<?= htmlspecialchars($row['id'] ?? '') ?>"
                                                    data-semester-id="<?= htmlspecialchars($row['semester_id'] ?? '') ?>"
                                                    data-semester="<?= htmlspecialchars($row['semester'] ?? '') ?>"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <a href="Controller/Semester/delete.php?id=<?= urlencode((string) ($row['id'] ?? '')) ?>" class="btn btn-danger btn-sm action-btn delete-semester" title="Delete" style="margin-left: 6px;">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No semesters found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'semesters'); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="semesterViewModal" tabindex="-1" role="dialog" aria-labelledby="semesterViewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="semesterViewModalLabel">
                    <i class="fas fa-calendar-check mr-2"></i>Semester Information
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="view_semester_id">ID</label>
                    <input type="text" class="form-control" id="view_semester_id" readonly>
                </div>
                <div class="form-group">
                    <label for="view_semester_code">Semester ID</label>
                    <input type="text" class="form-control" id="view_semester_code" readonly>
                </div>
                <div class="form-group">
                    <label for="view_semester_name">Semester</label>
                    <input type="text" class="form-control" id="view_semester_name" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="semesterEditModal" tabindex="-1" role="dialog" aria-labelledby="semesterEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/Semester/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="semesterEditModalLabel">Edit Semester</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_semester_id">

                    <div class="form-group">
                        <label for="edit_semester_code">Semester ID</label>
                        <input type="text" class="form-control" id="edit_semester_code" name="semester_id" placeholder="SEM1" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_semester_name">Semester</label>
                        <input type="text" class="form-control" id="edit_semester_name" name="semester" placeholder="1st Semester" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" id="updateSemesterBtn" class="btn btn-success" disabled><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
