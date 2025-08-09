<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/Wallet.php';
include_once '../models/Saving.php';
include_once '../models/Loan.php';
include_once '../models/KYC.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

$user_id = $_GET['user_id'] ?? 0;

if (empty($user_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'User ID is required.']);
    exit;
}

// --- Fetch Wallet Data ---
$wallet = new Wallet($db);
$wallet->getForUser($user_id);

// --- Fetch Savings Data ---
$saving = new Saving($db);
$saving->getForUser($user_id);

// --- Fetch Loan Data ---
$loan = new Loan($db);
$active_loan_result = $loan->getForUser($user_id); // This gets all loans, we'll filter for active ones.

$active_loans = [];
$total_loan_amount = 0;
while($row = $active_loan_result->fetch(PDO::FETCH_ASSOC)) {
    if ($row['status'] === 'disbursed' || $row['status'] === 'approved') {
        $active_loans[] = $row;
        $total_loan_amount += $row['amount_requested'];
    }
}

// --- Fetch KYC Status ---
$kyc = new KYC($db);
$kyc->getByUserId($user_id);
$kyc_status = $kyc->status ?? 'Not Submitted';


// --- Assemble Response ---
$dashboard_data = [
    'wallet_balance' => $wallet->balance ?? 0.00,
    'savings_balance' => $saving->balance ?? 0.00,
    'active_loan_count' => count($active_loans),
    'total_loan_amount' => $total_loan_amount,
    'kyc_status' => $kyc_status
];

http_response_code(200);
echo json_encode($dashboard_data);

?>
