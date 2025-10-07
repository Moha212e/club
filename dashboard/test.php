<?php
/**
 * Script de test pour vérifier les chemins et la connexion DB
 */

echo "<h2>Test des chemins et connexion DB</h2>";

// Test 1: Vérifier si les fichiers existent
echo "<h3>1. Vérification des fichiers</h3>";
$files = [
    'conf/database.php',
    'DAO/Database.php', 
    'DAO/UserDAO.php'
];

foreach($files as $file) {
    if(file_exists($file)) {
        echo "✅ $file - OK<br>";
    } else {
        echo "❌ $file - MANQUANT<br>";
    }
}

// Test 2: Inclusion des fichiers
echo "<h3>2. Test d'inclusion</h3>";
try {
    require_once 'conf/database.php';
    echo "✅ conf/database.php - OK<br>";
    
    require_once 'DAO/UserDAO.php';
    echo "✅ DAO/UserDAO.php - OK<br>";
    
} catch(Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

// Test 3: Connexion DB
echo "<h3>3. Test connexion base de données</h3>";
try {
    $userDAO = new UserDAO();
    echo "✅ Connexion DB - OK<br>";
    echo "✅ UserDAO créé - OK<br>";
} catch(Exception $e) {
    echo "❌ Erreur DB: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Informations</h3>";
echo "Répertoire actuel: " . getcwd() . "<br>";
echo "Script: " . __FILE__ . "<br>";
echo "Répertoire du script: " . __DIR__ . "<br>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>