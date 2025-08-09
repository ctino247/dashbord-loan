<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Transaction.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate transaction object
$transaction = new Transaction($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (empty($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(['message' => 'User ID is required']);
        exit;
    }

    $result = $transaction->getForUser($_GET['user_id']);
    $num = $result->rowCount();

    if ($num > 0) {
        $transactions_arr = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $transaction_item = array(
                'id' => $id,
                'user_id' => $user_id,
                'transaction_type' => $transaction_type,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $status,
                'description' => $description,
                'created_at' => $created_at
            );
            array_push($transactions_arr, $transaction_item);
        }
        echo json_encode($transactions_arr);
    } else {
        echo json_encode(['message' => 'No transactions found']);
    }

} elseif ($method === 'POST') {
    // This endpoint is specifically for creating withdrawal requests from the user side.
    // Other transaction types should be handled by the admin panel or internal logic.
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->user_id) || empty($data->amount) || empty($data->currency) || empty($data->wallet_address)) {
        http_response_code(400);
        echo json_encode(['message' => 'Incomplete data for withdrawal request. Please provide user_id, amount, currency, and wallet_address.']);
        exit;
    }

    // Check user's balance before creating the request
    include_once '../models/Wallet.php';
    $wallet = new Wallet($db);

    if (!$wallet->getForUser($data->user_id) || $wallet->balance < $data->amount) {
        http_response_code(400);
        echo json_encode(['message' => 'Insufficient wallet balance for this withdrawal.']);
        exit;
    }

    $transaction->user_id = $data->user_id;
    $transaction->amount = $data->amount;
    $transaction->currency = $data->currency;
    $transaction->wallet_address = $data->wallet_address;
    $transaction->description = 'User withdrawal request to ' . $data->wallet_address;

    if ($transaction->createWithdrawalRequest()) {
        http_response_code(201);
        echo json_encode(['message' => 'Withdrawal request submitted successfully. It will be processed after admin approval.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Withdrawal request could not be submitted.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}
?>
