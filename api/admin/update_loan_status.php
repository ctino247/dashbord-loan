<?php
require_once '../../config/config.php';
require_once '../helpers.php';

// Admin auth check would go here

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$loan_id = $_POST['loan_id'] ?? '';
$status = $_POST['status'] ?? ''; // 'approved' or 'rejected'

if (empty($loan_id) || !in_array($status, ['approved', 'rejected'])) {
    send_json_response(400, ['status' => 'error', 'message' => 'Invalid loan ID or status provided.']);
}

// --- MOCK DATABASE INTERACTION ---
// In a real app, you would execute an UPDATE query on the `loans` table:
// $stmt = $pdo->prepare("UPDATE loans SET status = ? WHERE id = ?");
// $stmt->execute([$status, $loan_id]);
// --- END MOCK ---

sleep(1);

send_json_response(200, ['status' => 'success', 'message' => "Loan #{$loan_id} has been {$status}."]);
?>
