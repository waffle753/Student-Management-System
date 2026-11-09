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
            "SELECT cs.*, csub.id AS curriculum_subject_id,
                    COALESCE(csub.subject_id, cs.subject_id) AS subject_id,
                    COALESCE(csub.program_id, cs.program_id) AS program_id,
                    s.SubjectCode, s.SubjectName, csub.year_level,
                    sec.section_name AS managed_section_name,
                    sem.semester AS SemesterName, sem.semester_id AS SemesterCode,
                    p.CourseCode, p.CourseName,
                    i.firstname, i.lastname
             FROM class_schedules cs
             LEFT JOIN curriculum_subjects csub ON csub.id = cs.curriculum_subject_id
             LEFT JOIN subject s ON s.ID = COALESCE(csub.subject_id, cs.subject_id)
             LEFT JOIN program p ON p.id = COALESCE(csub.program_id, cs.program_id)
             LEFT JOIN semester sem ON sem.ID = csub.semester_id
             LEFT JOIN sections sec ON sec.section_id = cs.section_id
             LEFT JOIN instructor i ON i.id = cs.instructor_id
             ORDER BY cs.id DESC"
        );
    }

    public function getSchedulesForInstructor($instructorId)
    {
        $stmt = $this->conn->prepare(
            "SELECT cs.id, cs.time_start, cs.time_end, cs.days, cs.room,
                    COALESCE(csub.subject_id, cs.subject_id) AS subject_id,
                    subject_record.SubjectCode, subject_record.SubjectName,
                    COALESCE(csub.program_id, cs.program_id) AS program_id,
                    program_record.CourseName AS Program_Name,
                    csub.year_level AS Year_Level,
                    semester_record.semester AS Semester_Name,
                    COALESCE(section_record.section_name, cs.section) AS Section_Name
             FROM class_schedules cs
             LEFT JOIN curriculum_subjects csub ON csub.id = cs.curriculum_subject_id
             LEFT JOIN subject subject_record
                    ON subject_record.ID = COALESCE(csub.subject_id, cs.subject_id)
             LEFT JOIN program program_record
                    ON program_record.id = COALESCE(csub.program_id, cs.program_id)
             LEFT JOIN semester semester_record ON semester_record.ID = csub.semester_id
             LEFT JOIN sections section_record ON section_record.section_id = cs.section_id
             WHERE cs.instructor_id = ?
             ORDER BY program_record.CourseName, csub.year_level, semester_record.ID, subject_record.SubjectCode"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $instructorId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $schedules = $stmt->get_result();
        $stmt->close();

        return $schedules;
    }

    public function getCurriculumSubjects()
    {
        return $this->conn->query(
            "SELECT cs.id AS curriculum_subject_id, cs.subject_id, cs.program_id,
                    cs.year_level, cs.semester_id, s.SubjectCode, s.SubjectName,
                    p.CourseCode, p.CourseName, sem.semester AS SemesterName,
                    sem.semester_id AS SemesterCode
             FROM curriculum_subjects cs
             INNER JOIN subject s ON s.id = cs.subject_id
             INNER JOIN program p ON p.id = cs.program_id
             INNER JOIN semester sem ON sem.ID = cs.semester_id
             WHERE NULLIF(TRIM(cs.year_level), '') IS NOT NULL
             ORDER BY p.CourseCode ASC, cs.year_level ASC, sem.ID ASC, s.SubjectCode ASC"
        );
    }

    public function getCurriculumSubject($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT cs.id, cs.subject_id, cs.program_id, cs.year_level, cs.semester_id
             FROM curriculum_subjects cs
             INNER JOIN semester sem ON sem.ID = cs.semester_id
             WHERE cs.id = ? AND NULLIF(TRIM(cs.year_level), '') IS NOT NULL
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $curriculumSubject = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $curriculumSubject ?: null;
    }

    public function hasScheduleConflict($sectionId, $semesterId, $timeStart, $timeEnd, $days, $excludeId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT cs.days
             FROM class_schedules cs
             INNER JOIN curriculum_subjects csub ON csub.id = cs.curriculum_subject_id
             WHERE cs.section_id = ? AND csub.semester_id = ? AND cs.id != ?
               AND cs.time_start < ? AND cs.time_end > ?"
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("iiiss", $sectionId, $semesterId, $excludeId, $timeEnd, $timeStart);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $requestedDays = array_flip(explode(',', $days));
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $existingDays = explode(',', (string) ($row['days'] ?? ''));
            if (array_intersect_key($requestedDays, array_flip($existingDays)) !== []) {
                $stmt->close();
                return true;
            }
        }

        $stmt->close();
        return false;
    }

    public function addClassSchedule($curriculumSubjectId, $subjectId, $programId, $sectionId, $section, $timeStart, $timeEnd, $days, $room, $instructorId)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO class_schedules
                (curriculum_subject_id, subject_id, program_id, section_id, section, time_start, time_end, days, room, instructor_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iiiisssssi", $curriculumSubjectId, $subjectId, $programId, $sectionId, $section, $timeStart, $timeEnd, $days, $room, $instructorId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateClassSchedule($id, $curriculumSubjectId, $subjectId, $programId, $sectionId, $section, $timeStart, $timeEnd, $days, $room, $instructorId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE class_schedules
             SET curriculum_subject_id = ?, subject_id = ?, program_id = ?, section_id = ?, section = ?,
                 time_start = ?, time_end = ?, days = ?, room = ?, instructor_id = ?
             WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iiiisssssii", $curriculumSubjectId, $subjectId, $programId, $sectionId, $section, $timeStart, $timeEnd, $days, $room, $instructorId, $id);
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
