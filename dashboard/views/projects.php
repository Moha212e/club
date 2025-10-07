<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets - HEPL Tech Lab</title>
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
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .page-header { background: white; border-radius: 15px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .create-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; font-weight: 600; }
        .create-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .projects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .project-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .project-card:hover { transform: translateY(-5px); }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #666; }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; color: #ccc; }
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
                <a href="?page=projects" class="active">
                    <i class="fas fa-project-diagram"></i>
                    Projets
                </a>
                <a href="?page=profile">
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
        
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-project-diagram"></i>
                        Mes Projets
                    </h1>
                    <p style="color: #666;">Gérez vos projets et collaborations</p>
                </div>
                <a href="#" class="create-btn">
                    <i class="fas fa-plus"></i>
                    Nouveau projet
                </a>
            </div>
        </div>

        <div class="projects-grid">
            <!-- État vide pour l'instant -->
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-folder-open"></i>
                <h3>Aucun projet pour le moment</h3>
                <p>Commencez par créer votre premier projet !</p>
                <br>
                <a href="#" class="create-btn">
                    <i class="fas fa-rocket"></i>
                    Créer mon premier projet
                </a>
            </div>
        </div>
    </div>
</body>
</html>