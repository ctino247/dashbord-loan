<?php
require_once __DIR__ . '/../config.php';

/**
 * Validates the data received from the Telegram Web App.
 */
function checkTelegramAuthorization($auth_data, $bot_token) {
    if (!isset($auth_data['hash'])) return false;
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
 * Fetches or registers a user based on Telegram data, creating all necessary accounts.
 */
function getOrRegisterUser($telegram_user) {
    global $pdo;

    try {
        // Check if user exists first, outside of a transaction for performance
        $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
        $stmt->execute([$telegram_user['id']]);
        $user = $stmt->fetch();
        if ($user) {
            return $user;
        }

        // If user does not exist, start a transaction to create them
        $pdo->beginTransaction();

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

        // Create savings account, fetching the default rate from settings
        $rate_stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'savings_interest_rate' LIMIT 1");
        $savings_rate = $rate_stmt->fetchColumn() ?: 0.00;

        $savings_stmt = $pdo->prepare("INSERT INTO savings (user_id, interest_rate) VALUES (?, ?)");
        $savings_stmt->execute([$user_id, $savings_rate]);

        $pdo->commit();

        // Return the newly created user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("User registration failed: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'A critical error occurred during user registration.']));
    }
}

/**
 * Main function to handle authentication for production.
 */
function handleTelegramAuth() {
    if (empty($_GET['initData'])) {
        http_response_code(401);
        die(json_encode(['error' => 'Authentication data not found.']));
    }

    parse_str($_GET['initData'], $auth_data);

    if (!checkTelegramAuthorization($auth_data, TELEGRAM_BOT_TOKEN)) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid authentication data.']));
    }

    $user_data = json_decode($auth_data['user'], true);
    if (!$user_data || !isset($user_data['id'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid user data provided.']));
    }

    return getOrRegisterUser($user_data);
}
?>
