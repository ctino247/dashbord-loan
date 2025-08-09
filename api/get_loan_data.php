<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

// --- MOCK DATA ---
$mock_active_loan = [
    'has_active_loan' => true,
    'amount' => 500.00,
    'interest_rate' => 12.00,
    'term' => 6, // in months
    'status' => 'active',
    'next_payment_due' => date('Y-m-d', strtotime('+15 days')),
    'payments_made' => 2,
    'payments_total' => 6,
];

$mock_loan_history = [
    ['id' => 'L101', 'amount' => 1000.00, 'status' => 'paid-off', 'date' => '2024-01-15'],
    ['id' => 'L102', 'amount' => 200.00, 'status' => 'rejected', 'date' => '2023-12-10'],
];
// --- END MOCK DATA ---

$loan_data = [
    'active_loan' => $mock_active_loan,
    'history' => $mock_loan_history,
];

send_json_response(200, [
    'status' => 'success',
    'data' => $loan_data
]);
?>
