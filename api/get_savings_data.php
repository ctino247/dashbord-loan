<?php
require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

try {
    // Get savings account details
    $savings_stmt = $pdo->prepare("SELECT balance, interest_rate FROM savings WHERE user_id = ? LIMIT 1");
    $savings_stmt->execute([$user_id]);
    $savings = $savings_stmt->fetch();

    // Get recent savings-related transactions
    $tx_stmt = $pdo->prepare("SELECT type, amount, currency, created_at FROM transactions WHERE user_id = ? AND type IN ('savings_deposit', 'savings_withdrawal', 'interest') ORDER BY id DESC LIMIT 10");
    $tx_stmt->execute([$user_id]);
    $transactions = $tx_stmt->fetchAll();

    // Get main wallet total balance for transfer info
    $usd_prices = ['BTC' => 20000, 'ETH' => 1500, 'USDT-TRC20' => 1, 'USD' => 1];
    $wallet_stmt = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
    $wallet_stmt->execute([$user_id]);
    $db_wallets = $wallet_stmt->fetchAll();
    $total_wallet_usd = 0;
    foreach ($db_wallets as $wallet) {
        $total_wallet_usd += $wallet['balance'] * ($usd_prices[$wallet['currency']] ?? 0);
    }

    $savings_data = [
        'balance' => $savings['balance'] ?? 0,
        'apy' => $savings['interest_rate'] ?? 0,
        'transactions' => array_map(function($tx) {
            return [
                'type' => $tx['type'],
                'amount' => ($tx['amount'] > 0 ? '+' : '') . rtrim(rtrim(number_format($tx['amount'], 2), '0'), '.') . ' ' . $tx['currency'],
                'date' => date('Y-m-d', strtotime($tx['created_at'])),
            ];
        }, $transactions),
        'wallet_balance_for_transfer' => $total_wallet_usd,
    ];

    send_json_response(200, ['status' => 'success', 'data' => $savings_data]);

} catch (Exception $e) {
    error_log("Savings data fetch failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch savings data.']);
}
?>
