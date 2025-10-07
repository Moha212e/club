<?php
require_once 'Database.php';

class UserDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Créer la table users si elle n'existe pas
     */
    public function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            role ENUM('student', 'admin') DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE
        )";
        
        try {
            $this->db->exec($sql);
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register($username, $email, $password, $firstName, $lastName) {
        // Vérifier si l'utilisateur existe déjà
        if ($this->userExists($username, $email)) {
            return ['success' => false, 'message' => 'Un utilisateur avec ce nom d\'utilisateur ou cet email existe déjà.'];
        }

        // Hasher le mot de passe
        $hashedPassword = password_hash($password . SALT, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Inscription réussie!', 'user_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de l\'inscription.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = TRUE";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password . SALT, $user['password'])) {
                // Enlever le mot de passe des données retournées
                unset($user['password']);
                return ['success' => true, 'message' => 'Connexion réussie!', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Nom d\'utilisateur/email ou mot de passe incorrect.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Vérifier si un utilisateur existe
     */
    private function userExists($username, $email) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Obtenir un utilisateur par ID
     */
    public function getUserById($id) {
        $sql = "SELECT id, username, email, first_name, last_name, role, created_at FROM users WHERE id = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Valider l'email
     */
    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valider le mot de passe
     */
    public function isValidPassword($password) {
        // Au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }

    /**
     * Obtenir tous les utilisateurs (pour la gestion des membres)
     */
    public function getAllUsers() {
        $sql = "SELECT id, username, email, first_name, last_name, role, is_active, created_at, updated_at 
                FROM users 
                ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Mettre à jour le rôle d'un utilisateur
     */
    public function updateUserRole($userId, $newRole) {
        // Vérifier que le rôle est valide
        if (!in_array($newRole, ['student', 'admin'])) {
            return ['success' => false, 'message' => 'Rôle invalide.'];
        }

        $sql = "UPDATE users SET role = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$newRole, $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Rôle mis à jour avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Utilisateur non trouvé.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Activer/Désactiver un compte utilisateur
     */
    public function toggleUserStatus($userId) {
        // D'abord récupérer le statut actuel
        $sql = "SELECT is_active FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé.'];
        }
        
        $newStatus = $user['is_active'] ? 0 : 1;
        
        $sql = "UPDATE users SET is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$newStatus, $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                $action = $newStatus ? 'activé' : 'désactivé';
                return ['success' => true, 'message' => "Compte $action avec succès."];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Obtenir les statistiques des membres
     */
    public function getMembersStats() {
        $sql = "SELECT 
                    COUNT(*) as total_members,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_members,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_members,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
                    SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as student_count
                FROM users";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Créer un nouveau membre (par un admin)
     */
    public function createMember($username, $email, $password, $firstName, $lastName, $role = 'student') {
        // Vérifier si l'utilisateur existe déjà
        if ($this->userExists($username, $email)) {
            return ['success' => false, 'message' => 'Un utilisateur avec ce nom d\'utilisateur ou cet email existe déjà.'];
        }

        // Valider le rôle
        if (!in_array($role, ['student', 'admin'])) {
            return ['success' => false, 'message' => 'Rôle invalide.'];
        }

        // Valider l'email
        if (!$this->isValidEmail($email)) {
            return ['success' => false, 'message' => 'Email invalide.'];
        }

        // Valider le mot de passe
        if (!$this->isValidPassword($password)) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule et 1 chiffre.'];
        }

        // Hasher le mot de passe
        $hashedPassword = password_hash($password . SALT, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $role]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Membre créé avec succès!', 'user_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la création du membre.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Mettre à jour les informations d'un membre
     */
    public function updateMember($userId, $username, $email, $firstName, $lastName, $role) {
        // Vérifier que le membre existe
        $existingUser = $this->getUserById($userId);
        if (!$existingUser) {
            return ['success' => false, 'message' => 'Membre non trouvé.'];
        }

        // Vérifier si l'email ou username est déjà utilisé par un autre utilisateur
        $sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email, $userId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Ce nom d\'utilisateur ou cet email est déjà utilisé par un autre membre.'];
        }

        // Valider le rôle
        if (!in_array($role, ['student', 'admin'])) {
            return ['success' => false, 'message' => 'Rôle invalide.'];
        }

        // Valider l'email
        if (!$this->isValidEmail($email)) {
            return ['success' => false, 'message' => 'Email invalide.'];
        }

        $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$username, $email, $firstName, $lastName, $role, $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Membre mis à jour avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Aucune modification effectuée.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Supprimer un membre (soft delete - désactiver)
     */
    public function deleteMember($userId) {
        // Empêcher la suppression de son propre compte
        if ($userId == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'];
        }

        $sql = "UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Membre supprimé avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Membre non trouvé.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Supprimer définitivement un membre (hard delete)
     */
    public function permanentlyDeleteMember($userId) {
        // Empêcher la suppression de son propre compte
        if ($userId == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'];
        }

        $sql = "DELETE FROM users WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Membre supprimé définitivement.'];
            } else {
                return ['success' => false, 'message' => 'Membre non trouvé.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }

    /**
     * Réinitialiser le mot de passe d'un membre
     */
    public function resetMemberPassword($userId, $newPassword) {
        // Valider le mot de passe
        if (!$this->isValidPassword($newPassword)) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule et 1 chiffre.'];
        }

        // Hasher le nouveau mot de passe
        $hashedPassword = password_hash($newPassword . SALT, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Mot de passe réinitialisé avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Membre non trouvé.'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()];
        }
    }
}
?>