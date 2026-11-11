<?php
$manualRole = strtolower(trim((string) ($_SESSION['role_name'] ?? '')));
$manualTitle = 'System User Manual';
$manualSections = [];

if ($manualRole === 'student') {
    $manualTitle = 'Student Portal User Manual';
    $manualSections = [
        [
            'title' => 'Dashboard',
            'purpose' => 'See your current academic placement and a summary of your study load and posted grades.',
            'fields' => [
                ['Current Subjects', 'Number of subjects scheduled for your current program, year, semester, and section.'],
                ['Grades Posted', 'Number of grades posted for your student record.'],
                ['Grades Pending', 'Number of current scheduled subjects that do not yet have a posted grade.'],
                ['Academic Placement', 'Your program, year level, section, semester, and academic year.'],
                ['Student Services', 'Shortcuts to your study load, grades, and personal information.'],
            ],
            'workflow' => [
                'Open Dashboard from the Student sidebar after signing in.',
                'Review your academic placement and current subject and grade counts.',
                'Choose a student service to open its details.',
            ],
            'table' => [],
        ],
        [
            'title' => 'Personal Information',
            'purpose' => 'Review your student record and update the personal and emergency-contact details that the portal allows you to edit.',
            'fields' => [
                ['Student Number', 'Identifies your student record; read-only.'],
                ['First Name, Middle Name, Last Name', 'Your registered name; read-only in the portal.'],
                ['Date of birth', 'Your date of birth; used to calculate age.'],
                ['Age', 'Calculated automatically from your date of birth; read-only.'],
                ['Gender', 'Select your gender; required.'],
                ['Civil status', 'Select your current civil status; optional.'],
                ['House / unit no., Street, Barangay, City / municipality, Province', 'Your residential address.'],
                ['Contact number', 'A phone number where you can be reached.'],
                ['Email address', 'A valid email address where you can be reached.'],
                ['Nationality', 'Choose your nationality from the list.'],
                ['Program / course', 'Your academic program; read-only.'],
                ['Year level, Section, Academic year, Semester, Department', 'Your current academic placement; maintained by school staff and read-only in this portal.'],
                ['Current GPA / grade', 'The grade summary field currently attached to your student record.'],
                ['Emergency first/middle/last name', 'Name of the person to contact in an emergency.'],
                ['Emergency contact number and email', 'Contact details for the emergency contact.'],
                ['Emergency house/unit no., street, barangay, city/municipality, province', 'Address details for the emergency contact.'],
                ['Save All Information', 'Validate and save the editable profile fields. Empty optional emergency fields clear the saved emergency contact details.'],
            ],
            'workflow' => [
                'Open Personal Information from the Student sidebar.',
                'Review the read-only student and academic information.',
                'Update editable personal or emergency contact fields.',
                'Select Save All Information and wait for the confirmation message.',
            ],
            'table' => [],
        ],
        [
            'title' => 'View Study Load',
            'purpose' => 'See the subjects and schedule associated with your current program, year, semester, and section.',
            'fields' => [
                ['Student ID, Course, Year, Semester, Section, Department', 'Confirm which student and academic placement the displayed schedule belongs to.'],
                ['Subject Code and Subject', 'Identify each course in your study load.'],
                ['Units and Total Units', 'Show each subject’s units and the total units in the displayed load.'],
                ['Section', 'Shows the section assigned to the scheduled subject.'],
                ['Time, Days, Room', 'Show when and where the class meets. TBA means no value has been assigned.'],
                ['Instructor', 'Shows the assigned instructor, or TBA if one has not been assigned.'],
                ['Print', 'Opens the browser print dialog for your study load.'],
            ],
            'workflow' => [
                'Choose View Study Load in the sidebar.',
                'Check the academic placement summary above the table.',
                'Use the subject rows to review units, meeting times, room, and instructor. On a narrow screen, scroll the table horizontally.',
                'Choose Print to print or save a copy of the study load.',
            ],
            'table' => ['Subject Code', 'Subject', 'Units', 'Section', 'Time', 'Days', 'Room', 'Instructor'],
        ],
        [
            'title' => 'View Grades',
            'purpose' => 'Review grades that instructors have saved for your subjects.',
            'fields' => [
                ['Subject', 'A subject on your current study load or with a previously posted grade.'],
                ['Grade', 'The posted grade on the school’s 1.00–5.00 scale. A blank cell means the instructor has not posted a grade yet. A grade of 3.00 or lower is passing.'],
                ['Average Grade', 'The average is shown only after every listed subject has a posted grade.'],
                ['No grades message', 'Appears when there are no scheduled subjects or posted grades to display.'],
            ],
            'workflow' => [
                'Choose View Grades in the sidebar.',
                'Review the Subject and Grade columns.',
                'If a subject is missing, contact the instructor or Registrar to check whether the grade has been posted.',
            ],
            'table' => ['Subject', 'Grade'],
        ],
    ];
} elseif ($manualRole === 'teacher') {
    $manualTitle = 'Teacher User Manual';
    $manualSections = [
        [
            'title' => 'Dashboard',
            'purpose' => 'Get a quick count of assigned students, classes, and classes scheduled for today.',
            'fields' => [
                ['Students', 'Count of students associated with your assigned classes.'],
                ['My Classes', 'Count of class schedules assigned to you.'],
                ["Today's Classes", 'Count of your schedules that match today’s day.'],
                ['Work Areas', 'Shortcuts to My Students, My Classes, and Grades.'],
            ],
            'workflow' => [
                'Review the summary boxes for your assigned students, classes, and today’s classes.',
                'Choose a Work Areas shortcut or use the sidebar to open My Students, My Classes, or Grades.',
            ],
            'table' => [],
        ],
        [
            'title' => 'My Student',
            'purpose' => 'Find students assigned to your classes and review their basic academic information.',
            'fields' => [
                ['Department, Program, Section, Year filters', 'Narrow the student list. Choose All to remove a filter.'],
                ['Filter', 'Apply the selected filters.'],
                ['Reset', 'Clear all selected filters and show all assigned students.'],
                ['Global Search', 'Search all visible student columns.'],
                ['Rows per page and pagination', 'Choose how many students to show on each page and move between result pages.'],
                ['Export CSV', 'Download the currently filtered student rows as a CSV file.'],
                ['Action / View', 'Open the student information dialog.'],
            ],
            'workflow' => [
                'Open My Student.',
                'Optionally select department, program, section, and year, then choose Filter.',
                'Use View to inspect a student or Export CSV to download the filtered list.',
            ],
            'table' => ['Student ID', 'First Name', 'Middle Name', 'Last Name', 'Program', 'Section', 'Year', 'Action'],
        ],
        [
            'title' => 'My Classes',
            'purpose' => 'Review your assigned course schedules and print the list when needed.',
            'fields' => [
                ['Subject Code and Subject', 'Identify the scheduled course.'],
                ['Program, Year, Semester, Section', 'Identify the academic group taking the course.'],
                ['Time and Days', 'Show when the class meets.'],
                ['Room', 'Show the assigned classroom.'],
                ['Print', 'Open the browser print dialog for the class schedule.'],
            ],
            'workflow' => ['Open My Classes, review the schedule table, and choose Print if you need a paper copy.'],
            'table' => ['Subject Code', 'Subject', 'Program', 'Year', 'Semester', 'Section', 'Time', 'Days', 'Room'],
        ],
        [
            'title' => 'Grades',
            'purpose' => 'Select an assigned subject and section, enter grades for its students, and save them for students to view.',
            'fields' => [
                ['Class card', 'Shows the subject, program, year level, and section. Select it to open the roster.'],
                ['Student ID and Student Name', 'Identify the student whose grade you are entering.'],
                ['Grade (1.00–5.00)', 'Enter a number from 1.00 through 5.00, up to two decimal places. A grade of 3.00 or lower is passing.'],
                ['Save grades', 'Validate and store the entered grades. Saved grades remain in the inputs when you reopen the class.'],
            ],
            'workflow' => [
                'Open Grades and select a class card.',
                'Enter or update each student’s grade. Leave a blank input unchanged if you do not want to save a grade for that student.',
                'Choose Save grades and confirm the success alert.',
                'Students can see posted grades in their Student Portal under View Grades.',
            ],
            'table' => ['Student ID', 'Student Name', 'Grade'],
        ],
    ];
} else {
    $roleDisplayName = trim((string) ($_SESSION['role_name'] ?? 'User'));
    $isAdministratorRole = strpos($manualRole, 'admin') !== false;
    $manualTitle = $manualRole === 'registrar'
        ? 'Registrar User Manual'
        : ($isAdministratorRole
            ? 'Administrator User Manual'
            : $roleDisplayName . ' User Manual');
    if ($isAdministratorRole || $manualRole === 'registrar') {
        $manualSections[] = [
            'title' => 'Dashboard',
            'purpose' => 'Review high-level student and academic setup counts, then open an available work area.',
            'fields' => [
                ['Active Students', 'Number of student records marked active in the system.'],
                ['Programs, Sections, Class Schedules', 'Counts of the corresponding academic setup records.'],
                ['Work Areas', 'Shortcuts appear only for pages your role is allowed to access.'],
            ],
            'workflow' => [
                'Open Dashboard from the sidebar after signing in.',
                'Review the summary counts.',
                'Choose a work area shortcut or use the sidebar to continue.',
            ],
            'table' => [],
        ];
    }
    $manualModules = [
        'students' => [
            'Students',
            'Maintain student records and academic placement.',
            [
                ['Student No.', 'Unique student identifier generated by the system.'],
                ['First Name, Middle Name, Last Name', 'Student’s registered name; first and last name are required.'],
                ['Date of Birth, Age', 'Enter the date of birth; age is calculated automatically.'],
                ['Gender, Civil Status, Nationality', 'Student’s personal profile attributes.'],
                ['House / unit no., Street, Barangay, City / municipality, Province', 'Student’s residential address.'],
                ['Contact Number, Email', 'Student contact details; email must be valid if provided.'],
                ['Program/Course, Year Level, Section', 'Academic program and cohort assignment. Select the program and year before choosing a matching section.'],
                ['School Year, Semester, Department, Current GPA / Grade', 'Academic period, organizational placement, and optional grade summary.'],
                ['Emergency first/middle/last name, contact number, address, email', 'Optional emergency contact details.'],
                ['Global Search', 'Search across the student table columns.'],
                ['Registrar filters', 'Filter the student list by department, program/course, year level, and section. Choose Filter to apply or Reset to clear them.'],
                ['Export CSV', 'Registrars can download the rows matching the current dropdown filters and global search. The export contains student and academic details, not contact or emergency information.'],
                ['Subjects and Grades actions', 'Open the student’s scheduled subjects or posted grades. Both views can be printed. Administrators can edit posted grades; Registrars can view but not edit them. Subject schedules are read-only in these views.'],
            ],
            ['Student ID', 'Name', 'Program', 'Section', 'Year', 'Actions'],
        ],
        'users' => [
            'Users',
            'Create and maintain sign-in accounts and link them to the correct role and student or instructor record.',
            [
                ['Username', 'Account name used to sign in; must be unique.'],
                ['Password', 'Credential for a new account. Use the system’s password requirements and do not share it.'],
                ['First Name, Last Name', 'Name displayed for the account.'],
                ['Role', 'Controls which pages and actions the account is allowed to access.'],
                ['Student Profile / Teacher Profile', 'Links the account to the correct student or instructor record when applicable.'],
            ],
            ['ID', 'Username', 'Name', 'Role', 'Actions'],
        ],
        'instructors' => [
            'Instructors',
            'Maintain teacher records used to assign class schedules and teacher accounts.',
            [
                ['Employee ID', 'School-issued unique employee identifier.'],
                ['First Name, Last Name', 'Instructor’s name.'],
                ['Assigned classes', 'Schedule rows associated with the instructor, including subject, program, year, section, days, and time.'],
            ],
            ['Employee ID', 'Instructor Name', 'Assigned Classes', 'Actions'],
        ],
        'programs' => [
            'Programs',
            'Maintain the academic programs referenced by students, sections, and curriculum subjects.',
            [
                ['Course Code', 'Short, unique program identifier.'],
                ['Course Name', 'Full program name.'],
            ],
            ['ID', 'Course Code', 'Course Name', 'Actions'],
        ],
        'departments' => [
            'Departments',
            'Maintain the department directory used to organize student records.',
            [
                ['Department Code', 'Short department identifier.'],
                ['Department Name', 'Full department name.'],
            ],
            ['ID', 'Department Code', 'Department Name', 'Actions'],
        ],
        'sections' => [
            'Sections',
            'Define student cohorts within a program and year level.',
            [
                ['Section Name', 'Section label, such as A or B.'],
                ['Program', 'Program this section belongs to.'],
                ['Year Level', 'Year in the program for this cohort.'],
            ],
            ['ID', 'Section Name', 'Program', 'Year Level', 'Actions'],
        ],
        'roles' => [
            'Roles',
            'Create access profiles. Permissions determine which pages each role can open; assigning a role to an account applies those permissions.',
            [
                ['Role Name', 'Name of the access profile, such as Registrar or Teacher.'],
                ['Allowed Pages checkboxes', 'Select each permission the role should receive. Leave a permission unchecked to deny that page.'],
            ],
            ['ID', 'Role Name', 'Actions'],
        ],
        'school_years' => [
            'School Years',
            'Maintain academic year records and mark the active year where needed.',
            [
                ['School Year', 'Academic year label used for student and academic records.'],
                ['Status', 'Indicates whether this school year is active.'],
            ],
            ['ID', 'School Year', 'Status', 'Actions'],
        ],
        'semesters' => [
            'Semesters',
            'Maintain semester labels used by student records and curriculum subjects.',
            [
                ['Semester ID', 'Short identifier for the semester.'],
                ['Semester', 'Display name of the semester.'],
            ],
            ['ID', 'Semester ID', 'Semester', 'Actions'],
        ],
        'subjects' => [
            'Subjects',
            'Maintain the subject catalog, including subject codes, names, and units.',
            [
                ['Subject Code', 'Unique short code used to identify the subject.'],
                ['Subject Name', 'Full subject title.'],
                ['Unit', 'Credit/unit value for the subject.'],
            ],
            ['ID', 'Subject Code', 'Subject Name', 'Unit', 'Actions'],
        ],
        'curriculum_subjects' => [
            'Curriculum Subjects',
            'Place a catalog subject into a program curriculum for a particular year level and semester.',
            [
                ['Subject', 'Catalog subject to add to the curriculum.'],
                ['Program', 'Program whose curriculum contains the subject.'],
                ['Year Level', 'Year level when the subject is normally taken.'],
                ['Semester', 'Semester when the subject is normally taken.'],
            ],
            ['ID', 'Subject', 'Program', 'Year', 'Semester', 'Actions'],
        ],
        'class_schedules' => [
            'Class Schedules',
            'Schedule curriculum subjects for sections, assign instructors, and define meeting details.',
            [
                ['Curriculum Subject', 'Subject/program/year/semester combination to schedule.'],
                ['Section', 'Cohort attending this schedule; select a section matching its program and year.'],
                ['Time Start, Time End', 'Start and end times. Avoid overlapping schedules for the same section and semester.'],
                ['Days', 'One or more days on which the class meets.'],
                ['Room', 'Classroom or location.'],
                ['Instructor', 'Teacher assigned to the schedule.'],
            ],
            ['ID', 'Subject', 'Program', 'Year Level', 'Semester', 'Section', 'Time', 'Days', 'Room', 'Instructor', 'Actions'],
        ],
    ];

    $rolePermissions = $manualRole === 'registrar'
        ? ['students', 'programs', 'departments', 'sections', 'school_years', 'semesters', 'subjects', 'curriculum_subjects', 'class_schedules']
        : getRolePermissions();
    foreach ($manualModules as $permission => [$title, $purpose, $fields, $table]) {
        if (in_array($permission, $rolePermissions, true)) {
            $manualSections[] = [
                'title' => $title,
                'purpose' => $purpose,
                'fields' => $fields,
                'workflow' => [
                    'Open ' . $title . ' from the sidebar.',
                    'Use the form to add a record or the row actions to view, edit, or delete an existing record.',
                    'Complete required fields, verify your selections, and save. Use the table to review the saved record.',
                ],
                'table' => $table,
            ];
        }
    }
}
?>

