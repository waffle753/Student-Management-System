<?php

require_once __DIR__ . "/../config/auth.php";
requireStaffLogin();
include_once __DIR__ . "/../models/Instructor.php";
include_once __DIR__ . "/../config/pagination.php";

$instructorModel = new Instructor();

$instructors = $instructorModel->getAllInstructors();
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

    
    </style>

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

                                        <div class="d-flex justify-content-center align-items-center" style="gap: 6px;">

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

