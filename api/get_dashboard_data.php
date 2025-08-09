<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

// Authenticate the user (uses the dev fallback if `initData` is not present)
$user = handleTelegramAuth();
$user_id = $user['id'];

// --- MOCK DATA ---
// This section simulates fetching data from the database using the $user_id.
// In a real implementation, you would replace this with actual database queries.

// Mock wallet balances
$mock_wallets = [
    ['currency' => 'BTC', 'balance' => 0.0512, 'usd_value' => 3100.50],
    ['currency' => 'USDT-TRC20', 'balance' => 1050.75, 'usd_value' => 1050.75],
    ['currency' => 'ETH', 'balance' => 0.15, 'usd_value' => 450.25],
];
$total_wallet_usd = array_sum(array_column($mock_wallets, 'usd_value'));

// Mock loan status
$mock_loan = [
    'has_active_loan' => true,
    'amount' => 500.00,
    'next_payment_due' => date('Y-m-d', strtotime('+15 days')),
];

// Mock savings balance
$mock_savings = [
    'balance' => 2000.00,
    'apy' => 5.00,
];

// Mock KYC status from user profile
$mock_profile = [
    'kyc_status' => 'pending', // Can be 'pending', 'approved', 'rejected'
];

// Mock recent transactions
$mock_transactions = [
    ['type' => 'deposit', 'description' => '+0.005 BTC', 'date' => date('Y-m-d', strtotime('-2 days'))],
    ['type' => 'withdrawal', 'description' => '-100.00 USDT', 'date' => date('Y-m-d', strtotime('-3 days'))],
    ['type' => 'loan', 'description' => 'Loan Disbursement', 'date' => date('Y-m-d', strtotime('-5 days'))],
];
// --- END MOCK DATA ---


// Assemble the data payload
$dashboard_data = [
    'user' => [
        'first_name' => $user['first_name'],
        'kyc_status' => $mock_profile['kyc_status'],
    ],
    'wallet' => [
        'total_usd' => number_format($total_wallet_usd, 2),
    ],
    'loan' => $mock_loan,
    'savings' => $mock_savings,
    'transactions' => $mock_transactions,
];

// Send the response
send_json_response(200, [
    'status' => 'success',
    'data' => $dashboard_data,
]);
?>
