<?php
/**
 * Debug pour le routeur principal
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug Routeur Principal</h2>";

// Informations sur la requête
echo "<h3>1. Informations de la requête</h3>";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Query String: " . ($_SERVER['QUERY_STRING'] ?? '(vide)') . "<br>";
echo "GET parameters:<br>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST parameters:<br>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}

// Test des sessions
echo "<h3>2. Sessions</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session data:<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test des inclusions
echo "<h3>3. Test des fichiers</h3>";
$files = [
    'conf/database.php',
    'DAO/UserDAO.php',
    'views/login.php',
    'views/dashboard.php',
    'actions/auth.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file (manquant)<br>";
    }
}

// Test création des classes
echo "<h3>4. Test des classes</h3>";
try {
    require_once 'conf/database.php';
    require_once 'DAO/UserDAO.php';
    
    $userDAO = new UserDAO();
    echo "✅ UserDAO créé<br>";
    
    // Test de connexion
    $testUser = $userDAO->getUserById(1);
    if ($testUser) {
        echo "✅ Test de récupération utilisateur réussi<br>";
    } else {
        echo "⚠️ Aucun utilisateur trouvé avec ID 1<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

echo "<h3>5. Actions disponibles</h3>";
echo "<a href='?'>Page d'accueil</a><br>";
echo "<a href='?page=auth'>Page d'authentification</a><br>";
echo "<a href='?page=dashboard'>Dashboard (nécessite connexion)</a><br>";
echo "<a href='debug-dashboard.php'>Debug dashboard spécifique</a><br>";
echo "<a href='test-userDAO.php'>Test UserDAO</a><br>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
h2, h3 { color: #333; }
pre { background: #e9ecef; padding: 10px; border-radius: 5px; overflow-x: auto; }
a { color: #007bff; }
</style>