<?php

include_once __DIR__ . "/../config/database.php";

class CurriculumSubject
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getAllCurriculumSubjects()
    {
        return $this->conn->query(
            "SELECT cs.id, cs.subject_id, cs.program_id,
                    s.SubjectCode, s.SubjectName,
                    p.CourseCode, p.CourseName
             FROM curriculum_subjects cs
             LEFT JOIN subject s ON s.id = cs.subject_id
             LEFT JOIN program p ON p.id = cs.program_id
             ORDER BY cs.id DESC"
        );
    }

    public function getStudentLoad($courseName)
    {
        $stmt = $this->conn->prepare(
            "SELECT s.id AS subject_id, s.SubjectCode, s.SubjectName, s.Unit,
                    COALESCE(TIME_FORMAT(csched.time_start, '%h:%i %p'), '') AS time_start,
                    COALESCE(TIME_FORMAT(csched.time_end, '%h:%i %p'), '') AS time_end,
                    COALESCE(csched.days, '') AS days,
                    COALESCE(csched.room, '') AS room,
                    TRIM(CONCAT(COALESCE(i.firstname, ''), ' ', COALESCE(i.lastname, ''))) AS instructor
             FROM curriculum_subjects csub
             INNER JOIN subject s ON s.id = csub.subject_id
             INNER JOIN program p ON p.id = csub.program_id
             LEFT JOIN class_schedules csched ON csched.subject_id = s.id
             LEFT JOIN instructor i ON i.id = csched.instructor_id
             WHERE p.CourseName = ?
             ORDER BY s.SubjectCode ASC"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $courseName);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $rows = [];
        $totalUnits = 0;
        $countedSubjects = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
            if (!isset($countedSubjects[$row['subject_id']])) {
                $totalUnits += (int) ($row['Unit'] ?? 0);
                $countedSubjects[$row['subject_id']] = true;
            }
        }

        $stmt->close();

        return [
            'subjects' => $rows,
            'total_units' => $totalUnits,
        ];
    }

    public function addCurriculumSubject($subjectId, $programId)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO curriculum_subjects (subject_id, program_id) VALUES (?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $subjectId, $programId);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateCurriculumSubject($id, $subjectId, $programId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE curriculum_subjects
             SET subject_id = ?, program_id = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iii", $subjectId, $programId, $id);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function deleteCurriculumSubject($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM curriculum_subjects WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
