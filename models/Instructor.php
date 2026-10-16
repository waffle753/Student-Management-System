<?php

include_once __DIR__ . "/../config/database.php";

class Instructor
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // Display all instructors
    public function getAllInstructors()
    {
        $sql = "SELECT *
                FROM instructor
                ORDER BY id DESC";

        return $this->conn->query($sql);
    }

    // Get one instructor
    public function getInstructorById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM instructor
             WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $instructor = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $instructor;
    }

    // Add instructor
    public function addInstructor($Employee_ID, $firstname, $lastname)
    {
        $sql = "INSERT INTO instructor
                (
                    Employee_ID,
                    firstname,
                    lastname
                )
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iss",
            $Employee_ID,
            $firstname,
            $lastname
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Update instructor
    public function updateInstructor(
        $id,
        $Employee_ID,
        $firstname,
        $lastname
    ) {
        $sql = "UPDATE instructor
                SET
                    Employee_ID = ?,
                    firstname = ?,
                    lastname = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "issi",
            $Employee_ID,
            $firstname,
            $lastname,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Delete instructor permanently
    public function deleteInstructor($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM instructor WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Check duplicate Employee ID
    public function instructorExists($Employee_ID, $excludeId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT id
             FROM instructor
             WHERE Employee_ID = ?
             AND id != ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $Employee_ID, $excludeId);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    
}