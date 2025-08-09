<?php
class Loan {
    private $conn;
    private $loans_table = 'loans';
    private $products_table = 'loan_products';

    // Loan Properties
    public $id;
    public $user_id;
    public $loan_product_id;
    public $amount_requested;
    public $amount_disbursed;
    public $interest_amount;
    public $total_repayment_amount;
    public $term_days;
    public $purpose;
    public $status;
    public $created_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create Loan Application
    public function create() {
        // First, get loan product details
        $product_query = 'SELECT * FROM ' . $this->products_table . ' WHERE id = :loan_product_id';
        $product_stmt = $this->conn->prepare($product_query);
        $product_stmt->bindParam(':loan_product_id', $this->loan_product_id);
        $product_stmt->execute();
        $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

        if(!$product) {
            return false; // Product not found
        }

        // Validate requested amount against product limits
        if ($this->amount_requested < $product['min_amount'] || $this->amount_requested > $product['max_amount']) {
            return false; // Amount not within limits
        }

        // Calculate interest
        $interest_rate = $product['interest_rate'];
        $this->term_days = $product['term_days'];
        $this->interest_amount = ($this->amount_requested * $interest_rate) / 100;
        $this->total_repayment_amount = $this->amount_requested + $this->interest_amount;

        // Create loan query
        $query = 'INSERT INTO ' . $this->loans_table . '
            SET
                user_id = :user_id,
                loan_product_id = :loan_product_id,
                amount_requested = :amount_requested,
                interest_amount = :interest_amount,
                total_repayment_amount = :total_repayment_amount,
                term_days = :term_days,
                purpose = :purpose,
                status = "pending"';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->loan_product_id = htmlspecialchars(strip_tags($this->loan_product_id));
        $this->amount_requested = htmlspecialchars(strip_tags($this->amount_requested));
        $this->purpose = htmlspecialchars(strip_tags($this->purpose));

        // Bind data
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':loan_product_id', $this->loan_product_id);
        $stmt->bindParam(':amount_requested', $this->amount_requested);
        $stmt->bindParam(':interest_amount', $this->interest_amount);
        $stmt->bindParam(':total_repayment_amount', $this->total_repayment_amount);
        $stmt->bindParam(':term_days', $this->term_days);
        $stmt->bindParam(':purpose', $this->purpose);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Get all loans for a specific user
    public function getForUser($user_id) {
        $query = 'SELECT
                    l.id,
                    l.status,
                    l.amount_requested,
                    l.total_repayment_amount,
                    l.created_at,
                    p.name as product_name,
                    p.interest_rate,
                    l.term_days
                FROM
                    ' . $this->loans_table . ' l
                LEFT JOIN
                    ' . $this->products_table . ' p ON l.loan_product_id = p.id
                WHERE
                    l.user_id = ?
                ORDER BY
                    l.created_at DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Get all available loan products
    public function getAllLoanProducts() {
        $query = 'SELECT * FROM ' . $this->products_table . ' WHERE is_active = 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
