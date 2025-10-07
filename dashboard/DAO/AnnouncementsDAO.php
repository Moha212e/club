<?php
require_once __DIR__ . '/Database.php';

class AnnouncementsDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createAnnouncementsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            visibility ENUM('public','private') DEFAULT 'public',
            pinned BOOLEAN DEFAULT FALSE,
            publish_date DATE NOT NULL,
            expire_date DATE NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_publish (publish_date),
            INDEX idx_expire (expire_date),
            INDEX idx_visibility_ann (visibility),
            INDEX idx_pinned (pinned)
        )";
        try { $this->db->exec($sql); return true; } catch (PDOException $e) { return false; }
    }

    public function listVisible($includePrivate, $limit = 100) {
        $limit = (int)$limit; if ($limit <= 0) $limit = 100;
        $visibilityFilter = $includePrivate ? '' : " AND a.visibility='public'";
        $today = date('Y-m-d');
        $sql = "SELECT a.*, u.first_name, u.last_name
                FROM announcements a JOIN users u ON a.created_by=u.id
                WHERE a.publish_date <= :today AND (a.expire_date IS NULL OR a.expire_date >= :today) $visibilityFilter
                ORDER BY a.pinned DESC, a.publish_date DESC, a.id DESC
                LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':today' => $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM announcements WHERE id=?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($title, $content, $visibility, $pinned, $publishDate, $expireDate, $createdBy) {
        if (!$title || !$content || !$publishDate) return ['success'=>false,'message'=>'Titre, contenu et date de publication requis'];
        $sql = "INSERT INTO announcements (title, content, visibility, pinned, publish_date, expire_date, created_by) VALUES (?,?,?,?,?,?,?)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$title, $content, $visibility, (int)$pinned, $publishDate, $expireDate, (int)$createdBy]);
        return $ok ? ['success'=>true,'id'=>$this->db->lastInsertId()] : ['success'=>false,'message'=>'Erreur création'];
    }

    public function update($id, $title, $content, $visibility, $pinned, $publishDate, $expireDate) {
        $sql = "UPDATE announcements SET title=?, content=?, visibility=?, pinned=?, publish_date=?, expire_date=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$title, $content, $visibility, (int)$pinned, $publishDate, $expireDate, (int)$id]);
        return $ok ? ['success'=>true] : ['success'=>false,'message'=>'Erreur mise à jour'];
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE id=?");
        return $stmt->execute([(int)$id]);
    }
}
