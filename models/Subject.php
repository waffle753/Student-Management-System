<?php

include_once __DIR__ . "/../config/database.php";

class Subject
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // Display all subjects
    public function getAllSubjects()
    {
        $sql = "SELECT *
                FROM subject
                ORDER BY ID DESC";

        return $this->conn->query($sql);
    }

    // Get one subject
    public function getSubjectById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM subject
             WHERE ID = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $subject = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $subject;
    }

    // Add subject
    public function addSubject($SubjectCode, $SubjectName, $Unit)
    {
        $nextIdResult = $this->conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM subject");
        $nextId = $nextIdResult && $nextIdResult->num_rows > 0 ? (int) $nextIdResult->fetch_assoc()['next_id'] : 1;

        $sql = "INSERT INTO subject
                (
                    id,
                    SubjectCode,
                    SubjectName,
                    Unit
                )
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "issi",
            $nextId,
            $SubjectCode,
            $SubjectName,
            $Unit
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Update subject
    public function updateSubject($id, $SubjectCode, $SubjectName, $Unit)
    {
        $sql = "UPDATE subject
                SET
                    SubjectCode = ?,
                    SubjectName = ?,
                    Unit = ?
                WHERE ID = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssii",
            $SubjectCode,
            $SubjectName,
            $Unit,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Delete subject permanently
    public function deleteSubject($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM subject WHERE ID = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Check duplicate Subject Code
    public function subjectExists($SubjectCode, $excludeId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT ID
             FROM subject
             WHERE SubjectCode = ?
             AND ID != ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $SubjectCode, $excludeId);

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
?>