<?php
require_once __DIR__ . '/config/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    $_SESSION = [];
    session_destroy();
}

header('Location: login.php');
exit;