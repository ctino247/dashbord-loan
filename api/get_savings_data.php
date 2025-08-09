<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

// --- MOCK DATA ---
$mock_savings_data = [
    'balance' => 2000.00,
    'apy' => 5.00,
    'transactions' => [
        ['type' => 'deposit', 'amount' => '+500.00', 'date' => '2024-07-20'],
        ['type' => 'interest', 'amount' => '+8.33', 'date' => '2024-07-31'],
        ['type' => 'deposit', 'amount' => '+1000.00', 'date' => '2024-07-10'],
    ],
    // This would be the user's combined wallet balance, for display purposes
    'wallet_balance_for_transfer' => 1234.56
];
// --- END MOCK DATA ---

send_json_response(200, [
    'status' => 'success',
    'data' => $mock_savings_data
]);
?>
