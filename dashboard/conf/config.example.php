<?php
// Template de configuration
// Copier ce fichier en config.php et remplacer les valeurs

$host = 'votre-host';
$dbname = 'votre-database';
$username = 'votre-username';
$password = 'votre-password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Erreur de connexion DB : " . $e->getMessage());
    die("Une erreur est survenue. Veuillez contacter l'administrateur.");
}
?>
