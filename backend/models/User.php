<?php
class User {
    private $conn;
    private $table = 'users';

    // User Properties
    public $id;
    public $telegram_id;
    public $username;
    public $first_name;
    public $last_name;
    public $email;
    public $status;
    public $created_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Find user by Telegram ID
    public function findByTelegramId($telegram_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE telegram_id = :telegram_id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':telegram_id', $telegram_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->telegram_id = $row['telegram_id'];
            $this->username = $row['username'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Create a new user or return existing one
    public function createOrUpdate($telegram_id, $username, $first_name, $last_name) {
        if ($this->findByTelegramId($telegram_id)) {
            // User exists, just return their info
            return true;
        }

        // User does not exist, create new user
        $query = 'INSERT INTO ' . $this->table . ' (telegram_id, username, first_name, last_name)
                  VALUES (:telegram_id, :username, :first_name, :last_name)';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->telegram_id = htmlspecialchars(strip_tags($telegram_id));
        $this->username = htmlspecialchars(strip_tags($username));
        $this->first_name = htmlspecialchars(strip_tags($first_name));
        $this->last_name = htmlspecialchars(strip_tags($last_name));

        // Bind data
        $stmt->bindParam(':telegram_id', $this->telegram_id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            // After creating user, create a wallet for them
            $this->createWallet();
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Update user's email
    public function updateEmail($user_id, $email) {
        $query = 'UPDATE ' . $this->table . ' SET email = :email WHERE id = :user_id';
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($user_id));
        $this->email = htmlspecialchars(strip_tags($email));

        $stmt->bindParam(':user_id', $this->id);
        $stmt->bindParam(':email', $this->email);

        if($stmt->execute()) {
            return true;
        }
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Get single user by ID
    public function get($user_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :user_id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->telegram_id = $row['telegram_id'];
            $this->username = $row['username'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Create a wallet for a new user
    private function createWallet() {
        $wallet_query = 'INSERT INTO wallets (user_id, balance) VALUES (:user_id, 0.00)';
        $stmt = $this->conn->prepare($wallet_query);
        $stmt->bindParam(':user_id', $this->id);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
