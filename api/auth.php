<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Validates the data received from the Telegram Web App.
 *
 * @param array $auth_data The data from Telegram
 * @param string $bot_token Your bot's token
 * @return bool
 */
function checkTelegramAuthorization($auth_data, $bot_token) {
    $check_hash = $auth_data['hash'];
    unset($auth_data['hash']);

    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);

    $secret_key = hash_hmac('sha256', $bot_token, 'WebAppData', true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);

    return hash_equals($hash, $check_hash);
}

/**
 * Fetches or registers a user based on Telegram data.
 *
 * @param array $telegram_user The user object from Telegram's initData
 * @return array The user record from the database
 */
function getOrRegisterUser($telegram_user) {
    global $pdo;

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_user['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        return $user;
    }

    // Register new user
    $stmt = $pdo->prepare(
        "INSERT INTO users (telegram_id, first_name, last_name, username) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([
        $telegram_user['id'],
        $telegram_user['first_name'],
        $telegram_user['last_name'] ?? null,
        $telegram_user['username'] ?? null
    ]);
    $user_id = $pdo->lastInsertId();

    // Create initial wallets
    $currencies = ['BTC', 'USDT-TRC20', 'ETH'];
    $wallet_stmt = $pdo->prepare("INSERT INTO wallets (user_id, currency) VALUES (?, ?)");
    foreach ($currencies as $currency) {
        $wallet_stmt->execute([$user_id, $currency]);
    }

    // Create user profile
    $profile_stmt = $pdo->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
    $profile_stmt->execute([$user_id]);

    // Create savings account
    $savings_stmt = $pdo->prepare("INSERT INTO savings (user_id, interest_rate) SELECT ?, setting_value FROM system_settings WHERE setting_key = 'savings_interest_rate'");
    $savings_stmt->execute([$user_id]);

    // Return the newly created user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


/**
 * Main function to handle authentication.
 *
 * @return array|null The authenticated user's data or null if auth fails.
 */
function handleTelegramAuth() {
    // In a real app, you get this from Telegram.WebApp.initData
    // For development, we can pass it as a query parameter.
    if (!isset($_GET['initData'])) {
        // --- DEVELOPMENT ONLY ---
        // This block allows development without a real Telegram session.
        // It should be removed or disabled in production.
        $is_dev_mode = true;
        if ($is_dev_mode) {
             $mock_user_data = [
                'id' => 123456789,
                'first_name' => 'Dev',
                'last_name' => 'User',
                'username' => 'devuser'
            ];
            return getOrRegisterUser($mock_user_data);
        }
        // --- END DEVELOPMENT ONLY ---

        http_response_code(401);
        echo json_encode(['error' => 'Authentication data not found.']);
        exit;
    }

    // Parse the initData string
    parse_str($_GET['initData'], $auth_data);
    $user_data = json_decode($auth_data['user'], true);

    // Validate the data
    if (!checkTelegramAuthorization($auth_data, TELEGRAM_BOT_TOKEN)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid authentication data.']);
        exit;
    }

    // Get or register the user
    return getOrRegisterUser($user_data);
}
?>
