<?php
require_once '../../config.php';
require_once '../helpers.php';

// Admin auth check would go here

try {
    $stmt = $pdo->query(
        "SELECT l.id, CONCAT(u.first_name, ' ', u.last_name) as user_name, l.amount, l.term, l.created_at, l.status
         FROM loans l
         JOIN users u ON l.user_id = u.id
         ORDER BY l.status = 'pending' DESC, l.id DESC"
    );
    $loans = $stmt->fetchAll();

    // Format date for display
    $loans = array_map(function($loan) {
        $loan['id'] = 'L' . str_pad($loan['id'], 4, '0', STR_PAD_LEFT);
        $loan['date'] = date('Y-m-d', strtotime($loan['created_at']));
        unset($loan['created_at']);
        return $loan;
    }, $loans);

    send_json_response(200, ['status' => 'success', 'data' => $loans]);

} catch (Exception $e) {
    error_log("Admin get_loans failed: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch loans.']);
}
?>
