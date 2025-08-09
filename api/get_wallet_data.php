<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

// --- MOCK DATA ---
// In a real app, this data would be fetched from the database
$mock_assets = [
    [
        'name' => 'Bitcoin',
        'ticker' => 'BTC',
        'icon' => 'assets/images/btc.png',
        'balance' => 0.0512,
        'usd_value' => 3100.50
    ],
    [
        'name' => 'Tether',
        'ticker' => 'USDT-TRC20',
        'icon' => 'assets/images/usdt.png',
        'balance' => 1050.75,
        'usd_value' => 1050.75
    ],
    [
        'name' => 'Ethereum',
        'ticker' => 'ETH',
        'icon' => 'assets/images/eth.png',
        'balance' => 0.15,
        'usd_value' => 450.25
    ],
];

$mock_transactions = [
    ['id' => 1, 'type' => 'deposit', 'status' => 'completed', 'amount' => '+0.005 BTC', 'date' => '2024-07-28'],
    ['id' => 2, 'type' => 'withdrawal', 'status' => 'pending', 'amount' => '-100.00 USDT', 'date' => '2024-07-27'],
    ['id' => 3, 'type' => 'loan_disbursement', 'status' => 'completed', 'amount' => '+$500.00', 'date' => '2024-07-25'],
];

$total_usd_value = array_sum(array_column($mock_assets, 'usd_value'));
// --- END MOCK DATA ---

$wallet_data = [
    'total_usd' => number_format($total_usd_value, 2),
    'assets' => $mock_assets,
    'transactions' => $mock_transactions,
    'deposit_addresses' => [ // This would come from system_settings table
        'btc' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
        'usdt' => 'TXkf1c4p1bZ1j1Y1Z1j1Y1Z1j1Y1Z1j1Y1',
        'eth' => '0x1234567890123456789012345678901234567890',
    ]
];

send_json_response(200, [
    'status' => 'success',
    'data' => $wallet_data
]);
?>
