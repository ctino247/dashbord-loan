<?php

/**
 * Sets the HTTP response code and headers for a JSON response,
 * then prints the JSON-encoded data and terminates the script.
 *
 * @param int   $statusCode The HTTP status code to send (e.g., 200, 404, 500).
 * @param array $data       The associative array to encode as JSON.
 */
function send_json_response($statusCode, $data) {
    // Ensure no previous output interferes with headers
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
    }
    echo json_encode($data);
    exit;
}

?>
