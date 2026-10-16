<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/Programs.php";
include_once __DIR__ . "/../config/pagination.php";

$programModel = new Program();
$programs = $programModel->getAllPrograms();
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $programs ? $programs->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($programs) {
    $programs->data_seek($offset);
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
    <h2 class="mb-4">Programs Management</h2>

    <?php if (isset($_GET['success']) || isset($_GET['updated']) || (isset($_GET['deleted']) && $_GET['deleted'] == 1)): ?>
        <script>
            Swal.fire({
                title: 'Success',
                text: 'Program saved successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                draggable: false
            }).then(() => {
                window.history.replaceState({}, document.title, 'index.php?page=programs');
            });
        </script>
    <?php endif; ?>

    <?php if ((isset($_GET['deleted']) && $_GET['deleted'] == 0) || isset($_GET['error'])): ?>
        <script>
            Swal.fire({
                title: 'Error',
                text: 'Program could not be saved or deleted.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                draggable: false
            }).then(() => {
                window.history.replaceState({}, document.title, 'index.php?page=programs');
            });
        </script>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Add New Program</h3>
        </div>

        <form method="POST" action="Controller/Programs/create.php" class="save-program-form">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Course Code</label>
                            <input type="text" name="CourseCode" class="form-control" placeholder="Enter Course Code" required>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="text" name="CourseName" class="form-control" placeholder="Enter Course Name" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Program</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Program Records</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table modern-data-table">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1;">ID</th>
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1;">Course Code</th>
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1;">Course Name</th>
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1; width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($programs && $programs->num_rows > 0): ?>
                            <?php while ($row = $programs->fetch_assoc()): ?>
                                <tr>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;"><?= htmlspecialchars($row['id']) ?></td>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;"><?= htmlspecialchars($row['CourseCode']) ?></td>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;"><?= htmlspecialchars($row['CourseName']) ?></td>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;">
                                        <div class="d-flex justify-content-center align-items-center" style="gap: 6px;">
                                            <button type="button"
                                                    class="btn btn-secondary btn-sm action-btn view-program"
                                                    data-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-course-code="<?= htmlspecialchars($row['CourseCode']) ?>"
                                                    data-course-name="<?= htmlspecialchars($row['CourseName']) ?>"
                                                    title="View">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </button>

                                            <button type="button"
                                                    class="btn btn-info btn-sm action-btn edit-program"
                                                    data-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-course-code="<?= htmlspecialchars($row['CourseCode']) ?>"
                                                    data-course-name="<?= htmlspecialchars($row['CourseName']) ?>"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <a href="Controller/Programs/delete.php?id=<?= urlencode($row['id']) ?>" class="btn btn-danger btn-sm action-btn delete-program" title="Delete">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No programs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'programs'); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="programViewModal" tabindex="-1" role="dialog" aria-labelledby="programViewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="programViewModalLabel">Program Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Program ID</label>
                    <input type="text" id="view_program_id" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Course Code</label>
                    <input type="text" id="view_program_code" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" id="view_program_name" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="programEditModal" tabindex="-1" role="dialog" aria-labelledby="programEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/Programs/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="programEditModalLabel">Edit Program</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_program_id">

                    <div class="form-group">
                        <label for="edit_program_code">Course Code</label>
                        <input type="text" class="form-control" id="edit_program_code" name="CourseCode" placeholder="BSIT" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_program_name">Course Name</label>
                        <input type="text" class="form-control" id="edit_program_name" name="CourseName" placeholder="Bachelor of Science in Information Technology" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" id="updateProgramBtn" class="btn btn-success" disabled><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
