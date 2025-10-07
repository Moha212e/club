# 📋 RAPPORT COMPLET DES FONCTIONNALITÉS
## Club Management System - HEPL Tech Lab

**Date de génération** : Octobre 2025  
**Version** : 1.0  
**Développé pour** : HEPL Tech Lab

---

## 🏗️ ARCHITECTURE DU PROJET

### Structure MVC
- **Modèle** : DAO (Data Access Objects) pour la gestion de la base de données
- **Vue** : Templates PHP dans `/views/`
- **Contrôleur** : Routeur central (`index.php`) + Actions dans `/actions/`

### Technologies utilisées
- **Backend** : PHP (POO)
- **Frontend** : HTML5, Tailwind CSS (CDN), JavaScript Vanilla, Feather Icons
- **Base de données** : MySQL avec PDO
- **Sécurité** : Sessions PHP, Hashing de mots de passe avec SALT
- **Hébergement** : AlwaysData

### Structure des fichiers
```
club/
├── dashboard/
│   ├── actions/           # Endpoints API pour les opérations CRUD
│   │   ├── auth.php       # Authentification (login/register)
│   │   ├── members.php    # Gestion des membres
│   │   ├── projects.php   # Gestion des projets
│   │   ├── tasks.php      # Gestion des tâches
│   │   ├── events.php     # Gestion des événements
│   │   ├── announcements.php # Gestion des annonces
│   │   ├── settings.php   # Gestion des paramètres
│   │   └── logout.php     # Déconnexion
│   ├── DAO/              # Data Access Objects
│   │   ├── Database.php   # Singleton de connexion DB
│   │   ├── UserDAO.php    # CRUD utilisateurs
│   │   ├── ProjectDAO.php # CRUD projets
│   │   ├── TaskDAO.php    # CRUD tâches
│   │   ├── EventsDAO.php  # CRUD événements
│   │   ├── AnnouncementsDAO.php # CRUD annonces
│   │   └── SettingsDAO.php # CRUD paramètres
│   ├── conf/             # Configuration
│   │   ├── database.php   # Connexion PDO
│   │   ├── database_setup.sql # Script SQL complet
│   │   └── config.php     # Configuration générale
│   ├── views/            # Templates
│   │   ├── dashboard.php  # Dashboard principal (SPA)
│   │   ├── login.php      # Page de connexion/inscription
│   │   └── profile.php    # Profil utilisateur
│   └── index.php         # Point d'entrée & routeur
├── index.html            # Site public
├── css/                  # Styles globaux
└── js/                   # Scripts globaux
```

---

## 🗄️ BASE DE DONNÉES

### Tables créées (8 tables)

