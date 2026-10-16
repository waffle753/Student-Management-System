    <?php
    require_once __DIR__ . "/../../config/auth.php";
    requireStaffLogin();

include_once __DIR__ . "/../../models/user.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Request&message=Please+use+the+user+form.");
    exit;
}

$user = new User();

$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$firstname = trim($_POST['firstname'] ?? '');
$lastname  = trim($_POST['lastname'] ?? '');
$role_id   = (int) ($_POST['role_id'] ?? 0);
$student_id = (int) ($_POST['student_id'] ?? 0);

if ($username === '' || $password === '' || $confirmPassword === '' || $firstname === '' || $lastname === '' || $role_id <= 0) {
    header("Location: ../../index.php?page=user&status=error&title=Required+Field&message=All+user+fields+are+required.");
    exit;
}

if ($password !== $confirmPassword) {
    header("Location: ../../index.php?page=user&status=error&title=Password+Mismatch&message=Password+and+confirm+password+must+match.");
    exit;
}

if (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'.-]+$/u', $firstname) || !preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'.-]+$/u', $lastname)) {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Name&message=First+and+last+names+must+contain+letters+only.");
    exit;
}

if ($user->userExists($username)) {
    header("Location: ../../index.php?page=user&status=error&title=Duplicate+Username&message=This+username+already+exists.");
    exit;
}

$role = $user->getRoleById($role_id);
if (!$role) {
    header("Location: ../../index.php?page=user&status=error&title=Invalid+Role&message=Select+a+valid+role.");
    exit;
}

if (strtolower($role['rolename']) === 'student') {
    if ($student_id <= 0 || !$user->studentIdAvailable($student_id)) {
        header("Location: ../../index.php?page=user&status=error&title=Student+Required&message=Select+an+available+student+profile+for+this+account.");
        exit;
    }
} else {
    $student_id = null;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

if ($user->addUser($username, $hashedPassword, $firstname, $lastname, $role_id, $student_id)) {
    header("Location: ../../index.php?page=user&status=success&title=Success&message=User+created+successfully.");
    exit;
}

header("Location: ../../index.php?page=user&status=error&title=Save+Failed&message=The+user+could+not+be+created.");
exit;