<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/Subject.php";
include_once __DIR__ . "/../config/pagination.php";

$subjectModel = new Subject();
$subjects = $subjectModel->getAllSubjects();
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $subjects ? $subjects->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($subjects) {
    $subjects->data_seek($offset);
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
    <h2 class="mb-4">Subject Management</h2>

    <?php if (isset($_GET['status'])): ?>
        <script>
            const subjectAlertStatus = <?= json_encode($_GET['status'] ?? 'error') ?>;
            const subjectAlertTitle = <?= json_encode($_GET['title'] ?? (($_GET['status'] ?? 'error') === 'success' ? 'Success' : 'Error')) ?>;
            const subjectAlertMessage = <?= json_encode($_GET['message'] ?? (($_GET['status'] ?? 'error') === 'success' ? 'Operation completed successfully.' : 'Something went wrong.')) ?>;

            Swal.fire({
                title: subjectAlertTitle,
                text: subjectAlertMessage,
                icon: subjectAlertStatus === 'success' ? 'success' : 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                draggable: false
            }).then(() => {
                const url = new URL(window.location.href);
                ['status', 'title', 'message'].forEach((key) => url.searchParams.delete(key));
                const nextUrl = url.searchParams.toString() ? `${url.pathname}?${url.searchParams.toString()}` : url.pathname;
                window.history.replaceState({}, document.title, nextUrl);
            });
        </script>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-book mr-2"></i>Add New Subject</h3>
        </div>

        <form method="POST" action="Controller/Subject/create.php">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Subject Code</label>
                            <input type="text" name="SubjectCode" class="form-control" placeholder="Enter Subject Code" required>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Subject Name</label>
                            <input type="text" name="SubjectName" class="form-control" placeholder="Enter Subject Name" required>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Unit</label>
                            <select name="Unit" class="form-control" required>
                                <option value="">Select Unit</option>
                                <?php for ($unit = 1; $unit <= 5; $unit++): ?>
                                    <option value="<?= $unit ?>"><?= $unit ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Subject</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-book mr-2"></i>Subject Records</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table modern-data-table">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1;">ID</th>
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1;">Subject Code</th>
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1;">Subject Name</th>
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1;">Unit</th>
                            <th style="padding: 0.9rem 0.8rem; font-size: 16px; font-weight: 700; border: 1px solid #dfe7f1; width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($subjects && $subjects->num_rows > 0): ?>
                            <?php while ($row = $subjects->fetch_assoc()): ?>
                                <?php $subjectId = $row['id'] ?? $row['ID'] ?? null; ?>
                                <tr>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;"><?= htmlspecialchars((string) ($subjectId ?? '')) ?></td>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;"><?= htmlspecialchars($row['SubjectCode']) ?></td>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;"><?= htmlspecialchars($row['SubjectName']) ?></td>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;"><?= htmlspecialchars((string) ($row['Unit'] ?? 0)) ?></td>
                                    <td style="padding: 0.85rem 0.8rem; border: 1px solid #edf2f7; vertical-align: middle;">
                                        <div class="d-flex justify-content-center align-items-center" style="gap: 6px;">
                                            <button type="button"
                                                    class="btn btn-secondary btn-sm action-btn view-subject"
                                                    data-id="<?= htmlspecialchars((string) ($subjectId ?? '')) ?>"
                                                    data-subject-code="<?= htmlspecialchars($row['SubjectCode']) ?>"
                                                    data-subject-name="<?= htmlspecialchars($row['SubjectName']) ?>"
                                                    data-unit="<?= htmlspecialchars((string) ($row['Unit'] ?? 0)) ?>"
                                                    title="View">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </button>

                                            <button type="button"
                                                    class="btn btn-info btn-sm action-btn edit-subject"
                                                    data-id="<?= htmlspecialchars((string) ($subjectId ?? '')) ?>"
                                                    data-subject-code="<?= htmlspecialchars($row['SubjectCode']) ?>"
                                                    data-subject-name="<?= htmlspecialchars($row['SubjectName']) ?>"
                                                    data-unit="<?= htmlspecialchars((string) ($row['Unit'] ?? 0)) ?>"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <a href="Controller/Subject/delete.php?id=<?= urlencode((string) ($subjectId ?? '')) ?>" class="btn btn-danger btn-sm action-btn delete-subject" title="Delete">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No subjects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'subjects'); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="subjectViewModal" tabindex="-1" role="dialog" aria-labelledby="subjectViewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="subjectViewModalLabel">Subject Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Subject ID</label>
                    <input type="text" id="view_subject_id" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" id="view_subject_code" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" id="view_subject_name" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" id="view_subject_unit" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="subjectEditModal" tabindex="-1" role="dialog" aria-labelledby="subjectEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/Subject/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="subjectEditModalLabel">Edit Subject</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_subject_id">

                    <div class="form-group">
                        <label for="edit_subject_code">Subject Code</label>
                        <input type="text" name="SubjectCode" id="edit_subject_code" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_subject_name">Subject Name</label>
                        <input type="text" name="SubjectName" id="edit_subject_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_subject_unit">Unit</label>
                        <select name="Unit" id="edit_subject_unit" class="form-control" required>
                            <option value="">Select Unit</option>
                            <?php for ($unit = 1; $unit <= 5; $unit++): ?>
                                <option value="<?= $unit ?>"><?= $unit ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="updateSubjectBtn" class="btn btn-primary" disabled>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
