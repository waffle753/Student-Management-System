<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/user.php';
require_once __DIR__ . '/../../models/studentquery.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    header('Location: ../../login.php?error=request');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$userModel = new User();
$user = $username !== '' ? $userModel->getUserByUsername($username) : false;

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: ../../login.php?error=invalid');
    exit;
}

$isStudent = strtolower($user['rolename'] ?? '') === 'student';
$studentId = $isStudent ? (int) ($user['student_id'] ?? 0) : 0;
if ($isStudent) {
    $studentModel = new Student();
    if ($studentId <= 0 || !$studentModel->getStudentById($studentId)) {
        header('Location: ../../login.php?error=invalid');
        exit;
    }
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role_id'] = (int) $user['role_id'];
$_SESSION['role_name'] = $user['rolename'];
$_SESSION['student_id'] = $isStudent ? $studentId : null;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

header('Location: ' . ($isStudent ? '../../student.php' : '../../index.php'));
exit;