#### 1. **users** - Gestion des utilisateurs
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```
- **Rôles** : `student` | `admin`
- **Index** : username, email, role
- **Fonctionnalités** : Activation/Désactivation des comptes

#### 2. **projects** - Gestion des projets
```sql
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    owner_id INT NOT NULL,
    status ENUM('planning', 'active', 'completed', 'on_hold', 'cancelled') DEFAULT 'planning',
    visibility ENUM('private', 'public') DEFAULT 'private',
    start_date DATE NULL,
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);
```
- **Status** : `planning` | `active` | `completed` | `on_hold` | `cancelled`
- **Visibilité** : `private` (admin uniquement) | `public` (tous)
- **Clé étrangère** : owner_id → users(id) ON DELETE CASCADE

#### 3. **project_members** - Membres des projets
```sql
CREATE TABLE project_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member', 'moderator', 'owner') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_user (project_id, user_id)
);
```
- **Rôles projet** : `member` | `moderator` | `owner`
- Permet de gérer les équipes de projets

#### 4. **tasks** - Gestion des tâches
```sql
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    assigned_to INT,
    created_by INT NOT NULL,
    project_id INT NOT NULL,
    due_date DATETIME,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```
- **Status** : `pending` | `in_progress` | `completed` | `cancelled`
- **Priorités** : `low` | `medium` | `high` | `urgent`
- **IMPORTANT** : Chaque tâche DOIT être liée à un projet (project_id NOT NULL)
- **CASCADE** : Si un projet est supprimé, toutes ses tâches le sont aussi

#### 5. **task_assignments** - Assignations multiples
```sql
CREATE TABLE task_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_task_user (task_id, user_id)
);
```
- Permet d'assigner **plusieurs personnes** à une même tâche
- Complète le champ `assigned_to` de la table `tasks`

#### 6. **events** - Gestion des événements
```sql
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    location VARCHAR(200),
    start_date DATE NOT NULL,
    end_date DATE NULL,
    visibility ENUM('public','private') DEFAULT 'public',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```
- **Visibilité** : `public` | `private`
- **Dates** : Format DATE uniquement (pas d'heures)
- Classification automatique : "À venir" vs "Passés"

#### 7. **announcements** - Gestion des annonces
```sql
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    visibility ENUM('public','private') DEFAULT 'public',
    pinned BOOLEAN DEFAULT FALSE,
    publish_date DATE NOT NULL,
    expire_date DATE NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```
- **Visibilité** : `public` | `private`
- **Épinglage** : Les annonces importantes peuvent être épinglées
- **Dates** : publish_date (obligatoire), expire_date (optionnel)

#### 8. **settings** - Paramètres généraux (clé/valeur)
```sql
CREATE TABLE settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```
- **Paramètres disponibles** :
  - `discord_link` : Lien Discord du club
  - `contact_email` : Email de contact
  - `club_charter_url` : URL de la charte du club
  - `default_project_visibility` : Visibilité par défaut des projets
  - `default_task_priority` : Priorité par défaut des tâches
  - `allow_multi_assignment` : Autoriser les assignations multiples

---

## 🔐 SYSTÈME D'AUTHENTIFICATION

### Fonctionnalités d'inscription
✅ **Endpoint** : `POST /actions/auth.php?action=register`
✅ **Champs requis** :
- Username (unique)
- Email (unique, validé)
- Password (validation stricte)
- Confirm Password
- First Name
- Last Name

✅ **Validation** :
- Email valide (regex)
- Mot de passe fort : min 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre
- Confirmation du mot de passe
- Unicité du username et email

✅ **Sécurité** :
- Hash avec `password_hash()` + SALT unique par utilisateur
- Protection contre les injections SQL (requêtes préparées PDO)

✅ **Comportement** :
- Auto-connexion après inscription réussie
- Redirection vers le dashboard
- Messages flash de succès/erreur

### Fonctionnalités de connexion
✅ **Endpoint** : `POST /actions/auth.php?action=login`
✅ **Modes de connexion** :
- Par username
- Par email
- Les deux sont acceptés

✅ **Vérifications** :
- Compte actif (is_active = TRUE)
- Mot de passe correct
- Utilisateur existe

✅ **Session créée** :
```php
$_SESSION['user_id']
$_SESSION['username']
$_SESSION['email']
$_SESSION['role']  // 'admin' ou 'student'
$_SESSION['first_name']
$_SESSION['last_name']
```

✅ **Messages flash** :
- Succès : "Connexion réussie"
- Erreurs : "Identifiants incorrects", "Compte désactivé", etc.

### Déconnexion
✅ **Endpoint** : `POST /actions/logout.php`
✅ **Comportement** :
- Destruction complète de la session
- Redirection vers la page de login
- Nettoyage des cookies de session

---

## 👥 GESTION DES MEMBRES

### Interface - Section "Membres" (Admin uniquement)

#### Statistiques (4 cartes avec dégradés colorés)
1. 📊 **Total membres** (Bleu)
   - Compte tous les utilisateurs (actifs + inactifs)
   - Icône : users
   - Effet hover : scale + ombre bleue

2. ✅ **Membres actifs** (Vert)
   - Utilisateurs avec is_active = TRUE
   - Icône : user-check
   - Effet hover : scale + ombre verte

3. 🛡️ **Administrateurs** (Violet)
   - Compte des utilisateurs avec role = 'admin'
   - Icône : shield
   - Effet hover : scale + ombre violette

4. 📚 **Étudiants** (Orange)
   - Compte des utilisateurs avec role = 'student'
   - Icône : book
   - Effet hover : scale + ombre orange

#### Liste des membres
✅ **Affichage** : Tableau avec colonnes
- Nom complet (First Name + Last Name)
- Email
- Username
- Rôle (badge coloré)
- Statut (badge coloré : Actif/Inactif)
- Actions (icônes)

✅ **Filtres** :
- Par rôle : Tous | Admin | Student
- Par statut : Tous | Actifs | Inactifs
- Bouton "Actualiser"

✅ **Actions par ligne** :
- ✏️ Modifier
- 🔄 Toggle statut (Activer/Désactiver)
- 🗑️ Supprimer
- 🔑 Réinitialiser le mot de passe

### API - Endpoints membres

#### 1. Récupérer tous les membres
```
POST /actions/members.php
Body: { "action": "get_all_users" }
Response: { "success": true, "users": [...] }
```

#### 2. Récupérer les statistiques
```
POST /actions/members.php
Body: { "action": "get_stats" }
Response: { 
    "success": true, 
    "data": { 
        "total": 10, 
        "active": 8, 
        "admins": 2, 
        "students": 8 
    } 
}
```

#### 3. Créer un membre
```
POST /actions/members.php
Body: { 
    "action": "create_member",
    "username": "john_doe",
    "email": "john@example.com",
    "password": "SecurePass123",
    "first_name": "John",
    "last_name": "Doe",
    "role": "student"
}
Response: { "success": true, "message": "Membre créé avec succès" }
```

#### 4. Modifier un membre
```
POST /actions/members.php
Body: { 
    "action": "update_member",
    "user_id": 5,
    "first_name": "John",
    "last_name": "Smith",
    "email": "john.smith@example.com",
    "role": "admin"
}
Response: { "success": true, "message": "Membre mis à jour" }
```

#### 5. Changer le rôle
```
POST /actions/members.php
Body: { 
    "action": "update_role",
    "user_id": 5,
    "new_role": "admin"
}
Response: { "success": true, "message": "Rôle mis à jour" }
```

#### 6. Toggle statut (Activer/Désactiver)
```
POST /actions/members.php
Body: { 
    "action": "toggle_status",
    "user_id": 5
}
Response: { "success": true, "message": "Statut mis à jour", "new_status": true }
```

#### 7. Supprimer un membre (soft delete)
```
POST /actions/members.php
Body: { 
    "action": "delete_member",
    "user_id": 5
}
Response: { "success": true, "message": "Membre désactivé" }
```

#### 8. Réinitialiser le mot de passe
```
POST /actions/members.php
Body: { 
    "action": "reset_password",
    "user_id": 5,
    "new_password": "NewSecurePass123"
}
Response: { "success": true, "message": "Mot de passe réinitialisé" }
```

### Permissions
- ⛔ **Section entière** : ADMIN UNIQUEMENT
- 🚫 **Protections** :
  - Impossible de modifier son propre rôle
  - Impossible de se supprimer soi-même
  - Impossible de se désactiver soi-même

---

## 📁 GESTION DES PROJETS

### Interface - Section "Projets"

#### Statistiques (4 cartes avec dégradés colorés)
1. 🗂️ **Total projets** (Cyan)
   - Nombre total de projets
   - Icône : folder
   - Effet hover : scale + ombre cyan

2. ▶️ **Projets actifs** (Vert)
   - Projets avec status = 'active'
   - Icône : play
   - Effet hover : scale + ombre verte

3. ✅ **Projets terminés** (Violet)
   - Projets avec status = 'completed'
   - Icône : check-circle
   - Effet hover : scale + ombre violette

4. ⚠️ **En retard** (Rouge)
   - Projets dont due_date < aujourd'hui ET status != 'completed'
   - Icône : alert-triangle
   - Effet hover : scale + ombre rouge

#### Grille de projets
✅ **Affichage** : Cartes en grille responsive
- Titre du projet
- Description (tronquée)
- Badge de statut (coloré)
- Badge de visibilité (Public/Privé)
- Dates (début/fin)
- Propriétaire
- Actions (admin uniquement)

✅ **Filtres** :
- Par statut : Tous | Planning | Active | Completed | On Hold | Cancelled
- Bouton "Nouveau projet" (admin uniquement, dégradé cyan)

✅ **Actions par carte** (admin uniquement) :
- ✏️ Modifier
- 🗑️ Supprimer

### API - Endpoints projets

#### 1. Récupérer tous les projets
```
GET /actions/projects.php?action=get_all_projects
Response: { 
    "success": true, 
    "data": [
        {
            "id": 1,
            "name": "Site web du club",
            "description": "...",
            "status": "active",
            "visibility": "public",
            "start_date": "2025-01-01",
            "due_date": "2025-06-30",
            "owner_name": "John Doe"
        }
    ] 
}
```
**Note** : Filtre automatique selon le rôle
- Admin : Voit TOUS les projets
- Student : Voit uniquement les projets publics

#### 2. Récupérer les statistiques
```
GET /actions/projects.php?action=get_projects_stats
Response: { 
    "success": true, 
    "data": { 
        "total": 15, 
        "active": 5, 
        "completed": 8, 
        "overdue": 2 
    } 
}
```
**Permission** : Admin uniquement

#### 3. Créer un projet
```
POST /actions/projects.php
Body: { 
    "action": "create_project",
    "name": "Application Mobile",
    "description": "Développement d'une app mobile pour le club",
    "status": "planning",
    "visibility": "private",
    "start_date": "2025-02-01",
    "due_date": "2025-08-31"
}
Response: { "success": true, "message": "Projet créé avec succès" }
```
**Permission** : Admin uniquement

#### 4. Modifier un projet
```
POST /actions/projects.php
Body: { 
    "action": "update_project",
    "id": 5,
    "name": "Application Mobile (mise à jour)",
    "description": "...",
    "status": "active",
    "visibility": "public",
    "start_date": "2025-02-01",
    "due_date": "2025-09-30"
}
Response: { "success": true, "message": "Projet mis à jour" }
```
**Permission** : Admin uniquement

#### 5. Supprimer un projet
```
POST /actions/projects.php
Body: { 
    "action": "delete_project",
    "id": 5
}
Response: { "success": true, "message": "Projet supprimé" }
```
**Permission** : Admin uniquement
**Cascade** : Supprime aussi toutes les tâches liées

#### 6. Changer le statut d'un projet
```
POST /actions/projects.php
Body: { 
    "action": "update_project_status",
    "id": 5,
    "status": "completed"
}
Response: { "success": true, "message": "Statut mis à jour" }
```
**Permission** : Admin uniquement

#### 7. Récupérer les utilisateurs pour assignation
```
GET /actions/projects.php?action=get_users_for_assignment
Response: { 
    "success": true, 
    "data": [
        { "id": 1, "name": "John Doe", "role": "admin" },
        { "id": 2, "name": "Jane Smith", "role": "student" }
    ] 
}
```

### Permissions & Visibilité
- 👁️ **Lecture** :
  - Projets **publics** : Tous les utilisateurs
  - Projets **privés** : Admins uniquement
- ✏️ **Écriture** (Créer/Modifier/Supprimer/Changer statut) : **ADMIN UNIQUEMENT**

---

## ✅ GESTION DES TÂCHES

### Interface - Section "Tâches"

#### Liste des tâches
✅ **Affichage** : Grille de cartes responsive
- Titre de la tâche
- Description (tronquée)
- Badge de statut coloré :
  - 🟡 En attente (Jaune)
  - 🔵 En cours (Bleu)
  - 🟢 Terminée (Vert)
- Badge de priorité :
  - 🟢 Low (Vert)
  - 🟡 Medium (Jaune)
  - 🟠 High (Orange)
  - 🔴 Urgent (Rouge)
- Projet lié
- Assignés (avatars/noms)
- Date d'échéance
- Actions

✅ **Pagination** : 10 tâches par page
- Navigation : Précédent | 1 2 3 ... | Suivant
- Tâches non terminées affichées en premier

✅ **Filtres** :
- Par statut : Tous | En attente | En cours | Terminée
- Par priorité : Tous | Low | Medium | High | Urgent
- Bouton "Nouvelle tâche" (admin uniquement, dégradé bleu)

✅ **Actions par carte** :
- ✏️ Modifier (admin OU assigné pour le statut)
- 🗑️ Supprimer (admin uniquement)
- 🔄 Changer le statut (admin OU assigné)

### API - Endpoints tâches

#### 1. Récupérer toutes les tâches
```
GET /actions/tasks.php?action=get_all_tasks
Response: { 
    "success": true, 
    "data": [
        {
            "id": 1,
            "title": "Créer la base de données",
            "description": "...",
            "status": "en_cours",
            "priority": "high",
            "project_name": "Site web",
            "project_id": 5,
            "assigned_users": [
                { "id": 2, "name": "John Doe" },
                { "id": 3, "name": "Jane Smith" }
            ],
            "due_date": "2025-11-01 17:00:00",
            "created_by_name": "Admin User"
        }
    ] 
}
```

#### 2. Récupérer les tâches d'un utilisateur
```
GET /actions/tasks.php?action=get_user_tasks&user_id=5
Response: { "success": true, "data": [...] }
```

#### 3. Récupérer une tâche par ID
```
GET /actions/tasks.php?action=get_task_by_id&id=5
Response: { 
    "success": true, 
    "data": {
        "id": 5,
        "title": "...",
        "description": "...",
        "status": "en_cours",
        "priority": "high",
        "project_id": 3,
        "assigned_users": [2, 5, 7],
        "due_date": "2025-11-01 17:00:00"
    } 
}
```

#### 4. Créer une tâche
```
POST /actions/tasks.php
Body: { 
    "action": "create_task",
    "title": "Développer l'API REST",
    "description": "Créer tous les endpoints nécessaires",
    "status": "en_attente",
    "priority": "high",
    "project_id": 3,
    "assigned_users": [2, 5, 7],  // IDs des utilisateurs
    "due_date": "2025-11-15 18:00:00"
}
Response: { "success": true, "message": "Tâche créée avec succès" }
```
**Permission** : Admin uniquement
**Validation** : project_id est OBLIGATOIRE

#### 5. Modifier une tâche
```
POST /actions/tasks.php
Body: { 
    "action": "update_task",
    "id": 5,
    "title": "Développer l'API REST (mise à jour)",
    "description": "...",
    "status": "en_cours",
    "priority": "urgent",
    "project_id": 3,
    "assigned_users": [2, 5],
    "due_date": "2025-11-20 18:00:00"
}
Response: { "success": true, "message": "Tâche mise à jour" }
```
**Permission** : Admin uniquement

#### 6. Changer le statut d'une tâche
```
POST /actions/tasks.php
Body: { 
    "action": "update_task_status",
    "id": 5,
    "status": "terminee"
}
Response: { "success": true, "message": "Statut mis à jour" }
```
**Permissions** :
- Admin : Peut changer le statut de n'importe quelle tâche
- Student : Peut changer le statut **uniquement** de ses propres tâches (celles où il est assigné)

#### 7. Supprimer une tâche
```
POST /actions/tasks.php
Body: { 
    "action": "delete_task",
    "id": 5
}
Response: { "success": true, "message": "Tâche supprimée" }
```
**Permission** : Admin uniquement

#### 8. Récupérer les statistiques des tâches
```
GET /actions/tasks.php?action=get_tasks_stats
Response: { 
    "success": true, 
    "data": { 
        "total": 25, 
        "pending": 8, 
        "in_progress": 12, 
        "completed": 5 
    } 
}
```

#### 9. Récupérer les tâches en retard
```
GET /actions/tasks.php?action=get_overdue_tasks
Response: { "success": true, "data": [...] }
```

#### 10. Récupérer les tâches à venir
```
GET /actions/tasks.php?action=get_upcoming_tasks&days=7
Response: { "success": true, "data": [...] }
```
**Paramètre** : `days` = nombre de jours (par défaut 7)

#### 11. Récupérer les utilisateurs pour assignation
```
GET /actions/tasks.php?action=get_users_for_assignment
Response: { 
    "success": true, 
    "data": [
        { "id": 1, "name": "John Doe" },
        { "id": 2, "name": "Jane Smith" }
    ] 
}
```

### Multi-assignation
✅ **Table** : `task_assignments`
✅ **Fonctionnement** :
- Une tâche peut être assignée à plusieurs utilisateurs
- Lors de la création/modification, le tableau `assigned_users` contient les IDs
- Les anciennes assignations sont supprimées et remplacées

### Permissions détaillées
- 👁️ **Lecture** : Tous
- ✏️ **Créer** : Admin uniquement
- ✏️ **Modifier (toutes les infos)** : Admin uniquement
- 🔄 **Modifier le statut uniquement** : Admin OU utilisateur assigné à la tâche
- 🗑️ **Supprimer** : Admin uniquement

### Contraintes importantes
- ⚠️ **Chaque tâche DOIT être liée à un projet** (project_id NOT NULL)
- ⚠️ Si un projet est supprimé, toutes ses tâches sont supprimées (CASCADE)

---

## 📅 GESTION DES ÉVÉNEMENTS

### Interface - Section "Événements"

#### Listes d'événements
✅ **2 sections** :
1. **Prochains événements** (start_date >= aujourd'hui OU end_date >= aujourd'hui)
2. **Événements passés** (end_date < aujourd'hui)

✅ **Affichage** : Tableau avec colonnes
- Titre
- Description (tronquée)
- Lieu
- Date de début
- Date de fin
- Visibilité (Public/Privé)
- Actions (admin uniquement)

✅ **Actions** :
- Bouton "Nouvel événement" (admin uniquement, dégradé violet)
- ✏️ Modifier (admin uniquement)
- 🗑️ Supprimer (admin uniquement)

### API - Endpoints événements

#### 1. Récupérer les événements à venir
```
GET /actions/events.php?action=list_upcoming&limit=10
Response: { 
    "success": true, 
    "data": [
        {
            "id": 1,
            "title": "Hackathon 2025",
            "description": "...",
            "location": "Campus HEPL",
            "start_date": "2025-03-15",
            "end_date": "2025-03-16",
            "visibility": "public",
            "created_by": 1
        }
    ] 
}
```
**Paramètre** : `limit` (optionnel, défaut = 100)

#### 2. Récupérer les événements passés
```
GET /actions/events.php?action=list_past&limit=10
Response: { "success": true, "data": [...] }
```
**Paramètre** : `limit` (optionnel, défaut = 100)

#### 3. Récupérer un événement par ID
```
GET /actions/events.php?action=get_by_id&id=5
Response: { 
    "success": true, 
    "data": {
        "id": 5,
        "title": "Hackathon 2025",
        "description": "...",
        "location": "Campus HEPL",
        "start_date": "2025-03-15",
        "end_date": "2025-03-16",
        "visibility": "public"
    } 
}
```

#### 4. Créer un événement
```
POST /actions/events.php
Body: { 
    "action": "create",
    "title": "Conférence IA",
    "description": "Introduction à l'intelligence artificielle",
    "location": "Amphi A",
    "start_date": "2025-04-10",
    "end_date": "2025-04-10",
    "visibility": "public"
}
Response: { "success": true, "message": "Événement créé avec succès" }
```
**Permission** : Admin uniquement
**Format dates** : YYYY-MM-DD (pas d'heures)

#### 5. Modifier un événement
```
POST /actions/events.php
Body: { 
    "action": "update",
    "id": 5,
    "title": "Conférence IA (mise à jour)",
    "description": "...",
    "location": "Amphi B",
    "start_date": "2025-04-12",
    "end_date": "2025-04-12",
    "visibility": "private"
}
Response: { "success": true, "message": "Événement mis à jour" }
```
**Permission** : Admin uniquement

#### 6. Supprimer un événement
```
POST /actions/events.php
Body: { 
    "action": "delete",
    "id": 5
}
Response: { "success": true, "message": "Événement supprimé" }
```
**Permission** : Admin uniquement

### Permissions & Visibilité
- 👁️ **Lecture** :
  - Événements **publics** : Tous les utilisateurs
  - Événements **privés** : Admins uniquement (côté API, filtrage automatique)
- ✏️ **Écriture** (Créer/Modifier/Supprimer) : **ADMIN UNIQUEMENT**

### Notes importantes
- Les dates sont au format **DATE** uniquement (pas de temps)
- Classification automatique "À venir" / "Passé" basée sur `start_date` et `end_date`
- Visibilité automatique selon le rôle de l'utilisateur connecté

---

## 📢 GESTION DES ANNONCES

### Interface - Section "Annonces"

#### Liste des annonces
✅ **Affichage** : Tableau avec colonnes
- Titre
- Contenu (tronqué)
- Visibilité (Public/Privé)
- Épinglé (badge)
- Date de publication
- Date d'expiration
- Actions (admin uniquement)

✅ **Tri** :
- Annonces épinglées en premier
- Puis par date de publication (plus récent en premier)

✅ **Actions** :
- Bouton "Nouvelle annonce" (admin uniquement, dégradé orange)
- ✏️ Modifier (admin uniquement)
- 🗑️ Supprimer (admin uniquement)

### API - Endpoints annonces

#### 1. Récupérer toutes les annonces
```
GET /actions/announcements.php?action=list&limit=50
Response: { 
    "success": true, 
    "data": [
        {
            "id": 1,
            "title": "Réunion de rentrée",
            "content": "La réunion de rentrée aura lieu le...",
            "visibility": "public",
            "pinned": true,
            "publish_date": "2025-01-15",
            "expire_date": "2025-02-15",
            "created_by": 1,
            "created_at": "2025-01-15 10:30:00"
        }
    ] 
}
```
**Paramètre** : `limit` (optionnel, défaut = 100)
**Filtrage automatique** : Admin voit tout, Student voit uniquement les annonces publiques

#### 2. Récupérer une annonce par ID
```
GET /actions/announcements.php?action=get&id=5
Response: { "success": true, "data": {...} }
```

#### 3. Créer une annonce
```
POST /actions/announcements.php
Body: { 
    "action": "create",
    "title": "Nouvelle formation disponible",
    "content": "Nous proposons une formation sur React...",
    "visibility": "public",
    "pinned": false,
    "publish_date": "2025-02-01",
    "expire_date": "2025-03-01"
}
Response: { "success": true, "message": "Annonce créée avec succès" }
```
**Permission** : Admin uniquement
**Champs optionnels** : expire_date, pinned (défaut false)

#### 4. Modifier une annonce
```
POST /actions/announcements.php
Body: { 
    "action": "update",
    "id": 5,
    "title": "Nouvelle formation disponible (mise à jour)",
    "content": "...",
    "visibility": "private",
    "pinned": true,
    "publish_date": "2025-02-01",
    "expire_date": "2025-03-15"
}
Response: { "success": true, "message": "Annonce mise à jour" }
```
**Permission** : Admin uniquement

#### 5. Supprimer une annonce
```
POST /actions/announcements.php
Body: { 
    "action": "delete",
    "id": 5
}
Response: { "success": true, "message": "Annonce supprimée" }
```
**Permission** : Admin uniquement

#### 6. Récupérer les statistiques
```
GET /actions/announcements.php?action=get_stats
Response: { 
    "success": true, 
    "data": { "total": 15 } 
}
```

#### 7. Récupérer les dernières annonces
```
GET /actions/announcements.php?action=list_latest&limit=5
Response: { "success": true, "data": [...] }
```
**Utilisation** : Pour le tableau de bord (widget)

### Fonctionnalités spéciales
✅ **Épinglage** : Les annonces importantes peuvent être épinglées (`pinned = TRUE`)
✅ **Expiration** : Les annonces peuvent avoir une date d'expiration (`expire_date`)
✅ **Visibilité** : Public (tous) ou Privé (admin uniquement)

### Permissions
- 👁️ **Lecture** :
  - Annonces **publiques** : Tous les utilisateurs
  - Annonces **privées** : Admins uniquement
- ✏️ **Écriture** (Créer/Modifier/Supprimer) : **ADMIN UNIQUEMENT**

---

## ⚙️ GESTION DES PARAMÈTRES

### Interface - Section "Paramètres" (Admin uniquement)

#### Formulaire de paramètres
✅ **Champs disponibles** :
1. **Lien Discord** (URL)
   - Placeholder : https://discord.gg/...
2. **Email de contact** (Email)
   - Placeholder : contact@club.be
3. **Charte du club (URL)**
   - Placeholder : https://...
4. **Visibilité par défaut des projets** (Select)
   - Options : Public | Privé
5. **Priorité par défaut des tâches** (Select)
   - Options : Low | Medium | High | Urgent
6. **Autoriser les assignations multiples** (Checkbox)

✅ **Bouton** : "Enregistrer" (en bas du formulaire)

### API - Endpoints paramètres

#### 1. Récupérer tous les paramètres
```
GET /actions/settings.php?action=get_settings
Response: { 
    "success": true, 
    "settings": {
        "discord_link": "https://discord.gg/techlab",
        "contact_email": "contact@techlab.be",
        "club_charter_url": "https://techlab.be/charter",
        "default_project_visibility": "private",
        "default_task_priority": "medium",
        "allow_multi_assignment": "1"
    } 
}
```
**Permission** : Admin uniquement

#### 2. Mettre à jour les paramètres
```
POST /actions/settings.php
Body: { 
    "action": "update_settings",
    "discord_link": "https://discord.gg/newtechlab",
    "contact_email": "info@techlab.be",
    "club_charter_url": "https://techlab.be/new-charter",
    "default_project_visibility": "public",
    "default_task_priority": "high",
    "allow_multi_assignment": "1"
}
Response: { "success": true, "message": "Paramètres mis à jour avec succès" }
```
**Permission** : Admin uniquement

### Utilisation des paramètres
Les paramètres sont utilisés pour :
- Afficher le lien Discord dans le dashboard
- Pré-remplir les valeurs par défaut lors de la création de projets/tâches
- Configurer le comportement de l'application

### Permissions
- ⛔ **Toute la section** : **ADMIN UNIQUEMENT**

---

## 📊 TABLEAU DE BORD (Dashboard Overview)

### Interface - Section "Tableau de bord"

#### Statistiques (4 cartes avec dégradés et effets)
1. 📁 **Projets** (Bleu)
   - Nombre total de projets visibles par l'utilisateur
   - Icône : folder (blanc sur fond bleu)
   - Effet hover : scale 105% + ombre bleue

2. 📅 **Événements** (Violet)
   - Nombre d'événements à venir
   - Icône : calendar (blanc sur fond violet)
   - Effet hover : scale 105% + ombre violette

3. 👥 **Membres** (Vert)
   - Nombre total de membres (admin uniquement, sinon 0)
   - Icône : users (blanc sur fond vert)
   - Effet hover : scale 105% + ombre verte

4. 📢 **Annonces** (Orange)
   - Nombre total d'annonces
   - Icône : message-circle (blanc sur fond orange)
   - Effet hover : scale 105% + ombre orange

#### Listes d'aperçu (4 widgets avec bordures colorées)
1. 🟣 **Prochains événements** (Bordure violette gauche)
   - 3 prochains événements
   - Titre + Date
   - Icône calendar dans carré violet
   - Effet hover : ombre

2. 🟠 **Dernières annonces** (Bordure orange gauche)
   - 3 dernières annonces
   - Titre + Date de création
   - Icône message-circle dans carré orange
   - Effet hover : ombre

3. 🔵 **Dernières tâches** (Bordure bleue gauche)
   - 2 dernières tâches
   - Titre + Statut (coloré selon le statut)
   - Icône check-square dans carré coloré
   - Effet hover : ombre

4. 🔷 **Derniers projets** (Bordure cyan gauche)
   - 2 derniers projets
   - Titre + Statut
   - Icône folder dans carré cyan
   - Effet hover : ombre

### Chargement dynamique
✅ **Au chargement de la page** :
```javascript
loadDashboardOverview();
```

✅ **Endpoints appelés** :
- `GET /actions/projects.php?action=get_all_projects` (pour compter)
- `GET /actions/events.php?action=list_upcoming`
- `GET /actions/members.php?action=get_stats` (si admin)
- `GET /actions/announcements.php?action=get_stats`
- `GET /actions/announcements.php?action=list_latest`
- `GET /actions/tasks.php?action=get_all_tasks`

### Visibilité
- Cette section est **uniquement visible dans l'onglet "Tableau de bord"**
- Les autres onglets (Projets, Membres, etc.) affichent leur propre contenu

---

## 🎨 DESIGN & UX

### Système de couleurs
Le dashboard utilise un système de couleurs cohérent avec des dégradés :

#### Couleurs principales par section
- 🔵 **Bleu** : Projets, Membres (total), Tâches en cours
- 🟣 **Violet** : Événements, Administrateurs
- 🟢 **Vert** : Actifs, En cours, Terminé, Membres actifs
- 🟠 **Orange** : Annonces, Étudiants
- 🔷 **Cyan** : Projets (cartes)
- 🔴 **Rouge** : En retard, Urgent
- 🟡 **Jaune** : En attente, Medium

#### Composants stylisés
✅ **Cartes de statistiques** :
- Dégradé : `from-{color}-50 to-{color}-100`
- Bordure : `border-{color}-200`
- Texte titre : `text-{color}-700`
- Texte valeur : `text-{color}-900` (text-4xl, font-bold)
- Icône : Blanc sur fond `{color}-500` avec ombre
- Effet hover : `scale-105` + `shadow-xl shadow-{color}-200`

✅ **Boutons d'action** :
- Dégradé : `from-{color}-500 to-{color}-600`
- Hover : `from-{color}-600 to-{color}-700`
- Ombre : `shadow-lg`
- Effet hover : `shadow-xl` + `scale-105`
- Transition : `duration-300`

✅ **Listes du dashboard** :
- Bordure gauche : 4px, couleur selon la section
- Icônes : Carrés colorés (32x32px) avec ombre
- Fond : Dégradé horizontal `from-{color}-50 to-transparent`
- Effet hover : `shadow-md`

✅ **Badges de statut** :
- Forme : Arrondi (rounded-full ou rounded-lg)
- Couleurs selon le contexte (statut, priorité, visibilité)
- Texte petit (text-xs) et semi-gras

### Animations et transitions
✅ **Effets appliqués** :
- `transition-all duration-300` : Sur les cartes et boutons
- `transform hover:scale-105` : Agrandissement au survol
- `hover:shadow-xl` : Ombre accentuée au survol
- Animations d'apparition progressive (card-animation avec delay)

### Responsive Design
✅ **Grilles adaptatives** :
- Desktop : `grid-cols-4` (4 colonnes)
- Tablet : `md:grid-cols-2` (2 colonnes)
- Mobile : `grid-cols-1` (1 colonne)

✅ **Sidebar** :
- Desktop : Fixe, largeur 256px
- Mobile : Overlay avec animation slide-in
- Toggle collapse/expand

### Icônes
✅ **Bibliothèque** : Feather Icons
✅ **Utilisation** : `<i data-feather="icon-name"></i>`
✅ **Initialisation** : `feather.replace()` après chaque rendu dynamique

### Fond général
✅ **Dégradé subtil** : `bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50`
- Remplace le fond blanc uniforme
- Ajoute de la profondeur sans surcharger

---

## 🔒 SÉCURITÉ & PERMISSIONS

### Système de rôles

#### Rôle : **Admin**
✅ **Permissions complètes** :
- Gestion des membres (CRUD complet)
- Gestion des projets (CRUD complet)
- Gestion des tâches (CRUD complet)
- Gestion des événements (CRUD complet)
- Gestion des annonces (CRUD complet)
- Gestion des paramètres
- Voir tous les projets (publics + privés)
- Voir tous les événements (publics + privés)
- Voir toutes les annonces (publiques + privées)
- Changer le statut de n'importe quelle tâche
- Voir les statistiques des membres

#### Rôle : **Student**
✅ **Permissions limitées** :
- Voir les projets publics uniquement
- Voir les événements publics uniquement
- Voir les annonces publiques uniquement
- Voir toutes les tâches
- Changer le statut uniquement de SES tâches (où il est assigné)
- ⛔ Pas d'accès à la gestion des membres
- ⛔ Pas de création/modification/suppression de projets
- ⛔ Pas de création/modification/suppression de tâches (sauf statut de ses tâches)
- ⛔ Pas de création/modification/suppression d'événements
- ⛔ Pas de création/modification/suppression d'annonces
- ⛔ Pas d'accès aux paramètres

### Protections implémentées

#### Backend (PHP)
✅ **Vérification de session** :
```php
if (!isset($_SESSION['user_id'])) {
    // Redirection vers login
}
```

✅ **Vérification de rôle** :
```php
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Permissions insuffisantes']);
    exit();
}
```

✅ **Protections spéciales** :
- Impossible de modifier son propre rôle
- Impossible de se supprimer soi-même
- Impossible de se désactiver soi-même
- Vérification de l'utilisateur assigné pour les modifications de statut de tâches

#### Frontend (JavaScript)
✅ **Variable globale** :
```javascript
const IS_ADMIN = <?php echo json_encode(isset($user['role']) && $user['role'] === 'admin'); ?>;
```

✅ **Affichage conditionnel** :
- Boutons "Nouveau..." cachés pour les non-admins
- Sections entières (Membres, Paramètres) cachées pour les non-admins
- Actions (Modifier, Supprimer) cachées selon les permissions

#### Base de données
✅ **Contraintes de clés étrangères** :
- ON DELETE CASCADE : Suppression en cascade (projets → tâches)
- ON DELETE SET NULL : Mise à NULL si l'utilisateur est supprimé
- UNIQUE : Évite les doublons (username, email, assignations)

✅ **Requêtes préparées PDO** :
- Protection contre les injections SQL
- Binding de paramètres pour toutes les requêtes

### Hashing des mots de passe
✅ **Algorithme** : `password_hash()` (bcrypt par défaut)
✅ **SALT** : Généré automatiquement et unique par utilisateur
✅ **Vérification** : `password_verify()`

---

## 🚀 FONCTIONNALITÉS AVANCÉES

### Single Page Application (SPA)
✅ **Navigation sans rechargement** :
- Système d'onglets dynamiques
- Fonction `switchTab(tabName)` pour changer de section
- Chargement AJAX des données

✅ **Onglets disponibles** :
1. Tableau de bord (dashboard)
2. Projets (projects)
3. Membres (members) - Admin uniquement
4. Tâches (tasks)
5. Événements (events)
6. Annonces (messages/announcements)
7. Paramètres (settings) - Admin uniquement

### Notifications Flash
✅ **Système de messages** :
```javascript
showNotification(message, type); // type: success, error, warning, info
```

✅ **Affichage** :
- Toast en haut à droite
- Couleurs selon le type
- Disparition automatique après 3 secondes
- Animation d'entrée/sortie fluide

### Pagination
✅ **Implémentation** :
- Tâches : 10 par page
- Navigation : Précédent | Numéros de page | Suivant
- Mise à jour dynamique sans rechargement

### Filtres en temps réel
✅ **Sections avec filtres** :
- Membres : Par rôle, Par statut
- Projets : Par statut
- Tâches : Par statut, Par priorité

✅ **Fonctionnement** :
- Filtrage côté client (JavaScript)
- Aucun rechargement de page
- Mise à jour immédiate de l'affichage

### Modals (fenêtres modales)
✅ **Actions CRUD** :
- Création (Ajouter)
- Modification (Éditer)
- Suppression (Confirmation)
- Réinitialisation de mot de passe

✅ **Caractéristiques** :
- Overlay sombre (bg-black bg-opacity-50)
- Centré à l'écran
- Bouton de fermeture (X)
- Validation des formulaires
- Fermeture au clic en dehors (overlay)

### Recherche
✅ **Barre de recherche** :
- Présente dans le header
- Placeholder : "Rechercher..."
- Icône loupe
- (Fonctionnalité à implémenter)

### Auto-création des tables
✅ **Au démarrage de l'application** :
```php
$userDAO->createUsersTable();
$taskDAO->createTasksTable();
$projectDAO->createProjectsTable();
$settingsDAO->createSettingsTable();
$eventsDAO->createEventsTable();
$announcementsDAO->createAnnouncementsTable();
```
- Crée les tables si elles n'existent pas
- Utilise `CREATE TABLE IF NOT EXISTS`
- Pas besoin d'exécuter manuellement le SQL

---

## 📱 SITE PUBLIC

### Structure
✅ **Fichier principal** : `index.html`
✅ **Sections** :
- Hero / Accueil
- Présentation du club
- Formulaires de contact
- Liens vers le dashboard

### Intégration
- Le site public et le dashboard sont séparés
- Navigation : Site public → Dashboard via bouton de connexion
- Dashboard accessible uniquement après authentification

---

## 🛠️ INSTALLATION & CONFIGURATION

### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx)
- Hébergement : AlwaysData (ou équivalent)

### Installation

#### Étape 1 : Configuration de la base de données
1. Se connecter à phpMyAdmin sur AlwaysData
2. Sélectionner la base de données `club_database`
3. Exécuter le script `dashboard/conf/database_setup.sql`
4. Vérifier que les 8 tables sont créées

#### Étape 2 : Configuration de la connexion
1. Copier `dashboard/conf/config.example.php` vers `dashboard/conf/config.php`
2. Éditer `dashboard/conf/config.php` :
```php
define('DB_HOST', 'mysql-votre-compte.alwaysdata.net');
define('DB_NAME', 'club_database');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
define('SALT', 'votre_salt_unique_et_secret');
```

#### Étape 3 : Permissions des fichiers
```bash
chmod 755 dashboard/
chmod 644 dashboard/conf/config.php
```

#### Étape 4 : Premier utilisateur admin
1. S'inscrire via l'interface web
2. Modifier le rôle dans phpMyAdmin :
```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

