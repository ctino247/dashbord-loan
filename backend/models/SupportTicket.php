<?php
class SupportTicket {
    private $conn;
    private $tickets_table = 'support_tickets';
    private $replies_table = 'ticket_replies';

    // Properties
    public $id;
    public $user_id;
    public $subject;
    public $status;
    public $created_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new support ticket
    public function create($user_id, $subject, $message) {
        try {
            $this->conn->beginTransaction();

            // 1. Create the ticket
            $ticket_query = 'INSERT INTO ' . $this->tickets_table . ' (user_id, subject, status) VALUES (:user_id, :subject, "open")';
            $ticket_stmt = $this->conn->prepare($ticket_query);
            $ticket_stmt->bindParam(':user_id', $user_id);
            $ticket_stmt->bindParam(':subject', $subject);
            $ticket_stmt->execute();
            $ticket_id = $this->conn->lastInsertId();

            // 2. Add the initial message as the first reply
            $reply_query = 'INSERT INTO ' . $this->replies_table . ' (ticket_id, user_id, message) VALUES (:ticket_id, :user_id, :message)';
            $reply_stmt = $this->conn->prepare($reply_query);
            $reply_stmt->bindParam(':ticket_id', $ticket_id);
            $reply_stmt->bindParam(':user_id', $user_id);
            $reply_stmt->bindParam(':message', $message);
            $reply_stmt->execute();

            $this->conn->commit();
            $this->id = $ticket_id;
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Add a reply to a ticket
    public function addReply($ticket_id, $user_id, $message) {
        // First, ensure the user owns the ticket before replying
        $check_query = 'SELECT user_id FROM ' . $this->tickets_table . ' WHERE id = :ticket_id';
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':ticket_id', $ticket_id);
        $check_stmt->execute();
        $ticket = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket || $ticket['user_id'] != $user_id) {
            return false; // User does not own this ticket
        }

        $query = 'INSERT INTO ' . $this->replies_table . ' (ticket_id, user_id, message) VALUES (:ticket_id, :user_id, :message)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ticket_id', $ticket_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':message', $message);

        // Also, update ticket status to 'in_progress' if it was 'closed'
        $update_status_query = 'UPDATE ' . $this->tickets_table . ' SET status = "in_progress" WHERE id = :ticket_id AND status != "open"';
        $update_stmt = $this->conn->prepare($update_status_query);
        $update_stmt->bindParam(':ticket_id', $ticket_id);
        $update_stmt->execute();

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all tickets for a user
    public function getForUser($user_id) {
        $query = 'SELECT id, subject, status, created_at FROM ' . $this->tickets_table . ' WHERE user_id = ? ORDER BY created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Get a single ticket with all its replies
    public function getTicketWithReplies($ticket_id) {
        $query = 'SELECT
                    t.id, t.subject, t.status, t.created_at,
                    r.id as reply_id, r.message, r.created_at as reply_created_at,
                    u.username as user_username,
                    a.username as admin_username
                FROM ' . $this->tickets_table . ' t
                LEFT JOIN ' . $this->replies_table . ' r ON t.id = r.ticket_id
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN admins a ON r.admin_id = a.id
                WHERE t.id = ?
                ORDER BY r.created_at ASC';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $ticket_id);
        $stmt->execute();
        return $stmt;
    }
}
?>
