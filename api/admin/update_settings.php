<?php
require_once '../../app_config.php';
require_once '../helpers.php';

// Admin auth check would go here

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$allowed_keys = [
    'loan_interest_rate',
    'savings_interest_rate',
    'btc_address',
    'usdt_trc20_address',
    'eth_address'
];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");

    foreach ($_POST as $key => $value) {
        // Sanitize key and value
        $sanitized_key = htmlspecialchars($key);
        $sanitized_value = htmlspecialchars($value);

        // Update only if the key is in the allowed list
        if (in_array($sanitized_key, $allowed_keys)) {
            $stmt->execute([$sanitized_value, $sanitized_key]);
        }
    }

    $pdo->commit();

    send_json_response(200, ['status' => 'success', 'message' => 'System settings have been updated successfully.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Admin update_settings failed: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to update settings.']);
}
?>
