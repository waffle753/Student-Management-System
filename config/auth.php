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