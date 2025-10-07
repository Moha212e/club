<?php
/**
 * Actions pour la gestion des tâches
 */

// Inclure les dépendances nécessaires
require_once __DIR__ . '/../conf/database.php';
require_once __DIR__ . '/../DAO/TaskDAO.php';
require_once __DIR__ . '/../DAO/UserDAO.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé - Connexion requise']);
    exit();
}

$taskDAO = new TaskDAO();
$userDAO = new UserDAO();
$taskDAO->createTasksTable(); // Créer la table si elle n'existe pas

$response = ['success' => false, 'message' => 'Action non reconnue'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'get_all_tasks':
            $tasks = $taskDAO->getAllTasks();
            if ($tasks !== false) {
                $response = ['success' => true, 'tasks' => $tasks];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des tâches'];
            }
            break;
            
        case 'get_user_tasks':
            $userId = $_SESSION['user_id'];
            $tasks = $taskDAO->getTasksByUser($userId);
            if ($tasks !== false) {
                $response = ['success' => true, 'tasks' => $tasks];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des tâches'];
            }
            break;
            
        case 'get_task_by_id':
            if (isset($_POST['task_id'])) {
                $taskId = (int)$_POST['task_id'];
                $task = $taskDAO->getTaskById($taskId);
                if ($task !== false) {
                    $response = ['success' => true, 'task' => $task];
                } else {
                    $response = ['success' => false, 'message' => 'Tâche non trouvée'];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID tâche manquant'];
            }
            break;
            
        case 'create_task':
            // Admin uniquement pour créer
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $response = ['success' => false, 'message' => 'Permissions insuffisantes'];
                break;
            }
            if (isset($_POST['title']) && isset($_POST['priority']) && isset($_POST['created_by']) && isset($_POST['project_id'])) {
                $title = trim($_POST['title']);
                $description = trim($_POST['description'] ?? '');
                $priority = $_POST['priority'];
                $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
                $projectId = (int)$_POST['project_id'];
                $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
                $createdBy = (int)$_POST['created_by'];
                $assignedUsers = isset($_POST['assigned_users']) ? array_filter($_POST['assigned_users']) : [];
                
                $result = $taskDAO->createTask($title, $description, $priority, $assignedTo, $projectId, $dueDate, $createdBy, $assignedUsers);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
            }
            break;
            
        case 'update_task':
            // Admin uniquement pour mise à jour des détails complets
            if (!isset($_POST['task_id']) || !isset($_POST['title']) || !isset($_POST['priority'])) {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
                break;
            }
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $response = ['success' => false, 'message' => 'Permissions insuffisantes'];
                break;
            }
            if (isset($_POST['task_id']) && isset($_POST['title']) && isset($_POST['priority'])) {
                $taskId = (int)$_POST['task_id'];
                $title = trim($_POST['title']);
                $description = trim($_POST['description'] ?? '');
                $status = $_POST['status'];
                $priority = $_POST['priority'];
                $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
                $projectId = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
                $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
                
                $result = $taskDAO->updateTask($taskId, $title, $description, $status, $priority, $assignedTo, $projectId, $dueDate);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
            }
            break;
            
        case 'update_task_status':
            if (isset($_POST['task_id']) && isset($_POST['status'])) {
                $taskId = (int)$_POST['task_id'];
                $status = $_POST['status'];
                // Autorisé si admin ou si l'utilisateur est l'assigné de la tâche
                $task = $taskDAO->getTaskById($taskId);
                if (!$task) {
                    $response = ['success' => false, 'message' => 'Tâche non trouvée'];
                    break;
                }
                $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
                $isAssignee = isset($task['assigned_to']) && (int)$task['assigned_to'] === (int)$_SESSION['user_id'];
                if (!$isAdmin && !$isAssignee) {
                    $response = ['success' => false, 'message' => 'Permissions insuffisantes'];
                    break;
                }
                $result = $taskDAO->updateTaskStatus($taskId, $status);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'Paramètres manquants'];
            }
            break;
            
        case 'delete_task':
            // Admin uniquement pour supprimer
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $response = ['success' => false, 'message' => 'Permissions insuffisantes'];
                break;
            }
            if (isset($_POST['task_id'])) {
                $taskId = (int)$_POST['task_id'];
                $result = $taskDAO->deleteTask($taskId);
                $response = $result;
            } else {
                $response = ['success' => false, 'message' => 'ID tâche manquant'];
            }
            break;
            
        case 'get_tasks_stats':
            $stats = $taskDAO->getTasksStats();
            if ($stats !== false) {
                $response = ['success' => true, 'stats' => $stats];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des statistiques'];
            }
            break;
            
        case 'get_overdue_tasks':
            $tasks = $taskDAO->getOverdueTasks();
            if ($tasks !== false) {
                $response = ['success' => true, 'tasks' => $tasks];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des tâches en retard'];
            }
            break;
            
        case 'get_upcoming_tasks':
            $tasks = $taskDAO->getUpcomingTasks();
            if ($tasks !== false) {
                $response = ['success' => true, 'tasks' => $tasks];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des tâches à venir'];
            }
            break;
            
        case 'get_users_for_assignment':
            $users = $userDAO->getAllUsers();
            if ($users !== false) {
                $response = ['success' => true, 'users' => $users];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la récupération des utilisateurs'];
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
