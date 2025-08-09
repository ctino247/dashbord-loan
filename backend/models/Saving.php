<?php
class Saving {
    private $conn;
    private $savings_table = 'savings';
    private $wallets_table = 'wallets';
    private $transactions_table = 'wallet_transactions';

    // Saving Properties
    public $id;
    public $user_id;
    public $balance;
    public $interest_rate;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get savings account for a user
    public function getForUser($user_id) {
        $query = 'SELECT * FROM ' . $this->savings_table . ' WHERE user_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->balance = $row['balance'];
            $this->interest_rate = $row['interest_rate'];
            return true;
        }
        // If user has no savings account yet, create one
        return $this->createAccount($user_id);
    }

    // Create a savings account for a user if it doesn't exist
    private function createAccount($user_id) {
        $query = 'INSERT INTO ' . $this->savings_table . ' (user_id, balance, interest_rate) VALUES (?, 0.00, 0.00)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->user_id = $user_id;
            $this->balance = 0.00;
            $this->interest_rate = 0.00;
            return true;
        }
        return false;
    }

    // Deposit funds from wallet to savings
    public function deposit($user_id, $amount) {
        try {
            $this->conn->beginTransaction();

            // 1. Check wallet balance
            $wallet_stmt = $this->conn->prepare('SELECT balance FROM ' . $this->wallets_table . ' WHERE user_id = :user_id FOR UPDATE');
            $wallet_stmt->bindParam(':user_id', $user_id);
            $wallet_stmt->execute();
            $wallet = $wallet_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$wallet || $wallet['balance'] < $amount) {
                $this->conn->rollBack();
                return false; // Insufficient funds
            }

            // 2. Decrease wallet balance
            $new_wallet_balance = $wallet['balance'] - $amount;
            $update_wallet_stmt = $this->conn->prepare('UPDATE ' . $this->wallets_table . ' SET balance = :balance WHERE user_id = :user_id');
            $update_wallet_stmt->bindParam(':balance', $new_wallet_balance);
            $update_wallet_stmt->bindParam(':user_id', $user_id);
            $update_wallet_stmt->execute();

            // 3. Increase savings balance
            $this->getForUser($user_id); // Ensure savings account exists
            $new_savings_balance = $this->balance + $amount;
            $update_savings_stmt = $this->conn->prepare('UPDATE ' . $this->savings_table . ' SET balance = :balance WHERE user_id = :user_id');
            $update_savings_stmt->bindParam(':balance', $new_savings_balance);
            $update_savings_stmt->bindParam(':user_id', $user_id);
            $update_savings_stmt->execute();

            // 4. Create transaction log
            $trans_stmt = $this->conn->prepare('INSERT INTO ' . $this->transactions_table . ' (user_id, transaction_type, amount, status, description) VALUES (:user_id, "savings_deposit", :amount, "completed", "Deposit to savings")');
            $trans_stmt->bindParam(':user_id', $user_id);
            $trans_stmt->bindParam(':amount', $amount);
            $trans_stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Withdraw funds from savings to wallet
    public function withdraw($user_id, $amount) {
        try {
            $this->conn->beginTransaction();

            // 1. Check savings balance
            $this->getForUser($user_id);
            if ($this->balance < $amount) {
                $this->conn->rollBack();
                return false; // Insufficient savings balance
            }

            // 2. Decrease savings balance
            $new_savings_balance = $this->balance - $amount;
            $update_savings_stmt = $this->conn->prepare('UPDATE ' . $this->savings_table . ' SET balance = :balance WHERE user_id = :user_id');
            $update_savings_stmt->bindParam(':balance', $new_savings_balance);
            $update_savings_stmt->bindParam(':user_id', $user_id);
            $update_savings_stmt->execute();

            // 3. Increase wallet balance
            $wallet_stmt = $this->conn->prepare('UPDATE ' . $this->wallets_table . ' SET balance = balance + :amount WHERE user_id = :user_id');
            $wallet_stmt->bindParam(':amount', $amount);
            $wallet_stmt->bindParam(':user_id', $user_id);
            $wallet_stmt->execute();

            // 4. Create transaction log
            $trans_stmt = $this->conn->prepare('INSERT INTO ' . $this->transactions_table . ' (user_id, transaction_type, amount, status, description) VALUES (:user_id, "savings_withdrawal", :amount, "completed", "Withdrawal from savings")');
            $trans_stmt->bindParam(':user_id', $user_id);
            $trans_stmt->bindParam(':amount', $amount);
            $trans_stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
