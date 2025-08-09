<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

// The form should submit which action to take
$action = $_POST['action'] ?? ''; // 'deposit' or 'withdraw'
$amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);

if (!in_array($action, ['deposit', 'withdraw']) || $amount <= 0) {
    send_json_response(400, ['status' => 'error', 'message' => 'Invalid action or amount provided.']);
}

// --- MOCK DATABASE INTERACTION ---
// In a real application, this would be a database transaction:
// 1. Check if the user has sufficient balance for the transfer.
// 2. START TRANSACTION;
// 3. DECREASE balance from the source account (wallet or savings).
// 4. INCREASE balance in the destination account (savings or wallet).
// 5. INSERT a record into the `transactions` table for this transfer.
// 6. COMMIT;
// --- END MOCK ---

sleep(1); // Simulate network/processing delay

$action_past_tense = ($action === 'deposit') ? 'deposited' : 'withdrawn';
$message = "Successfully {$action_past_tense} $" . number_format($amount, 2) . ".";

send_json_response(200, [
    'status' => 'success',
    'message' => $message
]);
?>
