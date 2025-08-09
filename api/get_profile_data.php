<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

// Authenticate the user
$user = handleTelegramAuth();
$user_id = $user['id'];

// --- MOCK DATA ---
// In a real application, this would be a single query joining the `users`
// and `user_profiles` tables to get all data related to the user.

// Combine base user data with more detailed profile data
$mock_profile_data = [
    'telegram_id' => $user['telegram_id'],
    'first_name' => $user['first_name'],
    'last_name' => $user['last_name'],
    'username' => $user['username'],
    'profile_picture_url' => 'assets/images/default-avatar.png', // Placeholder
    'email' => 'dev.user@example.com', // Mocked email
    'id_number' => 'AB1234567', // Mocked ID number
    'id_image_url' => 'assets/images/sample_id.png', // Mocked ID image URL
    'kyc_status' => 'approved', // Mocked status: 'pending', 'approved', 'rejected'
];

// --- END MOCK DATA ---

send_json_response(200, [
    'status' => 'success',
    'data' => $mock_profile_data
]);
?>
