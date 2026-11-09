<?php

require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();
include_once __DIR__ . "/../models/Instructor.php";
include_once __DIR__ . "/../models/ClassSchedule.php";
include_once __DIR__ . "/../config/pagination.php";

$instructorModel = new Instructor();
$classScheduleModel = new ClassSchedule();

$instructors = $instructorModel->getAllInstructors();
$scheduleResult = $classScheduleModel->getAllClassSchedules();
$schedulesByInstructor = [];
if (!$scheduleResult) {
    die("Unable to load instructor class schedules.");
}
while ($scheduleRow = $scheduleResult->fetch_assoc()) {
    $instructorId = (string) ($scheduleRow['instructor_id'] ?? '');
    $sectionName = trim((string) ($scheduleRow['managed_section_name'] ?? ''));
    if ($sectionName === '') {
        $sectionName = trim((string) ($scheduleRow['section'] ?? 'Section not set'));
    }

    $schedulesByInstructor[$instructorId][] = [
        'id' => (string) ($scheduleRow['id'] ?? ''),
        'subject_code' => (string) ($scheduleRow['SubjectCode'] ?? ''),
        'subject' => (string) ($scheduleRow['SubjectName'] ?? 'Subject unavailable'),
        'program' => (string) ($scheduleRow['CourseName'] ?? 'Program unavailable'),
        'year' => (string) ($scheduleRow['year_level'] ?? ''),
        'semester' => (string) ($scheduleRow['SemesterName'] ?? 'Semester not set'),
        'section' => $sectionName,
        'time' => substr((string) ($scheduleRow['time_start'] ?? ''), 0, 5) . ' - ' .
            substr((string) ($scheduleRow['time_end'] ?? ''), 0, 5),
        'days' => (string) ($scheduleRow['days'] ?? ''),
        'room' => (string) ($scheduleRow['room'] ?? ''),
    ];
}
$recordsPerPage = 10;
$currentPage = max(1, (int) ($_GET['list_page'] ?? 1));
$totalRecords = $instructors ? $instructors->num_rows : 0;
$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $recordsPerPage;
if ($instructors) {
    $instructors->data_seek($offset);
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

    .modern-data-table tbody tr:hover td {
        background-color: rgba(59, 130, 246, 0.04);
    }

    .instructor-action-buttons {
        display: grid;
        grid-template-columns: repeat(4, minmax(74px, 1fr));
        gap: 8px;
        align-items: stretch;
        min-width: 340px;
    }

    .instructor-action-buttons .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        width: 100%;
        min-height: 42px;
        margin: 0;
        padding: 0.35rem 0.4rem;
        line-height: 1.15;
        white-space: normal;
    }

    .instructor-assigned-subjects-table {
        min-width: 1150px;
    }

    .instructor-assigned-subjects-table th,
    .instructor-assigned-subjects-table td {
        white-space: nowrap;
    }

