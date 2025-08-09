<?php
require_once '../../config/config.php';
require_once '../helpers.php';

// Admin auth check would go here

// This data would be fetched from the `system_settings` table
$mock_settings = [
    'loan_interest_rate' => '10.00',
    'savings_interest_rate' => '5.00',
    'btc_address' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
    'usdt_trc20_address' => 'TXkf1c4p1bZ1j1Y1Z1j1Y1Z1j1Y1Z1j1Y1',
    'eth_address' => '0x1234567890123456789012345678901234567890',
];

send_json_response(200, ['status' => 'success', 'data' => $mock_settings]);
?>
