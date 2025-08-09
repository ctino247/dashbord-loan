<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$term = filter_var($_POST['term'] ?? 0, FILTER_VALIDATE_INT);
$purpose = trim(htmlspecialchars($_POST['purpose'] ?? ''));

if ($amount <= 0 || $term <= 0 || empty($purpose)) {
    send_json_response(400, ['status' => 'error', 'message' => 'Please provide a valid amount, term, and purpose.']);
}

try {
    // Check if user is eligible (KYC approved, no other active loan)
    $kyc_stmt = $pdo->prepare("SELECT kyc_status FROM user_profiles WHERE user_id = ?");
    $kyc_stmt->execute([$user_id]);
    if ($kyc_stmt->fetchColumn() !== 'approved') {
        send_json_response(403, ['status' => 'error', 'message' => 'Please complete and wait for KYC approval before applying for a loan.']);
    }

    $loan_stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status = 'active'");
    $loan_stmt->execute([$user_id]);
    if ($loan_stmt->fetchColumn() > 0) {
        send_json_response(409, ['status' => 'error', 'message' => 'You already have an active loan.']);
    }

    // Get default interest rate from settings
    $rate_stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'loan_interest_rate' LIMIT 1");
    $interest_rate = $rate_stmt->fetchColumn() ?: 10.00; // Default if not set

    // Insert new loan application
    $insert_stmt = $pdo->prepare(
        "INSERT INTO loans (user_id, amount, interest_rate, term, purpose, status) VALUES (?, ?, ?, ?, ?, 'pending')"
    );
    $insert_stmt->execute([$user_id, $amount, $interest_rate, $term, $purpose]);

    send_json_response(200, [
        'status' => 'success',
        'message' => 'Your loan application has been submitted successfully and is now pending review.'
    ]);

} catch (Exception $e) {
    error_log("Loan application failed for user_id {$user_id}: " . $e->getMessage());
    send_json_response(500, ['status' => 'error', 'message' => 'An error occurred while submitting your application.']);
}
?>
