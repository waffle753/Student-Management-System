<?php

$host = "localhost";
$user = "root";
$password = "1234";
$database = "student_management";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$schemaChecks = [
    ["information", "Student_Number", "VARCHAR(20)"],
    ["information", "Course", "VARCHAR(255)"],
    ["information", "School_Year", "VARCHAR(255)"],
    ["information", "Email", "VARCHAR(255)"],
    ["program", "CourseCode", "VARCHAR(45)"],
    ["program", "CourseName", "VARCHAR(255)"],
];

foreach ($schemaChecks as [$table, $column, $expectedType]) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");

    if ($check && $check->num_rows > 0) {
        $columnInfo = $check->fetch_assoc();
        $actualType = strtoupper($columnInfo['Type']);

        if (strpos($actualType, str_replace(" ", "", strtoupper($expectedType))) === false) {
            $conn->query("ALTER TABLE `$table` MODIFY `$column` $expectedType NOT NULL");
        }
    }
}

$studentLinkCheck = $conn->query("SHOW COLUMNS FROM `user` LIKE 'student_id'");
if ($studentLinkCheck && $studentLinkCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `user` ADD COLUMN `student_id` INT NULL UNIQUE");
}

$studentProfileColumns = [
    ['Date_Of_Birth', 'DATE NULL'],
    ['Gender', 'VARCHAR(20) NULL'],
    ['Civil_Status', 'VARCHAR(50) NULL'],
    ['House_Unit_No', 'VARCHAR(100) NULL'],
    ['Street', 'VARCHAR(255) NULL'],
    ['Barangay', 'VARCHAR(255) NULL'],
    ['City_Municipality', 'VARCHAR(255) NULL'],
    ['Province', 'VARCHAR(255) NULL'],
    ['Contact_Number', 'VARCHAR(30) NULL'],
    ['Nationality', 'VARCHAR(100) NULL'],
    ['Year_Level', 'VARCHAR(50) NULL'],
    ['Department', 'VARCHAR(255) NULL'],
    ['Current_GPA', 'VARCHAR(20) NULL'],
    ['Emergency_First_Name', 'VARCHAR(100) NULL'],
    ['Emergency_Middle_Name', 'VARCHAR(100) NULL'],
    ['Emergency_Last_Name', 'VARCHAR(100) NULL'],
    ['Emergency_Contact_Number', 'VARCHAR(30) NULL'],
    ['Emergency_House_Unit_No', 'VARCHAR(100) NULL'],
    ['Emergency_Street', 'VARCHAR(255) NULL'],
    ['Emergency_Barangay', 'VARCHAR(255) NULL'],
    ['Emergency_City_Municipality', 'VARCHAR(255) NULL'],
    ['Emergency_Province', 'VARCHAR(255) NULL'],
    ['Emergency_Email', 'VARCHAR(255) NULL'],
];

foreach ($studentProfileColumns as [$column, $definition]) {
    $columnCheck = $conn->query("SHOW COLUMNS FROM `information` LIKE '$column'");
    if ($columnCheck && $columnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE `information` ADD COLUMN `$column` $definition");
    }
}

$studentRoleCheck = $conn->query("SELECT id FROM `role` WHERE LOWER(rolename) = 'student' LIMIT 1");
if ($studentRoleCheck && $studentRoleCheck->num_rows === 0) {
    $studentRoleInsert = $conn->prepare("INSERT INTO `role` (rolename) VALUES (?)");
    if ($studentRoleInsert) {
        $studentRoleName = "Student";
        $studentRoleInsert->bind_param("s", $studentRoleName);
        $studentRoleInsert->execute();
        $studentRoleInsert->close();
    }
}

?>