<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Saving.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate saving object
$saving = new Saving($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (empty($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(['message' => 'User ID is required']);
        exit;
    }

    if ($saving->getForUser($_GET['user_id'])) {
        http_response_code(200);
        echo json_encode([
            'user_id' => $saving->user_id,
            'balance' => $saving->balance,
            'interest_rate' => $saving->interest_rate
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Savings account not found']);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->user_id) || empty($data->amount) || empty($data->action)) {
        http_response_code(400);
        echo json_encode(['message' => 'Incomplete data. Please provide user_id, amount, and action (deposit/withdraw).']);
        exit;
    }

    if ($data->amount <= 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Amount must be positive.']);
        exit;
    }

    if ($data->action === 'deposit') {
        if ($saving->deposit($data->user_id, $data->amount)) {
            http_response_code(200);
            echo json_encode(['message' => 'Successfully deposited to savings.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to deposit to savings. Check wallet balance.']);
        }
    } elseif ($data->action === 'withdraw') {
        if ($saving->withdraw($data->user_id, $data->amount)) {
            http_response_code(200);
            echo json_encode(['message' => 'Successfully withdrew from savings.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to withdraw from savings. Check savings balance.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid action. Must be "deposit" or "withdraw".']);
    }

} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}
?>
