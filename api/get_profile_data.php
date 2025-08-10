<?php
require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

try {
    $stmt = $pdo->prepare(
        "SELECT u.telegram_id, u.first_name, u.last_name, u.username, u.email, u.profile_picture_url, p.id_number, p.id_image_url, p.kyc_status
         FROM users u
         LEFT JOIN user_profiles p ON u.id = p.user_id
         WHERE u.id = ?"
    );
    $stmt->execute([$user_id]);
    $profile_data = $stmt->fetch();

    if (!$profile_data) {
        send_json_response(404, ['status' => 'error', 'message' => 'Profile not found.']);
    }

    // Ensure a default profile picture if none is set
    if (empty($profile_data['profile_picture_url'])) {
        $profile_data['profile_picture_url'] = 'assets/images/default-avatar.png';
    }

    send_json_response(200, ['status' => 'success', 'data' => $profile_data]);

} catch (Exception $e) {
    error_log("Profile data fetch failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'Failed to fetch profile data.']);
}
?>
