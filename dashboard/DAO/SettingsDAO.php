<?php
require_once __DIR__ . '/Database.php';

class SettingsDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createSettingsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS settings (
            `key` VARCHAR(100) PRIMARY KEY,
            `value` TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        try {
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllSettings() {
        $sql = "SELECT `key`, `value` FROM settings";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    public function getSetting($key, $default = null) {
        $sql = "SELECT `value` FROM settings WHERE `key` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    public function setSettings(array $keyValuePairs) {
        if (empty($keyValuePairs)) return true;
        $sql = "REPLACE INTO settings (`key`, `value`) VALUES (:key, :value)";
        $stmt = $this->db->prepare($sql);
        try {
            $this->db->beginTransaction();
            foreach ($keyValuePairs as $key => $value) {
                $stmt->execute([':key' => $key, ':value' => $value]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
