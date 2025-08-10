<?php
require_once '../../app_config.php';
require_once '../helpers.php';

// Admin auth check would go here

try {
    $stmt = $pdo->query(
        "SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as user_name, t.amount, t.currency, t.tx_hash as address, t.created_at, t.status
         FROM transactions t
         JOIN users u ON t.user_id = u.id
         WHERE t.type = 'withdrawal'
         ORDER BY t.status = 'requires_approval' DESC, t.id DESC"
    );
    $withdrawals = $stmt->fetchAll();

    // Format date for display
    $withdrawals = array_map(function($w) {
        $w['id'] = 'W' . str_pad($w['id'], 4, '0', STR_PAD_LEFT);
        $w['date'] = date('Y-m-d', strtotime($w['created_at']));
        unset($w['created_at']);
        return $w;
    }, $withdrawals);

    send_json_response(200, ['status' => 'success', 'data' => $withdrawals]);

} catch (Exception $e) {
    error_log("Admin get_withdrawals failed: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch withdrawal requests.']);
}
?>
