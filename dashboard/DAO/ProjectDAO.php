<?php
require_once 'Database.php';

class ProjectDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Créer la table projects si elle n'existe pas
     */
    public function createProjectsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            description TEXT,
            owner_id INT NOT NULL,
            status ENUM('planning', 'active', 'completed', 'on_hold', 'cancelled') DEFAULT 'planning',
            visibility ENUM('private', 'public') DEFAULT 'private',
            start_date DATE NULL,
            due_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_owner (owner_id),
            INDEX idx_status (status),
            INDEX idx_due_date (due_date)
        )";
        
        try {
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur création table projects: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Créer un nouveau projet
     */
    public function createProject($title, $description, $ownerId, $status = 'planning', $visibility = 'private', $startDate = null, $dueDate = null) {
        $sql = "INSERT INTO projects (title, description, owner_id, status, visibility, start_date, due_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$title, $description, $ownerId, $status, $visibility, $startDate, $dueDate]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur création projet: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer tous les projets avec les détails du propriétaire
     */
    public function getAllProjects() {
        $sql = "SELECT p.*, u.username as owner_username, u.first_name as owner_first_name, 
                       u.last_name as owner_last_name, u.email as owner_email
                FROM projects p 
                LEFT JOIN users u ON p.owner_id = u.id 
                ORDER BY p.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération projets: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les projets visibles selon le rôle
     * - Admin: tous les projets
     * - Non admin: uniquement visibilité = public
     */
    public function getVisibleProjects(bool $isAdmin) {
        if ($isAdmin) {
            return $this->getAllProjects();
        }

        $sql = "SELECT p.*, u.username as owner_username, u.first_name as owner_first_name, 
                       u.last_name as owner_last_name, u.email as owner_email
                FROM projects p 
                LEFT JOIN users u ON p.owner_id = u.id 
                WHERE p.visibility = 'public'
                ORDER BY p.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération projets visibles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer un projet par ID
     */
    public function getProjectById($projectId) {
        $sql = "SELECT p.*, u.username as owner_username, u.first_name as owner_first_name, 
                       u.last_name as owner_last_name, u.email as owner_email
                FROM projects p 
                LEFT JOIN users u ON p.owner_id = u.id 
                WHERE p.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$projectId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération projet: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les projets d'un utilisateur
     */
    public function getProjectsByUser($userId) {
        $sql = "SELECT p.*, u.username as owner_username, u.first_name as owner_first_name, 
                       u.last_name as owner_last_name, u.email as owner_email
                FROM projects p 
                LEFT JOIN users u ON p.owner_id = u.id 
                WHERE p.owner_id = ? 
                ORDER BY p.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération projets utilisateur: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mettre à jour un projet
     */
    public function updateProject($projectId, $title, $description, $status, $visibility, $startDate = null, $dueDate = null) {
        $sql = "UPDATE projects SET title = ?, description = ?, status = ?, visibility = ?, 
                start_date = ?, due_date = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$title, $description, $status, $visibility, $startDate, $dueDate, $projectId]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour projet: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un projet
     */
    public function deleteProject($projectId) {
        $sql = "DELETE FROM projects WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$projectId]);
        } catch (PDOException $e) {
            error_log("Erreur suppression projet: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le statut d'un projet
     */
    public function updateProjectStatus($projectId, $status) {
        $sql = "UPDATE projects SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $projectId]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour statut projet: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les statistiques des projets
     */
    public function getProjectsStats() {
        $sql = "SELECT 
                    COUNT(*) as total_projects,
                    COUNT(CASE WHEN status = 'planning' THEN 1 END) as planning_projects,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_projects,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_projects,
                    COUNT(CASE WHEN status = 'on_hold' THEN 1 END) as on_hold_projects,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_projects,
                    COUNT(CASE WHEN due_date < CURDATE() AND status NOT IN ('completed', 'cancelled') THEN 1 END) as overdue_projects
                FROM projects";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération stats projets: " . $e->getMessage());
            return [
                'total_projects' => 0,
                'planning_projects' => 0,
                'active_projects' => 0,
                'completed_projects' => 0,
                'on_hold_projects' => 0,
                'cancelled_projects' => 0,
                'overdue_projects' => 0
            ];
        }
    }
    
    /**
     * Récupérer les utilisateurs actifs pour l'assignation
     */
    public function getUsersForAssignment() {
        $sql = "SELECT id, username, first_name, last_name, email 
                FROM users 
                WHERE is_active = 1 
                ORDER BY first_name, last_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération utilisateurs: " . $e->getMessage());
            return [];
        }
    }
}
