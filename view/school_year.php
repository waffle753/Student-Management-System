<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/School_Year.php";
include_once __DIR__ . "/../config/pagination.php";

$schoolYear = new School_Year();
$schoolYears = $schoolYear->getAll();
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $schoolYears ? $schoolYears->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($schoolYears) {
    $schoolYears->data_seek($offset);
}

$status = $_GET['status'] ?? '';
$title = $_GET['title'] ?? '';
$message = $_GET['message'] ?? '';

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

<?php if ($page == 'schoolyear'): ?>

<div class="container-fluid">

    <h2 class="mb-4">School Year Management</h2>

    <?php if ($status !== ''): ?>
        <script>
            Swal.fire({
                title: <?= json_encode($title !== '' ? $title : ($status === 'success' ? 'Success' : 'Error')) ?>,
                text: <?= json_encode($message !== '' ? $message : ($status === 'success' ? 'Operation completed successfully.' : 'Something went wrong.')) ?>,
                icon: <?= json_encode($status === 'success' ? 'success' : 'error') ?>,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                draggable: false
            }).then(() => {
                const url = new URL(window.location.href);
                ['status', 'title', 'message'].forEach((key) => url.searchParams.delete(key));
                const path = url.searchParams.toString() ? `${url.pathname}?${url.searchParams.toString()}` : url.pathname;
                window.history.replaceState({}, document.title, path);
            });
        </script>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Add New School Year</h3>
        </div>

        <form method="POST" action="Controller/School_Year/create.php">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>School Year</label>
                            <input
                                type="text"
                                name="school_year"
                                class="form-control"
                                placeholder="2026-2027"
                                maxlength="20"
                                required
                            >
                            <small class="form-text text-muted">Example: 2026-2027</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" name="save" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save School Year
                </button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>School Year Records</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table modern-data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th style="width: 170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($schoolYears && $schoolYears->num_rows > 0): ?>
                            <?php while ($row = $schoolYears->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['school_year']) ?></td>
                                    <td>
                                        <form method="POST" action="Controller/School_Year/update.php" class="status-form" style="margin: 0;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                            <input type="hidden" name="school_year" value="<?= htmlspecialchars($row['school_year']) ?>">
                                            <input type="hidden" name="update" value="1">
                                            <select name="active" class="form-control form-control-sm status-change">
                                                <option value="yes" <?= strtolower((string)$row['active']) === 'yes' ? 'selected' : '' ?>>Activate</option>
                                                <option value="no" <?= strtolower((string)$row['active']) !== 'yes' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <button type="button"
                                                    class="btn btn-info btn-sm action-btn edit-schoolyear"
                                                    data-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-school-year="<?= htmlspecialchars($row['school_year']) ?>"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <form method="POST" action="Controller/School_Year/delete.php" style="margin: 0 0 0 6px; display: inline-block;">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm action-btn delete-schoolyear" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No school years found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'school years'); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="schoolYearEditModal" tabindex="-1" role="dialog" aria-labelledby="schoolYearEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/School_Year/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="schoolYearEditModalLabel">Edit School Year</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_schoolyear_id">
                    <input type="hidden" name="update" value="1">

                    <div class="form-group">
                        <label for="edit_schoolyear_value">School Year</label>
                        <input type="text" class="form-control" id="edit_schoolyear_value" name="school_year" placeholder="2026-2027" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_schoolyear_active">Status</label>
                        <select class="form-control" id="edit_schoolyear_active" name="active">
                            <option value="yes">Activate</option>
                            <option value="no">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" id="updateSchoolYearBtn" class="btn btn-success" disabled><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>