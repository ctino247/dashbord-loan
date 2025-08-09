<?php
// Start the session
session_start();

// Set headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include necessary files
// Note the path correction to get to the main backend directory
include_once __DIR__ . '/../../backend/config/database.php';
include_once __DIR__ . '/../../backend/models/Admin.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Login logic
    $database = new Database();
    $db = $database->connect();
    $admin = new Admin($db);

    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->username) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    if ($admin->findByUsername($data->username)) {
        // User found, verify password
        if (password_verify($data->password, $admin->password_hash)) {
            // Password is correct
            $_SESSION['admin_user_id'] = $admin->id;
            $_SESSION['admin_username'] = $admin->username;
            $_SESSION['admin_role'] = $admin->role;

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Login successful.']);
        } else {
            // Password is not correct
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        }
    } else {
        // User not found
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    }

} elseif ($method === 'GET') {
    // Check login status
    if (isset($_SESSION['admin_user_id'])) {
        http_response_code(200);
        echo json_encode([
            'loggedIn' => true,
            'user_id' => $_SESSION['admin_user_id'],
            'username' => $_SESSION['admin_username'],
            'role' => $_SESSION['admin_role']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['loggedIn' => false]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}
?>
