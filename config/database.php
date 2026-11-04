<?php

$host = "localhost";
$user = "root";
$password = "1234";
$database = "student_management";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$sectionsTableCheck = $conn->query("SHOW TABLES LIKE 'sections'");
if (!$sectionsTableCheck) {
    die("Unable to check section maintenance schema: " . $conn->error);
}
if ($sectionsTableCheck->num_rows === 0) {
    $sectionsTableCreated = $conn->query(
        "CREATE TABLE `sections` (
            `section_id` INT NOT NULL AUTO_INCREMENT,
            `section_name` VARCHAR(50) NOT NULL,
            `program_id` INT NOT NULL,
            PRIMARY KEY (`section_id`),
            UNIQUE KEY `uq_sections_name_program_year` (`section_name`, `program_id`, `year_level`),
            KEY `idx_sections_program_id` (`program_id`),
            CONSTRAINT `fk_sections_program`
                FOREIGN KEY (`program_id`) REFERENCES `program` (`id`)
                ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    if (!$sectionsTableCreated) {
        die("Unable to initialize section maintenance: " . $conn->error);
    }
}

$sectionYearLevelCheck = $conn->query("SHOW COLUMNS FROM `sections` LIKE 'year_level'");
if (!$sectionYearLevelCheck) {
    die("Unable to check section year level schema: " . $conn->error);
}
if ($sectionYearLevelCheck->num_rows === 0) {
    if (!$conn->query("ALTER TABLE `sections` ADD COLUMN `year_level` VARCHAR(20) NOT NULL DEFAULT '1' AFTER `section_name`")) {
        die("Unable to add section year level: " . $conn->error);
    }
}

$oldSectionUniqueIndex = $conn->query(
    "SHOW INDEX FROM `sections` WHERE Key_name = 'uq_sections_name_program'"
);
if (!$oldSectionUniqueIndex) {
    die("Unable to check section uniqueness schema: " . $conn->error);
}
if ($oldSectionUniqueIndex->num_rows > 0) {
    if (!$conn->query("ALTER TABLE `sections` DROP INDEX `uq_sections_name_program`")) {
        die("Unable to update section uniqueness schema: " . $conn->error);
    }
}

$sectionYearUniqueIndex = $conn->query(
    "SHOW INDEX FROM `sections` WHERE Key_name = 'uq_sections_name_program_year'"
);
if (!$sectionYearUniqueIndex) {
    die("Unable to check section year uniqueness schema: " . $conn->error);
}
if ($sectionYearUniqueIndex->num_rows === 0) {
    if (!$conn->query(
        "ALTER TABLE `sections`
         ADD UNIQUE KEY `uq_sections_name_program_year` (`section_name`, `program_id`, `year_level`)"
    )) {
        die("Unable to add section and year uniqueness constraint: " . $conn->error);
    }
}

$schemaChecks = [
    ["information", "Student_Number", "VARCHAR(20)"],
    ["information", "Course", "VARCHAR(255)"],
    ["information", "School_Year", "VARCHAR(255)"],
    ["information", "Email", "VARCHAR(255)"],
    ["program", "CourseCode", "VARCHAR(45)"],
    ["program", "CourseName", "VARCHAR(255)"],
];

foreach ($schemaChecks as [$table, $column, $expectedType]) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");

    if ($check && $check->num_rows > 0) {
        $columnInfo = $check->fetch_assoc();
        $actualType = strtoupper($columnInfo['Type']);

        if (strpos($actualType, str_replace(" ", "", strtoupper($expectedType))) === false) {
            $conn->query("ALTER TABLE `$table` MODIFY `$column` $expectedType NOT NULL");
        }
    }
}

$studentLinkCheck = $conn->query("SHOW COLUMNS FROM `user` LIKE 'student_id'");
if ($studentLinkCheck && $studentLinkCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `user` ADD COLUMN `student_id` INT NULL UNIQUE");
}

