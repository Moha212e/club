<?php
require_once __DIR__ . '/../conf/database.php';

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        try {
            // Configuration pour AlwaysData
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Test de connexion
            $this->connection->query('SELECT 1');
            
        } catch(PDOException $e) {
            $this->showConnectionError($e->getMessage());
        }
    }
    
    private function showConnectionError($errorMessage) {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Erreur de connexion - HEPL Tech Lab</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
                .error-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-title { color: #dc3545; font-size: 24px; margin-bottom: 20px; }
                .error-details { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545; }
                .solution { background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745; }
                .config-info { background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 15px 0; }
                code { background: #f1f3f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1 class="error-title">🔴 Erreur de connexion à AlwaysData</h1>
                
                <div class="error-details">
                    <strong>Erreur MySQL :</strong><br>
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
                
                <div class="config-info">
                    <strong>Configuration AlwaysData :</strong><br>
                    • Hôte : <code><?php echo DB_HOST; ?></code><br>
                    • Base de données : <code><?php echo DB_NAME; ?></code><br>
                    • Utilisateur : <code><?php echo DB_USER; ?></code><br>
                    • Mot de passe : <code><?php echo empty(DB_PASS) ? '(vide)' : '(configuré)'; ?></code>
                </div>
                
                <div class="solution">
                    <strong>🛠️ Solutions pour AlwaysData :</strong><br><br>
                    
                    <strong>1. Vérifiez vos identifiants AlwaysData :</strong><br>
                    • Connectez-vous à votre panel AlwaysData<br>
                    • Vérifiez les paramètres de connexion MySQL<br>
                    • Assurez-vous que la base <code><?php echo DB_NAME; ?></code> existe<br><br>
                    
                    <strong>2. Vérifiez les permissions :</strong><br>
                    • L'utilisateur <code><?php echo DB_USER; ?></code> doit avoir accès à la base<br>
                    • Vérifiez que le mot de passe est correct<br><br>
                    
                    <strong>3. Créez la base de données :</strong><br>
                    • Si la base n'existe pas, créez-la depuis le panel AlwaysData<br>
                    • Ou exécutez le script <code>conf/database_setup.sql</code> via phpMyAdmin<br><br>
                    
                    <strong>4. Testez la connexion :</strong><br>
                    • <a href="test.php">🧪 Script de test</a><br>
                    • <a href="conf/config.php" target="_blank">✅ Test de config simple</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Empêcher le clonage
    private function __clone() {}

    // Empêcher la désérialisation
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>