</style>

    <?php if (isset($_GET['status'])): ?>
        <script>
            const instructorAlertStatus = <?= json_encode($_GET['status'] ?? 'error') ?>;
            const instructorAlertTitle = <?= json_encode($_GET['title'] ?? 'Operation Result') ?>;
            const instructorAlertMessage = <?= json_encode($_GET['message'] ?? '') ?>;
            Swal.fire({
                title: instructorAlertTitle,
                text: instructorAlertMessage,
                icon: instructorAlertStatus === 'success' ? 'success' : 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                const url = new URL(window.location.href);
                ['status', 'title', 'message'].forEach((key) => url.searchParams.delete(key));
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Add Instructor</h3>
        </div>
        <form method="POST" action="Controller/Instructor/create.php">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="add_instructor_employee_id">Employee ID</label>
                            <input type="text" name="Employee_ID" id="add_instructor_employee_id" class="form-control"
                                inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="Enter 6-digit employee ID" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="add_instructor_firstname">First Name</label>
                            <input type="text" name="firstname" id="add_instructor_firstname" class="form-control"
                                maxlength="100" placeholder="Enter first name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="add_instructor_lastname">Last Name</label>
                            <input type="text" name="lastname" id="add_instructor_lastname" class="form-control"
                                maxlength="100" placeholder="Enter last name" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Instructor</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>

    <!-- ============================================================
         INSTRUCTOR RECORDS
    ============================================================ -->

    <div class="card mt-4">

        <div class="card-header bg-primary text-white">

            <h3 class="card-title">
                <i class="fas fa-chalkboard-teacher mr-2"></i>
                Instructor Records
            </h3>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table modern-data-table">

                    <thead>

                        <tr>

                            <th style="
                                    padding: 0.9rem 0.8rem;
                                    font-size: 16px;
                                    font-weight: 700;
                                    border: 1px solid #dfe7f1;
                                ">
                                ID
                            </th>

                            <th style="
                                    padding: 0.9rem 0.8rem;
                                    font-size: 16px;
                                    font-weight: 700;
                                    border: 1px solid #dfe7f1;
                                ">
                                Employee ID
                            </th>

                            <th style="
                                    padding: 0.9rem 0.8rem;
                                    font-size: 16px;
                                    font-weight: 700;
                                    border: 1px solid #dfe7f1;
                                ">
                                First Name
                            </th>

                            <th style="
                                    padding: 0.9rem 0.8rem;
                                    font-size: 16px;
                                    font-weight: 700;
                                    border: 1px solid #dfe7f1;
                                ">
                                Last Name
                            </th>

                            <th style="
                                    padding: 0.9rem 0.8rem;
                                    font-size: 16px;
                                    font-weight: 700;
                                    border: 1px solid #dfe7f1;
                                    width: 220px;
                                ">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($instructors && $instructors->num_rows > 0): ?>

                            <?php while ($row = $instructors->fetch_assoc()): ?>

                                <tr>

                                    <!-- ID -->
                                    <td style="
                                            padding: 0.85rem 0.8rem;
                                            border: 1px solid #edf2f7;
                                            vertical-align: middle;
                                        ">
                                        <?= htmlspecialchars($row['id']) ?>
                                    </td>


                                    <!-- Employee ID -->
                                    <td style="
                                            padding: 0.85rem 0.8rem;
                                            border: 1px solid #edf2f7;
                                            vertical-align: middle;
                                        ">
                                        <?= htmlspecialchars($row['Employee_ID']) ?>
                                    </td>


                                    <!-- First Name -->
                                    <td style="
                                            padding: 0.85rem 0.8rem;
                                            border: 1px solid #edf2f7;
                                            vertical-align: middle;
                                        ">
                                        <?= htmlspecialchars($row['firstname']) ?>
                                    </td>


                                    <!-- Last Name -->
                                    <td style="
                                            padding: 0.85rem 0.8rem;
                                            border: 1px solid #edf2f7;
                                            vertical-align: middle;
                                        ">
                                        <?= htmlspecialchars($row['lastname']) ?>
                                    </td>


                                    <!-- Actions -->
                                    <td style="
                                            padding: 0.85rem 0.8rem;
                                            border: 1px solid #edf2f7;
                                            vertical-align: middle;
                                        ">

                                        <div class="instructor-action-buttons">

                                            <!-- View -->
                                            <button type="button" class="btn btn-secondary btn-sm action-btn view-instructor"
                                                data-id="<?= htmlspecialchars($row['id']) ?>" data-employee-id="<?= htmlspecialchars(
                                                      $row['Employee_ID'],
                                                      ENT_QUOTES
                                                  ) ?>" data-firstname="<?= htmlspecialchars(
                                                       $row['firstname'],
                                                       ENT_QUOTES
                                                   ) ?>" data-lastname="<?= htmlspecialchars(
                                                        $row['lastname'],
                                                        ENT_QUOTES
                                                    ) ?>" title="View">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </button>


                                            <!-- Edit -->
                                            <button type="button" class="btn btn-info btn-sm action-btn edit-instructor"
                                                data-id="<?= htmlspecialchars($row['id']) ?>" data-employee-id="<?= htmlspecialchars(
                                                      $row['Employee_ID'],
                                                      ENT_QUOTES
                                                  ) ?>" data-firstname="<?= htmlspecialchars(
                                                       $row['firstname'],
                                                       ENT_QUOTES
                                                   ) ?>" data-lastname="<?= htmlspecialchars(
                                                        $row['lastname'],
                                                        ENT_QUOTES
                                                    ) ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <!-- Assigned Subjects -->
                                            <button type="button" class="btn btn-primary btn-sm action-btn view-instructor-subjects"
                                                data-id="<?= htmlspecialchars((string) $row['id'], ENT_QUOTES) ?>"
                                                data-name="<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname'], ENT_QUOTES) ?>"
                                                title="View assigned subjects">
                                                <i class="fas fa-book"></i>
                                                Subjects
                                            </button>

                                            <!-- Delete -->
                                            <a href="Controller/Instructor/delete.php?id=<?= urlencode($row['id']) ?>"
                                                class="btn btn-danger btn-sm action-btn delete-instructor" title="Delete">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="5" class="text-center text-muted py-4">
                                    No instructors found.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- ========================================================
                 PAGINATION
            ========================================================= -->

            <?php if (false): ?>

                <?php
                /*
                 * Preserve existing GET parameters.
                 * This allows page navigation to keep other
                 * query parameters if they exist.
                 */
                function instructorPageUrl($page)
                {
                    $params = $_GET;
                    $params['list_page'] = $page;

                    return '?' . http_build_query($params);
                }

                ?>

                <div class="
                        pagination-container
                        d-flex
                        justify-content-between
                        align-items-center
                        flex-wrap
                    ">

                    <!-- Showing information -->
                    <div class="pagination-info">

                        Showing

                        <strong>
                            <?= $offset + 1 ?>
                        </strong>

                        -

                        <strong>
                            <?= min(
                                $offset + $recordsPerPage,
                                $totalRecords
                            ) ?>
                        </strong>

                        of

                        <strong>
                            <?= $totalRecords ?>
                        </strong>

                        instructors

                    </div>


                    <!-- Pagination buttons -->
                    <nav aria-label="Instructor pagination">

                        <ul class="pagination mb-0">

                            <!-- Previous -->
                            <li class="
                                    page-item
                                    <?= $currentPage <= 1 ? 'disabled' : '' ?>
                                ">

                                <?php if ($currentPage > 1): ?>

                                    <a class="page-link" href="<?= htmlspecialchars(
                                        instructorPageUrl($currentPage - 1)
                                    ) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                        Previous
                                    </a>

                                <?php else: ?>

                                    <span class="page-link">
                                        <i class="fas fa-chevron-left"></i>
                                        Previous
                                    </span>

                                <?php endif; ?>

                            </li>


                            <?php

                            /*
                             * Display a maximum of 5 pages around
                             * the current page.
                             */
                            $startPage = max(
                                1,
                                $currentPage - 2
                            );

                            $endPage = min(
                                $totalPages,
                                $currentPage + 2
                            );

                            ?>


                            <!-- First page + ellipsis -->
                            <?php if ($startPage > 1): ?>

                                <li class="page-item">

                                    <a class="page-link" href="<?= htmlspecialchars(
                                        instructorPageUrl(1)
                                    ) ?>">
                                        1
                                    </a>

                                </li>


                                <?php if ($startPage > 2): ?>

                                    <li class="page-item disabled">

                                        <span class="page-link">
                                            ...
                                        </span>

                                    </li>

                                <?php endif; ?>

                            <?php endif; ?>


                            <!-- Page numbers -->
                            <?php for (
                                $page = $startPage;
                                $page <= $endPage;
                                $page++
                            ): ?>

                                <li class="
                                        page-item
                                        <?= $page === $currentPage
                                            ? 'active'
                                            : '' ?>
                                    ">

                                    <a class="page-link" href="<?= htmlspecialchars(
                                        instructorPageUrl($page)
                                    ) ?>">
                                        <?= $page ?>
                                    </a>

                                </li>

                            <?php endfor; ?>


                            <!-- Last page + ellipsis -->
                            <?php if ($endPage < $totalPages): ?>

                                <?php if (
                                    $endPage < $totalPages - 1
                                ): ?>

                                    <li class="page-item disabled">

                                        <span class="page-link">
                                            ...
                                        </span>

                                    </li>

                                <?php endif; ?>


                                <li class="page-item">

                                    <a class="page-link" href="<?= htmlspecialchars(
                                        instructorPageUrl($totalPages)
                                    ) ?>">
                                        <?= $totalPages ?>
                                    </a>

                                </li>

                            <?php endif; ?>


                            <!-- Next -->
                            <li class="
                                    page-item
                                    <?= $currentPage >= $totalPages
                                        ? 'disabled'
                                        : '' ?>
                                ">

                                <?php if (
                                    $currentPage < $totalPages
                                ): ?>

                                    <a class="page-link" href="<?= htmlspecialchars(
                                        instructorPageUrl($currentPage + 1)
                                    ) ?>">
                                        Next
                                        <i class="fas fa-chevron-right"></i>
                                    </a>

                                <?php else: ?>

                                    <span class="page-link">
                                        Next
                                        <i class="fas fa-chevron-right"></i>
                                    </span>

                                <?php endif; ?>

                            </li>

                        </ul>

                    </nav>

                </div>

            <?php endif; ?>

            <?php renderPagination($currentPage, $totalPages, $offset, $recordsPerPage, $totalRecords, 'instructors'); ?>

        </div>

    </div>

