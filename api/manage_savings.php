<?php
require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$action = $_POST['action'] ?? ''; // 'deposit' or 'withdraw'
$amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);

if (!in_array($action, ['deposit', 'withdraw']) || $amount <= 0) {
    send_json_response(400, ['status' => 'error', 'message' => 'Invalid action or amount provided.']);
}

try {
    $pdo->beginTransaction();

    // For simplicity, we assume savings are in a base currency (e.g., USD)
    // and we'll use the USDT wallet as the source/destination.
    // A real app might have a dedicated USD wallet or more complex logic.
    $usdt_wallet_stmt = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? AND currency = 'USDT-TRC20' FOR UPDATE");
    $usdt_wallet_stmt->execute([$user_id]);
    $usdt_wallet = $usdt_wallet_stmt->fetch();

    $savings_stmt = $pdo->prepare("SELECT id, balance FROM savings WHERE user_id = ? FOR UPDATE");
    $savings_stmt->execute([$user_id]);
    $savings = $savings_stmt->fetch();

    if ($action === 'deposit') {
        if (!$usdt_wallet || $usdt_wallet['balance'] < $amount) {
            $pdo->rollBack();
            send_json_response(400, ['status' => 'error', 'message' => 'Insufficient wallet balance for deposit.']);
        }
        // Decrease wallet, increase savings
        $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE id = ?")->execute([$amount, $usdt_wallet['id']]);
        $pdo->prepare("UPDATE savings SET balance = balance + ? WHERE id = ?")->execute([$amount, $savings['id']]);
        $tx_type = 'savings_deposit';
    } else { // withdraw
        if (!$savings || $savings['balance'] < $amount) {
            $pdo->rollBack();
            send_json_response(400, ['status' => 'error', 'message' => 'Insufficient savings balance for withdrawal.']);
        }
        // Increase wallet, decrease savings
        $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?")->execute([$amount, $usdt_wallet['id']]);
        $pdo->prepare("UPDATE savings SET balance = balance - ? WHERE id = ?")->execute([$amount, $savings['id']]);
        $tx_type = 'savings_withdrawal';
    }

    // Log the transaction
    $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, status) VALUES (?, ?, ?, 'USD', 'completed')")
        ->execute([$user_id, $tx_type, $amount]);

    $pdo->commit();

    $action_past_tense = ($action === 'deposit') ? 'deposited' : 'withdrawn';
    send_json_response(200, ['status' => 'success', 'message' => "Successfully {$action_past_tense} $" . number_format($amount, 2)]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Savings management failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'An error occurred during the transfer.']);
}
?>
