<?php
class Admin {
    private $conn;
    private $table = 'admins';

    // Properties
    public $id;
    public $username;
    public $password_hash;
    public $role;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Find admin by username
    public function findByUsername($username) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE username = :username LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password_hash = $row['password_hash'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }
}
?>
