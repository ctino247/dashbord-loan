<?php
// This is a test file for development purposes.
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../helpers/TelegramNotifier.php';
include_once '../models/User.php';

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit;
}

// Get user_id and message from query string
$user_id = $_GET['user_id'] ?? null;
$message = $_GET['message'] ?? 'This is a test message from the platform.';

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['message' => 'Please provide a user_id.']);
    exit;
}

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Get user's telegram_id
$user = new User($db);
if (!$user->get($user_id)) {
    http_response_code(404);
    echo json_encode(['message' => 'User not found.']);
    exit;
}

$telegram_id = $user->telegram_id;

// Send notification
$notifier = new TelegramNotifier($db);
if ($notifier->sendMessage($telegram_id, $message)) {
    http_response_code(200);
    echo json_encode(['message' => 'Notification sent successfully to user ' . $user_id . ' (Telegram ID: ' . $telegram_id . ').']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to send notification. Check bot token and logs.']);
}
?>
