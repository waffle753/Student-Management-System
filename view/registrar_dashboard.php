<section class="content-header">
    <div class="container-fluid">
        <h1 class="h3"><?= $isAdministratorRole ? 'Administrator Dashboard' : 'Registrar Dashboard' ?></h1>
        <p class="text-muted mb-0">Student records and academic setup at a glance.</p>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format((int) ($registrarDashboardStats['student_count'] ?? 0)) ?></h3>
                        <p>Active Students</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
                    <a href="index.php?page=students" class="small-box-footer">Open student records <i class="fas fa-arrow-circle-right" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format((int) ($registrarDashboardStats['program_count'] ?? 0)) ?></h3>
                        <p>Programs</p>
                    </div>
                    <div class="icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></div>
                    <a href="index.php?page=programs" class="small-box-footer">View programs <i class="fas fa-arrow-circle-right" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format((int) ($registrarDashboardStats['section_count'] ?? 0)) ?></h3>
                        <p>Sections</p>
                    </div>
                    <div class="icon"><i class="fas fa-users" aria-hidden="true"></i></div>
                    <a href="index.php?page=section" class="small-box-footer">View sections <i class="fas fa-arrow-circle-right" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format((int) ($registrarDashboardStats['schedule_count'] ?? 0)) ?></h3>
                        <p>Class Schedules</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></div>
                    <a href="index.php?page=class_schedule" class="small-box-footer">View schedules <i class="fas fa-arrow-circle-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        <section aria-labelledby="registrarWorkAreasHeading">
            <h2 id="registrarWorkAreasHeading" class="h5 mb-3">Work Areas</h2>
            <div class="row">
                <?php
                $registrarDashboardLinks = [
                    ['students', 'Students', 'user-graduate'],
                    ['programs', 'Programs', 'graduation-cap'],
                    ['departments', 'Departments', 'building'],
                    ['sections', 'Sections', 'users'],
                    ['subjects', 'Subjects', 'book'],
                    ['curriculum_subjects', 'Curriculum Subjects', 'book-open'],
                    ['class_schedules', 'Class Schedules', 'calendar-alt'],
                    ['school_years', 'School Years', 'calendar'],
                    ['semesters', 'Semesters', 'clock'],
                ];
                foreach ($registrarDashboardLinks as [$permission, $label, $icon]):
                    if (!hasRolePermission($permission)) {
                        continue;
                    }
                    $dashboardRoutes = [
                        'students' => 'students',
                        'programs' => 'programs',
                        'departments' => 'department',
                        'sections' => 'section',
                        'subjects' => 'subject',
                        'curriculum_subjects' => 'curriculum_subject',
                        'class_schedules' => 'class_schedule',
                        'school_years' => 'schoolyear',
                        'semesters' => 'semester',
                    ];
                    ?>
                    <div class="col-md-4 col-sm-6 mb-2">
                        <a class="btn btn-default btn-block text-left" href="index.php?page=<?= htmlspecialchars($dashboardRoutes[$permission], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fas fa-<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> mr-2 text-primary" aria-hidden="true"></i>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>