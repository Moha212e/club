<?php
/**
 * Actions d'authentification (connexion/inscription)
 */

// Inclure les dépendances nécessaires
require_once __DIR__ . '/../conf/database.php';
require_once __DIR__ . '/../DAO/UserDAO.php';

// Créer une instance de UserDAO
$userDAO = new UserDAO();
$userDAO->createUsersTable(); // Créer la table si elle n'existe pas

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'register') {
        // Traitement de l'inscription
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);

        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $message = 'Tous les champs sont obligatoires.';
            $messageType = 'error';
        } elseif (!$userDAO->isValidEmail($email)) {
            $message = 'Email invalide.';
            $messageType = 'error';
        } elseif ($password !== $confirmPassword) {
            $message = 'Les mots de passe ne correspondent pas.';
            $messageType = 'error';
        } elseif (!$userDAO->isValidPassword($password)) {
            $message = 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 minuscule et 1 chiffre.';
            $messageType = 'error';
        } else {
            $result = $userDAO->register($username, $email, $password, $firstName, $lastName);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            
            if ($result['success']) {
                // Auto-connexion après inscription
                $loginResult = $userDAO->login($username, $password);
                if ($loginResult['success']) {
                    $_SESSION['user_id'] = $loginResult['user']['id'];
                    $_SESSION['username'] = $loginResult['user']['username'];
                    $_SESSION['role'] = $loginResult['user']['role'];
                    header('Location: ?page=dashboard');
                    exit();
                }
            }
        }
    } elseif ($_POST['action'] === 'login') {
        // Traitement de la connexion
        $username = trim($_POST['login_username']);
        $password = $_POST['login_password'];

        if (empty($username) || empty($password)) {
            $message = 'Nom d\'utilisateur et mot de passe requis.';
            $messageType = 'error';
        } else {
            $result = $userDAO->login($username, $password);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['username'] = $result['user']['username'];
                $_SESSION['role'] = $result['user']['role'];
                header('Location: ?page=dashboard');
                exit();
            }
        }
        
        // Stocker le message d'erreur dans la session avant la redirection
        if (!empty($message)) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $messageType;
            // Debug temporaire
            error_log("Auth error message stored: " . $message);
        }
        
        // Rediriger vers la page de login même en cas d'erreur
        header('Location: ?page=login');
        exit();
    }
}

// Stocker les messages dans la session pour les afficher après redirection (pour l'inscription)
if (!empty($message)) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $messageType;
}
?>