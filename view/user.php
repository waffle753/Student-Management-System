<?php

require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();
include_once __DIR__ . "/../models/user.php";
include_once __DIR__ . "/../config/pagination.php";

$user = new User();

$users = $user->getAllUsers();
$roles = $user->getRoles();
$studentResult = $user->getStudentOptions();
$studentOptions = [];
if ($studentResult) {
    while ($studentOption = $studentResult->fetch_assoc()) {
        $studentOptions[] = $studentOption;
    }
}
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $users ? $users->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($users) {
    $users->data_seek($offset);
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

    .modern-data-table thead th:last-child {
        border-right: none;
    }

    .modern-data-table tbody td {
        padding: 0.85rem 0.8rem;
        vertical-align: middle;
        color: #374151;
        background: #ffffff;
        border-top: 1px solid #edf2f7;
        border-right: 1px solid #edf2f7;
        font-weight: 600;
    }

    .modern-data-table tbody td:last-child {
        border-right: none;
    }

    .modern-data-table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.04);
    }
</style>

<div class="container-fluid">

    <?php if (isset($_GET['status'])): ?>
        <script>
            const userAlertStatus = <?= json_encode($_GET['status'] ?? 'error') ?>;
            const userAlertTitle = <?= json_encode($_GET['title'] ?? (($_GET['status'] ?? 'error') === 'success' ? 'Success' : 'Error')) ?>;
            const userAlertMessage = <?= json_encode($_GET['message'] ?? (($_GET['status'] ?? 'error') === 'success' ? 'Operation completed successfully.' : 'Something went wrong.')) ?>;

            Swal.fire({
                title: userAlertTitle,
                text: userAlertMessage,
                icon: userAlertStatus === 'success' ? 'success' : 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                draggable: false
            }).then(() => {
                const url = new URL(window.location.href);
                ['status', 'title', 'message'].forEach((key) => url.searchParams.delete(key));
                const nextUrl = url.searchParams.toString()
                    ? `${url.pathname}?${url.searchParams.toString()}`
                    : url.pathname;
                window.history.replaceState({}, document.title, nextUrl);
            });
        </script>
    <?php endif; ?>

    <!-- ========================================================= -->
    <!-- PAGE TITLE -->
    <!-- ========================================================= -->

    <h2 class="mb-4">
        User Management
    </h2>


    <!-- ========================================================= -->
    <!-- ADD USER -->
    <!-- ========================================================= -->

    <div class="card">

        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-user-plus mr-2"></i>
                Add New User
            </h3>
        </div>


        <form method="POST" action="Controller/User/create.php">

            <div class="card-body">

                <div class="row">

                    <!-- USERNAME -->

                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Username
                            </label>

                            <input type="text" name="username" class="form-control" placeholder="Enter Username"
                                required>

                        </div>

                    </div>


                    <!-- PASSWORD -->

                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Password
                            </label>

                            <div class="position-relative">
                                <input type="password" name="password" id="add_user_password" class="form-control pr-5"
                                    placeholder="Enter Password" required>
                                <button type="button"
                                    class="btn btn-link position-absolute border-0 shadow-none p-0 text-muted"
                                    id="toggle_user_password" aria-label="Toggle password visibility"
                                    style="right: 12px; top: 50%; transform: translateY(-50%); z-index: 5;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                        </div>

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Confirm Password
                            </label>

                            <input type="password" name="confirm_password" id="add_user_confirm_password"
                                class="form-control" placeholder="Confirm Password" required>

                        </div>

                    </div>


                    <!-- FIRST NAME -->

                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                First Name
                            </label>

                            <input type="text" name="firstname" id="add_user_firstname" class="form-control"
                                placeholder="Enter First Name" required>

                        </div>

                    </div>


                    <!-- LAST NAME -->

                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Last Name
                            </label>

                            <input type="text" name="lastname" id="add_user_lastname" class="form-control"
                                placeholder="Enter Last Name" required>

                        </div>

                    </div>

                    <!-- ROLE -->

                    <div class="col-md-3">

                        <div class="form-group">

                            <label>
                                Role
                            </label>

                            <select name="role_id" id="add_role_id" class="form-control" required>

                                <option value="">
                                    Select Role
                                </option>


                                <?php if ($roles): ?>

                                    <?php while ($role = $roles->fetch_assoc()): ?>

                                        <option value="<?= htmlspecialchars($role['id']) ?>" data-role-name="<?= htmlspecialchars($role['rolename'], ENT_QUOTES) ?>">
                                            <?= htmlspecialchars($role['rolename']) ?>
                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="add_student_id">Student Profile</label>
                            <select name="student_id" id="add_student_id" class="form-control" disabled>
                                <option value="">Select student</option>
                                <?php foreach ($studentOptions as $studentOption): ?>
                                    <option value="<?= htmlspecialchars($studentOption['ID']) ?>">
                                        <?= htmlspecialchars($studentOption['Student_Number'] . ' - ' . $studentOption['First_Name'] . ' ' . $studentOption['Last_Name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                </div>

            </div>


            <!-- FORM FOOTER -->

            <div class="card-footer">

                <button type="submit" class="btn btn-primary">

                    <i class="fas fa-save"></i>

                    Save User

                </button>


                <button type="reset" class="btn btn-secondary">

                    Clear

                </button>

            </div>

        </form>

    </div>


    <!-- ========================================================= -->
    <!-- USER RECORDS -->
    <!-- ========================================================= -->

    <div class="card mt-4">

        <div class="card-header bg-primary text-white">

            <h3 class="card-title">
                <i class="fas fa-users mr-2"></i>
                User Records
            </h3>

        </div>


        <div class="card-body">

            <!-- ================================================= -->
            <!-- TABLE -->
            <!-- ================================================= -->

            <div class="table-responsive">

                <table class="table modern-data-table">

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Username
                            </th>

                            <th>
                                First Name
                            </th>

                            <th>
                                Last Name
                            </th>

                            <th>
                                Role
                            </th>

                            <th style="width:220px;">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($users && $users->num_rows > 0): ?>

                            <?php while ($row = $users->fetch_assoc()): ?>

                                <?php
                                $roleName =
                                    !empty($row['rolename'])
                                    ? $row['rolename']
                                    : 'No Role';

                                $roleId =
                                    $row['role_id'] ?? '';

                                ?>


                                <tr>

                                    <!-- ID -->

                                    <td>
                                        <?= htmlspecialchars($row['id']) ?>
                                    </td>


                                    <!-- USERNAME -->

                                    <td>
                                        <?= htmlspecialchars($row['username']) ?>
                                    </td>


                                    <!-- FIRST NAME -->

                                    <td>
                                        <?= htmlspecialchars($row['firstname']) ?>
                                    </td>


                                    <!-- LAST NAME -->

                                    <td>
                                        <?= htmlspecialchars($row['lastname']) ?>
                                    </td>


                                    <!-- ROLE -->

                                    <td>

                                        <?= htmlspecialchars($roleName) ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td>

                                        <!-- EDIT -->

                                        <div class="d-flex justify-content-center align-items-center">
                                            <button type="button" class="btn btn-info btn-sm action-btn" onclick="editUser(
                                                    '<?= htmlspecialchars($row['id'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($row['firstname'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($row['lastname'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($roleId, ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars((string) ($row['student_id'] ?? ''), ENT_QUOTES) ?>'
                                                )">

                                                <i class="fas fa-edit"></i>

                                                Edit

                                            </button>


                                            <!-- VIEW -->

                                            <button type="button" class="btn btn-secondary btn-sm action-btn" onclick="viewUser(
                                                    '<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($row['firstname'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($row['lastname'], ENT_QUOTES) ?>',
                                                    '<?= htmlspecialchars($roleName, ENT_QUOTES) ?>'
                                                )">

                                                <i class="fas fa-eye"></i>

                                                View

                                            </button>


                                            <!-- DELETE -->

                                            <a href="Controller/User/delete.php?id=<?= urlencode($row['id']) ?>"
                                                class="btn btn-danger btn-sm action-btn delete-user">

                                                <i class="fas fa-trash"></i>

                                                Delete

                                            </a>
                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6" class="text-center">
                                    No users found.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>
                <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'users'); ?>

            </div>

        </div>

    </div>

