<?php
require_once '../../config.php';
require_once '../helpers.php';

// Admin auth check would go here

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$loan_id_str = $_POST['loan_id'] ?? '';
// Extract number from ID like 'L001'
$loan_id = filter_var(ltrim($loan_id_str, 'L0'), FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? ''; // 'approved' or 'rejected'

if (!$loan_id || !in_array($status, ['approved', 'rejected'])) {
    send_json_response(400, ['status' => 'error', 'message' => 'Invalid loan ID or status provided.']);
}

try {
    $pdo->beginTransaction();

    // Update loan status, ensuring it's currently pending
    $loan_stmt = $pdo->prepare("UPDATE loans SET status = ? WHERE id = ? AND status = 'pending'");
    $loan_stmt->execute([$status, $loan_id]);

    if ($loan_stmt->rowCount() === 0) {
        $pdo->rollBack();
        send_json_response(404, ['status' => 'error', 'message' => 'Loan not found or has already been processed.']);
    }

    // If approved, disburse funds to the user's wallet
    if ($status === 'approved') {
        $loan_details_stmt = $pdo->prepare("SELECT user_id, amount FROM loans WHERE id = ?");
        $loan_details_stmt->execute([$loan_id]);
        $loan = $loan_details_stmt->fetch();

        // For simplicity, we disburse to the USDT wallet as a representation of USD
        $wallet_stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USDT-TRC20'");
        $wallet_stmt->execute([$loan['amount'], $loan['user_id']]);

        // Log the disbursement transaction
        $tx_stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, status) VALUES (?, 'loan_disbursement', ?, 'USD', 'completed')");
        $tx_stmt->execute([$loan['user_id'], $loan['amount']]);
    }

    $pdo->commit();

    send_json_response(200, ['status' => 'success', 'message' => "Loan #{$loan_id_str} has been {$status}."]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Admin update_loan_status failed for loan_id {$loan_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to update loan status.']);
}
?>
