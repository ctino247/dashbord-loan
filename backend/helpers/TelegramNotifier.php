<?php
class TelegramNotifier {
    private $bot_token;
    private $api_url = 'https://api.telegram.org/bot';

    // Constructor can be passed a token, or it will be fetched from DB
    public function __construct($db_connection) {
        include_once __DIR__ . '/../models/Settings.php';
        $settings = new Settings($db_connection);
        $this->bot_token = $settings->get('telegram_bot_token');
    }

    // Send a message to a specific chat (user)
    public function sendMessage($chat_id, $text) {
        if (empty($this->bot_token)) {
            // Log error or handle silently if bot is not configured
            error_log('Telegram Bot Token is not configured.');
            return false;
        }

        $url = $this->api_url . $this->bot_token . '/sendMessage';

        $post_fields = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML' // Optional: for formatting like <b>bold</b>
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $result = json_decode($response, true);
            if ($result['ok']) {
                return true;
            }
        }

        error_log('Failed to send Telegram message: ' . $response);
        return false;
    }
}
?>
