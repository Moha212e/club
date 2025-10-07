<?php
/**
 * Debug pour voir les erreurs du dashboard
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug Dashboard</h2>";

// Test 1: Vérifier les sessions
echo "<h3>1. Test des sessions</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Données de session:<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test 2: Simuler l'authentification
echo "<h3>2. Test d'authentification simulée</h3>";
if (!isset($_SESSION['user_id'])) {
    echo "❌ Pas d'utilisateur connecté<br>";
    echo "Simulation d'une connexion...<br>";
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'test';
    $_SESSION['role'] = 'student';
    echo "✅ Session simulée créée<br>";
} else {
    echo "✅ Utilisateur connecté: " . $_SESSION['username'] . "<br>";
}

// Test 3: Inclure les dépendances
echo "<h3>3. Test des inclusions</h3>";
try {
    require_once 'conf/database.php';
    echo "✅ database.php - OK<br>";
    
    require_once 'DAO/UserDAO.php';
    echo "✅ UserDAO.php - OK<br>";
    
    $userDAO = new UserDAO();
    echo "✅ UserDAO créé - OK<br>";
    
} catch(Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}

// Test 4: Récupérer l'utilisateur
echo "<h3>4. Test récupération utilisateur</h3>";
try {
    if (isset($userDAO) && isset($_SESSION['user_id'])) {
        $user = $userDAO->getUserById($_SESSION['user_id']);
        if ($user) {
            echo "✅ Utilisateur trouvé: " . $user['username'] . "<br>";
            echo "Données utilisateur:<br>";
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        } else {
            echo "❌ Utilisateur non trouvé avec ID: " . $_SESSION['user_id'] . "<br>";
        }
    } else {
        echo "❌ UserDAO ou session manquante<br>";
    }
} catch(Exception $e) {
    echo "❌ Erreur récupération utilisateur: " . $e->getMessage() . "<br>";
}

// Test 5: Inclure la vue dashboard
echo "<h3>5. Test de la vue dashboard</h3>";
try {
    if (isset($user) && $user) {
        $currentRoute = 'dashboard';
        $isAuthenticated = true;
        $isAdmin = ($user['role'] === 'admin');
        
        echo "Variables pour la vue:<br>";
        echo "• currentRoute: $currentRoute<br>";
        echo "• isAuthenticated: " . ($isAuthenticated ? 'true' : 'false') . "<br>";
        echo "• isAdmin: " . ($isAdmin ? 'true' : 'false') . "<br>";
        echo "• user: " . $user['username'] . "<br><br>";
        
        echo "🎯 <strong>Tentative d'inclusion de la vue dashboard...</strong><br><br>";
        
        // Inclure la vue
        if (file_exists('views/dashboard.php')) {
            echo "✅ Fichier views/dashboard.php trouvé<br>";
            echo "<hr>";
            include 'views/dashboard.php';
        } else {
            echo "❌ Fichier views/dashboard.php non trouvé<br>";
        }
    } else {
        echo "❌ Pas d'utilisateur valide pour la vue<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Erreur vue dashboard: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
h2, h3 { color: #333; }
pre { background: #e9ecef; padding: 10px; border-radius: 5px; overflow-x: auto; }
hr { margin: 20px 0; border: 1px solid #dee2e6; }
</style>