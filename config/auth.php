<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function isAuthenticated()
{
    return isset($_SESSION['user_id'], $_SESSION['role_name']);
}

function isStudentUser()
{
    return isAuthenticated() && strtolower($_SESSION['role_name']) === 'student';
}

function requireStudentLogin()
{
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }

    if (!isStudentUser() || (int) ($_SESSION['student_id'] ?? 0) <= 0) {
        header('Location: index.php');
        exit;
    }
}

function getApplicationBasePath()
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $controllerPosition = strpos($scriptName, '/Controller/');
    if ($controllerPosition !== false) {
        return substr($scriptName, 0, $controllerPosition);
    }

    $viewPosition = strpos($scriptName, '/view/');
    if ($viewPosition !== false) {
        return substr($scriptName, 0, $viewPosition);
    }

    return rtrim(dirname($scriptName), '/\\');
}

function requireStaffLogin()
{
    $basePath = getApplicationBasePath();
    if (!isAuthenticated()) {
        header('Location: ' . $basePath . '/login.php');
        exit;
    }

    if (isStudentUser()) {
        header('Location: ' . $basePath . '/student.php');
        exit;
    }

    $scriptName = strtolower(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('~/controller/([^/]+)/~', $scriptName, $matches)) {
        $controllerPermissions = [
            'classschedule' => 'class_schedules',
            'curriculumsubject' => 'curriculum_subjects',
            'department' => 'departments',
            'instructor' => 'instructors',
            'programs' => 'programs',
            'role' => 'roles',
            'schoolyear' => 'school_years',
            'semester' => 'semesters',
            'section' => 'sections',
            'subject' => 'subjects',
            'student' => 'students',
            'user' => 'users',
        ];
        $controller = str_replace(['_', '-'], '', $matches[1]);
        $permission = $controllerPermissions[$controller] ?? null;
        if ($permission !== null) {
            requireRolePermission($permission);
        }
    } elseif (preg_match('~/view/([^/]+)\.php$~', $scriptName, $matches)) {
        $viewPermissions = [
            'class_schedule' => 'class_schedules',
            'curriculum_subject' => 'curriculum_subjects',
            'department' => 'departments',
            'instructor' => 'instructors',
            'programs' => 'programs',
            'role' => 'roles',
            'school_year' => 'school_years',
            'semester' => 'semesters',
            'section' => 'sections',
            'subject' => 'subjects',
            'user' => 'users',
        ];
        $permission = $viewPermissions[$matches[1]] ?? null;
        if ($permission !== null) {
            requireRolePermission($permission);
        }
    } elseif (in_array(basename($scriptName), ['create.php', 'update.php', 'delete.php'], true)) {
        requireRolePermission('students');
    }
}

function getRolePermissions()
{
    static $permissionsByRole = null;
    if ($permissionsByRole !== null) {
        return $permissionsByRole;
    }

    require_once __DIR__ . '/database.php';
    global $conn;
    $roleId = (int) ($_SESSION['role_id'] ?? 0);
    $stmt = $conn->prepare(
        "SELECT permission_key FROM role_permissions WHERE role_id = ?"
    );
    if (!$stmt) {
        die("Unable to load role permissions: " . $conn->error);
    }
    $stmt->bind_param("i", $roleId);
    if (!$stmt->execute()) {
        die("Unable to load role permissions: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $permissionsByRole = [];
    while ($row = $result->fetch_assoc()) {
        $permissionsByRole[] = $row['permission_key'];
    }
    $stmt->close();

    return $permissionsByRole;
}

function hasRolePermission($permissionKey)
{
    return in_array($permissionKey, getRolePermissions(), true);
}

function requireRolePermission($permissionKey)
{
    if (in_array($permissionKey, ['teacher', 'teacher_students', 'teacher_classes', 'teacher_grades'], true)
        && strtolower(trim((string) ($_SESSION['role_name'] ?? ''))) === 'teacher') {
        return;
    }

    $pagePermissions = [
        'teacher' => 'students',
        'students' => 'students',
        'user' => 'users',
        'instructor' => 'instructors',
        'programs' => 'programs',
        'department' => 'departments',
        'section' => 'sections',
        'role' => 'roles',
        'schoolyear' => 'school_years',
        'semester' => 'semesters',
        'subject' => 'subjects',
        'curriculum_subject' => 'curriculum_subjects',
        'class_schedule' => 'class_schedules',
    ];
    $permission = $pagePermissions[$permissionKey] ?? $permissionKey;
    if (hasRolePermission($permission)) {
        return;
    }

    http_response_code(403);
    exit('You do not have permission to access this area.');
}

function getCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function isValidCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}