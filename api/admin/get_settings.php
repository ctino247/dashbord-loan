<?php
require_once '../../app_config.php';
require_once '../helpers.php';

// Admin auth check would go here

try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    send_json_response(200, ['status' => 'success', 'data' => $settings]);

} catch (Exception $e) {
    error_log("Admin get_settings failed: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch system settings.']);
}
?>
