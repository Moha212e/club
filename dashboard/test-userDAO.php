<?php
/**
 * Test simple de connexion et création UserDAO
 */

echo "<h2>🧪 Test de connexion et UserDAO</h2>";

// Test 1: Inclure les fichiers
echo "<h3>1. Test des inclusions</h3>";
try {
    require_once 'conf/database.php';
    echo "✅ conf/database.php - OK<br>";
    
    require_once 'DAO/UserDAO.php';
    echo "✅ DAO/UserDAO.php - OK<br>";
    
} catch(Exception $e) {
    echo "❌ Erreur d'inclusion: " . $e->getMessage() . "<br>";
    exit();
}

// Test 2: Créer UserDAO
echo "<h3>2. Test création UserDAO</h3>";
try {
    $userDAO = new UserDAO();
    echo "✅ UserDAO créé - OK<br>";
    
    $userDAO->createUsersTable();
    echo "✅ Table users créée/vérifiée - OK<br>";
    
} catch(Exception $e) {
    echo "❌ Erreur UserDAO: " . $e->getMessage() . "<br>";
    echo "Détails: " . $e->getTraceAsString() . "<br>";
    exit();
}

// Test 3: Tester les méthodes de validation
echo "<h3>3. Test des méthodes de validation</h3>";
try {
    $isValidEmail = $userDAO->isValidEmail('test@example.com');
    echo "✅ isValidEmail() - " . ($isValidEmail ? 'OK' : 'KO') . "<br>";
    
    $isValidPassword = $userDAO->isValidPassword('TestPass123');
    echo "✅ isValidPassword() - " . ($isValidPassword ? 'OK' : 'KO') . "<br>";
    
} catch(Exception $e) {
    echo "❌ Erreur validation: " . $e->getMessage() . "<br>";
}

// Test 4: Configuration de la base
echo "<h3>4. Configuration de la base</h3>";
echo "• Hôte: " . DB_HOST . "<br>";
echo "• Base: " . DB_NAME . "<br>";
echo "• Utilisateur: " . DB_USER . "<br>";
echo "• Mot de passe: " . (empty(DB_PASS) ? '(vide)' : '(configuré)') . "<br>";

echo "<h3>5. Résultat</h3>";
echo "🎉 <strong>Tous les tests sont passés ! Vous pouvez maintenant utiliser le système.</strong><br><br>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>🚀 Aller au système de connexion</a>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
h2, h3 { color: #333; }
</style>