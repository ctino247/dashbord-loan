<?php
require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

try {
    // For now, we assume a fixed USD value for crypto. A real app would use an API or a prices table.
    $usd_prices = ['BTC' => 20000, 'ETH' => 1500, 'USDT-TRC20' => 1, 'USD' => 1];

    // Get wallet balances
    $wallet_stmt = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
    $wallet_stmt->execute([$user_id]);
    $db_wallets = $wallet_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $assets = [];
    $total_usd = 0;
    $currency_names = ['BTC' => 'Bitcoin', 'ETH' => 'Ethereum', 'USDT-TRC20' => 'Tether'];

    foreach ($db_wallets as $currency => $balance) {
        $usd_value = $balance * ($usd_prices[$currency] ?? 0);
        $assets[] = [
            'name' => $currency_names[$currency] ?? $currency,
            'ticker' => $currency,
            'icon' => 'assets/images/' . strtolower(explode('-', $currency)[0]) . '.png',
            'balance' => (float)$balance,
            'usd_value' => $usd_value,
        ];
        $total_usd += $usd_value;
    }

    // Get transaction history
    $tx_stmt = $pdo->prepare("SELECT id, type, status, amount, currency, created_at FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 20");
    $tx_stmt->execute([$user_id]);
    $transactions = $tx_stmt->fetchAll();

    // Get deposit addresses from settings
    $settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE '%_address'");
    $addresses = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $wallet_data = [
        'total_usd' => number_format($total_usd, 2),
        'assets' => $assets,
        'transactions' => $transactions,
        'deposit_addresses' => [
            'btc' => $addresses['btc_address'] ?? '',
            'usdt' => $addresses['usdt_trc20_address'] ?? '',
            'eth' => $addresses['eth_address'] ?? '',
        ]
    ];

    send_json_response(200, ['status' => 'success', 'data' => $wallet_data]);

} catch (Exception $e) {
    error_log("Wallet data fetch failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch wallet data.']);
}
?>
