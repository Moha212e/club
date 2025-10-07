<?php
/**
 * Action de déconnexion
 */

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header('Location: ?page=auth');
exit();
?>