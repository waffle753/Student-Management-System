<?php

include_once __DIR__ . "/../config/database.php";

class Semester
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        $this->ensureSemesterIdString();
    }

    private function ensureSemesterIdString()
    {
        $check = $this->conn->query(
            "SELECT DATA_TYPE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'semester'
               AND COLUMN_NAME = 'semester_id'"
        );

        if (!$check || $check->num_rows === 0) {
            return;
        }

        $row = $check->fetch_assoc();
        $dataType = strtolower((string) ($row['DATA_TYPE'] ?? ''));

        $numericTypes = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'numeric'];

        if (in_array($dataType, $numericTypes, true)) {
            $this->conn->query("ALTER TABLE semester MODIFY semester_id VARCHAR(50) NOT NULL");
        }
    }

    // Display all semesters
    public function getAllSemesters()
    {
        $sql = "SELECT *
                FROM semester
                ORDER BY ID DESC";

        return $this->conn->query($sql);
    }

    // Get one semester
    public function getSemesterById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM semester
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

        $semester = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $semester;
    }

    // Add semester
    public function addSemester($semester_id, $semester)
    {
        $semester_id = trim((string) $semester_id);

        $nextIdResult = $this->conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM semester");
        $nextId = $nextIdResult && $nextIdResult->num_rows > 0 ? (int) $nextIdResult->fetch_assoc()['next_id'] : 1;

        $sql = "INSERT INTO semester
                (
                    id,
                    semester_id,
                    semester
                )
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iss",
            $nextId,
            $semester_id,
            $semester
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Update semester
    public function updateSemester($id, $semester_id, $semester)
    {
        $semester_id = trim((string) $semester_id);

        $sql = "UPDATE semester
                SET
                    semester_id = ?,
                    semester = ?
                WHERE ID = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssi",
            $semester_id,
            $semester,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Delete semester permanently
    public function deleteSemester($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM semester WHERE ID = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Check duplicate semester ID
    public function semesterExists($semester_id)
    {
        $semester_id = trim((string) $semester_id);

        $stmt = $this->conn->prepare(
            "SELECT ID
             FROM semester
             WHERE semester_id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $semester_id);

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