<?php
require_once '../../app_config.php';
require_once '../helpers.php';

// Admin auth check would go here

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$withdrawal_id_str = $_POST['withdrawal_id'] ?? '';
$tx_id = filter_var(ltrim($withdrawal_id_str, 'W'), FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? ''; // 'approved' or 'rejected'

if (!$tx_id || !in_array($status, ['approved', 'rejected'])) {
    send_json_response(400, ['status' => 'error', 'message' => 'Invalid withdrawal ID or status provided.']);
}

try {
    $pdo->beginTransaction();

    // Get the transaction details, ensuring it's a pending withdrawal
    $tx_stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND type = 'withdrawal' AND status = 'requires_approval' FOR UPDATE");
    $tx_stmt->execute([$tx_id]);
    $transaction = $tx_stmt->fetch();

    if (!$transaction) {
        $pdo->rollBack();
        send_json_response(404, ['status' => 'error', 'message' => 'Withdrawal request not found or has already been processed.']);
    }

    if ($status === 'approved') {
        // The funds were already deducted when the request was made.
        // We just mark the transaction as completed.
        $update_stmt = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?");
        $update_stmt->execute([$tx_id]);
    } else { // 'rejected'
        // The withdrawal is rejected, so we must credit the funds back to the user's wallet.
        $update_stmt = $pdo->prepare("UPDATE transactions SET status = 'failed' WHERE id = ?");
        $update_stmt->execute([$tx_id]);

        $wallet_stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?");
        $wallet_stmt->execute([$transaction['amount'], $transaction['wallet_id']]);
    }

    $pdo->commit();

    send_json_response(200, ['status' => 'success', 'message' => "Withdrawal #{$withdrawal_id_str} has been {$status}."]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Admin update_withdrawal_status failed for tx_id {$tx_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to process withdrawal request.']);
}
?>