</div>

<script>
    function syncStudentSelector(roleSelect, studentSelect) {
        const roleName = roleSelect.selectedOptions[0]?.dataset.roleName?.toLowerCase();
        const isStudent = roleName === 'student';
        studentSelect.disabled = !isStudent;
        studentSelect.required = isStudent;
        if (!isStudent) {
            studentSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('add_user_password');
        const togglePassword = document.getElementById('toggle_user_password');

        if (passwordInput && togglePassword) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                togglePassword.innerHTML = isPassword
                    ? '<i class="fas fa-eye-slash"></i>'
                    : '<i class="fas fa-eye"></i>';
                togglePassword.setAttribute(
                    'aria-label',
                    isPassword ? 'Hide password' : 'Show password'
                );
            });
        }

        const addUserForm = document.querySelector('form[action="Controller/User/create.php"]');
        const confirmPasswordInput = document.getElementById('add_user_confirm_password');
        const addRoleSelect = document.getElementById('add_role_id');
        const addStudentSelect = document.getElementById('add_student_id');

        if (addRoleSelect && addStudentSelect) {
            addRoleSelect.addEventListener('change', function () {
                syncStudentSelector(addRoleSelect, addStudentSelect);
            });
        }

        const editRoleSelect = document.getElementById('edit_role_id');
        const editStudentSelect = document.getElementById('edit_student_id');
        if (editRoleSelect && editStudentSelect) {
            editRoleSelect.addEventListener('change', function () {
                syncStudentSelector(editRoleSelect, editStudentSelect);
            });
        }

        if (addUserForm && passwordInput && confirmPasswordInput) {
            addUserForm.addEventListener('submit', function (event) {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Password Mismatch',
                        text: 'Password and Confirm Password must match.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6',
                        draggable: false
                    });
                }
            });
        }
    });