</div>


<!-- ================================================================
     VIEW INSTRUCTOR MODAL
================================================================ -->

<div class="modal fade" id="instructorViewModal" tabindex="-1" role="dialog" aria-labelledby="instructorViewModalLabel"
    aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header bg-secondary text-white">

                <h5 class="modal-title" id="instructorViewModalLabel">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    Instructor Information
                </h5>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>


            <div class="modal-body">

                <div class="form-group">

                    <label for="view_instructor_id">
                        ID
                    </label>

                    <input type="text" class="form-control" id="view_instructor_id" readonly>

                </div>


                <div class="form-group">

                    <label for="view_instructor_employee_id">
                        Employee ID
                    </label>

                    <input type="text" class="form-control" id="view_instructor_employee_id" readonly>

                </div>


                <div class="form-group">

                    <label for="view_instructor_firstname">
                        First Name
                    </label>

                    <input type="text" class="form-control" id="view_instructor_firstname" readonly>

                </div>


                <div class="form-group">

                    <label for="view_instructor_lastname">
                        Last Name
                    </label>

                    <input type="text" class="form-control" id="view_instructor_lastname" readonly>

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

<div class="modal fade" id="instructorSubjectsModal" tabindex="-1" role="dialog" aria-labelledby="instructorSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="instructorSubjectsModalLabel">
                    <i class="fas fa-book mr-2"></i>Assigned Subjects - <span id="instructorSubjectsName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table modern-data-table instructor-assigned-subjects-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Subject Code</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Program</th>
                                <th scope="col">Year Level</th>
                                <th scope="col">Semester</th>
                                <th scope="col">Section</th>
                                <th scope="col">Time</th>
                                <th scope="col">Days</th>
                                <th scope="col">Room</th>
                            </tr>
                        </thead>
                        <tbody id="instructorSubjectsRows"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const schedulesByInstructor = <?= json_encode(
            $schedulesByInstructor,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) ?>;
        const yearLabels = {
            '1': '1st Year',
            '2': '2nd Year',
            '3': '3rd Year',
            '4': '4th Year',
            '5': '5th Year'
        };
        const rows = document.getElementById('instructorSubjectsRows');
        const instructorName = document.getElementById('instructorSubjectsName');

        document.querySelectorAll('.view-instructor-subjects').forEach(function (button) {
            button.addEventListener('click', function () {
                instructorName.textContent = button.dataset.name || '';
                rows.replaceChildren();

                const schedules = schedulesByInstructor[button.dataset.id] || [];
                if (schedules.length === 0) {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 10;
                    cell.className = 'text-center text-muted py-4';
                    cell.textContent = 'No subjects are assigned to this instructor.';
                    row.appendChild(cell);
                    rows.appendChild(row);
                } else {
                    schedules.forEach(function (schedule) {
                        const row = document.createElement('tr');
                        const values = [
                            schedule.id,
                            schedule.subject_code || 'Code unavailable',
                            schedule.subject || 'Subject unavailable',
                            schedule.program || 'Program unavailable',
                            yearLabels[schedule.year] || 'Year not set',
                            schedule.semester || 'Semester not set',
                            schedule.section,
                            schedule.time,
                            schedule.days,
                            schedule.room
                        ];
                        values.forEach(function (value) {
                            const cell = document.createElement('td');
                            cell.textContent = value || '';
                            row.appendChild(cell);
                        });
                        rows.appendChild(row);
                    });
                }

                $('#instructorSubjectsModal').modal('show');
            });
        });
    })();
</script>

<!-- ================================================================
     EDIT INSTRUCTOR MODAL
================================================================ -->

<div class="modal fade" id="instructorEditModal" tabindex="-1" role="dialog" aria-labelledby="instructorEditModalLabel"
    aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <form method="POST" action="Controller/Instructor/update.php">

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title" id="instructorEditModalLabel">
                        Edit Instructor
                    </h5>

                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_instructor_id">


                    <div class="form-group">

                        <label for="edit_instructor_employee_id">
                            Employee ID
                        </label>

                        <input type="text" name="Employee_ID" id="edit_instructor_employee_id" class="form-control"
                            required>

                    </div>

                    <div class="form-group">

                        <label for="edit_instructor_firstname">
                            First Name
                        </label>

                        <input type="text" name="firstname" id="edit_instructor_firstname" class="form-control"
                            required>

                    </div>


                    <div class="form-group">

                        <label for="edit_instructor_lastname">
                            Last Name
                        </label>

                        <input type="text" name="lastname" id="edit_instructor_lastname" class="form-control" required>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" id="updateInstructorBtn" class="btn btn-primary" disabled>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>




</div>
