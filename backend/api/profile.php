<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/User.php';
include_once '../models/KYC.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Helper function to handle file uploads
function handleUpload($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error.'];
    }

    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }

    // Check file size (e.g., 5MB limit)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'Sorry, your file is too large.'];
    }

    // Allow certain file formats
    if(!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Sorry, only JPG, JPEG, & PNG files are allowed.'];
    }

    // Create a unique name
    $new_filename = uniqid('', true) . '.' . $imageFileType;
    $final_path = $target_dir . $new_filename;

    if (move_uploaded_file($file["tmp_name"], '../../' . $final_path)) {
        return ['success' => true, 'path' => $final_path];
    } else {
        return ['success' => false, 'message' => 'Sorry, there was an error uploading your file.'];
    }
}


$action = $_POST['action'] ?? '';
$user_id = $_POST['user_id'] ?? 0;

if (empty($user_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'User ID is required.']);
    exit;
}

switch ($action) {
    case 'upload_profile_image':
        $upload_result = handleUpload($_FILES['profile_image'] ?? null, 'uploads/profile_images/');

        if (!$upload_result['success']) {
            http_response_code(400);
            echo json_encode(['message' => $upload_result['message']]);
            exit;
        }

        // Update user's profile_image_path in DB
        $user = new User($db);
        $user->get($user_id);
        $user->profile_image_path = $upload_result['path'];

        $user = new User($db);
        if ($user->updateProfileImagePath($user_id, $upload_result['path'])) {
            http_response_code(200);
            echo json_encode(['message' => 'Profile image updated successfully.', 'path' => $upload_result['path']]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update profile image path in database.']);
        }
        break;

    case 'submit_kyc':
        $id_number = $_POST['id_number'] ?? '';
        if (empty($id_number)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID number is required.']);
            exit;
        }

        $upload_result = handleUpload($_FILES['id_image'] ?? null, 'uploads/kyc_ids/');

        if (!$upload_result['success']) {
            http_response_code(400);
            echo json_encode(['message' => $upload_result['message']]);
            exit;
        }

        $kyc = new KYC($db);
        $kyc->user_id = $user_id;
        $kyc->id_number = $id_number;
        $kyc->id_image_path = $upload_result['path'];

        if ($kyc->create()) {
            http_response_code(201);
            echo json_encode(['message' => 'KYC documents submitted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to submit KYC documents. A submission may already exist.']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['message' => 'Invalid action specified.']);
        break;
}
?>
