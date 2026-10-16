<?php
require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../models/role.php";
include_once __DIR__ . "/../config/pagination.php";

$roleModel = new Role();
$roles = $roleModel->getAllRoles();
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $roles ? $roles->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($roles) {
    $roles->data_seek($offset);
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
    <h2 class="mb-4">Role Management</h2>

    <?php if (isset($_GET['status'])): ?>
        <script>
            const roleAlertStatus = <?= json_encode($_GET['status'] ?? 'error') ?>;
            const roleAlertTitle = <?= json_encode($_GET['title'] ?? (($_GET['status'] ?? 'error') === 'success' ? 'Success' : 'Error')) ?>;
            const roleAlertMessage = <?= json_encode($_GET['message'] ?? (($_GET['status'] ?? 'error') === 'success' ? 'Operation completed successfully.' : 'Something went wrong.')) ?>;

            Swal.fire({
                title: roleAlertTitle,
                text: roleAlertMessage,
                icon: roleAlertStatus === 'success' ? 'success' : 'error',
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
            <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i>Add New Role</h3>
        </div>

        <form method="POST" action="Controller/Role/create.php">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Role Name</label>
                            <input type="text" name="rolename" class="form-control" placeholder="Enter Role Name" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Role</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i>Role Records</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table modern-data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Role Name</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($roles && $roles->num_rows > 0): ?>
                            <?php while ($row = $roles->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['rolename']) ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <button type="button"
                                                    class="btn btn-secondary btn-sm action-btn view-role"
                                                    data-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-role-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-role-name="<?= htmlspecialchars($row['rolename']) ?>"
                                                    title="View" style="margin-right: 6px;">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </button>

                                            <button type="button"
                                                    class="btn btn-info btn-sm action-btn edit-role"
                                                    data-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-role-id="<?= htmlspecialchars($row['id']) ?>"
                                                    data-role-name="<?= htmlspecialchars($row['rolename']) ?>"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <a href="Controller/Role/delete.php?id=<?= urlencode($row['id']) ?>" class="btn btn-danger btn-sm action-btn delete-role" title="Delete" style="margin-left: 6px;">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No roles found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'roles'); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="roleViewModal" tabindex="-1" role="dialog" aria-labelledby="roleViewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="roleViewModalLabel">Role Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Role ID</label>
                    <input type="text" id="view_role_id" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Role Name</label>
                    <input type="text" id="view_role_name" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="roleEditModal" tabindex="-1" role="dialog" aria-labelledby="roleEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="Controller/Role/update.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="roleEditModalLabel">Edit Role</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_role_id">

                    <div class="form-group">
                        <label for="edit_role_name">Role Name</label>
                        <input type="text" name="rolename" id="edit_role_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="updateRoleBtn" class="btn btn-primary" disabled>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