### Configuration optionnelle

#### Paramètres du club
Accessible via : Dashboard → Paramètres (admin uniquement)
- Discord link
- Contact email
- Club charter URL
- Valeurs par défaut

---

## 📝 CONVENTIONS DE CODE

### Backend (PHP)

#### Nommage
- Classes : PascalCase (`UserDAO`, `ProjectDAO`)
- Méthodes : camelCase (`getUserById`, `createTask`)
- Variables : snake_case (`$user_id`, `$is_active`)
- Constantes : UPPER_CASE (`DB_HOST`, `SALT`)

#### Structure des DAO
```php
class ExampleDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createTable() { }
    public function create($data) { }
    public function getById($id) { }
    public function getAll() { }
    public function update($id, $data) { }
    public function delete($id) { }
}
```

#### Réponses API (JSON)
```php
// Succès
echo json_encode([
    'success' => true,
    'data' => $result,
    'message' => 'Opération réussie'
]);

// Erreur
echo json_encode([
    'success' => false,
    'message' => 'Description de l\'erreur'
]);
```

### Frontend (JavaScript)

#### Nommage
- Fonctions : camelCase (`loadMembers`, `displayTasks`)
- Variables : camelCase (`userData`, `isAdmin`)
- Constantes : UPPER_CASE (`IS_ADMIN`, `API_URL`)

