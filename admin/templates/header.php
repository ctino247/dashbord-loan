<?php
// This should be at the top of any admin page that requires login
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= ucfirst($page) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            display: flex;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        .sidebar {
            width: 220px;
            background-color: var(--secondary-bg-color);
            min-height: 100vh;
            padding: 15px;
            flex-shrink: 0;
        }
        .sidebar h3 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--bg-color);
        }
        .sidebar a {
            display: block;
            color: var(--text-color);
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: background-color 0.2s;
        }
        .sidebar a.active, .sidebar a:hover {
            background-color: var(--accent-color);
            color: var(--button-text-color);
        }
        .sidebar .logout {
            border-top: 1px solid var(--bg-color);
            padding-top: 10px;
            margin-top: 15px;
        }
        .admin-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            height: 100vh;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h3>Admin Menu</h3>
    <a href="index.php?page=dashboard" class="<?= ($page === 'dashboard') ? 'active' : '' ?>">Dashboard</a>
    <a href="index.php?page=users" class="<?= ($page === 'users') ? 'active' : '' ?>">User Management</a>
    <a href="index.php?page=loans" class="<?= ($page === 'loans') ? 'active' : '' ?>">Loan Approvals</a>
    <a href="index.php?page=withdrawals" class="<?= ($page === 'withdrawals') ? 'active' : '' ?>">Withdrawal Requests</a>
    <a href="index.php?page=settings" class="<?= ($page === 'settings') ? 'active' : '' ?>">System Settings</a>
    <div class="logout">
        <a href="auth.php?action=logout">Logout</a>
    </div>
</div>
<main class="admin-content">
    <div class="card">
        <h1><?= ucfirst($page) ?></h1>
    </div>
