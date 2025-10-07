# Système de Routage - HEPL Tech Lab Dashboard

## 🚀 Installation Rapide

### 1. Base de données
Exécutez le script `conf/database_setup.sql` dans phpMyAdmin.

### 2. Configuration
Vérifiez `conf/database.php` - normalement déjà configuré pour `club_database`.

### 3. Accès
Allez sur : `http://localhost/club/dashboard/index.php`

## 📋 URLs Disponibles

### Publiques (sans connexion)
- `?page=` ou `?page=auth` → Page de connexion/inscription

### Privées (connexion requise)  
- `?page=dashboard` → Tableau de bord principal
- `?page=profile` → Profil utilisateur
- `?page=projects` → Gestion des projets

### Actions
- Déconnexion : POST avec `action=logout`

## 🔒 Sécurité Intégrée

✅ **URLs propres** - Pas de noms de fichiers visibles
✅ **Contrôle d'accès** - Vérification automatique des sessions  
✅ **Protection CSRF** - Actions sécurisées
✅ **Routage centralisé** - Point d'entrée unique

## 🎯 Fonctionnement

1. **index.php** = Point d'entrée unique
2. **Router class** = Gestion des routes et sécurité
3. **views/** = Pages d'affichage
4. **actions/** = Traitements POST

**Exemple d'URL sécurisée :**
- Avant : `dashboard.php` 
- Maintenant : `?page=dashboard`

## 📁 Fichiers Essentiels

```
dashboard/
├── index.php              # Point d'entrée + routeur
├── conf/database.php      # Configuration BDD
├── DAO/UserDAO.php        # Gestion utilisateurs
├── views/
│   ├── login.php         # Connexion/inscription
│   └── dashboard.php     # Tableau de bord
└── actions/
    ├── auth.php          # Traitement connexion
    └── logout.php        # Déconnexion
```

## ✅ Test Rapide

1. Allez sur `index.php`
2. Connectez-vous avec `admin` / `password`
3. Vous serez sur `?page=dashboard`
4. Les URLs sont propres et sécurisées !