#### Appels API (Fetch)
```javascript
async function loadData() {
    try {
        const response = await fetch('actions/endpoint.php?action=get_data');
        const data = await response.json();
        if (data.success) {
            // Traiter les données
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur réseau', 'error');
    }
}
```

---

## 🐛 POINTS D'ATTENTION & BUGS CONNUS

### Problèmes résolus
✅ Erreur "Unexpected token 'const'" → Corrigé (cache navigateur + syntaxe JS)
✅ Icône "megaphone" inexistante → Remplacée par "message-circle"
✅ API get_projects_stats retourne 400 pour non-admins → Utilise maintenant get_all_projects
✅ Login avec mauvais mot de passe → Page blanche corrigée (redirection ajoutée)
✅ Événements "passés" affichés comme "à venir" → Logique SQL corrigée
✅ Overview widget dupliqué dans tous les onglets → Restreint au tableau de bord uniquement
✅ Compteur de membres affiche 0 → Format de réponse API corrigé

### Améliorations possibles
💡 Implémenter la recherche globale
💡 Ajouter un système de notifications en temps réel
💡 Calendrier visuel pour les événements
💡 Tableau Kanban pour les tâches (était implémenté, retiré au profit des cartes)
💡 Export des données (CSV, PDF)
💡 Gestion des fichiers/pièces jointes
💡 Système de commentaires sur les tâches/projets
💡 Historique des modifications
💡 Dashboard analytics plus poussé
💡 Mode sombre
💡 PWA (Progressive Web App)

