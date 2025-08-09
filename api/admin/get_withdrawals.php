<?php
require_once '../../config/config.php';
require_once '../helpers.php';

// Admin auth check would go here

$mock_withdrawals = [
    ['id' => 'W001', 'user_name' => 'Mark Johnson', 'amount' => 0.01, 'currency' => 'BTC', 'address' => 'bc1q...', 'date' => '2024-07-29', 'status' => 'pending'],
    ['id' => 'W002', 'user_name' => 'Sarah Lee', 'amount' => 500.00, 'currency' => 'USDT-TRC20', 'address' => 'T...', 'date' => '2024-07-28', 'status' => 'pending'],
    ['id' => 'W003', 'user_name' => 'Dev User', 'amount' => 0.1, 'currency' => 'ETH', 'address' => '0x...', 'date' => '2024-07-26', 'status' => 'approved'],
];

send_json_response(200, ['status' => 'success', 'data' => $mock_withdrawals]);
?>
