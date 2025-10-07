<?php
/**
 * Version simplifiée pour diagnostic
 */

// Activer les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer les sessions
session_start();

echo "<h1>🔍 Diagnostic de connexion</h1>";

// Test si on arrive ici
echo "<p>✅ PHP fonctionne</p>";

// Inclure les fichiers nécessaires
try {
    require_once 'conf/database.php';
    echo "<p>✅ Configuration database chargée</p>";
    
    require_once 'DAO/UserDAO.php';
    echo "<p>✅ UserDAO chargé</p>";
    
    $userDAO = new UserDAO();
    echo "<p>✅ UserDAO instancié</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}

// Vérifier les routes
$page = $_GET['page'] ?? '';
echo "<p>📍 Page demandée: '$page'</p>";

// Vérifier l'authentification
$isAuth = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
echo "<p>🔐 Authentifié: " . ($isAuth ? 'OUI' : 'NON') . "</p>";

if ($isAuth) {
    echo "<p>👤 ID utilisateur: " . $_SESSION['user_id'] . "</p>";
    echo "<p>👤 Username: " . ($_SESSION['username'] ?? 'non défini') . "</p>";
}

// Traitement simple des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    echo "<h2>📝 Traitement POST</h2>";
    
    if ($_POST['action'] === 'login') {
        $username = trim($_POST['login_username'] ?? '');
        $password = $_POST['login_password'] ?? '';
        
        echo "<p>🔑 Tentative de connexion pour: " . htmlspecialchars($username) . "</p>";
        
        if (!empty($username) && !empty($password)) {
            try {
                $result = $userDAO->login($username, $password);
                echo "<p>📊 Résultat login: " . ($result['success'] ? 'SUCCÈS' : 'ÉCHEC') . "</p>";
                echo "<p>💬 Message: " . htmlspecialchars($result['message']) . "</p>";
                
                if ($result['success']) {
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['username'] = $result['user']['username'];
                    $_SESSION['role'] = $result['user']['role'];
                    
                    echo "<p>✅ Session créée. Redirection vers dashboard...</p>";
                    echo "<script>setTimeout(() => location.href = '?page=dashboard', 2000);</script>";
                } else {
                    $_SESSION['flash_message'] = $result['message'];
                    $_SESSION['flash_type'] = 'error';
                }
            } catch (Exception $e) {
                echo "<p>❌ Erreur de connexion: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    if ($_POST['action'] === 'register') {
        echo "<p>📝 Tentative d'inscription...</p>";
        // Logique d'inscription simplifiée
    }
}

// Routage simple
if ($page === 'dashboard') {
    if ($isAuth) {
        echo "<h2>🎯 Chargement du dashboard</h2>";
        try {
            $user = $userDAO->getUserById($_SESSION['user_id']);
            if ($user) {
                echo "<p>✅ Utilisateur trouvé: " . htmlspecialchars($user['username']) . "</p>";
                echo "<p>🚀 <a href='debug-dashboard.php'>Voir le dashboard debug</a></p>";
                
                // Variables pour la vue
                $currentRoute = 'dashboard';
                $isAuthenticated = true;
                $isAdmin = ($user['role'] === 'admin');
                
                // Inclure la vue dashboard
                if (file_exists('views/dashboard.php')) {
                    include 'views/dashboard.php';
                } else {
                    echo "<p>❌ Fichier dashboard.php non trouvé</p>";
                }
            } else {
                echo "<p>❌ Utilisateur non trouvé</p>";
                session_destroy();
                echo "<script>location.href = '?page=auth';</script>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Erreur dashboard: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p>🔒 Redirection vers la connexion...</p>";
        echo "<script>location.href = '?page=auth';</script>";
    }
} else {
    // Page de connexion
    echo "<h2>🔑 Page de connexion</h2>";
    
    // Messages flash
    $message = '';
    $messageType = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $messageType = $_SESSION['flash_type'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
    
    if ($message) {
        echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background: " . 
             ($messageType === 'success' ? '#d4edda' : '#f8d7da') . ";'>" . 
             htmlspecialchars($message) . "</div>";
    }
    
    // Inclure la vue login
    if (file_exists('views/login.php')) {
        include 'views/login.php';
    } else {
        echo "<p>❌ Fichier login.php non trouvé</p>";
    }
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
p { margin: 5px 0; }
</style>