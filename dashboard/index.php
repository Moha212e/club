<?php
/**
 * Système de routage sécurisé - HEPL Tech Lab
 * Point d'entrée unique pour toutes les pages du dashboard
 */

require_once 'conf/database.php';
require_once 'DAO/UserDAO.php';
require_once 'DAO/TaskDAO.php';
require_once 'DAO/ProjectDAO.php';
require_once 'DAO/SettingsDAO.php';
require_once 'DAO/EventsDAO.php';
require_once 'DAO/AnnouncementsDAO.php';

class Router {
    private $routes = [];
    private $userDAO;
    
    public function __construct() {
        $this->userDAO = new UserDAO();
        $this->userDAO->createUsersTable();
        
        // Créer la table des tâches
        $taskDAO = new TaskDAO();
        $taskDAO->createTasksTable();
        
        // Créer la table des projets
        $projectDAO = new ProjectDAO();
        $projectDAO->createProjectsTable();
        
        // Créer la table des paramètres
        $settingsDAO = new SettingsDAO();
        $settingsDAO->createSettingsTable();
        
        // Créer la table des événements
        $eventsDAO = new EventsDAO();
        $eventsDAO->createEventsTable();
        
        // Créer la table des annonces
        $annDAO = new AnnouncementsDAO();
        $annDAO->createAnnouncementsTable();
        
        $this->setupRoutes();
    }
    
    /**
     * Configuration des routes disponibles
     */
    private function setupRoutes() {
        // Routes publiques (accessibles sans connexion)
        $this->routes['public'] = [
            '' => 'views/login.php',           // Page par défaut
            'auth' => 'views/login.php',       // Authentification
            'login' => 'views/login.php',      // Connexion
            'register' => 'views/login.php'    // Inscription
        ];
        
        // Routes privées (nécessitent une connexion)
        $this->routes['private'] = [
            'dashboard' => 'views/dashboard.php',    // Tableau de bord
            'profile' => 'views/profile.php',        // Profil utilisateur
            'projects' => 'views/projects.php',      // Gestion projets
            'settings' => 'views/settings.php',      // Paramètres
            'admin' => 'views/admin.php'             // Administration
        ];
        
        // Routes d'action (POST uniquement)
        $this->routes['actions'] = [
            'logout' => 'actions/logout.php',
            'update-profile' => 'actions/update_profile.php',
            'create-project' => 'actions/create_project.php'
        ];
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    private function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Vérifier si l'utilisateur est admin
     */
    private function isAdmin() {
        return $this->isAuthenticated() && 
               isset($_SESSION['role']) && 
               $_SESSION['role'] === 'admin';
    }
    
    /**
     * Obtenir l'utilisateur connecté
     */
    private function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $this->userDAO->getUserById($_SESSION['user_id']);
    }
    
    /**
     * Gérer la requête et router vers la bonne page
     */
    public function handleRequest() {
        // Récupérer la route demandée
        $route = $_GET['page'] ?? '';
        $route = $this->sanitizeRoute($route);
        
        // Gestion des actions POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            return $this->handleAction($_POST['action']);
        }
        
        // Vérifier si c'est une route publique
        if (array_key_exists($route, $this->routes['public'])) {
            // Si déjà connecté, rediriger vers le dashboard
            if ($this->isAuthenticated() && $route !== '') {
                $this->redirect('dashboard');
                return;
            }
            return $this->loadView($this->routes['public'][$route], 'public');
        }
        
        // Vérifier si c'est une route privée
        if (array_key_exists($route, $this->routes['private'])) {
            // Vérifier l'authentification
            if (!$this->isAuthenticated()) {
                $this->redirect('auth');
                return;
            }
            
            // Vérifier les permissions admin si nécessaire
            if ($route === 'admin' && !$this->isAdmin()) {
                $this->showError('403', 'Accès refusé - Privilèges administrateur requis');
                return;
            }
            
            return $this->loadView($this->routes['private'][$route], 'private');
        }
        
        // Route non trouvée
        $this->showError('404', 'Page non trouvée');
    }
    
    /**
     * Gérer les actions POST
     */
    private function handleAction($action) {
        // Actions publiques
        if (in_array($action, ['login', 'register'])) {
            require_once 'actions/auth.php';
            return;
        }
        
        // Actions privées - vérifier l'authentification
        if (!$this->isAuthenticated()) {
            $this->redirect('auth');
            return;
        }
        
        // Vérifier si l'action existe
        if (array_key_exists($action, $this->routes['actions'])) {
            require_once $this->routes['actions'][$action];
            return;
        }
        
        $this->showError('400', 'Action non reconnue');
    }
    
    /**
     * Charger une vue avec les données nécessaires
     */
    private function loadView($viewPath, $type) {
        // Données communes à toutes les vues
        $data = [
            'currentRoute' => $_GET['page'] ?? '',
            'isAuthenticated' => $this->isAuthenticated(),
            'isAdmin' => $this->isAdmin(),
            'user' => $this->getCurrentUser(),
            'userDAO' => $this->userDAO
        ];
        
        // Vérifier que le fichier existe
        if (!file_exists($viewPath)) {
            $this->showError('500', 'Vue non trouvée: ' . $viewPath);
            return;
        }
        
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);
        
        // Inclure la vue
        require_once $viewPath;
    }
    
    /**
     * Nettoyer et valider la route
     */
    private function sanitizeRoute($route) {
        // Supprimer les caractères dangereux
        $route = preg_replace('/[^a-zA-Z0-9\-_]/', '', $route);
        return strtolower($route);
    }
    
    /**
     * Redirection sécurisée
     */
    public function redirect($page, $params = []) {
        $url = $_SERVER['PHP_SELF'] . '?page=' . urlencode($page);
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Afficher une page d'erreur
     */
    private function showError($code, $message) {
        http_response_code((int)$code);
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erreur <?php echo htmlspecialchars($code); ?> - HEPL Tech Lab</title>
            <style>
                body { font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; }
                .error-container { background: rgba(255,255,255,0.1); padding: 3rem; border-radius: 20px; backdrop-filter: blur(10px); }
                h1 { font-size: 4rem; margin: 0; }
                p { font-size: 1.2rem; margin: 1rem 0; }
                a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 1rem 2rem; border-radius: 10px; display: inline-block; margin-top: 1rem; }
                a:hover { background: rgba(255,255,255,0.3); }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1><?php echo htmlspecialchars($code); ?></h1>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="?page=<?php echo $this->isAuthenticated() ? 'dashboard' : 'auth'; ?>">
                    <?php echo $this->isAuthenticated() ? 'Retour au tableau de bord' : 'Retour à la connexion'; ?>
                </a>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Générer une URL sécurisée
     */
    public static function url($page, $params = []) {
        $url = $_SERVER['PHP_SELF'] . '?page=' . urlencode($page);
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        
        return $url;
    }
}

// Initialiser et démarrer le routeur
$router = new Router();
$router->handleRequest();
?>