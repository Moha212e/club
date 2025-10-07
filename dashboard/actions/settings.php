<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../DAO/SettingsDAO.php';

$settingsDAO = new SettingsDAO();
$settingsDAO->createSettingsTable();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Action inconnue'];

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

try {
    switch ($action) {
        case 'get_settings':
            if (!isAdmin()) {
                $response = ['success' => false, 'message' => 'Permissions insuffisantes'];
                break;
            }
            $settings = $settingsDAO->getAllSettings();
            $response = ['success' => true, 'data' => $settings];
            break;

        case 'update_settings':
            if (!isAdmin()) {
                $response = ['success' => false, 'message' => 'Permissions insuffisantes'];
                break;
            }
            $allowedKeys = [
                'discord_link',
                'contact_email',
                'club_charter_url',
                'projects_default_visibility',
                'tasks_default_priority',
                'tasks_allow_multi_assign'
            ];

            $incoming = [];
            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $incoming[$key] = trim((string)$_POST[$key]);
                }
            }

            if ($settingsDAO->setSettings($incoming)) {
                $response = ['success' => true, 'message' => 'Paramètres mis à jour'];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
            }
            break;

        default:
            $response = ['success' => false, 'message' => 'Action non supportée'];
    }
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
}

echo json_encode($response);
