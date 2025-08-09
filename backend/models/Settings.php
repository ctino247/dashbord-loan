<?php
class Settings {
    private $conn;
    private $table = 'settings';

    // Settings array
    public $settings_array = [];

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all settings and load them into the array
    public function getAll() {
        $query = 'SELECT setting_key, setting_value FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $this->settings_array = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->settings_array[$row['setting_key']] = $row['setting_value'];
        }
        return $this->settings_array;
    }

    // Get a single setting value by key
    public function get($key) {
        if (!empty($this->settings_array)) {
            return $this->settings_array[$key] ?? null;
        }

        $query = 'SELECT setting_value FROM ' . $this->table . ' WHERE setting_key = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $key);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : null;
    }

    // Update a setting
    public function update($key, $value) {
        $query = 'UPDATE ' . $this->table . ' SET setting_value = :setting_value WHERE setting_key = :setting_key';

        // Check if key exists, if not, insert it
        if ($this->get($key) === null) {
            $query = 'INSERT INTO ' . $this->table . ' (setting_key, setting_value) VALUES (:setting_key, :setting_value)';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':setting_key', $key);
        $stmt->bindParam(':setting_value', $value);

        if ($stmt->execute()) {
            // Update local cache
            $this->settings_array[$key] = $value;
            return true;
        }
        return false;
    }
}
?>
