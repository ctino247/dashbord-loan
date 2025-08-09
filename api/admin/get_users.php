<?php
require_once '../../config/config.php';
require_once '../helpers.php';

// In a real app, you would add an admin authentication check here.
// For example:
// session_start();
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     send_json_response(403, ['status' => 'error', 'message' => 'Forbidden']);
// }

$mock_users = [
    ['id' => 1, 'name' => 'Dev User', 'telegram' => '@devuser', 'email' => 'dev.user@example.com', 'kyc_status' => 'approved'],
    ['id' => 2, 'name' => 'Jane Doe', 'telegram' => '@janedoe', 'email' => 'jane@example.com', 'kyc_status' => 'pending'],
    ['id' => 3, 'name' => 'John Smith', 'telegram' => '@johnsmith', 'email' => 'john@example.com', 'kyc_status' => 'rejected'],
    ['id' => 4, 'name' => 'Emily White', 'telegram' => '@emilyw', 'email' => 'emily@example.com', 'kyc_status' => 'pending'],
];

send_json_response(200, ['status' => 'success', 'data' => $mock_users]);
?>
