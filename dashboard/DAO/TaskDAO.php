<?php
require_once 'Database.php';

class TaskDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Créer la table tasks si elle n'existe pas
     */
    public function createTasksTable() {
        $sql = "CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            assigned_to INT,
            created_by INT NOT NULL,
            project_id INT NOT NULL,
            due_date DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            INDEX idx_status (status),
            INDEX idx_priority (priority),
            INDEX idx_assigned_to (assigned_to),
            INDEX idx_created_by (created_by),
            INDEX idx_due_date (due_date),
            INDEX idx_project_id (project_id)
        )";
        
        try {
            $this->db->exec($sql);
            
            // Créer la table des assignations multiples
            $sqlAssignments = "CREATE TABLE IF NOT EXISTS task_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                task_id INT NOT NULL,
                user_id INT NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_task_user (task_id, user_id),
                INDEX idx_task_id (task_id),
                INDEX idx_user_id (user_id)
            )";
            
            $this->db->exec($sqlAssignments);
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Créer une nouvelle tâche
     */
    public function createTask($title, $description, $priority, $assignedTo, $projectId, $dueDate, $createdBy, $assignedUsers = []) {
        // Valider les données
        if (empty($title)) {
            return ['success' => false, 'message' => 'Le titre est obligatoire.'];
        }

        if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            return ['success' => false, 'message' => 'Priorité invalide.'];
        }

        if (empty($projectId)) {
            return ['success' => false, 'message' => 'Le projet est obligatoire.'];
        }

        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO tasks (title, description, priority, assigned_to, project_id, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$title, $description, $priority, $assignedTo, $projectId, $dueDate, $createdBy]);
            
            if ($result) {
                $taskId = $this->db->lastInsertId();
                
                // Ajouter les assignations multiples
                if (!empty($assignedUsers)) {
                    $assignSql = "INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)";
                    $assignStmt = $this->db->prepare($assignSql);
                    
                    foreach ($assignedUsers as $userId) {
                        $assignStmt->execute([$taskId, $userId]);
                    }
                }
                
                $this->db->commit();
                return ['success' => true, 'message' => 'Tâche créée avec succès!', 'task_id' => $taskId];
            } else {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Erreur lors de la création de la tâche.'];
            }
        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Obtenir toutes les tâches avec les informations des utilisateurs
     */
    public function getAllTasks() {
        $sql = "SELECT t.*, 
                       u1.first_name as assigned_first_name, 
                       u1.last_name as assigned_last_name,
                       u2.first_name as created_first_name, 
                       u2.last_name as created_last_name,
                       p.title as project_title
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                LEFT JOIN projects p ON t.project_id = p.id
                ORDER BY t.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tasks = $stmt->fetchAll();
            
            // Ajouter les assignations multiples pour chaque tâche
            foreach ($tasks as &$task) {
                $assignSql = "SELECT u.id, u.first_name, u.last_name, u.username 
                             FROM task_assignments ta 
                             JOIN users u ON ta.user_id = u.id 
                             WHERE ta.task_id = ?";
                $assignStmt = $this->db->prepare($assignSql);
                $assignStmt->execute([$task['id']]);
                $task['assignees'] = $assignStmt->fetchAll();
            }
            
            return $tasks;
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenir les tâches d'un utilisateur spécifique
     */
    public function getTasksByUser($userId) {
        $sql = "SELECT t.*, 
                       u1.first_name as assigned_first_name, 
                       u1.last_name as assigned_last_name,
                       u2.first_name as created_first_name, 
                       u2.last_name as created_last_name,
                       p.title as project_title
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.assigned_to = ? OR t.created_by = ?
                ORDER BY t.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenir une tâche par ID
     */
    public function getTaskById($taskId) {
        $sql = "SELECT t.*, 
                       u1.first_name as assigned_first_name, 
                       u1.last_name as assigned_last_name,
                       u2.first_name as created_first_name, 
                       u2.last_name as created_last_name,
                       p.title as project_title
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$taskId]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Mettre à jour une tâche
     */
    public function updateTask($taskId, $title, $description, $status, $priority, $assignedTo, $projectId, $dueDate) {
        // Valider les données
        if (empty($title)) {
            return ['success' => false, 'message' => 'Le titre est obligatoire.'];
        }

        if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Statut invalide.'];
        }

        if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            return ['success' => false, 'message' => 'Priorité invalide.'];
        }

        // Si la tâche est marquée comme terminée, ajouter la date de completion
        $completedAt = ($status === 'completed') ? 'CURRENT_TIMESTAMP' : 'NULL';
        
        $sql = "UPDATE tasks SET 
                    title = ?, 
                    description = ?, 
                    status = ?, 
                    priority = ?, 
                    assigned_to = ?, 
                    project_id = ?, 
                    due_date = ?,
                    completed_at = $completedAt,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$title, $description, $status, $priority, $assignedTo, $projectId, $dueDate, $taskId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Tâche mise à jour avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Tâche non trouvée.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Supprimer une tâche
     */
    public function deleteTask($taskId) {
        $sql = "DELETE FROM tasks WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$taskId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Tâche supprimée avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Tâche non trouvée.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Changer le statut d'une tâche
     */
    public function updateTaskStatus($taskId, $status) {
        if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Statut invalide.'];
        }

        $completedAt = ($status === 'completed') ? 'CURRENT_TIMESTAMP' : 'NULL';
        
        $sql = "UPDATE tasks SET 
                    status = ?, 
                    completed_at = $completedAt,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$status, $taskId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Statut mis à jour avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Tâche non trouvée.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Obtenir les statistiques des tâches
     */
    public function getTasksStats() {
        $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_tasks,
                    SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_tasks,
                    SUM(CASE WHEN due_date < NOW() AND status != 'completed' THEN 1 ELSE 0 END) as overdue_tasks
                FROM tasks";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenir les tâches en retard
     */
    public function getOverdueTasks() {
        $sql = "SELECT t.*, 
                       u1.first_name as assigned_first_name, 
                       u1.last_name as assigned_last_name,
                       u2.first_name as created_first_name, 
                       u2.last_name as created_last_name,
                       p.title as project_title
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.due_date < NOW() AND t.status != 'completed' AND t.status != 'cancelled'
                ORDER BY t.due_date ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Obtenir les tâches à venir (prochaines 7 jours)
     */
    public function getUpcomingTasks() {
        $sql = "SELECT t.*, 
                       u1.first_name as assigned_first_name, 
                       u1.last_name as assigned_last_name,
                       u2.first_name as created_first_name, 
                       u2.last_name as created_last_name,
                       p.title as project_title
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) 
                AND t.status != 'completed' AND t.status != 'cancelled'
                ORDER BY t.due_date ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>