---

## 📚 DOCUMENTATION DES ENDPOINTS

### Résumé des endpoints disponibles

#### Authentification (`/actions/auth.php`)
- `POST ?action=register` - Inscription
- `POST ?action=login` - Connexion
- `POST /actions/logout.php` - Déconnexion

#### Membres (`/actions/members.php`) - Admin uniquement
- `POST ?action=get_all_users` - Liste des membres
- `POST ?action=get_stats` - Statistiques
- `POST ?action=create_member` - Créer un membre
- `POST ?action=update_member` - Modifier un membre
- `POST ?action=update_role` - Changer le rôle
- `POST ?action=toggle_status` - Activer/Désactiver
- `POST ?action=delete_member` - Supprimer (soft delete)
- `POST ?action=reset_password` - Réinitialiser mot de passe

#### Projets (`/actions/projects.php`)
- `GET ?action=get_all_projects` - Liste des projets (filtré par visibilité)
- `GET ?action=get_project_by_id&id=X` - Détails d'un projet
- `GET ?action=get_projects_stats` - Statistiques (admin uniquement)
- `POST ?action=create_project` - Créer (admin uniquement)
- `POST ?action=update_project` - Modifier (admin uniquement)
- `POST ?action=delete_project` - Supprimer (admin uniquement)
- `POST ?action=update_project_status` - Changer statut (admin uniquement)
- `GET ?action=get_users_for_assignment` - Utilisateurs disponibles

