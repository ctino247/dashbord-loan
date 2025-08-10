<?php
require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

try {
    // Get active loan
    $active_loan_stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? AND status = 'active' LIMIT 1");
    $active_loan_stmt->execute([$user_id]);
    $active_loan = $active_loan_stmt->fetch();

    // A real app would need a separate table for payments to calculate this accurately
    if ($active_loan) {
        $active_loan['payments_made'] = 2; // Mocked for UI
        $active_loan['payments_total'] = $active_loan['term'];
    }

    // Get loan history
    $history_stmt = $pdo->prepare("SELECT id, amount, status, created_at FROM loans WHERE user_id = ? AND status IN ('paid-off', 'rejected') ORDER BY id DESC");
    $history_stmt->execute([$user_id]);
    $history = $history_stmt->fetchAll();

    $loan_data = [
        'active_loan' => $active_loan ? array_merge($active_loan, ['has_active_loan' => true]) : ['has_active_loan' => false],
        'history' => array_map(function($loan) {
            return [
                'id' => 'L' . str_pad($loan['id'], 4, '0', STR_PAD_LEFT),
                'amount' => $loan['amount'],
                'status' => $loan['status'],
                'date' => date('Y-m-d', strtotime($loan['created_at']))
            ];
        }, $history),
    ];

    send_json_response(200, ['status' => 'success', 'data' => $loan_data]);

} catch (Exception $e) {
    error_log("Loan data fetch failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch loan data.']);
}
?>
