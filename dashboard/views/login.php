<?php
// Récupérer les messages flash
$message = '';
$messageType = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - HEPL Tech Lab</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            min-height: 600px;
            display: flex;
        }

        .auth-forms {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-side {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 60px 40px;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        .form-title {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #333;
        }

        .form-subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .switch-form {
            text-align: center;
            margin-top: 20px;
        }

        .switch-form a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .switch-form a:hover {
            text-decoration: underline;
        }

        .message {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .side-content h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .side-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                max-width: 400px;
            }

            .auth-side {
                padding: 40px;
            }

            .auth-forms {
                padding: 40px;
            }
        }

        .input-icon {
            position: relative;
        }

        .input-icon input {
            padding-left: 45px;
        }

        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-forms">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire de connexion -->
            <div class="form-container active" id="loginForm">
                <h2 class="form-title">Connexion</h2>
                <p class="form-subtitle">Accédez à votre espace HEPL Tech Lab</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="login_username">Nom d'utilisateur ou Email</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="login_username" name="login_username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="login_password">Mot de passe</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="login_password" name="login_password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>
                
                <div class="switch-form">
                    <p>Pas encore de compte ? <a href="#" onclick="switchToRegister()">S'inscrire</a></p>
                </div>
            </div>

            <!-- Formulaire d'inscription -->
            <div class="form-container" id="registerForm">
                <h2 class="form-title">Inscription</h2>
                <p class="form-subtitle">Rejoignez la communauté HEPL Tech Lab</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="first_name">Prénom</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="last_name">Nom</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <div class="input-icon">
                            <i class="fas fa-at"></i>
                            <input type="text" id="username" name="username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <small style="color: #666; font-size: 12px;">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                </form>
                
                <div class="switch-form">
                    <p>Déjà un compte ? <a href="#" onclick="switchToLogin()">Se connecter</a></p>
                </div>
            </div>
        </div>

        <div class="auth-side">
            <div class="side-content">
                <div class="logo">
                    <i class="fas fa-code"></i>
                    HEPL Tech Lab
                </div>
                <h2>Bienvenue !</h2>
                <p>Rejoignez notre communauté d'étudiants passionnés de technologie. Développez vos compétences, travaillez sur des projets innovants et connectez-vous avec d'autres développeurs.</p>
            </div>
        </div>
    </div>

    <script>
        function switchToRegister() {
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('registerForm').classList.add('active');
        }

        function switchToLogin() {
            document.getElementById('registerForm').classList.remove('active');
            document.getElementById('loginForm').classList.add('active');
        }

        // Validation du mot de passe en temps réel
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (regex.test(password)) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        });

        // Validation de la confirmation du mot de passe
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            if (password === confirmPassword && password.length > 0) {
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        });
    </script>
</body>
</html>