#### Tâches (`/actions/tasks.php`)
- `GET ?action=get_all_tasks` - Liste des tâches
- `GET ?action=get_user_tasks&user_id=X` - Tâches d'un utilisateur
- `GET ?action=get_task_by_id&id=X` - Détails d'une tâche
- `POST ?action=create_task` - Créer (admin uniquement)
- `POST ?action=update_task` - Modifier (admin uniquement)
- `POST ?action=update_task_status` - Changer statut (admin ou assigné)
- `POST ?action=delete_task` - Supprimer (admin uniquement)
- `GET ?action=get_tasks_stats` - Statistiques
- `GET ?action=get_overdue_tasks` - Tâches en retard
- `GET ?action=get_upcoming_tasks&days=X` - Tâches à venir
- `GET ?action=get_users_for_assignment` - Utilisateurs disponibles

#### Événements (`/actions/events.php`) - CRUD admin uniquement
- `GET ?action=list_upcoming&limit=X` - Événements à venir
- `GET ?action=list_past&limit=X` - Événements passés
- `GET ?action=get_by_id&id=X` - Détails d'un événement
- `POST ?action=create` - Créer
- `POST ?action=update` - Modifier
- `POST ?action=delete` - Supprimer

#### Annonces (`/actions/announcements.php`) - CRUD admin uniquement
- `GET ?action=list&limit=X` - Liste des annonces
- `GET ?action=get&id=X` - Détails d'une annonce
- `POST ?action=create` - Créer
- `POST ?action=update` - Modifier
- `POST ?action=delete` - Supprimer
- `GET ?action=get_stats` - Statistiques
- `GET ?action=list_latest&limit=X` - Dernières annonces

