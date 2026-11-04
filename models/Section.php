<?php

include_once __DIR__ . "/../config/database.php";

class Section
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getAllSections()
    {
        return $this->conn->query(
            "SELECT s.section_id, s.section_name, s.program_id, s.year_level,
                    p.CourseCode, p.CourseName
             FROM sections s
             INNER JOIN program p ON p.id = s.program_id
             ORDER BY s.section_id DESC"
        );
    }

    public function sectionMatchesStudent($sectionId, $programName, $yearLevel)
    {
        $stmt = $this->conn->prepare(
            "SELECT s.section_id
             FROM sections s
             INNER JOIN program p ON p.id = s.program_id
             WHERE s.section_id = ? AND p.CourseName = ? AND s.year_level = ?
             LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("iss", $sectionId, $programName, $yearLevel);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $matches = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $matches;
    }

    public function sectionMatchesCurriculum($sectionId, $programId, $yearLevel)
    {
        $stmt = $this->conn->prepare(
            "SELECT section_id
             FROM sections
             WHERE section_id = ? AND program_id = ? AND year_level = ?
             LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("iis", $sectionId, $programId, $yearLevel);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $matches = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $matches;
    }

    public function sectionExists($sectionName, $programId, $yearLevel, $excludeId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT section_id
             FROM sections
             WHERE section_name = ? AND program_id = ? AND year_level = ? AND section_id != ?
             LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("sisi", $sectionName, $programId, $yearLevel, $excludeId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function addSection($sectionName, $programId, $yearLevel)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO sections (section_name, program_id, year_level) VALUES (?, ?, ?)"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sis", $sectionName, $programId, $yearLevel);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateSection($id, $sectionName, $programId, $yearLevel)
    {
        $stmt = $this->conn->prepare(
            "UPDATE sections
             SET section_name = ?, program_id = ?, year_level = ?
             WHERE section_id = ?"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sisi", $sectionName, $programId, $yearLevel, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function deleteSection($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM sections WHERE section_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
