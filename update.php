<?php
require_once __DIR__ . "/config/auth.php";
requireStaffLogin();
include "models/studentquery.php";
include "models/Programs.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student = new Student();

    $id = trim($_POST['id']);
    $student_no = trim($_POST['student_no']);
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $course = trim($_POST['course']);
    $school_year = trim($_POST['school_year']);
    $email = trim($_POST['email']);

    $programModel = new Program();
    $programs = $programModel->getAllPrograms();
    $validCourses = [];

    if ($programs) {
        while ($program = $programs->fetch_assoc()) {
            $validCourses[] = $program['CourseName'];
        }
    }

    // Validation
    if (
        $student_no === "" ||
        $fname === "" ||
        $lname === "" ||
        $course === "" ||
        $school_year === "" ||
        $email === ""
    ) {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon:'error',
                title:'Validation Error',
                text:'Please fill in all required fields.'
            }).then(()=>{
                history.back();
            });
        </script>";

        exit; // VERY IMPORTANT
    }

    if (!in_array($course, $validCourses, true)) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon:'error',
                title:'Invalid Course',
                text:'Please select a valid course from the list.'
            }).then(()=>{
                history.back();
            });
        </script>";

        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon:'error',
                title:'Invalid Email',
                text:'Please enter a valid email address.'
            }).then(()=>{
                history.back();
            });
        </script>";

        exit;
    }

    if ($student->studentExists($student_no, $id)) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon:'error',
                title:'Duplicate Student Number',
                text:'This student number already exists.'
            }).then(()=>{
                history.back();
            });
        </script>";

        exit;
    }

    if (
        $student->updateStudent(
            $id,
            $student_no,
            $fname,
            $mname,
            $lname,
            $course,
            $school_year,
            $email
        )
    ) {

        header("Location: index.php?updated=1");
        exit;

    } else {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon:'error',
                title:'Error',
                text:'Update failed.'
            });
        </script>";
    }
}