<?php
require_once __DIR__ . '/config/auth.php';

if (isAuthenticated()) {
    header('Location: ' . (isStudentUser() ? 'student.php' : 'index.php'));
    exit;
}

$errorMessages = [
    'invalid' => 'The username or password is incorrect, or the account is not linked to an active student profile.',
    'request' => 'Your request could not be verified. Please try again.',
];
$error = $errorMessages[$_GET['error'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Student Management System</title>
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(135deg, #e9f1f5, #f8fafb 55%, #d9ebe7);
        }
        .login-box { width: min(100%, 420px); }
        .login-card { border-top: 4px solid #168a78; box-shadow: 0 18px 50px rgba(26, 58, 68, .12); }
        .login-mark { color: #168a78; font-size: 2rem; }
        .form-control { height: 46px; }
    </style>
</head>
<body>
    <main class="login-box">
        <section class="card login-card">
            <div class="card-body login-card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="fas fa-graduation-cap login-mark" aria-hidden="true"></i>
                    <h1 class="h3 mt-3 mb-1">Student Portal</h1>
                    <p class="text-muted mb-0">Sign in to manage your personal information</p>
                </div>

                <?php if ($error !== ''): ?>
                    <script>
                        Swal.fire({
                            title: 'Sign in failed',
                            text: <?= json_encode($error, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then(function () {
                            const url = new URL(window.location.href);
                            url.searchParams.delete('error');
                            window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
                        });
                    </script>
                <?php endif; ?>

                <form method="post" action="Controller/Auth/login.php" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" autocomplete="username" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block mt-4">
                        <i class="fas fa-sign-in-alt mr-1" aria-hidden="true"></i> Sign in
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>