#### Paramètres (`/actions/settings.php`) - Admin uniquement
- `GET ?action=get_settings` - Récupérer tous les paramètres
- `POST ?action=update_settings` - Mettre à jour les paramètres

---

## 📊 STATISTIQUES DU PROJET

### Lignes de code (estimation)
- **Backend PHP** : ~3000 lignes
  - DAO : ~1200 lignes
  - Actions : ~800 lignes
  - Config : ~200 lignes
  - Router : ~300 lignes
  - Vues : ~500 lignes

- **Frontend JavaScript** : ~2500 lignes
  - Dashboard : ~2000 lignes
  - Login : ~300 lignes
  - Autres : ~200 lignes

- **SQL** : ~160 lignes
- **HTML** : ~1500 lignes
- **Total** : ~7160 lignes de code

### Fichiers
- **Total** : ~30 fichiers
- **DAO** : 7 classes
- **Actions** : 8 endpoints
- **Vues** : 4 templates
- **Configuration** : 3 fichiers

### Tables de base de données
- **8 tables** principales
- **~25 colonnes** en moyenne par table
- **Indexes** : ~15 index pour optimisation
- **Foreign keys** : 12 contraintes de clés étrangères

---

## 🎯 CONCLUSION

### Résumé des fonctionnalités principales

