<?php
require_once '../DAO/ProjectDAO.php';
require_once '../DAO/UserDAO.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté (la session est déjà démarrée via conf/database.php)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$projectDAO = new ProjectDAO();
$userDAO = new UserDAO();
// S'assurer que la table existe
$projectDAO->createProjectsTable();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_all_projects':
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            $projects = $projectDAO->getVisibleProjects($isAdmin);
            echo json_encode(['success' => true, 'data' => $projects]);
            break;
            
        case 'get_project_by_id':
            $projectId = $_POST['project_id'] ?? null;
            if (!$projectId) {
                throw new Exception('ID du projet requis');
            }
            
            $project = $projectDAO->getProjectById($projectId);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }
            
            echo json_encode(['success' => true, 'data' => $project]);
            break;
            
        case 'get_user_projects':
            $userId = $_SESSION['user_id'];
            $projects = $projectDAO->getProjectsByUser($userId);
            echo json_encode(['success' => true, 'data' => $projects]);
            break;
            
        case 'create_project':
            // Permissions: admin uniquement
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Permissions insuffisantes');
            }
            
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $ownerId = $_POST['owner_id'] ?? $_SESSION['user_id'];
            $status = $_POST['status'] ?? 'planning';
            $visibility = $_POST['visibility'] ?? 'private';
            $startDate = $_POST['start_date'] ?: null;
            $dueDate = $_POST['due_date'] ?: null;
            
            if (empty($title)) {
                throw new Exception('Le titre est requis');
            }
            
            // Valider les statuts et visibilité
            $validStatuses = ['planning', 'active', 'completed', 'on_hold', 'cancelled'];
            $validVisibility = ['private', 'public'];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Statut invalide');
            }
            
            if (!in_array($visibility, $validVisibility)) {
                throw new Exception('Visibilité invalide');
            }
            
            $projectId = $projectDAO->createProject($title, $description, $ownerId, $status, $visibility, $startDate, $dueDate);
            
            if ($projectId) {
                echo json_encode(['success' => true, 'message' => 'Projet créé avec succès', 'project_id' => $projectId]);
            } else {
                throw new Exception('Erreur lors de la création du projet');
            }
            break;
            
        case 'update_project':
            // Permissions: admin uniquement
            $projectId = $_POST['project_id'] ?? null;
            if (!$projectId) {
                throw new Exception('ID du projet requis');
            }
            
            $project = $projectDAO->getProjectById($projectId);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }
            
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Permissions insuffisantes');
            }
            
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'planning';
            $visibility = $_POST['visibility'] ?? 'private';
            $startDate = $_POST['start_date'] ?: null;
            $dueDate = $_POST['due_date'] ?: null;
            
            if (empty($title)) {
                throw new Exception('Le titre est requis');
            }
            
            // Valider les statuts et visibilité
            $validStatuses = ['planning', 'active', 'completed', 'on_hold', 'cancelled'];
            $validVisibility = ['private', 'public'];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Statut invalide');
            }
            
            if (!in_array($visibility, $validVisibility)) {
                throw new Exception('Visibilité invalide');
            }
            
            $success = $projectDAO->updateProject($projectId, $title, $description, $status, $visibility, $startDate, $dueDate);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Projet mis à jour avec succès']);
            } else {
                throw new Exception('Erreur lors de la mise à jour du projet');
            }
            break;
            
        case 'delete_project':
            // Permissions: admin uniquement
            $projectId = $_POST['project_id'] ?? null;
            if (!$projectId) {
                throw new Exception('ID du projet requis');
            }
            
            $project = $projectDAO->getProjectById($projectId);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }
            
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Permissions insuffisantes');
            }
            
            $success = $projectDAO->deleteProject($projectId);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Projet supprimé avec succès']);
            } else {
                throw new Exception('Erreur lors de la suppression du projet');
            }
            break;
            
        case 'update_project_status':
            $projectId = $_POST['project_id'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$projectId || !$status) {
                throw new Exception('ID du projet et statut requis');
            }
            
            // Vérifier les permissions
            $project = $projectDAO->getProjectById($projectId);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }
            
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                throw new Exception('Permissions insuffisantes');
            }
            
            $validStatuses = ['planning', 'active', 'completed', 'on_hold', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Statut invalide');
            }
            
            $success = $projectDAO->updateProjectStatus($projectId, $status);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
            } else {
                throw new Exception('Erreur lors de la mise à jour du statut');
            }
            break;
            
        case 'get_projects_stats':
            // Vérifier les permissions (admin seulement)
            if ($_SESSION['role'] !== 'admin') {
                throw new Exception('Permissions insuffisantes');
            }
            
            $stats = $projectDAO->getProjectsStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'get_users_for_assignment':
            $users = $projectDAO->getUsersForAssignment();
            echo json_encode(['success' => true, 'data' => $users]);
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
