<?php
class Wallet {
    private $conn;
    private $table = 'wallets';

    // Wallet Properties
    public $id;
    public $user_id;
    public $balance;
    public $updated_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get wallet for a user
    public function getForUser($user_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE user_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->balance = $row['balance'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update wallet balance (should only be called from within a transaction)
    public function updateBalance($user_id, $new_balance) {
        $query = 'UPDATE ' . $this->table . ' SET balance = ? WHERE user_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $new_balance);
        $stmt->bindParam(2, $user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
