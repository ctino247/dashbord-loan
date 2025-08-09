<?php
session_start();
require_once '../config/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    handle_login();
} elseif ($action === 'logout') {
    handle_logout();
} else {
    header('Location: login.php');
    exit;
}

function handle_login() {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // --- MOCK AUTHENTICATION ---
    // In a real application, you would fetch the admin user from the database.
    // $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    // $stmt->execute([$username]);
    // $admin = $stmt->fetch();
    // if ($admin && password_verify($password, $admin['password_hash'])) { ... }

    // For this mock, we'll use a hardcoded username and password.
    // The password is 'password123'. The hash should be generated securely.
    // Example hash for 'password123':
    $valid_username = 'admin';
    $valid_password_hash = '$2y$10$YwnISbmlR.5flvTmtg04guI4gECrY3s4jA7n9dJtIuFszd8l.1aB2'; // Hash for "password123"

    if ($username === $valid_username && password_verify($password, $valid_password_hash)) {
        // Prevent session fixation
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;

        // Clear any previous login errors
        unset($_SESSION['login_error']);

        header('Location: index.php');
        exit;
    } else {
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: login.php');
        exit;
    }
}

function handle_logout() {
    // Unset all of the session variables
    $_SESSION = [];

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session
    session_destroy();

    header('Location: login.php');
    exit;
}
?>
