<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Simple router for admin panel
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Admin header
include 'templates/header.php';

// Page content
switch ($page) {
    case 'dashboard':
        include 'pages/dashboard.php';
        break;
    case 'users':
        include 'pages/users.php';
        break;
    case 'loans':
        include 'pages/loans.php';
        break;
    case 'withdrawals':
        include 'pages/withdrawals.php';
        break;
    case 'settings':
        include 'pages/settings.php';
        break;
    default:
        include 'pages/dashboard.php';
        break;
}

// Admin footer
include 'templates/footer.php';
?>
