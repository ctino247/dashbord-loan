<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

$user = handleTelegramAuth();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['status' => 'error', 'message' => 'Method Not Allowed.']);
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_email':
        handle_update_email($user_id, $pdo);
        break;
    case 'submit_kyc':
        handle_submit_kyc($user_id, $pdo);
        break;
    default:
        send_json_response(400, ['status' => 'error', 'message' => 'Invalid action specified.']);
}

function handle_update_email($user_id, $pdo) {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_json_response(400, ['status' => 'error', 'message' => 'A valid email address is required.']);
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $user_id]);
        send_json_response(200, ['status' => 'success', 'message' => 'Email updated successfully.']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate entry)
            send_json_response(409, ['status' => 'error', 'message' => 'This email address is already in use.']);
        } else {
            error_log("Email update failed for user_id {$user_id}: " . $e->getMessage());
            send_json_response(500, ['status' => 'error', 'message' => 'Failed to update email.']);
        }
    }
}

function handle_submit_kyc($user_id, $pdo) {
    $id_number = trim($_POST['id_number'] ?? '');

    if (empty($id_number)) {
        send_json_response(400, ['status' => 'error', 'message' => 'ID number is required.']);
    }
    if (!isset($_FILES['id_image']) || $_FILES['id_image']['error'] !== UPLOAD_ERR_OK) {
        send_json_response(400, ['status' => 'error', 'message' => 'ID image upload failed or is missing.']);
    }

    $file = $_FILES['id_image'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_mime_types)) {
        send_json_response(400, ['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
    }
    if ($file['size'] > 5 * 1024 * 1024) { // 5 MB limit
        send_json_response(400, ['status' => 'error', 'message' => 'File is too large. Maximum size is 5MB.']);
    }

    $upload_dir = __DIR__ . '/../assets/uploads/kyc/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = 'kyc_' . $user_id . '_' . bin2hex(random_bytes(16)) . '.' . $file_extension;
    $destination = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        send_json_response(500, ['status' => 'error', 'message' => 'Failed to save the uploaded file.']);
    }

    $file_url = '/assets/uploads/kyc/' . $new_filename;

    try {
        $stmt = $pdo->prepare("UPDATE user_profiles SET id_number = ?, id_image_url = ?, kyc_status = 'pending' WHERE user_id = ?");
        $stmt->execute([$id_number, $file_url, $user_id]);
        send_json_response(200, [
            'status' => 'success',
            'message' => 'KYC information submitted successfully and is now pending review.'
        ]);
    } catch (Exception $e) {
        error_log("KYC submission failed for user_id {$user_id}: " . $e->getMessage());
        // Attempt to delete the orphaned file
        if (file_exists($destination)) {
            unlink($destination);
        }
        send_json_response(500, ['status' => 'error', 'message' => 'Failed to submit KYC information.']);
    }
}
?>
