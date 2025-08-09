<?php
require_once '../../config/config.php';
require_once '../helpers.php';

// Admin auth check would go here

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$withdrawal_id = $_POST['withdrawal_id'] ?? '';
$status = $_POST['status'] ?? ''; // 'approved' or 'rejected'

if (empty($withdrawal_id) || !in_array($status, ['approved', 'rejected'])) {
    send_json_response(400, ['status' => 'error', 'message' => 'Invalid withdrawal ID or status provided.']);
}

// --- MOCK DATABASE INTERACTION ---
// In a real app, you would:
// 1. START TRANSACTION
// 2. Update the `transactions` table status.
// 3. If approved, DECREASE the user's wallet balance.
// 4. If rejected, do nothing to the balance.
// 5. COMMIT
// --- END MOCK ---

sleep(1);

send_json_response(200, ['status' => 'success', 'message' => "Withdrawal #{$withdrawal_id} has been {$status}."]);
?>