$classScheduleProgramCheck = $conn->query("SHOW COLUMNS FROM `class_schedules` LIKE 'program_id'");
if ($classScheduleProgramCheck && $classScheduleProgramCheck->num_rows === 0) {
    if ($conn->query("ALTER TABLE `class_schedules` ADD COLUMN `program_id` INT NULL")) {
        $conn->query(
            "UPDATE class_schedules cs
             INNER JOIN (
                 SELECT subject_id, MIN(program_id) AS program_id
                 FROM curriculum_subjects
                 GROUP BY subject_id
                 HAVING COUNT(DISTINCT program_id) = 1
             ) curriculum ON curriculum.subject_id = cs.subject_id
             SET cs.program_id = curriculum.program_id"
        );
    }
}

$curriculumSubjectColumns = [
    ['year_level', 'VARCHAR(20) NULL'],
    ['semester_id', 'INT NULL'],
];
foreach ($curriculumSubjectColumns as [$column, $definition]) {
    $columnCheck = $conn->query("SHOW COLUMNS FROM `curriculum_subjects` LIKE '$column'");
    if ($columnCheck && $columnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE `curriculum_subjects` ADD COLUMN `$column` $definition");
    }
}

$scheduleColumns = [
    ['curriculum_subject_id', 'INT NULL'],
    ['section', 'VARCHAR(50) NULL'],
];
$curriculumScheduleColumnAdded = false;
foreach ($scheduleColumns as [$column, $definition]) {
    $columnCheck = $conn->query("SHOW COLUMNS FROM `class_schedules` LIKE '$column'");
    if ($columnCheck && $columnCheck->num_rows === 0) {
        if ($conn->query("ALTER TABLE `class_schedules` ADD COLUMN `$column` $definition") && $column === 'curriculum_subject_id') {
            $curriculumScheduleColumnAdded = true;
        }
    }
}
if ($curriculumScheduleColumnAdded) {
    $conn->query(
        "UPDATE class_schedules cs
         INNER JOIN (
             SELECT subject_id, program_id, MIN(id) AS curriculum_subject_id
             FROM curriculum_subjects
             GROUP BY subject_id, program_id
             HAVING COUNT(*) = 1
         ) curriculum
           ON curriculum.subject_id = cs.subject_id
          AND curriculum.program_id = cs.program_id
         SET cs.curriculum_subject_id = curriculum.curriculum_subject_id
         WHERE cs.curriculum_subject_id IS NULL"
    );
}

$scheduleSectionColumnCheck = $conn->query("SHOW COLUMNS FROM `class_schedules` LIKE 'section_id'");
if (!$scheduleSectionColumnCheck) {
    die("Unable to check class schedule section schema: " . $conn->error);
}
if ($scheduleSectionColumnCheck->num_rows === 0) {
    if (!$conn->query("ALTER TABLE `class_schedules` ADD COLUMN `section_id` INT NULL")) {
        die("Unable to add class schedule section column: " . $conn->error);
    }

    $conn->query(
        "UPDATE class_schedules cs
         INNER JOIN curriculum_subjects csub ON csub.id = cs.curriculum_subject_id
         INNER JOIN (
             SELECT section_name, program_id, year_level, MIN(section_id) AS section_id
             FROM sections
             GROUP BY section_name, program_id, year_level
             HAVING COUNT(*) = 1
         ) sec
           ON sec.section_name = cs.section
          AND sec.program_id = csub.program_id
          AND sec.year_level = csub.year_level
         SET cs.section_id = sec.section_id
         WHERE cs.section_id IS NULL"
    );
}

