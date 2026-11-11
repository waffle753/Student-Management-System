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
            "SELECT cs.id, cs.subject_id, cs.program_id, cs.year_level, cs.semester_id,
                    s.SubjectCode, s.SubjectName,
                    p.CourseCode, p.CourseName, sem.semester_id AS SemesterCode, sem.semester AS SemesterName
             FROM curriculum_subjects cs
             LEFT JOIN subject s ON s.id = cs.subject_id
             LEFT JOIN program p ON p.id = cs.program_id
             LEFT JOIN semester sem ON sem.ID = cs.semester_id
             ORDER BY cs.id DESC"
        );
    }

    public function getStudentLoad($courseName, $yearLevel = '', $semesterName = '', $semesterId = 0, $sectionId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT csched.id AS class_schedule_id, csub.id AS curriculum_subject_id, csub.year_level, csub.semester_id,
                    sem.semester AS semester_name, s.id AS subject_id, s.SubjectCode, s.SubjectName, s.Unit,
                    section_record.section_name AS section,
                    COALESCE(TIME_FORMAT(csched.time_start, '%h:%i %p'), '') AS time_start,
                    COALESCE(TIME_FORMAT(csched.time_end, '%h:%i %p'), '') AS time_end,
                    COALESCE(csched.days, '') AS days,
                    COALESCE(csched.room, '') AS room,
                    TRIM(CONCAT(COALESCE(i.firstname, ''), ' ', COALESCE(i.lastname, ''))) AS instructor
             FROM curriculum_subjects csub
             INNER JOIN subject s ON s.id = csub.subject_id
             INNER JOIN program p ON p.id = csub.program_id
             LEFT JOIN semester sem ON sem.ID = csub.semester_id
             INNER JOIN sections section_record
                    ON section_record.section_id = ?
                   AND section_record.program_id = csub.program_id
                   AND section_record.year_level = csub.year_level
             INNER JOIN class_schedules csched
                    ON csched.curriculum_subject_id = csub.id
                   AND (
                        csched.section_id = section_record.section_id
                        OR (
                            csched.section_id IS NULL
                            AND csched.section = section_record.section_name
                            AND COALESCE(csched.program_id, csub.program_id) = section_record.program_id
                        )
                   )
             LEFT JOIN instructor i ON i.id = csched.instructor_id
             WHERE p.CourseName = ?
               AND ? <> ''
               AND csub.year_level = ?
               AND (
                    (? > 0 AND csub.semester_id = ?)
                    OR (? = 0 AND ? <> '' AND sem.semester = ?)
               )
             ORDER BY s.SubjectCode ASC, csched.section ASC"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "isssiiiss",
            $sectionId,
            $courseName,
            $yearLevel,
            $yearLevel,
            $semesterId,
            $semesterId,
            $semesterId,
            $semesterName,
            $semesterName
        );

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
            if (!isset($countedSubjects[$row['curriculum_subject_id']])) {
                $totalUnits += (int) ($row['Unit'] ?? 0);
                $countedSubjects[$row['curriculum_subject_id']] = true;
            }
        }

        $stmt->close();

        return [
            'subjects' => $rows,
            'total_units' => $totalUnits,
        ];
    }

    public function curriculumEntryExists($subjectId, $programId, $yearLevel, $semesterId, $excludeId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT id
             FROM curriculum_subjects
             WHERE subject_id = ? AND program_id = ? AND year_level = ? AND semester_id = ? AND id != ?
             LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("iisii", $subjectId, $programId, $yearLevel, $semesterId, $excludeId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function curriculumReferencesExist($subjectId, $programId, $semesterId)
    {
        $stmt = $this->conn->prepare(
            "SELECT s.ID
             FROM subject s
             INNER JOIN program p ON p.id = ?
             INNER JOIN semester sem ON sem.ID = ?
             WHERE s.ID = ?
             LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("iii", $programId, $semesterId, $subjectId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function addCurriculumSubject($subjectId, $programId, $yearLevel, $semesterId)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO curriculum_subjects (subject_id, program_id, year_level, semester_id) VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iisi", $subjectId, $programId, $yearLevel, $semesterId);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateCurriculumSubject($id, $subjectId, $programId, $yearLevel, $semesterId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE curriculum_subjects
             SET subject_id = ?, program_id = ?, year_level = ?, semester_id = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iisii", $subjectId, $programId, $yearLevel, $semesterId, $id);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function deleteCurriculumSubject($id)
    {
        $hasSchedules = $this->curriculumSubjectHasSchedules($id);
        if ($hasSchedules !== false) {
            return false;
        }

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

    public function curriculumSubjectHasSchedules($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM class_schedules WHERE curriculum_subject_id = ? LIMIT 1"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $hasSchedules = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $hasSchedules;
    }
}
