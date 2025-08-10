<?php
require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

try {
    // For now, we assume a fixed USD value for crypto. A real app would use an API or a prices table.
    $usd_prices = ['BTC' => 20000, 'ETH' => 1500, 'USDT-TRC20' => 1, 'USD' => 1];

    // Get total wallet value
    $wallet_stmt = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
    $wallet_stmt->execute([$user_id]);
    $wallets = $wallet_stmt->fetchAll();
    $total_wallet_usd = 0;
    foreach ($wallets as $wallet) {
        $total_wallet_usd += $wallet['balance'] * ($usd_prices[$wallet['currency']] ?? 0);
    }

    // Get active loan
    $loan_stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? AND status = 'active' LIMIT 1");
    $loan_stmt->execute([$user_id]);
    $active_loan = $loan_stmt->fetch();

    // Get savings balance
    $savings_stmt = $pdo->prepare("SELECT balance, interest_rate FROM savings WHERE user_id = ? LIMIT 1");
    $savings_stmt->execute([$user_id]);
    $savings = $savings_stmt->fetch();

    // Get KYC status
    $kyc_stmt = $pdo->prepare("SELECT kyc_status FROM user_profiles WHERE user_id = ? LIMIT 1");
    $kyc_stmt->execute([$user_id]);
    $kyc_status = $kyc_stmt->fetchColumn();

    // Get recent transactions
    $tx_stmt = $pdo->prepare("SELECT type, amount, currency, created_at FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 5");
    $tx_stmt->execute([$user_id]);
    $recent_transactions = $tx_stmt->fetchAll();

    // Assemble the data payload
    $dashboard_data = [
        'user' => [
            'first_name' => $user['first_name'],
            'kyc_status' => $kyc_status,
        ],
        'wallet' => [
            'total_usd' => number_format($total_wallet_usd, 2),
        ],
        'loan' => [
            'has_active_loan' => (bool)$active_loan,
            'amount' => $active_loan['amount'] ?? 0,
            // A more complex query would be needed for a precise due date
            'next_payment_due' => $active_loan ? date('Y-m-d', strtotime('+30 days')) : null,
        ],
        'savings' => [
            'balance' => $savings['balance'] ?? 0,
            'apy' => $savings['interest_rate'] ?? 0,
        ],
        'transactions' => array_map(function($tx) {
            return [
                'type' => $tx['type'],
                'description' => ($tx['amount'] > 0 ? '+' : '') . rtrim(rtrim(number_format($tx['amount'], 8), '0'), '.') . ' ' . $tx['currency'],
                'date' => date('Y-m-d', strtotime($tx['created_at'])),
            ];
        }, $recent_transactions),
    ];

    send_json_response(200, [
        'status' => 'success',
        'data' => $dashboard_data,
    ]);

} catch (Exception $e) {
    error_log("Dashboard data fetch failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch dashboard data.']);
}
?>
