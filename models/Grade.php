<?php

include_once __DIR__ . "/../config/database.php";

class Grade
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getGradesForStudent($studentId)
    {
        $stmt = $this->conn->prepare(
            "SELECT schedule.id AS class_schedule_id, grade_record.grade, grade_record.updated_at,
                    subject_record.SubjectCode, subject_record.SubjectName,
                    program_record.CourseName AS Program_Name,
                    curriculum.year_level AS Year_Level,
                    semester_record.semester AS Semester_Name,
                    COALESCE(section_record.section_name, schedule.section) AS Section_Name
             FROM student_grades grade_record
             INNER JOIN class_schedules schedule ON schedule.id = grade_record.class_schedule_id
             LEFT JOIN curriculum_subjects curriculum
                    ON curriculum.id = schedule.curriculum_subject_id
             LEFT JOIN subject subject_record
                    ON subject_record.ID = COALESCE(curriculum.subject_id, schedule.subject_id)
             LEFT JOIN program program_record
                    ON program_record.id = COALESCE(curriculum.program_id, schedule.program_id)
             LEFT JOIN semester semester_record ON semester_record.ID = curriculum.semester_id
             LEFT JOIN sections section_record ON section_record.section_id = schedule.section_id
             WHERE grade_record.student_id = ?
             ORDER BY semester_record.ID DESC, subject_record.SubjectCode ASC"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $studentId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $grades = $stmt->get_result();
        $stmt->close();

        return $grades;
    }

    public function updateStudentGrades($studentId, array $grades)
    {
        if ($studentId < 1 || $grades === [] || !$this->conn->begin_transaction()) {
            return false;
        }

        $checkStmt = $this->conn->prepare(
            "SELECT id FROM student_grades
             WHERE student_id = ? AND class_schedule_id = ?
             LIMIT 1 FOR UPDATE"
        );
        $updateStmt = $this->conn->prepare(
            "UPDATE student_grades
             SET grade = ?
             WHERE student_id = ? AND class_schedule_id = ?"
        );
        if (!$checkStmt || !$updateStmt) {
            if ($checkStmt) {
                $checkStmt->close();
            }
            if ($updateStmt) {
                $updateStmt->close();
            }
            $this->conn->rollback();
            return false;
        }

        foreach ($grades as $scheduleId => $gradeValue) {
            $checkStmt->bind_param("ii", $studentId, $scheduleId);
            if (!$checkStmt->execute()) {
                $checkStmt->close();
                $updateStmt->close();
                $this->conn->rollback();
                return false;
            }

            $gradeRecord = $checkStmt->get_result();
            $recordExists = $gradeRecord && $gradeRecord->num_rows === 1;
            if ($gradeRecord) {
                $gradeRecord->free();
            }
            if (!$recordExists) {
                $checkStmt->close();
                $updateStmt->close();
                $this->conn->rollback();
                return false;
            }

            $updateStmt->bind_param("dii", $gradeValue, $studentId, $scheduleId);
            if (!$updateStmt->execute()) {
                $checkStmt->close();
                $updateStmt->close();
                $this->conn->rollback();
                return false;
            }
        }

        $checkStmt->close();
        $updateStmt->close();
        if (!$this->conn->commit()) {
            $this->conn->rollback();
            return false;
        }

        return true;
    }

    public function getGradesForInstructor($instructorId)
    {
        $stmt = $this->conn->prepare(
            "SELECT grade_record.class_schedule_id, grade_record.student_id, grade_record.grade
             FROM student_grades grade_record
             INNER JOIN class_schedules schedule
                     ON schedule.id = grade_record.class_schedule_id
             WHERE schedule.instructor_id = ?"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $instructorId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[(int) $row['class_schedule_id']][(int) $row['student_id']] = (string) $row['grade'];
        }
        $stmt->close();

        return $grades;
    }

    public function saveScheduleGrades($scheduleId, $instructorId, $grades)
    {
        if (!$this->conn->begin_transaction()) {
            return false;
        }

        $scheduleStmt = $this->conn->prepare(
            "SELECT id FROM class_schedules WHERE id = ? AND instructor_id = ? LIMIT 1"
        );
        if (!$scheduleStmt) {
            $this->conn->rollback();
            return false;
        }

        $scheduleStmt->bind_param("ii", $scheduleId, $instructorId);
        if (!$scheduleStmt->execute()) {
            $scheduleStmt->close();
            $this->conn->rollback();
            return false;
        }
        $scheduleExists = $scheduleStmt->get_result()->num_rows === 1;
        $scheduleStmt->close();
        if (!$scheduleExists) {
            $this->conn->rollback();
            return false;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO student_grades (class_schedule_id, student_id, instructor_id, grade)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE instructor_id = VALUES(instructor_id), grade = VALUES(grade)"
        );
        if (!$stmt) {
            $this->conn->rollback();
            return false;
        }

        foreach ($grades as $studentId => $grade) {
            $studentId = (int) $studentId;
            $grade = (float) $grade;
            $stmt->bind_param("iidd", $scheduleId, $studentId, $instructorId, $grade);
            if (!$stmt->execute()) {
                $stmt->close();
                $this->conn->rollback();
                return false;
            }
        }

        $stmt->close();
        if (!$this->conn->commit()) {
            $this->conn->rollback();
            return false;
        }

        return true;
    }
}