✅ **Système d'authentification complet**
- Inscription / Connexion / Déconnexion
- Sécurité renforcée (hashing, validation)

✅ **Gestion des membres** (Admin)
- CRUD complet
- Statistiques
- Gestion des rôles et statuts

✅ **Gestion des projets**
- CRUD complet (admin)
- Visibilité public/privé
- Statistiques détaillées

✅ **Gestion des tâches**
- CRUD avec permissions granulaires
- Multi-assignation
- Lien obligatoire avec un projet
- Pagination et filtres

✅ **Gestion des événements** (Admin)
- CRUD complet
- Classification automatique (à venir/passés)
- Visibilité public/privé

✅ **Gestion des annonces** (Admin)
- CRUD complet
- Épinglage
- Dates de publication et expiration

✅ **Paramètres configurables** (Admin)
- Discord, Email, Charte
- Valeurs par défaut

✅ **Dashboard moderne**
- Design coloré et professionnel
- Statistiques en temps réel
- Navigation SPA
- Responsive

### Points forts du projet
🎨 **Design moderne** : Dégradés, animations, effets hover
🔒 **Sécurité** : Permissions granulaires, protection des données
🏗️ **Architecture MVC** : Code organisé et maintenable
📱 **Responsive** : Fonctionne sur tous les appareils
⚡ **Performance** : SPA, requêtes optimisées, indexes DB
🛠️ **Maintenabilité** : Code commenté, conventions respectées

---

**Développé avec ❤️ pour HEPL Tech Lab**  
**Version 1.0 - Octobre 2025**

