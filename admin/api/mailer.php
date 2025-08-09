<?php
session_start();

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

// Check if admin is logged in
if (!isset($_SESSION['admin_user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

include_once __DIR__ . '/../../backend/config/database.php';
include_once __DIR__ . '/../../backend/models/User.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents("php://input"));

if (empty($data->subject) || empty($data->message) || empty($data->target_user_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data. Please provide target_user_id, subject, and message.']);
    exit;
}

$subject = $data->subject;
$message = $data->message;
$target_user_id = $data->target_user_id;
$headers = 'From: no-reply@loanplatform.com' . "\r\n" .
           'Reply-To: no-reply@loanplatform.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

$sent_count = 0;
$failed_count = 0;

if ($target_user_id === 'all') {
    // Send to all users
    // This requires a new method in the User model to get all users
    $query = 'SELECT email FROM users WHERE email IS NOT NULL AND email != ""';
    $stmt = $db->prepare($query);
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (mail($row['email'], $subject, $message, $headers)) {
            $sent_count++;
        } else {
            $failed_count++;
        }
    }
} else {
    // Send to a specific user
    $user = new User($db);
    if ($user->get($target_user_id)) {
        if (!empty($user->email)) {
            if (mail($user->email, $subject, $message, $headers)) {
                $sent_count++;
            } else {
                $failed_count++;
            }
        } else {
            $failed_count++; // User has no email address
        }
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Target user not found.']);
        exit;
    }
}

if ($failed_count > 0) {
    http_response_code(500);
    echo json_encode(['message' => "Process completed. Sent: $sent_count, Failed: $failed_count."]);
} else {
    http_response_code(200);
    echo json_encode(['message' => "Successfully sent $sent_count email(s)."]);
}

?>