$scheduleSectionForeignKey = $conn->query(
    "SELECT CONSTRAINT_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'class_schedules'
       AND CONSTRAINT_NAME = 'fk_class_schedules_section'
     LIMIT 1"
);
if (!$scheduleSectionForeignKey) {
    die("Unable to check class schedule section relationship: " . $conn->error);
}
if ($scheduleSectionForeignKey->num_rows === 0) {
    if (!$conn->query(
        "ALTER TABLE `class_schedules`
         ADD CONSTRAINT `fk_class_schedules_section`
         FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`)
         ON UPDATE CASCADE ON DELETE RESTRICT"
    )) {
        die("Unable to add class schedule section relationship: " . $conn->error);
    }
}

$studentProfileColumns = [
    ['Date_Of_Birth', 'DATE NULL'],
    ['Gender', 'VARCHAR(20) NULL'],
    ['Civil_Status', 'VARCHAR(50) NULL'],
    ['House_Unit_No', 'VARCHAR(100) NULL'],
    ['Street', 'VARCHAR(255) NULL'],
    ['Barangay', 'VARCHAR(255) NULL'],
    ['City_Municipality', 'VARCHAR(255) NULL'],
    ['Province', 'VARCHAR(255) NULL'],
    ['Contact_Number', 'VARCHAR(30) NULL'],
    ['Nationality', 'VARCHAR(100) NULL'],
    ['Year_Level', 'VARCHAR(50) NULL'],
    ['Semester', 'VARCHAR(100) NULL'],
    ['Department', 'VARCHAR(255) NULL'],
    ['Current_GPA', 'VARCHAR(20) NULL'],
    ['Emergency_First_Name', 'VARCHAR(100) NULL'],
    ['Emergency_Middle_Name', 'VARCHAR(100) NULL'],
    ['Emergency_Last_Name', 'VARCHAR(100) NULL'],
    ['Emergency_Contact_Number', 'VARCHAR(30) NULL'],
    ['Emergency_House_Unit_No', 'VARCHAR(100) NULL'],
    ['Emergency_Street', 'VARCHAR(255) NULL'],
    ['Emergency_Barangay', 'VARCHAR(255) NULL'],
    ['Emergency_City_Municipality', 'VARCHAR(255) NULL'],
    ['Emergency_Province', 'VARCHAR(255) NULL'],
    ['Emergency_Email', 'VARCHAR(255) NULL'],
    ['Semester_ID', 'INT NULL'],
    ['Section_ID', 'INT NULL'],
];

$semesterIdColumnAdded = false;
foreach ($studentProfileColumns as [$column, $definition]) {
    $columnCheck = $conn->query("SHOW COLUMNS FROM `information` LIKE '$column'");
    if ($columnCheck && $columnCheck->num_rows === 0) {
        if ($conn->query("ALTER TABLE `information` ADD COLUMN `$column` $definition") && $column === 'Semester_ID') {
            $semesterIdColumnAdded = true;
        }
    }
}

$studentSectionForeignKey = $conn->query(
    "SELECT CONSTRAINT_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'information'
       AND CONSTRAINT_NAME = 'fk_information_section'
     LIMIT 1"
);
if (!$studentSectionForeignKey) {
    die("Unable to check student section relationship: " . $conn->error);
}
if ($studentSectionForeignKey->num_rows === 0) {
    if (!$conn->query(
        "ALTER TABLE `information`
         ADD CONSTRAINT `fk_information_section`
         FOREIGN KEY (`Section_ID`) REFERENCES `sections` (`section_id`)
         ON UPDATE CASCADE ON DELETE RESTRICT"
    )) {
        die("Unable to add student section relationship: " . $conn->error);
    }
}

if ($semesterIdColumnAdded) {
    $conn->query(
        "UPDATE information info
         INNER JOIN (
             SELECT semester, MIN(ID) AS ID
             FROM semester
             GROUP BY semester
             HAVING COUNT(*) = 1
         ) sem ON sem.semester = info.Semester
         SET info.Semester_ID = sem.ID
         WHERE info.Semester_ID IS NULL AND info.Semester <> ''"
    );
}

$studentRoleCheck = $conn->query("SELECT id FROM `role` WHERE LOWER(rolename) = 'student' LIMIT 1");
if ($studentRoleCheck && $studentRoleCheck->num_rows === 0) {
    $studentRoleInsert = $conn->prepare("INSERT INTO `role` (rolename) VALUES (?)");
    if ($studentRoleInsert) {
        $studentRoleName = "Student";
        $studentRoleInsert->bind_param("s", $studentRoleName);
        $studentRoleInsert->execute();
        $studentRoleInsert->close();
    }
}

?>