<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$currency = $_POST['currency'] ?? '';
$address = trim($_POST['address'] ?? '');
$amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);

if (empty($currency) || empty($address) || $amount <= 0) {
    send_json_response(400, ['status' => 'error', 'message' => 'Please fill in all fields with valid values.']);
}

// --- MOCK DATABASE INTERACTION ---
// In a real application, you would:
// 1. Check if the user has a sufficient balance for the requested currency.
// 2. Insert a new record into the `transactions` table with a status of 'requires_approval'.
// 3. Send a notification to the admin.
// For now, we just simulate success.
// --- END MOCK ---

// A small delay to simulate processing
sleep(1);

send_json_response(200, [
    'status' => 'success',
    'message' => 'Your withdrawal request has been submitted and is now pending approval.'
]);
?>
