<?php

include_once __DIR__ . "/../config/database.php";

class Program
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // Display all programs
    public function getAllPrograms()
    {
        $sql = "SELECT *
                FROM program
                ORDER BY id DESC";

        return $this->conn->query($sql);
    }

    // Get one program
    public function getProgramById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM program
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

        $program = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $program;
    }

    // Add program
    public function addProgram($CourseCode, $CourseName)
    {
        $sql = "INSERT INTO program
                (
                    CourseCode,
                    CourseName
                )
                VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ss",
            $CourseCode,
            $CourseName
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Update program
    public function updateProgram($id, $CourseCode, $CourseName)
    {
        $sql = "UPDATE program
                SET
                    CourseCode = ?,
                    CourseName = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssi",
            $CourseCode,
            $CourseName,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Delete program permanently
    public function deleteProgram($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM program WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Check duplicate Course Code
    public function programExists($CourseCode)
    {
        $stmt = $this->conn->prepare(
            "SELECT id
             FROM program
             WHERE CourseCode = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $CourseCode);

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