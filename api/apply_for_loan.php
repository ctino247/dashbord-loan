<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$term = filter_var($_POST['term'] ?? 0, FILTER_VALIDATE_INT);
$purpose = trim(htmlspecialchars($_POST['purpose'] ?? ''));

if ($amount <= 0 || $term <= 0 || empty($purpose)) {
    send_json_response(400, ['status' => 'error', 'message' => 'Please provide a valid amount, term, and purpose.']);
}

// --- MOCK DATABASE INTERACTION ---
// In a real app, you would:
// 1. Check if the user is eligible for a loan (e.g., KYC approved, no other active loan).
// 2. Insert a new record into the `loans` table with a status of 'pending'.
// 3. Notify admins about the new application.
// --- END MOCK ---

sleep(1); // Simulate processing

send_json_response(200, [
    'status' => 'success',
    'message' => 'Your loan application has been submitted successfully and is now pending review.'
]);
?>