<style>
    .manual-section {
        overflow: hidden;
    }

    .manual-section > summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        list-style: none;
    }

    .manual-section > summary::-webkit-details-marker {
        display: none;
    }

    .manual-section > summary::after {
        width: .5rem;
        height: .5rem;
        margin-left: 1rem;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        content: '';
        transform: rotate(45deg);
        transition: transform 220ms ease;
        flex: 0 0 auto;
    }

    .manual-section[open] > summary::after {
        transform: rotate(225deg);
    }

    @supports (interpolate-size: allow-keywords) {
        .manual-section {
            interpolate-size: allow-keywords;
        }

        .manual-section::details-content {
            block-size: 0;
            overflow: clip;
            transition: block-size 220ms ease, content-visibility 220ms allow-discrete;
        }

        .manual-section[open]::details-content {
            block-size: auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .manual-section > summary::after,
        .manual-section::details-content {
            transition: none;
        }
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <h1 class="h3"><?= htmlspecialchars($manualTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">
            This guide reflects your <?= htmlspecialchars(ucfirst($manualRole), ENT_QUOTES, 'UTF-8') ?> role.
            It explains the purpose of available pages, their main fields and tables, and the usual workflow.
        </p>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <?php if ($manualSections === []): ?>
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">No pages are assigned to this role yet. Contact an administrator if you need access.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($manualSections as $section): ?>
                <details class="card card-outline card-primary manual-section">
                    <summary class="card-header">
                        <h2 class="card-title font-weight-bold"><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </summary>
                    <div class="card-body">
                        <p><?= htmlspecialchars($section['purpose'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php if ($section['fields'] !== []): ?>
                            <h3 class="h6 font-weight-bold">Fields and controls</h3>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr><th scope="col">Field / control</th><th scope="col">Purpose</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($section['fields'] as [$field, $description]): ?>
                                            <tr>
                                                <th scope="row"><?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?></th>
                                                <td><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <?php if ($section['table'] !== []): ?>
                            <h3 class="h6 font-weight-bold">Table columns</h3>
                            <p><?= htmlspecialchars(implode(' · ', $section['table']), ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <h3 class="h6 font-weight-bold">How to use</h3>
                        <ol class="mb-0">
                            <?php foreach ($section['workflow'] as $step): ?>
                                <li><?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </details>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="alert alert-info">
            <strong>Settings:</strong> open Settings in the sidebar to return to this manual or securely log out.
            Never share your password, and log out when you finish using a shared computer.
        </div>
    </div>
</section>
