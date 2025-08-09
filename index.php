<?php
require_once 'config/config.php';
require_once 'api/auth.php';

// Authenticate user
$user = handleTelegramAuth();

// Simple router
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Header
include 'templates/header.php';

// Page content
switch ($page) {
    case 'dashboard':
        include 'templates/dashboard.php';
        break;
    case 'profile':
        include 'templates/profile.php';
        break;
    case 'wallet':
        include 'templates/wallet.php';
        break;
    case 'loans':
        include 'templates/loans.php';
        break;
    case 'savings':
        include 'templates/savings.php';
        break;
    default:
        include 'templates/dashboard.php';
        break;
}

// Footer
include 'templates/footer.php';
?>
