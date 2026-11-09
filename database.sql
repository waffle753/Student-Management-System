-- Import this file with phpMyAdmin or the MySQL command line.
-- It creates the empty schema expected by config/database.php.

CREATE DATABASE IF NOT EXISTS `student_management`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `student_management`;

CREATE TABLE IF NOT EXISTS `program` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `CourseCode` VARCHAR(45) NOT NULL,
    `CourseName` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_program_course_code` (`CourseCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `departments` (
    `department_id` INT NOT NULL AUTO_INCREMENT,
    `department_code` VARCHAR(45) NOT NULL,
    `department_name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`department_id`),
    UNIQUE KEY `uq_departments_code` (`department_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `rolename` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_role_rolename` (`rolename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_key` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`role_id`, `permission_key`),
    CONSTRAINT `fk_role_permissions_role`
        FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `school_year` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `SchoolYear` VARCHAR(255) NOT NULL,
    `Active` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_school_year_name` (`SchoolYear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `semester` (
    `ID` INT NOT NULL AUTO_INCREMENT,
    `semester_id` VARCHAR(50) NOT NULL,
    `semester` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uq_semester_code` (`semester_id`),
    UNIQUE KEY `uq_semester_name` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subject` (
    `ID` INT NOT NULL AUTO_INCREMENT,
    `SubjectCode` VARCHAR(45) NOT NULL,
    `SubjectName` VARCHAR(255) NOT NULL,
    `Unit` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uq_subject_code` (`SubjectCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `instructor` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `Employee_ID` INT UNSIGNED NOT NULL,
    `firstname` VARCHAR(100) NOT NULL,
    `lastname` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_instructor_employee_id` (`Employee_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sections` (
    `section_id` INT NOT NULL AUTO_INCREMENT,
    `section_name` VARCHAR(50) NOT NULL,
    `year_level` VARCHAR(20) NOT NULL,
    `program_id` INT NOT NULL,
    PRIMARY KEY (`section_id`),
    UNIQUE KEY `uq_sections_name_program_year` (`section_name`, `program_id`, `year_level`),
    KEY `idx_sections_program_id` (`program_id`),
    CONSTRAINT `fk_sections_program`
        FOREIGN KEY (`program_id`) REFERENCES `program` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `information` (
    `ID` INT NOT NULL AUTO_INCREMENT,
    `Student_Number` VARCHAR(20) NOT NULL,
    `First_Name` VARCHAR(100) NOT NULL,
    `Middle_Name` VARCHAR(100) NOT NULL DEFAULT '',
    `Last_Name` VARCHAR(100) NOT NULL,
    `Course` VARCHAR(255) NOT NULL DEFAULT '',
    `School_Year` VARCHAR(255) NOT NULL DEFAULT '',
    `Semester` VARCHAR(100) NOT NULL DEFAULT '',
    `Semester_ID` INT NULL,
    `Section_ID` INT NULL,
    `Email` VARCHAR(255) NOT NULL DEFAULT '',
    `Date_Of_Birth` DATE NULL,
    `Gender` VARCHAR(20) NULL,
    `Civil_Status` VARCHAR(50) NULL,
    `House_Unit_No` VARCHAR(100) NULL,
    `Street` VARCHAR(255) NULL,
    `Barangay` VARCHAR(255) NULL,
    `City_Municipality` VARCHAR(255) NULL,
    `Province` VARCHAR(255) NULL,
    `Contact_Number` VARCHAR(30) NULL,
    `Nationality` VARCHAR(100) NULL,
    `Year_Level` VARCHAR(50) NULL,
    `Department` VARCHAR(255) NULL,
    `Current_GPA` VARCHAR(20) NULL,
    `Emergency_First_Name` VARCHAR(100) NULL,
    `Emergency_Middle_Name` VARCHAR(100) NULL,
    `Emergency_Last_Name` VARCHAR(100) NULL,
    `Emergency_Contact_Number` VARCHAR(30) NULL,
    `Emergency_House_Unit_No` VARCHAR(100) NULL,
    `Emergency_Street` VARCHAR(255) NULL,
    `Emergency_Barangay` VARCHAR(255) NULL,
    `Emergency_City_Municipality` VARCHAR(255) NULL,
    `Emergency_Province` VARCHAR(255) NULL,
    `Emergency_Email` VARCHAR(255) NULL,
    `Active` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `uq_information_student_number` (`Student_Number`),
    KEY `idx_information_semester_id` (`Semester_ID`),
    KEY `idx_information_section_id` (`Section_ID`),
    CONSTRAINT `fk_information_section`
        FOREIGN KEY (`Section_ID`) REFERENCES `sections` (`section_id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `firstname` VARCHAR(100) NOT NULL,
    `lastname` VARCHAR(100) NOT NULL,
    `role_id` INT NOT NULL,
    `student_id` INT NULL,
    `instructor_id` INT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_username` (`username`),
    UNIQUE KEY `uq_user_student_id` (`student_id`),
    UNIQUE KEY `uq_user_instructor_id` (`instructor_id`),
    KEY `idx_user_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `curriculum_subjects` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `subject_id` INT NOT NULL,
    `program_id` INT NOT NULL,
    `year_level` VARCHAR(20) NOT NULL,
    `semester_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_curriculum_entry` (`subject_id`, `program_id`, `year_level`, `semester_id`),
    KEY `idx_curriculum_program_year_semester` (`program_id`, `year_level`, `semester_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `class_schedules` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `curriculum_subject_id` INT NULL,
    `subject_id` INT NULL,
    `program_id` INT NULL,
    `section_id` INT NULL,
    `section` VARCHAR(50) NULL,
    `time_start` TIME NOT NULL,
    `time_end` TIME NOT NULL,
    `days` VARCHAR(50) NOT NULL,
    `room` VARCHAR(100) NOT NULL,
    `instructor_id` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_class_schedules_curriculum_subject_id` (`curriculum_subject_id`),
    KEY `idx_class_schedules_section_id` (`section_id`),
    KEY `idx_class_schedules_instructor_id` (`instructor_id`),
    CONSTRAINT `fk_class_schedules_section`
        FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `role` (`rolename`) VALUES ('Admin'), ('Student');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_key`)
SELECT `id`, permission_key
FROM `role`
CROSS JOIN (
    SELECT 'students' AS permission_key
    UNION ALL SELECT 'users'
    UNION ALL SELECT 'instructors'
    UNION ALL SELECT 'programs'
    UNION ALL SELECT 'departments'
    UNION ALL SELECT 'sections'
    UNION ALL SELECT 'roles'
    UNION ALL SELECT 'school_years'
    UNION ALL SELECT 'semesters'
    UNION ALL SELECT 'subjects'
    UNION ALL SELECT 'curriculum_subjects'
    UNION ALL SELECT 'class_schedules'
) permissions
WHERE `rolename` IN ('Admin', 'Student');
