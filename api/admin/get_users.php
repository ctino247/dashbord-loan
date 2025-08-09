<?php
require_once '../../config.php';
require_once '../helpers.php';

/*
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    send_json_response(403, ['status' => 'error', 'message' => 'Forbidden']);
}
*/

try {
    // A more advanced query could include search and pagination
    $stmt = $pdo->query(
        "SELECT u.id, u.first_name, u.last_name, u.username, u.email, p.kyc_status
         FROM users u
         LEFT JOIN user_profiles p ON u.id = p.user_id
         ORDER BY u.id DESC"
    );
    $users = $stmt->fetchAll();

    // Combine first and last name for display
    $users = array_map(function($user) {
        $user['name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        unset($user['first_name'], $user['last_name']);
        return $user;
    }, $users);

    send_json_response(200, ['status' => 'success', 'data' => $users]);

} catch (Exception $e) {
    error_log("Admin get_users failed: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch users.']);
}
?>
