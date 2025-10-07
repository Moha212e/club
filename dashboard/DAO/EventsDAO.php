<?php
require_once __DIR__ . '/Database.php';

class EventsDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createEventsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            location VARCHAR(200),
            start_date DATE NOT NULL,
            end_date DATE NULL,
            visibility ENUM('public','private') DEFAULT 'public',
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_start (start_date),
            INDEX idx_visibility (visibility)
        )";
        try { $this->db->exec($sql); return true; } catch (PDOException $e) { return false; }
    }

    public function listUpcoming($includePrivate, $limit = 50) {
        $limit = (int)$limit; if ($limit <= 0) { $limit = 50; }
        $visibilityFilter = $includePrivate ? '' : " AND e.visibility='public'";
        // Événements à venir ou en cours: si end_date >= aujourd'hui, sinon start_date >= aujourd'hui
        $sql = "SELECT e.*, u.first_name, u.last_name
                FROM events e JOIN users u ON e.created_by=u.id
                WHERE (
                    (e.end_date IS NOT NULL AND e.end_date >= CURDATE())
                    OR (e.end_date IS NULL AND e.start_date >= CURDATE())
                )$visibilityFilter
                ORDER BY e.start_date ASC
                LIMIT $limit";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function listPast($includePrivate, $limit = 50) {
        $limit = (int)$limit; if ($limit <= 0) { $limit = 50; }
        $visibilityFilter = $includePrivate ? '' : " AND e.visibility='public'";
        // Passés: end_date < aujourd'hui, ou pas de end_date et start_date < aujourd'hui
        $sql = "SELECT e.*, u.first_name, u.last_name
                FROM events e JOIN users u ON e.created_by=u.id
                WHERE (
                    (e.end_date IS NOT NULL AND e.end_date < CURDATE())
                    OR (e.end_date IS NULL AND e.start_date < CURDATE())
                )$visibilityFilter
                ORDER BY e.start_date DESC
                LIMIT $limit";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id=?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($title, $description, $location, $startDate, $endDate, $visibility, $createdBy) {
        if (!$title || !$startDate) return ['success'=>false,'message'=>'Titre et date de début requis'];
        $sql = "INSERT INTO events (title, description, location, start_date, end_date, visibility, created_by)
                VALUES (?,?,?,?,?,?,?)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$title, $description, $location, $startDate, $endDate, $visibility, (int)$createdBy]);
        return $ok ? ['success'=>true,'event_id'=>$this->db->lastInsertId()] : ['success'=>false,'message'=>'Erreur création'];
    }

    public function update($id, $title, $description, $location, $startDate, $endDate, $visibility) {
        $sql = "UPDATE events SET title=?, description=?, location=?, start_date=?, end_date=?, visibility=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$title, $description, $location, $startDate, $endDate, $visibility, (int)$id]);
        return $ok ? ['success'=>true] : ['success'=>false,'message'=>'Erreur mise à jour'];
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id=?");
        return $stmt->execute([(int)$id]);
    }
}
