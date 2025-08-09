<?php
require_once '../../config/config.php';
require_once '../helpers.php';

// Admin auth check would go here

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? ''; // 'approved' or 'rejected'

if (!$user_id || !in_array($status, ['approved', 'rejected'])) {
    send_json_response(400, ['status' => 'error', 'message' => 'Invalid user ID or status provided.']);
}

// --- MOCK DATABASE INTERACTION ---
// In a real app, you would execute an UPDATE query on the `user_profiles` table:
// $stmt = $pdo->prepare("UPDATE user_profiles SET kyc_status = ? WHERE user_id = ?");
// $stmt->execute([$status, $user_id]);
// --- END MOCK ---

sleep(1); // Simulate processing

send_json_response(200, ['status' => 'success', 'message' => "User #{$user_id} KYC status has been updated to {$status}."]);
?>
