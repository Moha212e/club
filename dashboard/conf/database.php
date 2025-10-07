<?php
// Configuration de la base de données - AlwaysData
define('DB_HOST', 'mysql-club.alwaysdata.net');
define('DB_NAME', 'club_database');
define('DB_USER', 'club');
define('DB_PASS', 'club2025..');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Configurations alternatives pour développement local
// Décommentez ces lignes pour tester en local :
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// Configuration de sécurité
define('SECRET_KEY', 'votre_cle_secrete_changez_moi');
define('SALT', 'tech_lab_salt_2025');

// Configuration des sessions (ne modifier que si la session n'est pas encore démarrée)
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Mettre à 1 en HTTPS
    session_start();
}
?>