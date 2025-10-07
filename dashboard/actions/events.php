<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../DAO/EventsDAO.php';

$dao = new EventsDAO();
$dao->createEventsTable();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Action inconnue'];

function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }

function normalizeDate($input) {
    if (!$input) return '';
    $s = trim(str_replace('T', ' ', $input));
    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $s, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
        return $s;
    }
    // If datetime provided, keep only date
    if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+\d{2}:\d{2}(?::\d{2})?$/', $s, $m)) {
        return $m[1];
    }
    return $s;
}

try {
    switch ($action) {
        case 'list_upcoming':
            $includePrivate = isAdmin();
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $response = ['success'=>true, 'data'=>$dao->listUpcoming($includePrivate, $limit)];
            break;
        case 'list_past':
            $includePrivate = isAdmin();
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $response = ['success'=>true, 'data'=>$dao->listPast($includePrivate, $limit)];
            break;
        case 'get_by_id':
            if (!isset($_GET['id'])) { $response = ['success'=>false,'message'=>'ID manquant']; break; }
            $event = $dao->getById((int)$_GET['id']);
            $response = $event ? ['success'=>true,'data'=>$event] : ['success'=>false,'message'=>'Introuvable'];
            break;
        case 'create':
            if (!isAdmin()) { $response = ['success'=>false,'message'=>'Permissions insuffisantes']; break; }
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $start = normalizeDate($_POST['start_date'] ?? ($_POST['start_datetime'] ?? ''));
            $end = normalizeDate($_POST['end_date'] ?? ($_POST['end_datetime'] ?? ''));
            $visibility = $_POST['visibility'] ?? 'public';
            $createdBy = $_SESSION['user_id'] ?? 0;
            $response = $dao->create($title, $description, $location, $start, $end ?: null, $visibility, $createdBy);
            break;
        case 'update':
            if (!isAdmin()) { $response = ['success'=>false,'message'=>'Permissions insuffisantes']; break; }
            $id = (int)$_POST['id'];
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $start = normalizeDate($_POST['start_date'] ?? ($_POST['start_datetime'] ?? ''));
            $end = normalizeDate($_POST['end_date'] ?? ($_POST['end_datetime'] ?? ''));
            $visibility = $_POST['visibility'] ?? 'public';
            $response = $dao->update($id, $title, $description, $location, $start, $end ?: null, $visibility);
            break;
        case 'delete':
            if (!isAdmin()) { $response = ['success'=>false,'message'=>'Permissions insuffisantes']; break; }
            $id = (int)($_POST['id'] ?? 0);
            $response = ['success'=>$dao->delete($id)];
            break;
        default:
            $response = ['success'=>false,'message'=>'Action non supportée'];
    }
} catch (Throwable $e) {
    $response = ['success'=>false,'message'=>'Erreur: '.$e->getMessage()];
}

echo json_encode($response);
