<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Telegram Loan Platform</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.0">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --bg-color: #18222d;
            --text-color: #ffffff;
            --hint-color: #b1c3d5;
            --link-color: #62bcf9;
            --button-color: #3e9de6;
            --button-text-color: #ffffff;
            --secondary-bg-color: #222e3a;
            --header-bg-color: #1f2a37;
            --accent-color: #52a5e4;
            --destructive-color: #e53935;
            --success-color: #4caf50;
        }
    </style>
</head>
<body data-theme="dark">
    <div class="app-container">
        <header class="app-header">
            <h1>Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h1>
        </header>
        <main class="app-content">
