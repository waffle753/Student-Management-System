<?php
require_once __DIR__ . "/../../config/auth.php";
requireStaffLogin();

include_once __DIR__ . "/../../models/Programs.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $program = new Program();

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $CourseCode = trim($_POST['CourseCode'] ?? '');
    $CourseName = trim($_POST['CourseName'] ?? '');

    if (
        $id <= 0 ||
        $CourseCode === "" ||
        $CourseName === ""
    ) {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields.'
            }).then(() => {
                history.back();
            });
        </script>
        ";

        exit;
    }

    if (
        $program->updateProgram(
            $id,
            $CourseCode,
            $CourseName
        )
    ) {

        header("Location: ../../index.php?page=programs&updated=1");
        exit;

    } else {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Update failed.'
            }).then(() => {
                history.back();
            });
        </script>
        ";

        exit;
    }
}
?>