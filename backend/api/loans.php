<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Loan.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate loan object
$loan = new Loan($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['user_id'])) {
        // Get loans for a specific user
        $result = $loan->getForUser($_GET['user_id']);
        $num = $result->rowCount();

        if ($num > 0) {
            $loans_arr = array();
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $loan_item = array(
                    'id' => $id,
                    'product_name' => $product_name,
                    'amount_requested' => $amount_requested,
                    'total_repayment_amount' => $total_repayment_amount,
                    'interest_rate' => $interest_rate,
                    'term_days' => $term_days,
                    'status' => $status,
                    'created_at' => $created_at
                );
                array_push($loans_arr, $loan_item);
            }
            echo json_encode($loans_arr);
        } else {
            echo json_encode(['message' => 'No loans found']);
        }
    } elseif (isset($_GET['products'])) {
        // Get all available loan products
        $result = $loan->getAllLoanProducts();
        $num = $result->rowCount();

        if ($num > 0) {
            $products_arr = array();
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $product_item = array(
                    'id' => $id,
                    'name' => $name,
                    'interest_rate' => $interest_rate,
                    'min_amount' => $min_amount,
                    'max_amount' => $max_amount,
                    'term_days' => $term_days
                );
                array_push($products_arr, $product_item);
            }
            echo json_encode($products_arr);
        } else {
            echo json_encode(['message' => 'No loan products found']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Missing parameters. Provide user_id or products.']);
    }
} elseif ($method === 'POST') {
    // Create a new loan application
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->user_id) || empty($data->loan_product_id) || empty($data->amount_requested) || empty($data->purpose)) {
        http_response_code(400);
        echo json_encode(['message' => 'Incomplete data. Please provide user_id, loan_product_id, amount_requested, and purpose.']);
        exit;
    }

    $loan->user_id = $data->user_id;
    $loan->loan_product_id = $data->loan_product_id;
    $loan->amount_requested = $data->amount_requested;
    $loan->purpose = $data->purpose;

    if ($loan->create()) {
        http_response_code(201);
        echo json_encode(['message' => 'Loan application submitted successfully.', 'loan_id' => $loan->id]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Loan application could not be submitted. Please check the requested amount against the product limits.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}
?>
