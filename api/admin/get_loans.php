<?php
require_once '../../config/config.php';
require_once '../helpers.php';

// Admin auth check would go here

$mock_loans = [
    ['id' => 'L001', 'user_name' => 'John Smith', 'amount' => 1000.00, 'term' => 12, 'date' => '2024-07-28', 'status' => 'pending'],
    ['id' => 'L002', 'user_name' => 'Emily White', 'amount' => 500.00, 'term' => 6, 'date' => '2024-07-27', 'status' => 'pending'],
    ['id' => 'L003', 'user_name' => 'Dev User', 'amount' => 2500.00, 'term' => 24, 'date' => '2024-07-25', 'status' => 'approved'],
];

send_json_response(200, ['status' => 'success', 'data' => $mock_loans]);
?>
