<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../DAO/AnnouncementsDAO.php';

$dao = new AnnouncementsDAO();
$dao->createAnnouncementsTable();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Action inconnue'];

function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }

function normalizeDateOnly($s) {
    if (!$s) return '';
    $s = trim($s);
    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $s, $m)) return $m[3].'-'.$m[2].'-'.$m[1];
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
    return $s;
}

try {
    switch ($action) {
        case 'list':
            $includePrivate = isAdmin();
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $response = ['success'=>true,'data'=>$dao->listVisible($includePrivate, $limit)];
            break;
        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            $data = $dao->getById($id);
            $response = $data ? ['success'=>true,'data'=>$data] : ['success'=>false,'message'=>'Introuvable'];
            break;
        case 'create':
            if (!isAdmin()) { $response = ['success'=>false,'message'=>'Permissions insuffisantes']; break; }
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $visibility = $_POST['visibility'] ?? 'public';
            $pinned = isset($_POST['pinned']) && $_POST['pinned'] == '1';
            $publish = normalizeDateOnly($_POST['publish_date'] ?? '');
            $expire = normalizeDateOnly($_POST['expire_date'] ?? '');
            $createdBy = $_SESSION['user_id'] ?? 0;
            $response = $dao->create($title, $content, $visibility, $pinned, $publish, $expire ?: null, $createdBy);
            break;
        case 'update':
            if (!isAdmin()) { $response = ['success'=>false,'message'=>'Permissions insuffisantes']; break; }
            $id = (int)$_POST['id'];
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $visibility = $_POST['visibility'] ?? 'public';
            $pinned = isset($_POST['pinned']) && $_POST['pinned'] == '1';
            $publish = normalizeDateOnly($_POST['publish_date'] ?? '');
            $expire = normalizeDateOnly($_POST['expire_date'] ?? '');
            $response = $dao->update($id, $title, $content, $visibility, $pinned, $publish, $expire ?: null);
            break;
        case 'delete':
            if (!isAdmin()) { $response = ['success'=>false,'message'=>'Permissions insuffisantes']; break; }
            $id = (int)$_POST['id'];
            $response = ['success'=>$dao->delete($id)];
            break;
        case 'get_stats':
            $includePrivate = isAdmin();
            $list = $dao->listVisible($includePrivate, 10000);
            $response = ['success'=>true,'data'=>['total'=>count($list)]];
            break;
        case 'list_latest':
            $includePrivate = isAdmin();
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $response = ['success'=>true,'data'=>$dao->listVisible($includePrivate, $limit)];
            break;
        default:
            $response = ['success'=>false,'message'=>'Action non supportée'];
    }
} catch (Throwable $e) {
    $response = ['success'=>false,'message'=>'Erreur: '.$e->getMessage()];
}

echo json_encode($response);
