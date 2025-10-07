# HEPL Tech Lab - Dashboard

## Installation et Configuration

### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx) ou XAMPP/WAMP pour le développement

### Étapes d'installation

#### 1. Configuration de la base de données
1. Ouvrez phpMyAdmin ou votre gestionnaire MySQL
2. Exécutez le script SQL situé dans `conf/database_setup.sql`
3. Cela créera la base de données `club_tech_lab` et les tables nécessaires

#### 2. Configuration des paramètres
1. Ouvrez le fichier `conf/database.php`
2. Modifiez les paramètres de connexion MySQL selon votre configuration :
   ```php
   define('DB_HOST', 'localhost');     // Votre hôte MySQL
   define('DB_NAME', 'club_tech_lab'); // Nom de la base de données
   define('DB_USER', 'root');          // Votre utilisateur MySQL
   define('DB_PASS', '');              // Votre mot de passe MySQL
   ```
3. Changez la clé secrète pour la sécurité :
   ```php
   define('SECRET_KEY', 'votre_nouvelle_cle_secrete_unique');
   ```

#### 3. Déploiement
1. Placez tous les fichiers dans votre répertoire web (htdocs, www, etc.)
2. Assurez-vous que PHP a les permissions d'écriture si nécessaire
3. Accédez à `http://localhost/club/dashboard/login.php`

### Structure du projet

```
dashboard/
├── conf/
│   ├── database.php          # Configuration de la base de données
│   ├── database_setup.sql    # Script de création de la base
│   └── config.php           # Configuration générale
├── DAO/
│   ├── Database.php         # Classe de connexion singleton
│   └── UserDAO.php          # Gestion des utilisateurs
├── login.php                # Page de connexion/inscription
└── dashboard.php            # Tableau de bord principal
```

### Fonctionnalités

#### Page de connexion/inscription (`login.php`)
- **Inscription** : Création de nouveaux comptes utilisateurs
  - Validation des champs (email, mot de passe fort)
  - Vérification d'unicité (username/email)
  - Hashage sécurisé des mots de passe
- **Connexion** : Authentification des utilisateurs
  - Support username ou email
  - Gestion des sessions sécurisées
- **Design responsive** avec animations

#### Tableau de bord (`dashboard.php`)
- **Profil utilisateur** : Affichage des informations
- **Statistiques** : Projets, collaborations, contributions
- **Actions rapides** : Créer projet, rejoindre Discord, ressources
- **Navigation sécurisée** avec déconnexion

### Sécurité implémentée

1. **Mots de passe** :
   - Hashage avec `password_hash()` et salt
   - Validation forte (8+ caractères, majuscule, minuscule, chiffre)

2. **Base de données** :
   - Requêtes préparées (protection SQL injection)
   - Connexion singleton sécurisée

3. **Sessions** :
   - Configuration sécurisée (httponly, cookies only)
   - Vérification d'authentification sur chaque page

4. **Validation** :
   - Filtrage et validation des entrées
   - Protection XSS avec `htmlspecialchars()`

### Compte administrateur par défaut

- **Username** : `admin`
- **Email** : `admin@hepl-techlab.be`
- **Mot de passe** : `password`

⚠️ **Important** : Changez ce mot de passe après la première connexion !

### Développement futur

La structure est prête pour :
- Gestion des projets
- Système de collaboration
- API REST
- Upload de fichiers
- Système de notifications

### Support et dépannage

1. **Erreur de connexion MySQL** :
   - Vérifiez les paramètres dans `conf/database.php`
   - Assurez-vous que MySQL est démarré
   - Vérifiez les droits d'accès de l'utilisateur

2. **Page blanche** :
   - Activez l'affichage des erreurs PHP
   - Vérifiez les logs d'erreur du serveur
   - Vérifiez les chemins des fichiers inclus

3. **Problème de session** :
   - Vérifiez que le répertoire de session PHP est accessible en écriture
   - Videz le cache du navigateur

### Technologies utilisées

- **Backend** : PHP 7.4+, MySQL, PDO
- **Frontend** : HTML5, CSS3, JavaScript vanilla
- **Sécurité** : Sessions PHP, password hashing, requêtes préparées
- **Design** : CSS Grid/Flexbox, animations, responsive design