<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/SupportTicket.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate ticket object
$ticket = new SupportTicket($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['user_id'])) {
        // Get all tickets for a user
        $result = $ticket->getForUser($_GET['user_id']);
        $num = $result->rowCount();
        if ($num > 0) {
            $tickets_arr = array();
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                array_push($tickets_arr, $row);
            }
            echo json_encode($tickets_arr);
        } else {
            echo json_encode([]);
        }
    } elseif (isset($_GET['ticket_id'])) {
        // Get a single ticket with its replies
        $result = $ticket->getTicketWithReplies($_GET['ticket_id']);
        $num = $result->rowCount();
        if ($num > 0) {
            $replies_arr = array();
            $ticket_details = null;
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                if (!$ticket_details) {
                    $ticket_details = [
                        'id' => $row['id'],
                        'subject' => $row['subject'],
                        'status' => $row['status'],
                        'created_at' => $row['created_at'],
                        'replies' => []
                    ];
                }
                if ($row['reply_id']) {
                    array_push($replies_arr, [
                        'reply_id' => $row['reply_id'],
                        'message' => $row['message'],
                        'author' => $row['user_username'] ?? $row['admin_username'] ?? 'System',
                        'author_type' => $row['user_username'] ? 'user' : 'admin',
                        'reply_created_at' => $row['reply_created_at']
                    ]);
                }
            }
            $ticket_details['replies'] = $replies_arr;
            echo json_encode($ticket_details);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ticket not found']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Missing user_id or ticket_id parameter.']);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->ticket_id)) {
        // Add a reply
        if (empty($data->user_id) || empty($data->message)) {
            http_response_code(400);
            echo json_encode(['message' => 'Incomplete data for reply. Provide ticket_id, user_id, and message.']);
            exit;
        }
        if ($ticket->addReply($data->ticket_id, $data->user_id, $data->message)) {
            http_response_code(200);
            echo json_encode(['message' => 'Reply added successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to add reply. You may not be the owner of this ticket.']);
        }
    } else {
        // Create a new ticket
        if (empty($data->user_id) || empty($data->subject) || empty($data->message)) {
            http_response_code(400);
            echo json_encode(['message' => 'Incomplete data for new ticket. Provide user_id, subject, and message.']);
            exit;
        }
        if ($ticket->create($data->user_id, $data->subject, $data->message)) {
            http_response_code(201);
            echo json_encode(['message' => 'Support ticket created successfully.', 'ticket_id' => $ticket->id]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create support ticket.']);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}
?>
