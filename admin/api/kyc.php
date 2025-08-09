<?php
session_start();

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');

// Check if admin is logged in
if (!isset($_SESSION['admin_user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

include_once __DIR__ . '/../../backend/config/database.php';
include_once __DIR__ . '/../../backend/models/KYC.php';

$database = new Database();
$db = $database->connect();
$kyc = new KYC($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get all KYC submissions (or filter by status, e.g., 'pending')
    $status = $_GET['status'] ?? 'pending';

    // This needs a new method in the KYC model. For now, a direct query.
    $query = 'SELECT k.id, k.user_id, k.id_number, k.id_image_path, k.status, k.created_at, u.username
              FROM kyc_documents k
              JOIN users u ON k.user_id = u.id
              WHERE k.status = :status
              ORDER BY k.created_at ASC';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->execute();

    $num = $stmt->rowCount();
    if ($num > 0) {
        $kyc_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($kyc_arr, $row);
        }
        echo json_encode($kyc_arr);
    } else {
        echo json_encode([]);
    }
} elseif ($method === 'POST') {
    // Update KYC status
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->kyc_id) || empty($data->status)) {
        http_response_code(400);
        echo json_encode(['message' => 'KYC ID and new status are required.']);
        exit;
    }

    // This also needs a new method in the KYC model.
    $query = 'UPDATE kyc_documents SET status = :status WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $data->status);
    $stmt->bindParam(':id', $data->kyc_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['message' => 'KYC status updated successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to update KYC status.']);
    }
}
?>
