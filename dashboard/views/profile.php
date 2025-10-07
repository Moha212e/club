<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - HEPL Tech Lab</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f6fa; color: #333; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-content { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-size: 1.5rem; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 10px; transition: background 0.3s; display: flex; align-items: center; gap: 0.5rem; }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.2); }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 2rem; }
        .profile-card { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .profile-header { text-align: center; margin-bottom: 2rem; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; margin: 0 auto 1rem; }
        .back-btn { background: #6c757d; color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; margin-bottom: 2rem; }
        .back-btn:hover { background: #5a6268; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="logo">
                <i class="fas fa-code"></i>
                HEPL Tech Lab
            </div>
            <div class="nav-links">
                <a href="?page=dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="?page=projects">
                    <i class="fas fa-project-diagram"></i>
                    Projets
                </a>
                <a href="?page=profile" class="active">
                    <i class="fas fa-user"></i>
                    Profil
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <a href="?page=dashboard" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Retour au dashboard
        </a>
        
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p style="color: #666;">@<?php echo htmlspecialchars($user['username']); ?></p>
            </div>
            
            <div style="display: grid; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                    <strong>Rôle:</strong>
                    <span><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                    <strong>Membre depuis:</strong>
                    <span><?php echo date('d/m/Y à H:i', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>