</script>

<!-- ============================================================= -->
<!-- EDIT USER MODAL -->
<!-- ============================================================= -->

<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">


            <div class="modal-header bg-primary">

                <h5 class="modal-title text-white">

                    <i class="fas fa-user-edit"></i>

                    Edit User

                </h5>


                <button type="button" class="close text-white" data-dismiss="modal">

                    <span>
                        &times;
                    </span>

                </button>

            </div>


            <!-- UPDATE CONTROLLER -->

            <form method="POST" action="Controller/User/update.php">

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_id">


                    <!-- USERNAME -->

                    <div class="form-group">

                        <label>
                            Username
                        </label>

                        <input type="text" name="username" id="edit_username" class="form-control" required>

                    </div>


                    <!-- FIRST NAME -->

                    <div class="form-group">

                        <label>
                            First Name
                        </label>

                        <input type="text" name="firstname" id="edit_firstname" class="form-control" required>

                    </div>


                    <!-- LAST NAME -->

                    <div class="form-group">

                        <label>
                            Last Name
                        </label>

                        <input type="text" name="lastname" id="edit_lastname" class="form-control" required>

                    </div>


                    <!-- ROLE -->

                    <div class="form-group">

                        <label>
                            Role
                        </label>

                        <select name="role_id" id="edit_role_id" class="form-control" required>

                            <option value="">
                                Select Role
                            </option>


                            <?php

                            $editRoles =
                                $user->getRoles();

                            if ($editRoles):

                                while (
                                    $role =
                                    $editRoles->fetch_assoc()
                                ):

                                    ?>

                                    <option value="<?= htmlspecialchars($role['id']) ?>" data-role-name="<?= htmlspecialchars($role['rolename'], ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($role['rolename']) ?>
                                    </option>

                                    <?php

                                endwhile;

                            endif;

                            ?>

                        </select>

                    </div>

                    <div class="form-group">
                        <label for="edit_student_id">Student Profile</label>
                        <select name="student_id" id="edit_student_id" class="form-control" disabled>
                            <option value="">Select student</option>
                            <?php foreach ($studentOptions as $studentOption): ?>
                                <option value="<?= htmlspecialchars($studentOption['ID']) ?>">
                                    <?= htmlspecialchars($studentOption['Student_Number'] . ' - ' . $studentOption['First_Name'] . ' ' . $studentOption['Last_Name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mt-4 border-top pt-3">
                        <div class="mb-2">
                            <strong>Password Actions</strong>
                        </div>

                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                            <div class="mb-0">
                                <input type="hidden" name="id" id="reset_password_id">
                                <button type="button" class="btn btn-warning btn-sm" id="resetPasswordBtn">
                                    <i class="fas fa-key"></i>
                                    Reset Password
                                </button>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm" id="showChangePasswordBtn">
                                <i class="fas fa-lock"></i>
                                Change Password
                            </button>
                        </div>

                        <div id="changePasswordForm" style="display:none; margin-top: 12px;">
                            <input type="hidden" name="id" id="change_password_id">

                            <div class="form-group mb-0">
                                <label>New Password</label>
                                <div class="position-relative">
                                    <input type="password" name="new_password" id="edit_new_password"
                                        class="form-control pr-5" placeholder="Enter new password"
                                        minlength="6" autocomplete="new-password">
                                    <button type="button"
                                        class="btn btn-link position-absolute border-0 shadow-none p-0 text-muted"
                                        id="toggle_edit_password" aria-label="Toggle password visibility"
                                        style="right: 12px; top: 50%; transform: translateY(-50%); z-index: 5;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-primary btn-sm" id="saveNewPasswordBtn">
                                    <i class="fas fa-save"></i>
                                    Save New Password
                                </button>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">

                        Cancel

                    </button>


                    <button type="submit" id="updateUserBtn" class="btn btn-primary" disabled>

                        <i class="fas fa-save"></i>

                        Update User

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ============================================================= -->
<!-- VIEW USER MODAL -->
<!-- ============================================================= -->

<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">


            <div class="modal-header bg-secondary">

                <h5 class="modal-title text-white">

                    <i class="fas fa-user"></i>

                    User Information

                </h5>


                <button type="button" class="close text-white" data-dismiss="modal">

                    <span>
                        &times;
                    </span>

                </button>

            </div>


            <div class="modal-body">

                <!-- USERNAME -->

                <div class="form-group">

                    <label>
                        Username
                    </label>

                    <input type="text" id="view_username" class="form-control" readonly>

                </div>


                <!-- FIRST NAME -->

                <div class="form-group">

                    <label>
                        First Name
                    </label>

                    <input type="text" id="view_firstname" class="form-control" readonly>

                </div>


                <!-- LAST NAME -->

                <div class="form-group">

                    <label>
                        Last Name
                    </label>

                    <input type="text" id="view_lastname" class="form-control" readonly>

                </div>


                <!-- ROLE -->

                <div class="form-group">

                    <label>
                        Role
                    </label>

                    <input type="text" id="view_role" class="form-control" readonly>

                </div>

            </div>


            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">

                    Close

                </button>

            </div>

        </div>

    </div>

</div>


<!-- ============================================================= -->
<!-- JAVASCRIPT -->
<!-- ============================================================= -->

<script>

    const toggleEditPasswordButton = document.getElementById('toggle_edit_password');
    const showChangePasswordBtn = document.getElementById('showChangePasswordBtn');
    const changePasswordForm = document.getElementById('changePasswordForm');
    const editPasswordInput = document.getElementById('edit_new_password');

    if (showChangePasswordBtn && changePasswordForm && editPasswordInput && toggleEditPasswordButton) {
        showChangePasswordBtn.addEventListener('click', function () {
            const isHidden = changePasswordForm.style.display === 'none';
            changePasswordForm.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                editPasswordInput.focus();
            }
        });

        toggleEditPasswordButton.addEventListener('click', function () {
            const isPassword = editPasswordInput.type === 'password';
            editPasswordInput.type = isPassword ? 'text' : 'password';
            toggleEditPasswordButton.innerHTML = isPassword
                ? '<i class="fas fa-eye-slash"></i>'
                : '<i class="fas fa-eye"></i>';
            toggleEditPasswordButton.setAttribute(
                'aria-label',
                isPassword ? 'Hide password' : 'Show password'
            );
        });
    }

    function submitPasswordAction(endpoint, formData, failureMessage) {
        fetch(endpoint, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.redirected) {
                    throw new Error('Password action did not complete.');
                }

                window.location.assign(response.url);
            })
            .catch(function () {
                Swal.fire({
                    title: 'Request Failed',
                    text: failureMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    }

    const resetPasswordButton = document.getElementById('resetPasswordBtn');
    if (resetPasswordButton) {
        resetPasswordButton.addEventListener('click', function () {
            const userId = document.getElementById('reset_password_id').value;
            if (!userId) {
                Swal.fire('Select a user first.', '', 'error');
                return;
            }

            Swal.fire({
                title: 'Reset this password?',
                text: 'The password will be reset to the default password.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reset password',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                const formData = new FormData();
                formData.append('id', userId);
                submitPasswordAction(
                    'Controller/User/reset_password.php',
                    formData,
                    'The password could not be reset.'
                );
            });
        });
    }

    const saveNewPasswordButton = document.getElementById('saveNewPasswordBtn');
    if (saveNewPasswordButton) {
        saveNewPasswordButton.addEventListener('click', function () {
            const userId = document.getElementById('change_password_id').value;
            const newPassword = editPasswordInput.value;
            if (!userId) {
                Swal.fire('Select a user first.', '', 'error');
                return;
            }

            if (newPassword.length < 6) {
                Swal.fire({
                    title: 'Password too short',
                    text: 'Use at least 6 characters for the new password.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                editPasswordInput.focus();
                return;
            }

            const formData = new FormData();
            formData.append('id', userId);
            formData.append('new_password', newPassword);
            submitPasswordAction(
                'Controller/User/change_password.php',
                formData,
                'The new password could not be saved.'
            );
        });
    }

    /* ================================================================
       EDIT USER
       ================================================================ */

    function editUser(
        id,
        username,
        firstname,
        lastname,
        role_id,
        student_id
    ) {

        document.getElementById("edit_id").value =
            id;

        document.getElementById("reset_password_id").value =
            id;

        document.getElementById("change_password_id").value =
            id;

        document.getElementById("edit_username").value =
            username;

        document.getElementById("edit_firstname").value =
            firstname;

        document.getElementById("edit_lastname").value =
            lastname;

        document.getElementById("edit_role_id").value =
            role_id;

        document.getElementById("edit_student_id").value =
            student_id;
        syncStudentSelector(
            document.getElementById("edit_role_id"),
            document.getElementById("edit_student_id")
        );

        const updateUserButton = document.getElementById("updateUserBtn");
        updateUserButton.dataset.originalUsername = username;
        updateUserButton.dataset.originalFirstname = firstname;
        updateUserButton.dataset.originalLastname = lastname;
        updateUserButton.dataset.originalRoleId = role_id;
        updateUserButton.dataset.originalStudentId = student_id;
        updateUserButton.disabled = true;

        document.getElementById("edit_new_password").value = "";
        document.getElementById("changePasswordForm").style.display = "none";
        document.getElementById("edit_new_password").type = "password";
        document.getElementById("toggle_edit_password").innerHTML = '<i class="fas fa-eye"></i>';

        $("#editUserModal").modal("show");

    }

    document.addEventListener("input", updateUserButtonState);
    document.addEventListener("change", updateUserButtonState);

    function updateUserButtonState(event) {
        if (!event.target.matches("#edit_username, #edit_firstname, #edit_lastname, #edit_role_id, #edit_student_id")) {
            return;
        }

        const updateUserButton = document.getElementById("updateUserBtn");

        if (!updateUserButton) {
            return;
        }

        updateUserButton.disabled =
            document.getElementById("edit_username").value === updateUserButton.dataset.originalUsername &&
            document.getElementById("edit_firstname").value === updateUserButton.dataset.originalFirstname &&
            document.getElementById("edit_lastname").value === updateUserButton.dataset.originalLastname &&
            document.getElementById("edit_role_id").value === updateUserButton.dataset.originalRoleId &&
            document.getElementById("edit_student_id").value === updateUserButton.dataset.originalStudentId;
    }

    const updateUserForm = document.querySelector('form[action="Controller/User/update.php"]');
    const editUserFirstName = document.getElementById('edit_firstname');
    const editUserLastName = document.getElementById('edit_lastname');

    [editUserFirstName, editUserLastName].forEach(function (input) {
        if (input) {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\s'.-]/gu, '');
            });
        }
    });

    if (updateUserForm) {
        updateUserForm.addEventListener('submit', function (event) {
            if (!userNamePattern.test(editUserFirstName.value.trim()) || !userNamePattern.test(editUserLastName.value.trim())) {
                event.preventDefault();
                Swal.fire({
                    title: 'Invalid Name',
                    text: 'First and last names must contain letters only.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    draggable: false
                });
            }
        });
    }


    /* ================================================================
       VIEW USER
       ================================================================ */

    function viewUser(
        username,
        firstname,
        lastname,
        role
    ) {

        document.getElementById("view_username").value =
            username;

        document.getElementById("view_firstname").value =
            firstname;

        document.getElementById("view_lastname").value =
            lastname;

        document.getElementById("view_role").value =
            role;


        $("#viewUserModal").modal("show");

    }


</script>