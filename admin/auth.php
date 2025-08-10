<?php
session_start();
require_once '../app_config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    handle_login($pdo);
} elseif ($action === 'logout') {
    handle_logout();
} else {
    header('Location: login.php');
    exit;
}

function handle_login($pdo) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Username and password are required.';
        header('Location: login.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            unset($_SESSION['login_error']);
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: login.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Admin login failed: " . $e->getMessage());
        $_SESSION['login_error'] = 'An error occurred. Please try again.';
        header('Location: login.php');
        exit;
    }
}

function handle_logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
