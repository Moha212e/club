<?php
/**
 * Actions pour la gestion des membres
 */

// Inclure les dépendances nécessaires
require_once __DIR__ . '/../conf/database.php';
require_once __DIR__ . '/../DAO/UserDAO.php';

// Vérifier que l'utilisateur est connecté et admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé - Privilèges administrateur requis']);
    exit();
}

$userDAO = new UserDAO();
$response = ['success' => false, 'message' => 'Action non reconnue'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'get_all_users':
            $users = $userDAO->getAllUsers();
            if ($users !== false) {
                $response = ['success' => true, 'users' => $users];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des utilisateurs'];
            }
            break;
            
        case 'update_role':
            if (isset($_POST['user_id']) && isset($_POST['new_role'])) {
                $userId = (int)$_POST['user_id'];
                $newRole = $_POST['new_role'];
                
                // Empêcher de modifier son propre rôle
                if ($userId == $_SESSION['user_id']) {
                    $response = ['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre rôle'];
                } else {
                    $result = $userDAO->updateUserRole($userId, $newRole);
                    $response = $result;
                }
            } else {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
            }
            break;
            
        case 'toggle_status':
            if (isset($_POST['user_id'])) {
                $userId = (int)$_POST['user_id'];
                
                // Empêcher de désactiver son propre compte
                if ($userId == $_SESSION['user_id']) {
                    $response = ['success' => false, 'message' => 'Vous ne pouvez pas désactiver votre propre compte'];
                } else {
                    $result = $userDAO->toggleUserStatus($userId);
                    $response = $result;
                }
            } else {
                $response = ['success' => false, 'message' => 'ID utilisateur manquant'];
            }
            break;
            
        case 'get_stats':
            $stats = $userDAO->getMembersStats();
            if ($stats !== false) {
                $response = ['success' => true, 'data' => $stats];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des statistiques'];
            }
            break;
            
        case 'create_member':
            if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && 
                isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['role'])) {
                
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $firstName = trim($_POST['first_name']);
                $lastName = trim($_POST['last_name']);
                $role = $_POST['role'];
                
                $result = $userDAO->createMember($username, $email, $password, $firstName, $lastName, $role);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
            }
            break;
            
        case 'update_member':
            if (isset($_POST['user_id']) && isset($_POST['username']) && isset($_POST['email']) && 
                isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['role'])) {
                
                $userId = (int)$_POST['user_id'];
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $firstName = trim($_POST['first_name']);
                $lastName = trim($_POST['last_name']);
                $role = $_POST['role'];
                
                // Empêcher de modifier son propre compte
                if ($userId == $_SESSION['user_id']) {
                    $response = ['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre compte'];
                } else {
                    $result = $userDAO->updateMember($userId, $username, $email, $firstName, $lastName, $role);
                    $response = $result;
                }
            } else {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
            }
            break;
            
        case 'delete_member':
            if (isset($_POST['user_id'])) {
                $userId = (int)$_POST['user_id'];
                $result = $userDAO->deleteMember($userId);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'ID utilisateur manquant'];
            }
            break;
            
        case 'permanently_delete_member':
            if (isset($_POST['user_id'])) {
                $userId = (int)$_POST['user_id'];
                $result = $userDAO->permanentlyDeleteMember($userId);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'ID utilisateur manquant'];
            }
            break;
            
        case 'reset_password':
            if (isset($_POST['user_id']) && isset($_POST['new_password'])) {
                $userId = (int)$_POST['user_id'];
                $newPassword = $_POST['new_password'];
                $result = $userDAO->resetMemberPassword($userId, $newPassword);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Action non reconnue'];
    }
}

// Retourner la réponse en JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
