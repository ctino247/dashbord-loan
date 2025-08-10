<?php
require_once __DIR__ . '/../app_config.php';
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

if (empty($currency) || empty($address) || !is_numeric($amount) || $amount <= 0) {
    send_json_response(400, ['status' => 'error', 'message' => 'Please fill in all fields with valid values.']);
}

try {
    $pdo->beginTransaction();

    // 1. Get the wallet to check balance and get wallet_id, and lock the row for update
    $wallet_stmt = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? AND currency = ? FOR UPDATE");
    $wallet_stmt->execute([$user_id, $currency]);
    $wallet = $wallet_stmt->fetch();

    if (!$wallet || $wallet['balance'] < $amount) {
        $pdo->rollBack();
        send_json_response(400, ['status' => 'error', 'message' => 'Insufficient balance.']);
    }

    // 2. Insert a new transaction record for the withdrawal request
    // The amount is stored as a positive value, the 'type' indicates the direction.
    $tx_stmt = $pdo->prepare(
        "INSERT INTO transactions (user_id, wallet_id, type, amount, currency, status, tx_hash) VALUES (?, ?, 'withdrawal', ?, ?, 'requires_approval', ?)"
    );
    $tx_stmt->execute([$user_id, $wallet['id'], $amount, $currency, $address]);
    $transaction_id = $pdo->lastInsertId();

    // 3. Deduct the amount from the user's balance
    $new_balance = $wallet['balance'] - $amount;
    $update_stmt = $pdo->prepare("UPDATE wallets SET balance = ? WHERE id = ?");
    $update_stmt->execute([$new_balance, $wallet['id']]);

    $pdo->commit();

    send_json_response(200, [
        'status' => 'success',
        'message' => 'Your withdrawal request has been submitted and is pending approval.'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Withdrawal request failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'An error occurred while submitting your request.']);
}
?>
