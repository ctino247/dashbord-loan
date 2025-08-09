<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, PUT, GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/User.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate user object
$user = new User($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST': // Login / Register
        // Get raw posted data
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->telegram_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Telegram ID is required']);
            exit;
        }

        // Use createOrUpdate which handles both new and existing users
        if ($user->createOrUpdate($data->telegram_id, $data->username ?? null, $data->first_name ?? null, $data->last_name ?? null)) {
            // User found or created, return user data
            http_response_code(200);
            echo json_encode([
                'id' => $user->id,
                'telegram_id' => $user->telegram_id,
                'username' => $user->username,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'status' => $user->status,
                'created_at' => $user->created_at
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'User could not be created or found']);
        }
        break;

    case 'PUT': // Update email
        // Get raw posted data
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->user_id) || empty($data->email)) {
            http_response_code(400);
            echo json_encode(['message' => 'User ID and Email are required']);
            exit;
        }

        // simple email validation
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid email format']);
            exit;
        }

        if ($user->updateEmail($data->user_id, $data->email)) {
            http_response_code(200);
            echo json_encode(['message' => 'Email updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Email could not be updated']);
        }
        break;

    case 'GET': // Get user details
        if (empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'User ID is required']);
            exit;
        }

        if ($user->get($_GET['id'])) {
            http_response_code(200);
            echo json_encode([
                'id' => $user->id,
                'telegram_id' => $user->telegram_id,
                'username' => $user->username,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'status' => $user->status,
                'created_at' => $user->created_at
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}
?>
