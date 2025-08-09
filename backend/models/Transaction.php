<?php
class Transaction {
    private $conn;
    private $transactions_table = 'wallet_transactions';
    private $wallets_table = 'wallets';

    // Transaction Properties
    public $id;
    public $user_id;
    public $transaction_type;
    public $amount;
    public $currency;
    public $external_tx_id;
    public $wallet_address;
    public $status;
    public $description;
    public $created_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new transaction and update wallet balance
    // This is a generic method that can be used by other more specific methods
    public function create() {
        // Debit types: decrease wallet balance
        $debit_types = ['withdrawal', 'loan_repayment', 'savings_deposit', 'manual_debit'];
        // Credit types: increase wallet balance
        $credit_types = ['deposit', 'loan_disbursement', 'savings_withdrawal', 'manual_credit'];

        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Get current wallet balance
            $wallet_query = 'SELECT balance FROM ' . $this->wallets_table . ' WHERE user_id = :user_id FOR UPDATE';
            $wallet_stmt = $this->conn->prepare($wallet_query);
            $wallet_stmt->bindParam(':user_id', $this->user_id);
            $wallet_stmt->execute();
            $wallet = $wallet_stmt->fetch(PDO::FETCH_ASSOC);
            $current_balance = $wallet['balance'];

            $new_balance = 0;

            if (in_array($this->transaction_type, $debit_types)) {
                // Check for sufficient funds
                if ($current_balance < $this->amount) {
                    $this->conn->rollBack();
                    return false; // Insufficient funds
                }
                $new_balance = $current_balance - $this->amount;
            } elseif (in_array($this->transaction_type, $credit_types)) {
                $new_balance = $current_balance + $this->amount;
            } else {
                // Invalid transaction type
                $this->conn->rollBack();
                return false;
            }

            // Update wallet balance
            $update_wallet_query = 'UPDATE ' . $this->wallets_table . ' SET balance = :new_balance WHERE user_id = :user_id';
            $update_stmt = $this->conn->prepare($update_wallet_query);
            $update_stmt->bindParam(':new_balance', $new_balance);
            $update_stmt->bindParam(':user_id', $this->user_id);
            $update_stmt->execute();

            // Insert transaction record
            $query = 'INSERT INTO ' . $this->transactions_table . '
                SET
                    user_id = :user_id,
                    transaction_type = :transaction_type,
                    amount = :amount,
                    currency = :currency,
                    external_tx_id = :external_tx_id,
                    wallet_address = :wallet_address,
                    status = :status,
                    description = :description';

            $stmt = $this->conn->prepare($query);

            // Bind data
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':transaction_type', $this->transaction_type);
            $stmt->bindParam(':amount', $this->amount);
            $stmt->bindParam(':currency', $this->currency);
            $stmt->bindParam(':external_tx_id', $this->external_tx_id);
            $stmt->bindParam(':wallet_address', $this->wallet_address);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':description', $this->description);

            $stmt->execute();
            $this->id = $this->conn->lastInsertId();

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollBack();
            printf("Error: %s.\n", $e->getMessage());
            return false;
        }
    }

    // Create a withdrawal request (doesn't change balance until approved)
    public function createWithdrawalRequest() {
        // This transaction type does not immediately affect the wallet balance.
        // It's a pending transaction that an admin needs to approve.
        // The balance will be updated by an admin action later.

        $query = 'INSERT INTO ' . $this->transactions_table . '
            SET
                user_id = :user_id,
                transaction_type = "withdrawal",
                amount = :amount,
                currency = :currency,
                wallet_address = :wallet_address,
                status = "pending",
                description = :description';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':currency', $this->currency);
        $stmt->bindParam(':wallet_address', $this->wallet_address);
        $stmt->bindParam(':description', $this->description);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Get all transactions for a specific user
    public function getForUser($user_id) {
        $query = 'SELECT * FROM ' . $this->transactions_table . ' WHERE user_id = ? ORDER BY created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }
}
?>
