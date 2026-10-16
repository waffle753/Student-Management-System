<?php

include_once __DIR__ . "/../config/database.php";

class ClassSchedule
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getAllClassSchedules()
    {
        return $this->conn->query(
            "SELECT cs.*, s.SubjectCode, s.SubjectName,
                    i.firstname, i.lastname
             FROM class_schedules cs
             LEFT JOIN subject s ON s.ID = cs.subject_id
             LEFT JOIN instructor i ON i.id = cs.instructor_id
             ORDER BY cs.id DESC"
        );
    }

    public function getCurriculumSubjects()
    {
        return $this->conn->query(
            "SELECT DISTINCT s.id, s.SubjectCode, s.SubjectName
             FROM curriculum_subjects cs
             INNER JOIN subject s ON s.id = cs.subject_id
             ORDER BY s.SubjectCode ASC"
        );
    }

    public function addClassSchedule($subjectId, $timeStart, $timeEnd, $days, $room, $instructorId)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO class_schedules
                (subject_id, time_start, time_end, days, room, instructor_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("issssi", $subjectId, $timeStart, $timeEnd, $days, $room, $instructorId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateClassSchedule($id, $subjectId, $timeStart, $timeEnd, $days, $room, $instructorId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE class_schedules
             SET subject_id = ?, time_start = ?, time_end = ?, days = ?, room = ?, instructor_id = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("issssii", $subjectId, $timeStart, $timeEnd, $days, $room, $instructorId, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function deleteClassSchedule($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM class_schedules WHERE id = ?");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
