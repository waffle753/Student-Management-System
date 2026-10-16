<?php

include_once __DIR__ . "/../config/database.php";

class Student
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // Display all students
    public function getAllStudents()
    {
        $sql = "SELECT * FROM information WHERE Active = 0 ORDER BY ID DESC";
        return $this->conn->query($sql);
    }

    // Get one student
    public function getStudentById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM information WHERE ID = ? AND Active = 0");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // Add student
    public function addStudent(
        $student_no,
        $fname,
        $mname,
        $lname,
        $course,
        $school_year,
        $email,
        $dateOfBirth,
        $gender,
        $civilStatus,
        $houseUnitNo,
        $street,
        $barangay,
        $cityMunicipality,
        $province,
        $contactNumber,
        $nationality,
        $yearLevel,
        $department,
        $currentGpa,
        $emergencyFirstName,
        $emergencyMiddleName,
        $emergencyLastName,
        $emergencyContactNumber,
        $emergencyHouseUnitNo,
        $emergencyStreet,
        $emergencyBarangay,
        $emergencyCityMunicipality,
        $emergencyProvince,
        $emergencyEmail
    ) {
        $sql = "INSERT INTO information
                (
                    Student_Number,
                    First_Name,
                    Middle_Name,
                    Last_Name,
                    Course,
                    School_Year,
                    Email,
                    Date_Of_Birth,
                    Gender,
                    Civil_Status,
                    House_Unit_No,
                    Street,
                    Barangay,
                    City_Municipality,
                    Province,
                    Contact_Number,
                    Nationality,
                    Year_Level,
                    Department,
                    Current_GPA,
                    Emergency_First_Name,
                    Emergency_Middle_Name,
                    Emergency_Last_Name,
                    Emergency_Contact_Number,
                    Emergency_House_Unit_No,
                    Emergency_Street,
                    Emergency_Barangay,
                    Emergency_City_Municipality,
                    Emergency_Province,
                    Emergency_Email,
                    Active
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0
                )";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $values = [
            $student_no,
            $fname,
            $mname,
            $lname,
            $course,
            $school_year,
            $email,
            $dateOfBirth,
            $gender,
            $civilStatus,
            $houseUnitNo,
            $street,
            $barangay,
            $cityMunicipality,
            $province,
            $contactNumber,
            $nationality,
            $yearLevel,
            $department,
            $currentGpa,
            $emergencyFirstName,
            $emergencyMiddleName,
            $emergencyLastName,
            $emergencyContactNumber,
            $emergencyHouseUnitNo,
            $emergencyStreet,
            $emergencyBarangay,
            $emergencyCityMunicipality,
            $emergencyProvince,
            $emergencyEmail,
        ];
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);

        return $stmt->execute();
    }

    // Update student
    public function updateStudent($id, $student_no, $fname, $mname, $lname, $course, $school_year, $email)
    {
        $sql = "UPDATE information
            SET
                Student_Number = ?,
                First_Name = ?,
                Middle_Name = ?,
                Last_Name = ?,
                Course = ?,
                School_Year = ?,
                Email = ?
            WHERE ID = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param(
            "sssssssi",
            $student_no,
            $fname,
            $mname,
            $lname,
            $course,
            $school_year,
            $email,
            $id
        );

        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        $stmt->close();

        return true;
    }

    public function updateStudentProfile(
        $id,
        $fname,
        $mname,
        $lname,
        $email,
        $dateOfBirth,
        $gender,
        $civilStatus,
        $houseUnitNo,
        $street,
        $barangay,
        $cityMunicipality,
        $province,
        $contactNumber,
        $nationality,
        $programCourse,
        $yearLevel,
        $academicYear,
        $department,
        $currentGpa,
        $emergencyFirstName,
        $emergencyMiddleName,
        $emergencyLastName,
        $emergencyContactNumber,
        $emergencyHouseUnitNo,
        $emergencyStreet,
        $emergencyBarangay,
        $emergencyCityMunicipality,
        $emergencyProvince,
        $emergencyEmail
    ) {
        $stmt = $this->conn->prepare(
            "UPDATE information
             SET First_Name = ?, Middle_Name = ?, Last_Name = ?, Email = ?,
                 Date_Of_Birth = ?, Gender = ?, Civil_Status = ?, House_Unit_No = ?,
                 Street = ?, Barangay = ?, City_Municipality = ?, Province = ?,
                 Contact_Number = ?, Nationality = ?, Course = ?, Year_Level = ?,
                 School_Year = ?, Department = ?, Current_GPA = ?,
                 Emergency_First_Name = ?, Emergency_Middle_Name = ?,
                 Emergency_Last_Name = ?, Emergency_Contact_Number = ?,
                 Emergency_House_Unit_No = ?, Emergency_Street = ?,
                 Emergency_Barangay = ?, Emergency_City_Municipality = ?,
                 Emergency_Province = ?, Emergency_Email = ?
             WHERE ID = ? AND Active = 0"
        );

        if (!$stmt) {
            return false;
        }

        $values = [
            $fname, $mname, $lname, $email, $dateOfBirth, $gender, $civilStatus,
            $houseUnitNo, $street, $barangay, $cityMunicipality, $province,
            $contactNumber, $nationality, $programCourse, $yearLevel,
            $academicYear, $department, $currentGpa, $emergencyFirstName,
            $emergencyMiddleName, $emergencyLastName, $emergencyContactNumber,
            $emergencyHouseUnitNo, $emergencyStreet, $emergencyBarangay,
            $emergencyCityMunicipality, $emergencyProvince, $emergencyEmail, $id,
        ];
        $types = str_repeat('s', count($values) - 1) . 'i';
        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    // Soft Delete
    public function deleteStudent($id)
    {
        $stmt = $this->conn->prepare("UPDATE information SET Active = 1 WHERE ID = ?");

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // Check duplicate Student Number
    public function studentExists($student_no, $excludeId = null)
    {
        if ($excludeId !== null) {
            $stmt = $this->conn->prepare("SELECT ID FROM information WHERE Student_Number = ? AND ID != ?");
            $stmt->bind_param("si", $student_no, $excludeId);
        } else {
            $stmt = $this->conn->prepare("SELECT ID FROM information WHERE Student_Number = ?");
            $stmt->bind_param("s", $student_no);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}