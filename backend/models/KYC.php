<?php
class KYC {
    private $conn;
    private $table = 'kyc_documents';

    // Properties
    public $id;
    public $user_id;
    public $id_number;
    public $id_image_path;
    public $status;
    public $created_at;
    public $updated_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new KYC submission
    public function create() {
        // Check if a submission already exists for this user
        if ($this->getByUserId($this->user_id)) {
            // A submission already exists, maybe update it instead?
            // For now, let's prevent duplicates.
            return false;
        }

        $query = 'INSERT INTO ' . $this->table . '
            SET
                user_id = :user_id,
                id_number = :id_number,
                id_image_path = :id_image_path,
                status = "pending"';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->id_number = htmlspecialchars(strip_tags($this->id_number));
        $this->id_image_path = htmlspecialchars(strip_tags($this->id_image_path));

        // Bind data
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':id_number', $this->id_number);
        $stmt->bindParam(':id_image_path', $this->id_image_path);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Get KYC document by user ID
    public function getByUserId($user_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->id_number = $row['id_number'];
            $this->id_image_path = $row['id_image_path'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
}
?>
