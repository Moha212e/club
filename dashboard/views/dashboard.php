<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Club Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        const IS_ADMIN = <?php echo json_encode(isset($user) && is_array($user) && isset($user['role']) && $user['role'] === 'admin'); ?>;
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#F1F5F9',
                        accent: '#10B981'
                    }
                }
            }
        }
    </script>
    <style>
        /* Styles personnalisés pour les onglets */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-item.active {
            background-color: #3B82F6 !important;
            color: white !important;
        }

        .nav-item {
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .nav-item:hover::before {
            left: 100%;
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-animation {
            animation: slideInUp 0.6s ease-out;
        }

        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Styles pour le Kanban */
        .kanban-column {
            min-height: 400px;
            transition: all 0.3s ease;
        }

        .kanban-column.drag-over {
            background-color: rgba(59, 130, 246, 0.1);
            border: 2px dashed #3B82F6;
        }

        .kanban-task {
            transition: all 0.2s ease;
            cursor: move;
        }

        .kanban-task:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .kanban-task.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }

        /* Animation pour les tâches qui changent de colonne */
        .task-moving {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Styles pour les priorités */
        .priority-low { border-left-color: #3B82F6 !important; }
        .priority-medium { border-left-color: #F59E0B !important; }
        .priority-high { border-left-color: #F97316 !important; }
        .priority-urgent { border-left-color: #EF4444 !important; }

        /* Limitation de texte */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Container Principal -->
    <div class="flex h-screen">
        <!-- Overlay pour mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="bg-white shadow-lg transition-all duration-300 ease-in-out w-64 min-h-screen relative z-50">
            <div class="flex flex-col h-full">
                <!-- Header Sidebar -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <i data-feather="activity" class="w-5 h-5 text-white"></i>
                        </div>
                        <h1 id="logo-text" class="text-xl font-bold text-gray-800">Club Pro</h1>
                    </div>
                    <button id="toggle-sidebar" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <i data-feather="menu" class="w-5 h-5 text-gray-600"></i>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2">
                    <a href="#" class="nav-item active flex items-center space-x-3 p-3 rounded-lg bg-primary text-white" data-tab="dashboard">
                        <i data-feather="home" class="w-5 h-5"></i>
                        <span class="nav-text">Tableau de bord</span>
                    </a>
                    <a href="#" class="nav-item flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors" data-tab="projects">
                        <i data-feather="folder" class="w-5 h-5"></i>
                        <span class="nav-text">Projets</span>
                    </a>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="#" class="nav-item flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors" data-tab="members">
                        <i data-feather="users" class="w-5 h-5"></i>
                        <span class="nav-text">Membres</span>
                    </a>
                    <?php endif; ?>
                    <a href="#" class="nav-item flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors" data-tab="events">
                        <i data-feather="calendar" class="w-5 h-5"></i>
                        <span class="nav-text">Événements</span>
                    </a>
                    <a href="#" class="nav-item flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors" data-tab="tasks">
                        <i data-feather="check-square" class="w-5 h-5"></i>
                        <span class="nav-text">Tâches</span>
                    </a>
                    <a href="#" class="nav-item flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors" data-tab="messages">
                        <i data-feather="message-circle" class="w-5 h-5"></i>
                        <span class="nav-text">Messages</span>
                    </a>
                    <a href="#" class="nav-item flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors" data-tab="settings">
                        <i data-feather="settings" class="w-5 h-5"></i>
                        <span class="nav-text">Paramètres</span>
                    </a>
                </nav>

                <!-- Footer Sidebar -->
                <div class="p-4 border-t border-gray-200">
                    <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                        </div>
                        <div class="nav-text">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenu Principal -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header Principal -->
            <header class="bg-white shadow-sm border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button id="mobile-menu-button" class="p-2 rounded-lg hover:bg-gray-100 transition-colors lg:hidden">
                            <i data-feather="menu" class="w-6 h-6 text-gray-600"></i>
                        </button>
                        <div>
                            <h2 id="page-title" class="text-2xl font-bold text-gray-800">Tableau de bord</h2>
                            <p id="page-description" class="text-gray-600 mt-1">Vue globale du club - projets, événements et membres</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Recherche -->
                        <div class="relative hidden md:block">
                            <input type="text" placeholder="Rechercher..." 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <i data-feather="search" class="absolute left-3 top-2.5 w-5 h-5 text-gray-400"></i>
                        </div>
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                            <i data-feather="bell" class="w-6 h-6"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                        </button>
                        <!-- Déconnexion -->
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center space-x-2">
                                <i data-feather="log-out" class="w-5 h-5"></i>
                                <span>Déconnexion</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-auto p-6 bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50">
                <!-- Section Tableau de bord -->
                <div id="dashboard-section" class="tab-content">
                    <!-- Stats cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-blue-200">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-blue-700">Projets</p>
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="folder" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <p id="stat-projects" class="text-4xl font-bold mt-3 text-blue-900">-</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-purple-200">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-purple-700">Événements</p>
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="calendar" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <p id="stat-events" class="text-4xl font-bold mt-3 text-purple-900">-</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-green-200">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-green-700">Membres</p>
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="users" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <p id="stat-members" class="text-4xl font-bold mt-3 text-green-900">-</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-orange-200">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-orange-700">Annonces</p>
                                <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="message-circle" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <p id="stat-announcements" class="text-4xl font-bold mt-3 text-orange-900">-</p>
                        </div>
                    </div>

                    <!-- Lists -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white border-l-4 border-l-purple-500 border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center space-x-2 mb-4">
                                <i data-feather="calendar" class="w-5 h-5 text-purple-600"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Prochains événements</h3>
                            </div>
                            <div id="list-next-events" class="space-y-3">
                                <p class="text-gray-500">Chargement...</p>
                            </div>
                        </div>
                        <div class="bg-white border-l-4 border-l-orange-500 border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center space-x-2 mb-4">
                                <i data-feather="message-circle" class="w-5 h-5 text-orange-600"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Dernières annonces</h3>
                            </div>
                            <div id="list-latest-announcements" class="space-y-3">
                                <p class="text-gray-500">Chargement...</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white border-l-4 border-l-blue-500 border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center space-x-2 mb-4">
                                <i data-feather="check-square" class="w-5 h-5 text-blue-600"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Dernières tâches</h3>
                            </div>
                            <div id="list-latest-tasks" class="space-y-3">
                                <p class="text-gray-500">Chargement...</p>
                            </div>
                        </div>
                        <div class="bg-white border-l-4 border-l-cyan-500 border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center space-x-2 mb-4">
                                <i data-feather="folder" class="w-5 h-5 text-cyan-600"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Derniers projets</h3>
                            </div>
                            <div id="list-latest-projects" class="space-y-3">
                                <p class="text-gray-500">Chargement...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Projets -->
                <div id="projects-section" class="tab-content">
                    <!-- Statistiques des projets -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 border border-cyan-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-cyan-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-cyan-700">Total projets</p>
                                    <p id="total-projects" class="text-4xl font-bold text-cyan-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-cyan-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="folder" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-green-700">Projets actifs</p>
                                    <p id="active-projects" class="text-4xl font-bold text-green-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="play" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-purple-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-700">Projets terminés</p>
                                    <p id="completed-projects" class="text-4xl font-bold text-purple-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="check-circle" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-red-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-red-700">En retard</p>
                                    <p id="overdue-projects" class="text-4xl font-bold text-red-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="alert-triangle" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtres et actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <h3 class="text-lg font-semibold text-gray-800">Gestion des projets</h3>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <select id="projects-status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Tous les statuts</option>
                                        <option value="planning">Planification</option>
                                        <option value="active">Actif</option>
                                        <option value="completed">Terminé</option>
                                        <option value="on_hold">En attente</option>
                                        <option value="cancelled">Annulé</option>
                                    </select>
                                    <button id="add-project-btn" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-cyan-600 text-white rounded-lg hover:from-cyan-600 hover:to-cyan-700 transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105" style="display:none;">
                                    <i data-feather="plus" class="w-4 h-4"></i>
                                    <span>Nouveau projet</span>
                                </button>
                                        </div>
                                        </div>
                                    </div>
                        
                        <!-- Grille des projets -->
                        <div id="projects-grid" class="p-6">
                            <div id="projects-loading" class="flex items-center justify-center py-12">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                                <span class="ml-3 text-gray-600">Chargement des projets...</span>
                                    </div>
                            <div id="projects-error" class="hidden text-center py-12">
                                <i data-feather="alert-circle" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
                                <p class="text-gray-600">Erreur lors du chargement des projets</p>
                                <button onclick="loadProjects()" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    Réessayer
                                </button>
                                </div>
                            <div id="projects-content" class="hidden">
                                <!-- Les projets seront chargés ici -->
                                        </div>
                        </div>
                    </div>
                </div>

                <!-- Section Membres -->
                <?php if ($user['role'] === 'admin'): ?>
                <div id="members-section" class="tab-content">
                    <!-- Statistiques des membres -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-blue-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-700">Total membres</p>
                                    <p id="total-members" class="text-4xl font-bold text-blue-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="users" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-green-700">Membres actifs</p>
                                    <p id="active-members" class="text-4xl font-bold text-green-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="user-check" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-purple-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-700">Administrateurs</p>
                                    <p id="admin-count" class="text-4xl font-bold text-purple-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="shield" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-xl hover:shadow-orange-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-orange-700">Étudiants</p>
                                    <p id="student-count" class="text-4xl font-bold text-orange-900 mt-3">-</p>
                                </div>
                                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-feather="book" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des membres -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">Gestion des membres</h3>
                                <div class="flex items-center space-x-4">
                                    <!-- Filtres -->
                                    <select id="role-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Tous les rôles</option>
                                        <option value="admin">Administrateurs</option>
                                        <option value="student">Étudiants</option>
                                    </select>
                                    <select id="status-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Tous les statuts</option>
                                        <option value="active">Actifs</option>
                                        <option value="inactive">Inactifs</option>
                                    </select>
                                    <button id="refresh-members" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2">
                                        <i data-feather="refresh-cw" class="w-4 h-4"></i>
                                        <span>Actualiser</span>
                                    </button>
                                    <button id="add-member-btn" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i data-feather="user-plus" class="w-4 h-4"></i>
                                    <span>Nouveau membre</span>
                                </button>
                            </div>
                        </div>
                        </div>
                        
                        <!-- Tableau des membres -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Membre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="members-table-body" class="bg-white divide-y divide-gray-200">
                                    <!-- Les données seront chargées via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Message de chargement -->
                        <div id="loading-message" class="p-8 text-center text-gray-500">
                            <i data-feather="loader" class="w-8 h-8 animate-spin mx-auto mb-4"></i>
                            <p>Chargement des membres...</p>
                        </div>
                        
                        <!-- Message d'erreur -->
                        <div id="error-message" class="p-8 text-center text-red-500 hidden">
                            <i data-feather="alert-circle" class="w-8 h-8 mx-auto mb-4"></i>
                            <p>Erreur lors du chargement des membres</p>
                        </div>
                    </div>
                </div>

                <!-- Modals CRUD pour les membres -->
                
                <!-- Modal Ajouter Membre -->
                <div id="add-member-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Nouveau membre</h3>
                                    <button id="close-add-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <form id="add-member-form" class="p-6 space-y-4">
                                            <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                                    <input type="text" name="first_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                                    <input type="text" name="last_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                                    <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                                    <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <p class="text-xs text-gray-500 mt-1">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                                    <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="student">Étudiant</option>
                                        <option value="admin">Administrateur</option>
                                    </select>
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" id="cancel-add-member" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                                </button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Créer le membre
                                                </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Modifier Membre -->
                <div id="edit-member-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Modifier le membre</h3>
                                    <button id="close-edit-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                                </button>
                                            </div>
                        </div>
                            <form id="edit-member-form" class="p-6 space-y-4">
                                <input type="hidden" name="user_id" id="edit-user-id">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                                    <input type="text" name="first_name" id="edit-first-name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                                    <input type="text" name="last_name" id="edit-last-name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                                    <input type="text" name="username" id="edit-username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" id="edit-email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                                    <select name="role" id="edit-role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="student">Étudiant</option>
                                        <option value="admin">Administrateur</option>
                                    </select>
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" id="cancel-edit-member" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Mettre à jour
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Supprimer Membre -->
                <div id="delete-member-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Supprimer le membre</h3>
                                    <button id="close-delete-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                                <div class="flex items-center space-x-4 mb-4">
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                        <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Êtes-vous sûr de vouloir supprimer ce membre ?</p>
                                        <p class="text-sm text-gray-500" id="delete-member-name"></p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mb-6">
                                    Cette action désactivera le compte du membre. Il ne pourra plus se connecter.
                                </p>
                                <div class="flex justify-end space-x-3">
                                    <button id="cancel-delete-member" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button id="confirm-delete-member" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Réinitialiser Mot de Passe -->
                <div id="reset-password-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Réinitialiser le mot de passe</h3>
                                    <button id="close-reset-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <form id="reset-password-form" class="p-6 space-y-4">
                                <input type="hidden" name="user_id" id="reset-user-id">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe</label>
                                    <input type="password" name="new_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <p class="text-xs text-gray-500 mt-1">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</p>
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" id="cancel-reset-password" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Réinitialiser
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- Section Membres masquée pour non-admins -->
                <?php endif; ?>
                
                <!-- Modals CRUD pour les tâches -->
                
                <!-- Modal Ajouter Tâche -->
                <div id="add-task-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Nouvelle tâche</h3>
                                    <button id="close-add-task-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <form id="add-task-form" class="p-6 space-y-4">
                                <input type="hidden" name="created_by" value="<?php echo $_SESSION['user_id'] ?? ''; ?>">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                                        <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorité *</label>
                                        <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="low">Faible</option>
                                            <option value="medium" selected>Moyenne</option>
                                            <option value="high">Élevée</option>
                                            <option value="urgent">Urgente</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Projet *</label>
                                        <select name="project_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Sélectionner un projet</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance</label>
                                        <input type="datetime-local" name="due_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Assignée à</label>
                                    <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Non assignée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Assignations multiples</label>
                                    <div id="multiple-assignees" class="space-y-2">
                                        <div class="flex items-center space-x-2">
                                            <select name="assigned_users[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="">Sélectionner un utilisateur</option>
                                            </select>
                                            <button type="button" onclick="addAssignee()" class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                                <i data-feather="plus" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" id="cancel-add-task" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Créer la tâche
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Modifier Tâche -->
                <div id="edit-task-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Modifier la tâche</h3>
                                    <button id="close-edit-task-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <form id="edit-task-form" class="p-6 space-y-4">
                                <input type="hidden" name="task_id" id="edit-task-id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                                        <input type="text" name="title" id="edit-task-title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                                        <select name="status" id="edit-task-status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="pending">En attente</option>
                                            <option value="in_progress">En cours</option>
                                            <option value="completed">Terminée</option>
                                            <option value="cancelled">Annulée</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Priorité *</label>
                                        <select name="priority" id="edit-task-priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="low">Faible</option>
                                            <option value="medium">Moyenne</option>
                                            <option value="high">Élevée</option>
                                            <option value="urgent">Urgente</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Assignée à</label>
                                        <select name="assigned_to" id="edit-task-assigned" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Non assignée</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" id="edit-task-description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance</label>
                                    <input type="datetime-local" name="due_date" id="edit-task-due-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" id="cancel-edit-task" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Mettre à jour
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Supprimer Tâche -->
                <div id="delete-task-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Supprimer la tâche</h3>
                                    <button id="close-delete-task-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center space-x-4 mb-4">
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                        <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Êtes-vous sûr de vouloir supprimer cette tâche ?</p>
                                        <p class="text-sm text-gray-500" id="delete-task-title"></p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mb-6">
                                    Cette action est irréversible. La tâche sera définitivement supprimée.
                                </p>
                                <div class="flex justify-end space-x-3">
                                    <button id="cancel-delete-task" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button id="confirm-delete-task" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modals CRUD pour les projets -->
                
                <!-- Modal Ajouter Projet -->
                <div id="add-project-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Nouveau projet</h3>
                                    <button id="close-add-project-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <form id="add-project-form" class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre du projet *</label>
                                        <input type="text" id="add-project-title" name="title" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea id="add-project-description" name="description" rows="3"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Propriétaire</label>
                                        <select id="add-project-owner" name="owner_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Sélectionner un propriétaire</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                        <select id="add-project-status" name="status"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="planning">Planification</option>
                                            <option value="active">Actif</option>
                                            <option value="on_hold">En attente</option>
                                            <option value="completed">Terminé</option>
                                            <option value="cancelled">Annulé</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Visibilité</label>
                                        <select id="add-project-visibility" name="visibility"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="private">Privé</option>
                                            <option value="public">Public</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                                        <input type="date" id="add-project-start-date" name="start_date"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance</label>
                                        <input type="date" id="add-project-due-date" name="due_date"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                                <div class="flex justify-end space-x-3 mt-6">
                                    <button type="button" id="cancel-add-project" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Créer le projet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Modifier Projet -->
                <div id="edit-project-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Modifier le projet</h3>
                                    <button id="close-edit-project-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <form id="edit-project-form" class="p-6">
                                <input type="hidden" id="edit-project-id" name="project_id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre du projet *</label>
                                        <input type="text" id="edit-project-title" name="title" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea id="edit-project-description" name="description" rows="3"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                        <select id="edit-project-status" name="status"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="planning">Planification</option>
                                            <option value="active">Actif</option>
                                            <option value="on_hold">En attente</option>
                                            <option value="completed">Terminé</option>
                                            <option value="cancelled">Annulé</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Visibilité</label>
                                        <select id="edit-project-visibility" name="visibility"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="private">Privé</option>
                                            <option value="public">Public</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                                        <input type="date" id="edit-project-start-date" name="start_date"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance</label>
                                        <input type="date" id="edit-project-due-date" name="due_date"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                                <div class="flex justify-end space-x-3 mt-6">
                                    <button type="button" id="cancel-edit-project" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Mettre à jour
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Supprimer Projet -->
                <div id="delete-project-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800">Supprimer le projet</h3>
                                    <button id="close-delete-project-modal" class="text-gray-400 hover:text-gray-600">
                                        <i data-feather="x" class="w-6 h-6"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center space-x-4 mb-4">
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                        <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Êtes-vous sûr de vouloir supprimer ce projet ?</p>
                                        <p class="text-sm text-gray-500" id="delete-project-title"></p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mb-6">
                                    Cette action est irréversible. Le projet et toutes ses tâches associées seront définitivement supprimés.
                                </p>
                                <div class="flex justify-end space-x-3">
                                    <button id="cancel-delete-project" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Annuler
                                    </button>
                                    <button id="confirm-delete-project" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Autres sections -->
                <div id="events-section" class="tab-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Calendrier des événements</h3>
                            <?php if (defined('IS_ADMIN') ? IS_ADMIN : (isset($_SESSION['role']) && $_SESSION['role']==='admin')): ?>
                            <button id="add-event-btn" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i data-feather="plus" class="w-4 h-4"></i>
                                <span>Nouvel événement</span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="border border-gray-200 rounded-lg">
                                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-800">À venir</h4>
                                    <button id="refresh-events" class="text-sm px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Actualiser</button>
                                </div>
                                <div id="upcoming-events" class="divide-y divide-gray-200">
                                    <div class="p-4 text-gray-500">Chargement...</div>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg">
                                <div class="p-4 border-b border-gray-200">
                                    <h4 class="font-semibold text-gray-800">Passés</h4>
                                </div>
                                <div id="past-events" class="divide-y divide-gray-200">
                                    <div class="p-4 text-gray-500">Chargement...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Ajouter/Éditer Événement -->
                    <div id="event-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="bg-white rounded-xl shadow-xl max-w-xl w-full">
                                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                                    <h3 id="event-modal-title" class="text-lg font-semibold text-gray-800">Nouvel événement</h3>
                                    <button id="close-event-modal" class="text-gray-400 hover:text-gray-600"><i data-feather="x" class="w-6 h-6"></i></button>
                                </div>
                                <form id="event-form" class="p-6 space-y-4">
                                    <input type="hidden" name="id">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                                        <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Lieu</label>
                                            <input type="text" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Visibilité</label>
                                            <select name="visibility" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="public">Public</option>
                                                <option value="private">Privé</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Début *</label>
                                            <input type="date" name="start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Fin</label>
                                            <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                    </div>
                                    <div class="flex justify-end space-x-3 pt-2">
                                        <button type="button" id="cancel-event" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Annuler</button>
                                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tasks-section" class="tab-content">
                    <!-- Statistiques des tâches -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 card-animation">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total tâches</p>
                                    <p id="total-tasks" class="text-2xl font-bold text-gray-800 mt-2">-</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i data-feather="check-square" class="w-6 h-6 text-blue-600"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 card-animation" style="animation-delay: 0.1s">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">En cours</p>
                                    <p id="in-progress-tasks" class="text-2xl font-bold text-yellow-600 mt-2">-</p>
                                </div>
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 card-animation" style="animation-delay: 0.2s">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Terminées</p>
                                    <p id="completed-tasks" class="text-2xl font-bold text-green-600 mt-2">-</p>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 card-animation" style="animation-delay: 0.3s">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">En retard</p>
                                    <p id="overdue-tasks" class="text-2xl font-bold text-red-600 mt-2">-</p>
                                </div>
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i data-feather="alert-triangle" class="w-6 h-6 text-red-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres et actions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">Gestion des tâches</h3>
                                <div class="flex items-center space-x-4">
                                    <!-- Filtres -->
                                    <select id="task-status-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="in_progress">En cours</option>
                                        <option value="completed">Terminées</option>
                                        <option value="cancelled">Annulées</option>
                                    </select>
                                    <select id="task-priority-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Toutes les priorités</option>
                                        <option value="low">Faible</option>
                                        <option value="medium">Moyenne</option>
                                        <option value="high">Élevée</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                    <button id="refresh-tasks" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2">
                                        <i data-feather="refresh-cw" class="w-4 h-4"></i>
                                        <span>Actualiser</span>
                                    </button>
                                    <button id="add-task-btn" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105" style="display:none;">
                                        <i data-feather="plus" class="w-4 h-4"></i>
                                        <span>Nouvelle tâche</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section des tâches avec cartes -->
                        <div class="p-6">
                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4">Gestion des tâches</h2>
                                
                                <!-- Filtres et actions -->
                                <div class="flex flex-wrap items-center gap-4 mb-6">
                                    <select id="task-status-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="in_progress">En cours</option>
                                        <option value="completed">Terminées</option>
                                        <option value="cancelled">Annulées</option>
                                    </select>
                                    
                                    <select id="task-priority-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Toutes les priorités</option>
                                        <option value="low">Faible</option>
                                        <option value="medium">Moyenne</option>
                                        <option value="high">Élevée</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                    
                                    <button id="refresh-tasks" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2">
                                        <i data-feather="refresh-cw" class="w-4 h-4"></i>
                                        <span>Actualiser</span>
                                    </button>
                                    <button id="add-task-btn" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105" style="display:none;">
                                        <i data-feather="plus" class="w-4 h-4"></i>
                                        <span>Nouvelle tâche</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- État de chargement -->
                            <div id="tasks-loading" class="text-center py-12 hidden">
                                <div class="inline-flex items-center space-x-2 text-gray-500">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                    <span>Chargement des tâches...</span>
                                </div>
                            </div>
                            
                            <!-- État d'erreur -->
                            <div id="tasks-error" class="text-center py-12 hidden">
                                <div class="text-red-500">
                                    <i data-feather="alert-circle" class="w-12 h-12 mx-auto mb-4"></i>
                                    <p class="text-lg font-medium">Erreur lors du chargement</p>
                                    <p class="text-sm text-gray-500 mt-2">Veuillez réessayer plus tard</p>
                                </div>
                            </div>
                            
                            <!-- État vide -->
                            <div id="tasks-empty" class="text-center py-12 hidden">
                                <div class="text-gray-500">
                                    <i data-feather="check-square" class="w-12 h-12 mx-auto mb-4"></i>
                                    <p class="text-lg font-medium">Aucune tâche trouvée</p>
                                    <p class="text-sm text-gray-500 mt-2">Créez votre première tâche pour commencer</p>
                                </div>
                            </div>
                            
                            <!-- Grille des cartes de tâches -->
                            <div id="tasks-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                                <!-- Les cartes de tâches seront ajoutées ici -->
                            </div>
                            
                            <!-- Pagination -->
                            <div id="tasks-pagination" class="mt-8 flex items-center justify-between hidden">
                                <div class="text-sm text-gray-700">
                                    Affichage de <span id="tasks-start">1</span> à <span id="tasks-end">10</span> sur <span id="tasks-total">0</span> tâches
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button id="prev-page" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Précédent
                                    </button>
                                    <div id="page-numbers" class="flex items-center space-x-1">
                                        <!-- Les numéros de page seront ajoutés ici -->
                                    </div>
                                    <button id="next-page" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Suivant
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="messages-section" class="tab-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Annonces</h3>
                            <?php if (defined('IS_ADMIN') ? IS_ADMIN : (isset($_SESSION['role']) && $_SESSION['role']==='admin')): ?>
                            <button id="add-announcement-btn" onclick="openAnnouncementModal()" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i data-feather="plus" class="w-4 h-4"></i>
                                <span>Nouvelle annonce</span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div id="announcements-list" class="divide-y divide-gray-200">
                            <div class="p-4 text-gray-500">Chargement...</div>
                        </div>
                    </div>

                    <!-- Modal Ajouter/Éditer Annonce -->
                    <div id="announcement-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="bg-white rounded-xl shadow-xl max-w-xl w-full">
                                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                                    <h3 id="announcement-modal-title" class="text-lg font-semibold text-gray-800">Nouvelle annonce</h3>
                                    <button id="close-announcement-modal" class="text-gray-400 hover:text-gray-600"><i data-feather="x" class="w-6 h-6"></i></button>
                                </div>
                                <form id="announcement-form" onsubmit="return submitAnnouncementForm(event)" class="p-6 space-y-4">
                                    <input type="hidden" name="id">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                                        <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Contenu *</label>
                                        <textarea name="content" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Visibilité</label>
                                            <select name="visibility" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="public">Public</option>
                                                <option value="private">Privé</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center space-x-3 mt-6 md:mt-0">
                                            <input id="ann-pinned" name="pinned" type="checkbox" value="1" class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                                            <label for="ann-pinned" class="text-sm text-gray-700">Épingler</label>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Publication *</label>
                                            <input type="date" name="publish_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiration</label>
                                            <input type="date" name="expire_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                    </div>
                                    <div class="flex justify-end space-x-3 pt-2">
                                        <button type="button" id="cancel-announcement" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Annuler</button>
                                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="settings-section" class="tab-content">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Paramètres du club</h3>
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">Admin uniquement</span>
                    </div>
                        <form id="settings-form" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lien Discord</label>
                                    <input type="url" name="discord_link" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="https://discord.gg/...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email de contact</label>
                                    <input type="email" name="contact_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="contact@club.be">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Charte du club (URL)</label>
                                    <input type="url" name="club_charter_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="https://...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Visibilité par défaut des projets</label>
                                    <select name="projects_default_visibility" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="public">Public</option>
                                        <option value="private">Privé</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorité par défaut des tâches</label>
                                    <select name="tasks_default_priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="low">Faible</option>
                                        <option value="medium">Moyenne</option>
                                        <option value="high">Élevée</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <input id="tasks_allow_multi_assign" name="tasks_allow_multi_assign" type="checkbox" value="1" class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                                    <label for="tasks_allow_multi_assign" class="text-sm text-gray-700">Autoriser les multi-assignations de tâches</label>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" id="save-settings" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Enregistrer</button>
                            </div>
                        </form>
                        <p id="settings-note" class="text-xs text-gray-500 mt-4">Ces paramètres affectent le site public et certaines valeurs par défaut du dashboard.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Variables globales
        let sidebarCollapsed = false;
        let isMobile = false;
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('toggle-sidebar');
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const logoText = document.getElementById('logo-text');
        const navTexts = document.querySelectorAll('.nav-text');

        // Configuration des onglets
        const tabConfig = {
            dashboard: {
                title: 'Tableau de bord',
                description: 'Vue globale du club - projets, événements et membres'
            },
            projects: {
                title: 'Gestion des projets',
                description: 'Créez et suivez vos projets avec statut et progression'
            },
            members: {
                title: 'Gestion des membres',
                description: 'Gérez les membres du club avec leurs rôles et participations'
            },
            events: {
                title: 'Calendrier des événements',
                description: 'Planifiez et organisez vos événements et réunions'
            },
            tasks: {
                title: 'Gestion des tâches',
                description: 'Assignez et suivez les tâches par projet et par membre'
            },
            messages: {
                title: 'Messages internes',
                description: 'Communications et discussions entre membres du club'
            },
            settings: {
                title: 'Paramètres & Authentification',
                description: 'Configurez les rôles, permissions et profils membres'
            }
        };

        // Détecter si mobile
        function checkIfMobile() {
            isMobile = window.innerWidth < 1024;
            return isMobile;
        }

        // Basculer la sidebar
        function toggleSidebar() {
            if (isMobile) {
                toggleMobileSidebar();
            } else {
                toggleDesktopSidebar();
            }
        }

        // Sidebar mobile
        function toggleMobileSidebar() {
            const isHidden = sidebar.classList.contains('-translate-x-full');
            
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('fixed', 'top-0', 'left-0', 'h-full');
                sidebarOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Sidebar desktop
        function toggleDesktopSidebar() {
            sidebarCollapsed = !sidebarCollapsed;
            
            if (sidebarCollapsed) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-16');
                logoText.style.display = 'none';
                navTexts.forEach(text => text.style.display = 'none');
                toggleButton.innerHTML = '<i data-feather="chevron-right" class="w-5 h-5 text-gray-600"></i>';
            } else {
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64');
                logoText.style.display = 'block';
                navTexts.forEach(text => text.style.display = 'block');
                toggleButton.innerHTML = '<i data-feather="menu" class="w-5 h-5 text-gray-600"></i>';
            }
            
            feather.replace();
        }

        // Fermer sidebar mobile
        function closeMobileSidebar() {
            if (isMobile) {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Initialiser sidebar
        function initializeSidebar() {
            checkIfMobile();
            
            if (isMobile) {
                sidebar.classList.add('fixed', 'top-0', 'left-0', 'h-full', '-translate-x-full');
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64');
                logoText.style.display = 'block';
                navTexts.forEach(text => text.style.display = 'block');
                sidebarCollapsed = false;
            } else {
                sidebar.classList.remove('fixed', 'top-0', 'left-0', 'h-full', '-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Changer d'onglet
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            const targetTab = document.getElementById(tabName + '-section');
            if (targetTab) {
                targetTab.classList.add('active');
            }
            
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active', 'bg-primary', 'text-white');
                item.classList.add('text-gray-700');
            });
            
            const activeNavItem = document.querySelector(`[data-tab="${tabName}"]`);
            if (activeNavItem) {
                activeNavItem.classList.add('active', 'bg-primary', 'text-white');
                activeNavItem.classList.remove('text-gray-700');
            }
            
            const config = tabConfig[tabName];
            if (config) {
                document.getElementById('page-title').textContent = config.title;
                document.getElementById('page-description').textContent = config.description;
            }
            
            if (isMobile) {
                closeMobileSidebar();
            }
            
            feather.replace();
        }

        // Notification
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                warning: 'bg-yellow-500',
                error: 'bg-red-500',
                info: 'bg-blue-500'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white p-4 rounded-lg shadow-lg z-50 transform translate-x-full opacity-0 transition-all duration-300`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('translate-x-full', 'opacity-0');
            }, 100);
            
            setTimeout(() => {
                notification.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Gestion des membres
        let membersData = [];
        let filteredMembers = [];

        // Gestion des tâches
        let tasksData = [];
        let filteredTasks = [];
        let currentPage = 1;
        let tasksPerPage = 10;
        let totalPages = 1;
        let usersData = [];

        // Charger les membres
        async function loadMembers() {
            try {
                showLoading(true);
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_all_users'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    membersData = data.users;
                    filteredMembers = [...membersData];
                    displayMembers();
                    loadStats();
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Erreur de connexion');
            } finally {
                showLoading(false);
            }
        }

        // Charger les statistiques
        async function loadStats() {
            try {
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_stats'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('total-members').textContent = data.stats.total_members || 0;
                    document.getElementById('active-members').textContent = data.stats.active_members || 0;
                    document.getElementById('admin-count').textContent = data.stats.admin_count || 0;
                    document.getElementById('student-count').textContent = data.stats.student_count || 0;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
            }
        }

        // Afficher les membres
        function displayMembers() {
            const tbody = document.getElementById('members-table-body');
            tbody.innerHTML = '';

            if (filteredMembers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Aucun membre trouvé
                        </td>
                    </tr>
                `;
                return;
            }

            filteredMembers.forEach(member => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';
                
                const isCurrentUser = member.id == <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
                const statusClass = member.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                const statusText = member.is_active ? 'Actif' : 'Inactif';
                const roleClass = member.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
                const roleText = member.role === 'admin' ? 'Administrateur' : 'Étudiant';
                
                row.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                ${member.first_name.charAt(0).toUpperCase()}${member.last_name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">${member.first_name} ${member.last_name}</p>
                                <p class="text-xs text-gray-500">@${member.username}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">${member.email}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${roleClass}">${roleText}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass}">${statusText}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">${formatDate(member.created_at)}</td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            ${!isCurrentUser ? `
                                <button onclick="editMember(${member.id}, '${member.first_name}', '${member.last_name}', '${member.username}', '${member.email}', '${member.role}')" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded-lg transition-colors" title="Modifier">
                                    <i data-feather="edit" class="w-4 h-4"></i>
                                </button>
                                <button onclick="resetPassword(${member.id}, '${member.first_name} ${member.last_name}')" class="text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 p-2 rounded-lg transition-colors" title="Réinitialiser mot de passe">
                                    <i data-feather="key" class="w-4 h-4"></i>
                                </button>
                                <button onclick="toggleStatus(${member.id})" class="text-${member.is_active ? 'red' : 'green'}-600 hover:text-${member.is_active ? 'red' : 'green'}-800 hover:bg-${member.is_active ? 'red' : 'green'}-50 p-2 rounded-lg transition-colors" title="${member.is_active ? 'Désactiver' : 'Activer'}">
                                    <i data-feather="${member.is_active ? 'user-x' : 'user-check'}" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteMember(${member.id}, '${member.first_name} ${member.last_name}')" class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Supprimer">
                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                </button>
                            ` : '<span class="text-xs text-gray-400">Vous</span>'}
                        </div>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
            
            feather.replace();
        }

        // Mettre à jour le rôle
        async function updateRole(userId, newRole) {
            try {
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_role&user_id=${userId}&new_role=${newRole}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadMembers(); // Recharger la liste
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la mise à jour du rôle', 'error');
            }
        }

        // Basculer le statut
        async function toggleStatus(userId) {
            try {
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=toggle_status&user_id=${userId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadMembers(); // Recharger la liste
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la mise à jour du statut', 'error');
            }
        }

        // Filtrer les membres
        function filterMembers() {
            const roleFilter = document.getElementById('role-filter').value;
            const statusFilter = document.getElementById('status-filter').value;
            
            filteredMembers = membersData.filter(member => {
                const roleMatch = !roleFilter || member.role === roleFilter;
                const statusMatch = !statusFilter || 
                    (statusFilter === 'active' && member.is_active) ||
                    (statusFilter === 'inactive' && !member.is_active);
                
                return roleMatch && statusMatch;
            });
            
            displayMembers();
        }

        // Afficher/masquer le chargement
        function showLoading(show) {
            const loading = document.getElementById('loading-message');
            const error = document.getElementById('error-message');
            const table = document.getElementById('members-table-body').parentElement.parentElement;
            
            if (show) {
                loading.classList.remove('hidden');
                error.classList.add('hidden');
                table.classList.add('hidden');
            } else {
                loading.classList.add('hidden');
                table.classList.remove('hidden');
            }
        }

        // Afficher l'erreur
        function showError(message) {
            const error = document.getElementById('error-message');
            error.querySelector('p').textContent = message;
            error.classList.remove('hidden');
            document.getElementById('loading-message').classList.add('hidden');
        }

        // Formater la date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        // Variables pour les modals
        let currentMemberId = null;

        // Ouvrir modal d'ajout
        function openAddMemberModal() {
            document.getElementById('add-member-modal').classList.remove('hidden');
            document.getElementById('add-member-form').reset();
        }

        // Fermer modal d'ajout
        function closeAddMemberModal() {
            document.getElementById('add-member-modal').classList.add('hidden');
        }

        // Ouvrir modal d'édition
        function editMember(id, firstName, lastName, username, email, role) {
            currentMemberId = id;
            document.getElementById('edit-user-id').value = id;
            document.getElementById('edit-first-name').value = firstName;
            document.getElementById('edit-last-name').value = lastName;
            document.getElementById('edit-username').value = username;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-role').value = role;
            document.getElementById('edit-member-modal').classList.remove('hidden');
        }

        // Fermer modal d'édition
        function closeEditMemberModal() {
            document.getElementById('edit-member-modal').classList.add('hidden');
        }

        // Ouvrir modal de suppression
        function deleteMember(id, name) {
            currentMemberId = id;
            document.getElementById('delete-member-name').textContent = name;
            document.getElementById('delete-member-modal').classList.remove('hidden');
        }

        // Fermer modal de suppression
        function closeDeleteMemberModal() {
            document.getElementById('delete-member-modal').classList.add('hidden');
        }

        // Ouvrir modal de réinitialisation mot de passe
        function resetPassword(id, name) {
            currentMemberId = id;
            document.getElementById('reset-user-id').value = id;
            document.getElementById('reset-password-modal').classList.remove('hidden');
        }

        // Fermer modal de réinitialisation
        function closeResetPasswordModal() {
            document.getElementById('reset-password-modal').classList.add('hidden');
        }

        // Créer un membre
        async function createMember(formData) {
            try {
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeAddMemberModal();
                    loadMembers();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la création du membre', 'error');
            }
        }

        // Mettre à jour un membre
        async function updateMember(formData) {
            try {
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeEditMemberModal();
                    loadMembers();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la mise à jour du membre', 'error');
            }
        }

        // Supprimer un membre
        async function confirmDeleteMember() {
            try {
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_member&user_id=${currentMemberId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeDeleteMemberModal();
                    loadMembers();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression du membre', 'error');
            }
        }

        // Réinitialiser le mot de passe
        async function resetMemberPassword(formData) {
            try {
                const response = await fetch('actions/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeResetPasswordModal();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la réinitialisation du mot de passe', 'error');
            }
        }

        // ===== GESTION DES TÂCHES =====

        // Charger les tâches
        async function loadTasks() {
            try {
                console.log('Loading tasks...');
                showTasksLoading(true);
                const response = await fetch('actions/tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_all_tasks'
                });
                
                const data = await response.json();
                console.log('Tasks response:', data);
                
                if (data.success) {
                    tasksData = data.tasks || [];
                    filteredTasks = [...tasksData];
                    console.log('Tasks loaded:', tasksData.length);
                    
                    displayTasks();
                    loadTasksStats();
                } else {
                    console.error('Error loading tasks:', data.message);
                    showTasksError(data.message);
                }
            } catch (error) {
                console.error('Error loading tasks:', error);
                showTasksError('Erreur de connexion');
            } finally {
                showTasksLoading(false);
            }
        }

        // Charger les utilisateurs pour l'assignation
        async function loadUsers() {
            try {
                const response = await fetch('actions/tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_users_for_assignment'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    usersData = data.users;
                    populateUserSelects();
                }
            } catch (error) {
                console.error('Erreur lors du chargement des utilisateurs:', error);
            }
        }

        // Charger les projets pour l'assignation
        async function loadProjectsForTasks() {
            try {
                const response = await fetch('actions/projects.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_all_projects'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const projectSelect = document.querySelector('select[name="project_id"]');
                    if (projectSelect) {
                        projectSelect.innerHTML = '<option value="">Sélectionner un projet</option>' +
                            data.data.map(project => `<option value="${project.id}">${project.title}</option>`).join('');
                    }
                }
            } catch (error) {
                console.error('Erreur lors du chargement des projets:', error);
            }
        }

        // Ajouter un assigné
        function addAssignee() {
            const container = document.getElementById('multiple-assignees');
            const userSelect = document.querySelector('select[name="assigned_users[]"]');
            const userOptions = userSelect.innerHTML;
            
            const newAssignee = document.createElement('div');
            newAssignee.className = 'flex items-center space-x-2';
            newAssignee.innerHTML = `
                <select name="assigned_users[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    ${userOptions}
                </select>
                <button type="button" onclick="removeAssignee(this)" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    <i data-feather="x" class="w-4 h-4"></i>
                </button>
            `;
            
            container.appendChild(newAssignee);
            feather.replace();
        }

        // Supprimer un assigné
        function removeAssignee(button) {
            button.parentElement.remove();
        }

        // Charger les statistiques des tâches
        async function loadTasksStats() {
            try {
                const response = await fetch('actions/tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_tasks_stats'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('total-tasks').textContent = data.stats.total_tasks || 0;
                    document.getElementById('in-progress-tasks').textContent = data.stats.in_progress_tasks || 0;
                    document.getElementById('completed-tasks').textContent = data.stats.completed_tasks || 0;
                    document.getElementById('overdue-tasks').textContent = data.stats.overdue_tasks || 0;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
            }
        }

        // Afficher les tâches en mode cartes avec pagination
        function displayTasks() {
            const tasksGrid = document.getElementById('tasks-grid');
            const tasksEmpty = document.getElementById('tasks-empty');
            const tasksPagination = document.getElementById('tasks-pagination');

            if (filteredTasks.length === 0) {
                tasksEmpty.classList.remove('hidden');
                tasksGrid.classList.add('hidden');
                tasksPagination.classList.add('hidden');
                return;
            }

            tasksEmpty.classList.add('hidden');
            tasksGrid.classList.remove('hidden');
            

            // Trier les tâches : non terminées en premier
            const sortedTasks = [...filteredTasks].sort((a, b) => {
                // Priorité : pending, in_progress, cancelled, completed
                const statusOrder = { 'pending': 1, 'in_progress': 2, 'cancelled': 3, 'completed': 4 };
                const aOrder = statusOrder[a.status] || 5;
                const bOrder = statusOrder[b.status] || 5;
                
                if (aOrder !== bOrder) {
                    return aOrder - bOrder;
                }
                
                // Si même statut, trier par priorité (urgent en premier)
                const priorityOrder = { 'urgent': 1, 'high': 2, 'medium': 3, 'low': 4 };
                const aPriority = priorityOrder[a.priority] || 5;
                const bPriority = priorityOrder[b.priority] || 5;
                
                if (aPriority !== bPriority) {
                    return aPriority - bPriority;
                }
                
                // Si même priorité, trier par date d'échéance
                if (a.due_date && b.due_date) {
                    return new Date(a.due_date) - new Date(b.due_date);
                }
                
                return 0;
            });

            // Calculer la pagination
            totalPages = Math.ceil(sortedTasks.length / tasksPerPage);
            const startIndex = (currentPage - 1) * tasksPerPage;
            const endIndex = startIndex + tasksPerPage;
            const tasksToShow = sortedTasks.slice(startIndex, endIndex);

            // Vider la grille
            tasksGrid.innerHTML = '';

            // Créer les cartes de tâches
            tasksToShow.forEach(task => {
                const taskCard = createTaskCard(task);
                tasksGrid.appendChild(taskCard);
            });

            // Mettre à jour la pagination
            updatePagination(sortedTasks.length, startIndex, endIndex);

            // Réinitialiser les icônes Feather
            feather.replace();
        }

        // Créer une carte de tâche
        function createTaskCard(task) {
            const card = document.createElement('div');
            card.className = 'bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow';
            card.dataset.taskId = task.id;
            card.dataset.status = task.status;
            
            const statusColors = {
                'pending': 'bg-gray-100 text-gray-800',
                'in_progress': 'bg-yellow-100 text-yellow-800',
                'completed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            };

            const statusText = {
                'pending': 'En attente',
                'in_progress': 'En cours',
                'completed': 'Terminée',
                'cancelled': 'Annulée'
            };

            const priorityColors = {
                'low': 'bg-blue-100 text-blue-800',
                'medium': 'bg-yellow-100 text-yellow-800',
                'high': 'bg-orange-100 text-orange-800',
                'urgent': 'bg-red-100 text-red-800'
            };

            const priorityText = {
                'low': 'Faible',
                'medium': 'Moyenne',
                'high': 'Élevée',
                'urgent': 'Urgente'
            };

            const assignedTo = task.assigned_first_name && task.assigned_last_name 
                ? `${task.assigned_first_name} ${task.assigned_last_name}` 
                : 'Non assignée';
            
            // Afficher les assignations multiples
            const assignees = task.assignees || [];
            const assigneesText = assignees.length > 0 
                ? assignees.map(a => `${a.first_name} ${a.last_name}`).join(', ')
                : 'Non assignée';

            const dueDate = task.due_date ? new Date(task.due_date).toLocaleDateString('fr-FR') : null;
            const isOverdue = task.due_date && new Date(task.due_date) < new Date() && task.status !== 'completed';
            const createdDate = new Date(task.created_at).toLocaleDateString('fr-FR');

            card.innerHTML = `
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 line-clamp-2">${task.title}</h3>
                    ${IS_ADMIN ? `
                    <div class=\"flex items-center space-x-2 ml-4\">
                        <button onclick=\"editTask(${task.id})\" class=\"text-gray-400 hover:text-blue-600 p-1 rounded transition-colors\" title=\"Modifier\">
                            <i data-feather=\"edit\" class=\"w-4 h-4\"></i>
                        </button>
                        <button onclick=\"deleteTask(${task.id}, '${task.title.replace(/"/g, '&quot;').replace(/'/g, "&#39;")}')\" class=\"text-gray-400 hover:text-red-600 p-1 rounded transition-colors\" title=\"Supprimer\">
                            <i data-feather=\"trash-2\" class=\"w-4 h-4\"></i>
                        </button>
                    </div>` : ''}
                </div>
                
                ${task.description ? `<p class="text-gray-600 text-sm mb-4 line-clamp-3">${task.description}</p>` : ''}
                
                <div class="flex items-center justify-between mb-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[task.status]}">
                        ${statusText[task.status]}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${priorityColors[task.priority]}">
                        ${priorityText[task.priority]}
                    </span>
                </div>
                
                ${isOverdue ? '<div class="mb-4"><span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">⚠️ En retard</span></div>' : ''}
                
                <div class="space-y-2 text-sm text-gray-500">
                    <div class="flex items-center">
                        <i data-feather="user" class="w-4 h-4 mr-2"></i>
                        <span>${assigneesText}</span>
                    </div>
                    ${task.project_title ? `
                    <div class="flex items-center">
                        <i data-feather="folder" class="w-4 h-4 mr-2"></i>
                        <span>Projet: ${task.project_title}</span>
                    </div>` : ''}
                    ${dueDate ? `
                        <div class="flex items-center">
                            <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
                            <span>Échéance: ${dueDate}</span>
                        </div>
                    ` : ''}
                    <div class="flex items-center">
                        <i data-feather="clock" class="w-4 h-4 mr-2"></i>
                        <span>Créée le: ${createdDate}</span>
                    </div>
                </div>
                
                ${task.status !== 'completed' ? `
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <button onclick="updateTaskStatus(${task.id}, 'completed')" class="w-full px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-md transition-colors">
                            Marquer comme terminée
                        </button>
                    </div>
                ` : ''}
            `;
            
            return card;
        }

        // Mettre à jour la pagination
        function updatePagination(totalTasks, startIndex, endIndex) {
            const tasksPagination = document.getElementById('tasks-pagination');
            const tasksStart = document.getElementById('tasks-start');
            const tasksEnd = document.getElementById('tasks-end');
            const tasksTotal = document.getElementById('tasks-total');
            const prevButton = document.getElementById('prev-page');
            const nextButton = document.getElementById('next-page');
            const pageNumbers = document.getElementById('page-numbers');

            if (totalTasks === 0) {
                tasksPagination.classList.add('hidden');
                return;
            }

            tasksPagination.classList.remove('hidden');
            tasksStart.textContent = startIndex + 1;
            tasksEnd.textContent = Math.min(endIndex, totalTasks);
            tasksTotal.textContent = totalTasks;

            // Boutons précédent/suivant
            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;

            // Numéros de page
            pageNumbers.innerHTML = '';
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                    i === currentPage 
                        ? 'bg-primary text-white' 
                        : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
                }`;
                pageButton.textContent = i;
                pageButton.addEventListener('click', () => goToPage(i));
                pageNumbers.appendChild(pageButton);
            }
        }

        // Aller à une page spécifique
        function goToPage(page) {
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                displayTasks();
            }
        }

        // Filtrer les tâches
        function filterTasks() {
            const statusFilter = document.getElementById('task-status-filter').value;
            const priorityFilter = document.getElementById('task-priority-filter').value;
            
            filteredTasks = tasksData.filter(task => {
                const statusMatch = !statusFilter || task.status === statusFilter;
                const priorityMatch = !priorityFilter || task.priority === priorityFilter;
                
                return statusMatch && priorityMatch;
            });
            
            displayTasks();
        }

        // Afficher/masquer le chargement des tâches
        function showTasksLoading(show) {
            const loading = document.getElementById('tasks-loading');
            const error = document.getElementById('tasks-error');
            const empty = document.getElementById('tasks-empty');
            const grid = document.getElementById('tasks-grid');
            const pagination = document.getElementById('tasks-pagination');
            
            if (show) {
                if (loading) loading.classList.remove('hidden');
                if (error) error.classList.add('hidden');
                if (empty) empty.classList.add('hidden');
                if (grid) grid.classList.add('hidden');
                if (pagination) pagination.classList.add('hidden');
            } else {
                if (loading) loading.classList.add('hidden');
            }
        }

        // Afficher l'erreur des tâches
        function showTasksError(message) {
            const error = document.getElementById('tasks-error');
            const loading = document.getElementById('tasks-loading');
            const grid = document.getElementById('tasks-grid');
            const pagination = document.getElementById('tasks-pagination');
            
            if (error) {
                const errorText = error.querySelector('p');
                if (errorText) errorText.textContent = message;
                error.classList.remove('hidden');
            }
            if (loading) loading.classList.add('hidden');
            if (grid) grid.classList.add('hidden');
            if (pagination) pagination.classList.add('hidden');
        }

        // Remplir les selects d'utilisateurs
        function populateUserSelects() {
            const selects = document.querySelectorAll('select[name="assigned_to"]');
            selects.forEach(select => {
                select.innerHTML = '<option value="">Non assignée</option>';
                usersData.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.first_name} ${user.last_name}`;
                    select.appendChild(option);
                });
            });
        }

        // Variables pour les modals de tâches
        let currentTaskId = null;

        // Ouvrir modal d'ajout de tâche
        function openAddTaskModal() {
            document.getElementById('add-task-modal').classList.remove('hidden');
            document.getElementById('add-task-form').reset();
            loadProjectsForTasks();
        }

        // Fermer modal d'ajout de tâche
        function closeAddTaskModal() {
            document.getElementById('add-task-modal').classList.add('hidden');
        }

        // Ouvrir modal d'édition de tâche
        function editTask(taskId) {
            const task = tasksData.find(t => t.id == taskId);
            if (!task) return;

            currentTaskId = taskId;
            document.getElementById('edit-task-id').value = taskId;
            document.getElementById('edit-task-title').value = task.title;
            document.getElementById('edit-task-description').value = task.description || '';
            document.getElementById('edit-task-status').value = task.status;
            document.getElementById('edit-task-priority').value = task.priority;
            document.getElementById('edit-task-assigned').value = task.assigned_to || '';
            document.getElementById('edit-task-due-date').value = task.due_date ? task.due_date.slice(0, 16) : '';
            
            document.getElementById('edit-task-modal').classList.remove('hidden');
        }

        // Fermer modal d'édition de tâche
        function closeEditTaskModal() {
            document.getElementById('edit-task-modal').classList.add('hidden');
        }

        // Ouvrir modal de suppression de tâche
        function deleteTask(taskId, title) {
            currentTaskId = taskId;
            document.getElementById('delete-task-title').textContent = title;
            document.getElementById('delete-task-modal').classList.remove('hidden');
        }

        // Fermer modal de suppression de tâche
        function closeDeleteTaskModal() {
            document.getElementById('delete-task-modal').classList.add('hidden');
        }

        // Créer une tâche
        async function createTask(formData) {
            try {
                const response = await fetch('actions/tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeAddTaskModal();
                    loadTasks();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la création de la tâche', 'error');
            }
        }

        // Mettre à jour une tâche
        async function updateTask(formData) {
            try {
                const response = await fetch('actions/tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeEditTaskModal();
                    loadTasks();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la mise à jour de la tâche', 'error');
            }
        }

        // Mettre à jour le statut d'une tâche
        async function updateTaskStatus(taskId, newStatus) {
            try {
                const response = await fetch('actions/tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_task_status&task_id=${taskId}&status=${newStatus}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadTasks();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la mise à jour du statut', 'error');
            }
        }

        // Supprimer une tâche
        async function confirmDeleteTask() {
            try {
                const response = await fetch('actions/tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_task&task_id=${currentTaskId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeDeleteTaskModal();
                    loadTasks();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression de la tâche', 'error');
            }
        }

        // ==================== FONCTIONS POUR LES PROJETS ====================
        
        // Variables pour les projets
        let projectsData = [];
        let currentProjectId = null;

        // Charger les projets
        async function loadProjects() {
            try {
                showProjectsLoading();
                
                const response = await fetch('actions/projects.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_all_projects'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    projectsData = data.data;
                    displayProjects(projectsData);
                    loadProjectsStats();
                } else {
                    showProjectsError();
                }
            } catch (error) {
                console.error('Erreur lors du chargement des projets:', error);
                showProjectsError();
            }
        }

        // Afficher les projets
        function displayProjects(projects) {
            const content = document.getElementById('projects-content');
            const loading = document.getElementById('projects-loading');
            const error = document.getElementById('projects-error');
            
            if (loading) loading.classList.add('hidden');
            if (error) error.classList.add('hidden');
            if (content) content.classList.remove('hidden');
            
            if (!content) return;
            
            if (projects.length === 0) {
                content.innerHTML = `
                    <div class="text-center py-12">
                        <i data-feather="folder" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <p class="text-gray-600">Aucun projet trouvé</p>
                        ${IS_ADMIN ? `
                        <button onclick="openAddProjectModal()" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Créer le premier projet
                        </button>` : ''}
                    </div>
                `;
                feather.replace();
                return;
            }
            
            const projectsHtml = projects.map(project => createProjectCard(project)).join('');
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    ${projectsHtml}
                </div>
            `;
            
            feather.replace();
        }

        // Créer une carte de projet
        function createProjectCard(project) {
            const statusColors = {
                'planning': 'bg-blue-100 text-blue-800',
                'active': 'bg-green-100 text-green-800',
                'completed': 'bg-purple-100 text-purple-800',
                'on_hold': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            
            const statusLabels = {
                'planning': 'Planification',
                'active': 'Actif',
                'completed': 'Terminé',
                'on_hold': 'En attente',
                'cancelled': 'Annulé'
            };
            
            const statusColor = statusColors[project.status] || 'bg-gray-100 text-gray-800';
            const statusLabel = statusLabels[project.status] || project.status;
            
            const dueDate = project.due_date ? new Date(project.due_date).toLocaleDateString('fr-FR') : 'Non définie';
            const isOverdue = project.due_date && new Date(project.due_date) < new Date() && project.status !== 'completed' && project.status !== 'cancelled';
            
            return `
                <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">${project.title}</h3>
                            <p class="text-sm text-gray-600 mb-3">${project.description || 'Aucune description'}</p>
                        </div>
                        ${IS_ADMIN ? `
                        <div class="flex space-x-2 ml-4">
                            <button onclick="editProject(${project.id})" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded-lg transition-colors" title="Modifier">
                                <i data-feather=\"edit\" class=\"w-4 h-4\"></i>
                            </button>
                            <button onclick="deleteProject(${project.id}, '${project.title.replace(/"/g, '&quot;').replace(/'/g, "&#39;")}')" class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Supprimer">
                                <i data-feather=\"trash-2\" class=\"w-4 h-4\"></i>
                            </button>
                        </div>` : ''}
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Statut</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColor}">${statusLabel}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Propriétaire</span>
                            <span class="text-sm font-medium text-gray-800">${project.owner_first_name} ${project.owner_last_name}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Visibilité</span>
                            <span class="text-sm font-medium ${project.visibility === 'public' ? 'text-green-600' : 'text-gray-600'}">
                                ${project.visibility === 'public' ? 'Public' : 'Privé'}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Échéance</span>
                            <span class="text-sm font-medium ${isOverdue ? 'text-red-600' : 'text-gray-800'}">${dueDate}</span>
                        </div>
                        
                        ${project.start_date ? `
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Début</span>
                            <span class="text-sm font-medium text-gray-800">${new Date(project.start_date).toLocaleDateString('fr-FR')}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        // Charger les statistiques des projets
        async function loadProjectsStats() {
            try {
                const response = await fetch('actions/projects.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_projects_stats'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.data;
                    document.getElementById('total-projects').textContent = stats.total_projects || 0;
                    document.getElementById('active-projects').textContent = stats.active_projects || 0;
                    document.getElementById('completed-projects').textContent = stats.completed_projects || 0;
                    document.getElementById('overdue-projects').textContent = stats.overdue_projects || 0;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques des projets:', error);
            }
        }

        // Afficher le chargement des projets
        function showProjectsLoading() {
            const loading = document.getElementById('projects-loading');
            const error = document.getElementById('projects-error');
            const content = document.getElementById('projects-content');
            
            if (loading) loading.classList.remove('hidden');
            if (error) error.classList.add('hidden');
            if (content) content.classList.add('hidden');
        }

        // Afficher l'erreur des projets
        function showProjectsError() {
            const loading = document.getElementById('projects-loading');
            const error = document.getElementById('projects-error');
            const content = document.getElementById('projects-content');
            
            if (loading) loading.classList.add('hidden');
            if (error) error.classList.remove('hidden');
            if (content) content.classList.add('hidden');
        }

        // Filtrer les projets
        function filterProjects() {
            const statusFilter = document.getElementById('projects-status-filter').value;
            let filteredProjects = projectsData;
            
            if (statusFilter) {
                filteredProjects = projectsData.filter(project => project.status === statusFilter);
            }
            
            displayProjects(filteredProjects);
        }

        // Charger les utilisateurs pour l'assignation
        async function loadUsersForProjects() {
            try {
                const response = await fetch('actions/projects.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_users_for_assignment'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const userSelect = document.getElementById('add-project-owner');
                    if (userSelect) {
                        userSelect.innerHTML = '<option value="">Sélectionner un propriétaire</option>' +
                            data.data.map(user => `<option value="${user.id}">${user.first_name} ${user.last_name} (${user.username})</option>`).join('');
                    }
                }
            } catch (error) {
                console.error('Erreur lors du chargement des utilisateurs:', error);
            }
        }

        // Ouvrir modal d'ajout de projet
        function openAddProjectModal() {
            document.getElementById('add-project-modal').classList.remove('hidden');
            document.getElementById('add-project-form').reset();
            loadUsersForProjects();
        }

        // Fermer modal d'ajout de projet
        function closeAddProjectModal() {
            document.getElementById('add-project-modal').classList.add('hidden');
        }

        // Ouvrir modal d'édition de projet
        function editProject(projectId) {
            const project = projectsData.find(p => p.id == projectId);
            if (!project) return;
            
            currentProjectId = projectId;
            document.getElementById('edit-project-id').value = projectId;
            document.getElementById('edit-project-title').value = project.title;
            document.getElementById('edit-project-description').value = project.description || '';
            document.getElementById('edit-project-status').value = project.status;
            document.getElementById('edit-project-visibility').value = project.visibility;
            document.getElementById('edit-project-start-date').value = project.start_date || '';
            document.getElementById('edit-project-due-date').value = project.due_date || '';
            
            document.getElementById('edit-project-modal').classList.remove('hidden');
        }

        // Fermer modal d'édition de projet
        function closeEditProjectModal() {
            document.getElementById('edit-project-modal').classList.add('hidden');
        }

        // Ouvrir modal de suppression de projet
        function deleteProject(projectId, title) {
            currentProjectId = projectId;
            document.getElementById('delete-project-title').textContent = title;
            document.getElementById('delete-project-modal').classList.remove('hidden');
        }

        // Fermer modal de suppression de projet
        function closeDeleteProjectModal() {
            document.getElementById('delete-project-modal').classList.add('hidden');
        }

        // Créer un projet
        async function createProject(formData) {
            try {
                const response = await fetch('actions/projects.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeAddProjectModal();
                    loadProjects();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la création du projet', 'error');
            }
        }

        // Mettre à jour un projet
        async function updateProject(formData) {
            try {
                const response = await fetch('actions/projects.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeEditProjectModal();
                    loadProjects();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la mise à jour du projet', 'error');
            }
        }

        // Confirmer suppression de projet
        async function confirmDeleteProject() {
            try {
                const response = await fetch('actions/projects.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_project&project_id=${currentProjectId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeDeleteProjectModal();
                    loadProjects();
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression du projet', 'error');
            }
        }

        // ============================================
        // DASHBOARD OVERVIEW
        // ============================================
        async function loadDashboardOverview() {
            try {
                // Charger les statistiques (certaines sont admin-only, on utilise donc les listes pour compter)
                const [projectsRes, eventsRes, membersRes, announcementsRes] = await Promise.all([
                    fetch('actions/projects.php?action=get_all_projects'),
                    fetch('actions/events.php?action=list_upcoming'),
                    IS_ADMIN ? fetch('actions/members.php?action=get_stats') : Promise.resolve({json: () => ({success: false})}),
                    fetch('actions/announcements.php?action=get_stats')
                ]);

                const projects = await projectsRes.json();
                const events = await eventsRes.json();
                const members = IS_ADMIN ? await membersRes.json() : {success: false};
                const announcements = await announcementsRes.json();

                // Mettre à jour les compteurs
                document.getElementById('stat-projects').textContent = projects.success ? projects.data.length : '0';
                document.getElementById('stat-events').textContent = events.success ? events.data.length : '0';
                document.getElementById('stat-members').textContent = members.success ? members.data.total : '0';
                document.getElementById('stat-announcements').textContent = announcements.success ? announcements.data.total : '0';

                // Charger les listes
                loadDashboardLists();
            } catch (error) {
                console.error('Erreur chargement dashboard:', error);
            }
        }

        async function loadDashboardLists() {
            try {
                // Prochains événements
                const eventsRes = await fetch('actions/events.php?action=list_upcoming');
                const eventsData = await eventsRes.json();
                const eventsContainer = document.getElementById('list-next-events');
                
                if (eventsData.success && eventsData.data.length > 0) {
                    eventsContainer.innerHTML = eventsData.data.slice(0, 3).map(event => `
                        <div class="flex items-start space-x-3 p-3 bg-gradient-to-r from-purple-50 to-transparent rounded-lg border-l-2 border-purple-400 hover:shadow-md transition-all duration-200">
                            <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-feather="calendar" class="w-4 h-4 text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">${event.title}</p>
                                <p class="text-sm text-purple-600">${new Date(event.start_date).toLocaleDateString('fr-FR')}</p>
                            </div>
                        </div>
                    `).join('');
                } else {
                    eventsContainer.innerHTML = '<p class="text-gray-500 text-sm italic">Aucun événement à venir</p>';
                }

                // Dernières annonces
                const announcementsRes = await fetch('actions/announcements.php?action=list_latest');
                const announcementsData = await announcementsRes.json();
                const announcementsContainer = document.getElementById('list-latest-announcements');
                
                if (announcementsData.success && announcementsData.data.length > 0) {
                    announcementsContainer.innerHTML = announcementsData.data.slice(0, 3).map(announcement => `
                        <div class="flex items-start space-x-3 p-3 bg-gradient-to-r from-orange-50 to-transparent rounded-lg border-l-2 border-orange-400 hover:shadow-md transition-all duration-200">
                            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-feather="message-circle" class="w-4 h-4 text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">${announcement.title}</p>
                                <p class="text-sm text-orange-600">${new Date(announcement.created_at).toLocaleDateString('fr-FR')}</p>
                            </div>
                        </div>
                    `).join('');
                } else {
                    announcementsContainer.innerHTML = '<p class="text-gray-500 text-sm italic">Aucune annonce</p>';
                }

                // Dernières tâches
                const tasksRes = await fetch('actions/tasks.php?action=get_all_tasks');
                const tasksData = await tasksRes.json();
                const tasksContainer = document.getElementById('list-latest-tasks');
                
                if (tasksData.success && tasksData.data.length > 0) {
                    const statusColors = {
                        'en_attente': { bg: 'from-yellow-50', border: 'border-yellow-400', icon: 'bg-yellow-500', text: 'text-yellow-700' },
                        'en_cours': { bg: 'from-blue-50', border: 'border-blue-400', icon: 'bg-blue-500', text: 'text-blue-700' },
                        'terminee': { bg: 'from-green-50', border: 'border-green-400', icon: 'bg-green-500', text: 'text-green-700' }
                    };
                    tasksContainer.innerHTML = tasksData.data.slice(0, 2).map(task => {
                        const color = statusColors[task.status] || statusColors['en_attente'];
                        const statusLabel = { 'en_attente': 'En attente', 'en_cours': 'En cours', 'terminee': 'Terminée' }[task.status] || task.status;
                        return `
                        <div class="flex items-start space-x-3 p-3 bg-gradient-to-r ${color.bg} to-transparent rounded-lg border-l-2 ${color.border} hover:shadow-md transition-all duration-200">
                            <div class="w-8 h-8 ${color.icon} rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-feather="check-square" class="w-4 h-4 text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">${task.title}</p>
                                <p class="text-sm ${color.text}">${statusLabel}</p>
                            </div>
                        </div>
                    `;}).join('');
                } else {
                    tasksContainer.innerHTML = '<p class="text-gray-500 text-sm italic">Aucune tâche</p>';
                }

                // Derniers projets
                const projectsRes = await fetch('actions/projects.php?action=get_all_projects');
                const projectsData = await projectsRes.json();
                const projectsContainer = document.getElementById('list-latest-projects');
                
                if (projectsData.success && projectsData.data.length > 0) {
                    const projectStatusColors = {
                        'en_cours': { bg: 'from-cyan-50', border: 'border-cyan-400', icon: 'bg-cyan-500', text: 'text-cyan-700' },
                        'termine': { bg: 'from-green-50', border: 'border-green-400', icon: 'bg-green-500', text: 'text-green-700' },
                        'en_attente': { bg: 'from-gray-50', border: 'border-gray-400', icon: 'bg-gray-500', text: 'text-gray-700' }
                    };
                    projectsContainer.innerHTML = projectsData.data.slice(0, 2).map(project => {
                        const color = projectStatusColors[project.status] || projectStatusColors['en_attente'];
                        const statusLabel = { 'en_cours': 'En cours', 'termine': 'Terminé', 'en_attente': 'En attente' }[project.status] || project.status;
                        return `
                        <div class="flex items-start space-x-3 p-3 bg-gradient-to-r ${color.bg} to-transparent rounded-lg border-l-2 ${color.border} hover:shadow-md transition-all duration-200">
                            <div class="w-8 h-8 ${color.icon} rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-feather="folder" class="w-4 h-4 text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">${project.name}</p>
                                <p class="text-sm ${color.text}">${statusLabel}</p>
                            </div>
                        </div>
                    `;}).join('');
                } else {
                    projectsContainer.innerHTML = '<p class="text-gray-500 text-sm italic">Aucun projet</p>';
                }

                feather.replace();
            } catch (error) {
                console.error('Erreur chargement listes dashboard:', error);
            }
        }

        // Événements
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
            
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tabName = this.getAttribute('data-tab');
                    if (tabName) {
                        switchTab(tabName);
                        // Charger les données si on accède à une section spécifique
                        if (tabName === 'dashboard') {
                            loadDashboardOverview();
                        } else if (tabName === 'members') {
                            loadMembers();
                        } else if (tabName === 'tasks') {
                            loadTasks();
                            loadUsers();
                        } else if (tabName === 'projects') {
                            loadProjects();
                        } else if (tabName === 'settings' && IS_ADMIN) {
                            loadSettings();
                        } else if (tabName === 'events') {
                            loadEvents();
                        } else if (tabName === 'announcements') {
                            loadAnnouncements();
                        }
                    }
                });
            });
            
            // Événements pour les filtres des membres
            const roleFilterEl = document.getElementById('role-filter');
            if (roleFilterEl) roleFilterEl.addEventListener('change', filterMembers);
            const statusFilterEl = document.getElementById('status-filter');
            if (statusFilterEl) statusFilterEl.addEventListener('change', filterMembers);
            const refreshMembersEl = document.getElementById('refresh-members');
            if (refreshMembersEl) refreshMembersEl.addEventListener('click', loadMembers);
             
             // Événements pour les filtres des tâches
            const taskStatusFilterEl = document.getElementById('task-status-filter');
            if (taskStatusFilterEl) taskStatusFilterEl.addEventListener('change', filterTasks);
            const taskPriorityFilterEl = document.getElementById('task-priority-filter');
            if (taskPriorityFilterEl) taskPriorityFilterEl.addEventListener('change', filterTasks);
            const refreshTasksEl = document.getElementById('refresh-tasks');
            if (refreshTasksEl) refreshTasksEl.addEventListener('click', loadTasks);
             
             // Événements pour les filtres des projets
            const projectsStatusFilterEl = document.getElementById('projects-status-filter');
            if (projectsStatusFilterEl) projectsStatusFilterEl.addEventListener('change', filterProjects);
            if (IS_ADMIN) {
                document.getElementById('add-project-btn').style.display = '';
                document.getElementById('add-project-btn').addEventListener('click', openAddProjectModal);
                const saveSettingsBtn = document.getElementById('save-settings');
                if (saveSettingsBtn) {
                    saveSettingsBtn.addEventListener('click', saveSettings);
                }
                const addEventBtn = document.getElementById('add-event-btn');
                if (addEventBtn) addEventBtn.addEventListener('click', () => openEventModal());
            }
            const refreshEventsBtn = document.getElementById('refresh-events');
            if (refreshEventsBtn) refreshEventsBtn.addEventListener('click', loadEvents);
             
             // Événements pour la pagination
            const prevPageBtn = document.getElementById('prev-page');
            if (prevPageBtn) prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    goToPage(currentPage - 1);
                }
            });
             
            const nextPageBtn = document.getElementById('next-page');
            if (nextPageBtn) nextPageBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    goToPage(currentPage + 1);
                }
            });
             
             // Événements pour les modals des membres
            const addMemberBtn = document.getElementById('add-member-btn');
            if (addMemberBtn) addMemberBtn.addEventListener('click', openAddMemberModal);
            const closeAddMemberBtn = document.getElementById('close-add-modal');
            if (closeAddMemberBtn) closeAddMemberBtn.addEventListener('click', closeAddMemberModal);
            const cancelAddMemberBtn = document.getElementById('cancel-add-member');
            if (cancelAddMemberBtn) cancelAddMemberBtn.addEventListener('click', closeAddMemberModal);
             
             // Événements pour les modals des tâches
            if (IS_ADMIN) {
                const addTaskBtn = document.getElementById('add-task-btn');
                if (addTaskBtn) {
                    addTaskBtn.style.display = '';
                    addTaskBtn.addEventListener('click', openAddTaskModal);
                }
            }
            const closeAddTaskBtn = document.getElementById('close-add-task-modal');
            if (closeAddTaskBtn) closeAddTaskBtn.addEventListener('click', closeAddTaskModal);
            const cancelAddTaskBtn = document.getElementById('cancel-add-task');
            if (cancelAddTaskBtn) cancelAddTaskBtn.addEventListener('click', closeAddTaskModal);
             
             
            const closeEditTaskBtn = document.getElementById('close-edit-task-modal');
            if (closeEditTaskBtn) closeEditTaskBtn.addEventListener('click', closeEditTaskModal);
            const cancelEditTaskBtn = document.getElementById('cancel-edit-task');
            if (cancelEditTaskBtn) cancelEditTaskBtn.addEventListener('click', closeEditTaskModal);
             
            const closeDeleteTaskBtn = document.getElementById('close-delete-task-modal');
            if (closeDeleteTaskBtn) closeDeleteTaskBtn.addEventListener('click', closeDeleteTaskModal);
            const cancelDeleteTaskBtn = document.getElementById('cancel-delete-task');
            if (cancelDeleteTaskBtn) cancelDeleteTaskBtn.addEventListener('click', closeDeleteTaskModal);
            const confirmDeleteTaskBtn = document.getElementById('confirm-delete-task');
            if (confirmDeleteTaskBtn) confirmDeleteTaskBtn.addEventListener('click', confirmDeleteTask);
             
            // Événements pour les modals des projets
            const closeAddProjectBtn = document.getElementById('close-add-project-modal');
            if (closeAddProjectBtn) closeAddProjectBtn.addEventListener('click', closeAddProjectModal);
            const cancelAddProjectBtn = document.getElementById('cancel-add-project');
            if (cancelAddProjectBtn) cancelAddProjectBtn.addEventListener('click', closeAddProjectModal);
             
            const closeEditProjectBtn = document.getElementById('close-edit-project-modal');
            if (closeEditProjectBtn) closeEditProjectBtn.addEventListener('click', closeEditProjectModal);
            const cancelEditProjectBtn = document.getElementById('cancel-edit-project');
            if (cancelEditProjectBtn) cancelEditProjectBtn.addEventListener('click', closeEditProjectModal);
             
            const closeDeleteProjectBtn = document.getElementById('close-delete-project-modal');
            if (closeDeleteProjectBtn) closeDeleteProjectBtn.addEventListener('click', closeDeleteProjectModal);
            const cancelDeleteProjectBtn = document.getElementById('cancel-delete-project');
            if (cancelDeleteProjectBtn) cancelDeleteProjectBtn.addEventListener('click', closeDeleteProjectModal);
            const confirmDeleteProjectBtn = document.getElementById('confirm-delete-project');
            if (confirmDeleteProjectBtn) confirmDeleteProjectBtn.addEventListener('click', confirmDeleteProject);
             
            const closeEditMemberBtn = document.getElementById('close-edit-modal');
            if (closeEditMemberBtn) closeEditMemberBtn.addEventListener('click', closeEditMemberModal);
            const cancelEditMemberBtn = document.getElementById('cancel-edit-member');
            if (cancelEditMemberBtn) cancelEditMemberBtn.addEventListener('click', closeEditMemberModal);
             
            const closeDeleteMemberBtn = document.getElementById('close-delete-modal');
            if (closeDeleteMemberBtn) closeDeleteMemberBtn.addEventListener('click', closeDeleteMemberModal);
            const cancelDeleteMemberBtn = document.getElementById('cancel-delete-member');
            if (cancelDeleteMemberBtn) cancelDeleteMemberBtn.addEventListener('click', closeDeleteMemberModal);
            const confirmDeleteMemberBtn = document.getElementById('confirm-delete-member');
            if (confirmDeleteMemberBtn) confirmDeleteMemberBtn.addEventListener('click', confirmDeleteMember);
             
            const closeResetModalBtn = document.getElementById('close-reset-modal');
            if (closeResetModalBtn) closeResetModalBtn.addEventListener('click', closeResetPasswordModal);
            const cancelResetPasswordBtn = document.getElementById('cancel-reset-password');
            if (cancelResetPasswordBtn) cancelResetPasswordBtn.addEventListener('click', closeResetPasswordModal);
             
            // Événements pour les formulaires des membres
            const addMemberForm = document.getElementById('add-member-form');
            if (addMemberForm) addMemberForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'create_member');
                createMember(new URLSearchParams(formData));
            });
             
            const editMemberForm = document.getElementById('edit-member-form');
            if (editMemberForm) editMemberForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'update_member');
                updateMember(new URLSearchParams(formData));
            });
             
            const resetPasswordForm = document.getElementById('reset-password-form');
            if (resetPasswordForm) resetPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'reset_password');
                resetMemberPassword(new URLSearchParams(formData));
            });
             
            // Événements pour les formulaires des tâches
            const addTaskForm = document.getElementById('add-task-form');
            if (addTaskForm) addTaskForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'create_task');
                createTask(new URLSearchParams(formData));
            });
             
            const editTaskForm = document.getElementById('edit-task-form');
            if (editTaskForm) editTaskForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'update_task');
                updateTask(new URLSearchParams(formData));
            });
             
            // Événements pour les formulaires des projets
            const addProjectForm = document.getElementById('add-project-form');
            if (addProjectForm) addProjectForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'create_project');
                createProject(new URLSearchParams(formData));
            });
             
            const editProjectForm = document.getElementById('edit-project-form');
            if (editProjectForm) editProjectForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'update_project');
                updateProject(new URLSearchParams(formData));
            });
            
            // Fermer les modals en cliquant sur l'overlay
            document.querySelectorAll('[id$="-modal"]').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                    }
                });
            });
            
            if (toggleButton) {
                toggleButton.addEventListener('click', toggleSidebar);
            }
            
            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', function() {
                    if (isMobile) {
                        toggleMobileSidebar();
                    }
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeMobileSidebar);
            }
            
            window.addEventListener('resize', function() {
                const wasMobile = isMobile;
                checkIfMobile();
                
                if (wasMobile !== isMobile) {
                    initializeSidebar();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && isMobile) {
                    closeMobileSidebar();
                }
            });
            
            switchTab('dashboard');
            loadDashboardOverview();
            
            setTimeout(() => {
                showNotification('Dashboard Club Pro chargé avec succès !', 'success');
            }, 1000);
            
            feather.replace();
            // Charger les annonces au démarrage si besoin
            loadAnnouncements();
        });

        // Charger les paramètres (admin)
        async function loadSettings() {
            try {
                const response = await fetch('actions/settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=get_settings'
                });
                const data = await response.json();
                if (data.success) {
                    const form = document.getElementById('settings-form');
                    if (!form) return;
                    const s = data.data || {};
                    form.discord_link.value = s.discord_link || '';
                    form.contact_email.value = s.contact_email || '';
                    form.club_charter_url.value = s.club_charter_url || '';
                    form.projects_default_visibility.value = s.projects_default_visibility || 'public';
                    form.tasks_default_priority.value = s.tasks_default_priority || 'medium';
                    document.getElementById('tasks_allow_multi_assign').checked = (s.tasks_allow_multi_assign === '1');
                } else {
                    showNotification(data.message || 'Erreur lors du chargement des paramètres', 'error');
                }
            } catch (e) {
                showNotification('Erreur réseau lors du chargement des paramètres', 'error');
            }
        }

        // Enregistrer les paramètres (admin)
        async function saveSettings() {
            const form = document.getElementById('settings-form');
            if (!form) return;
            const params = new URLSearchParams();
            params.append('action', 'update_settings');
            params.append('discord_link', form.discord_link.value || '');
            params.append('contact_email', form.contact_email.value || '');
            params.append('club_charter_url', form.club_charter_url.value || '');
            params.append('projects_default_visibility', form.projects_default_visibility.value || 'public');
            params.append('tasks_default_priority', form.tasks_default_priority.value || 'medium');
            params.append('tasks_allow_multi_assign', document.getElementById('tasks_allow_multi_assign').checked ? '1' : '0');
            try {
                const response = await fetch('actions/settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params.toString()
                });
                const data = await response.json();
                if (data.success) {
                    showNotification('Paramètres enregistrés', 'success');
                } else {
                    showNotification(data.message || 'Échec de l\'enregistrement', 'error');
                }
            } catch (e) {
                showNotification('Erreur réseau lors de l\'enregistrement', 'error');
            }
        }

        // ===== Événements =====
        function renderEventRow(ev) {
            const start = ev.start_date ? new Date(ev.start_date + 'T00:00:00').toLocaleDateString('fr-FR') : '';
            const end = ev.end_date ? new Date(ev.end_date + 'T00:00:00').toLocaleDateString('fr-FR') : '';
            const privateTag = ev.visibility === 'private' ? '<span class="ml-2 text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full">Privé</span>' : '';
            const actions = IS_ADMIN ? `
                <div class="flex items-center gap-2">
                    <button class="text-blue-600 hover:underline" data-action="edit" data-id="${ev.id}">Éditer</button>
                    <button class="text-red-600 hover:underline" data-action="delete" data-id="${ev.id}">Supprimer</button>
                </div>` : '';
            return `
                <div class="p-4 flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">${ev.title}${privateTag}</p>
                        <p class="text-sm text-gray-600">${start}${end ? ' — ' + end : ''}${ev.location ? ' · ' + ev.location : ''}</p>
                        ${ev.description ? `<p class="mt-1 text-gray-600 text-sm">${ev.description}</p>` : ''}
                    </div>
                    ${actions}
                </div>`;
        }

        async function loadEvents() {
            try {
                const [upRes, pastRes] = await Promise.all([
                    fetch('actions/events.php?action=list_upcoming'),
                    fetch('actions/events.php?action=list_past')
                ]);
                const upData = await upRes.json();
                const pastData = await pastRes.json();
                const upEl = document.getElementById('upcoming-events');
                const pastEl = document.getElementById('past-events');
                if (upData.success) {
                    upEl.innerHTML = upData.data.length ? upData.data.map(renderEventRow).join('') : '<div class="p-4 text-gray-500">Aucun événement à venir</div>';
                } else {
                    upEl.innerHTML = '<div class="p-4 text-red-600">Erreur de chargement</div>';
                }
                if (pastData.success) {
                    pastEl.innerHTML = pastData.data.length ? pastData.data.map(renderEventRow).join('') : '<div class="p-4 text-gray-500">Aucun événement passé</div>';
                } else {
                    pastEl.innerHTML = '<div class="p-4 text-red-600">Erreur de chargement</div>';
                }
                if (IS_ADMIN) bindEventRowActions();
                feather.replace();
            } catch (e) {
                showNotification('Erreur lors du chargement des événements', 'error');
            }
        }

        function bindEventRowActions() {
            document.querySelectorAll('#upcoming-events [data-action], #past-events [data-action]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-id');
                    const action = btn.getAttribute('data-action');
                    if (action === 'edit') {
                        const res = await fetch('actions/events.php?action=get_by_id&id=' + encodeURIComponent(id));
                        const data = await res.json();
                        if (data.success) openEventModal(data.data);
                    } else if (action === 'delete') {
                        if (!confirm('Supprimer cet événement ?')) return;
                        const fd = new URLSearchParams();
                        fd.append('action', 'delete');
                        fd.append('id', id);
                        const delRes = await fetch('actions/events.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: fd.toString() });
                        const delData = await delRes.json();
                        showNotification(delData.success ? 'Événement supprimé' : (delData.message || 'Échec suppression'), delData.success ? 'success' : 'error');
                        if (delData.success) loadEvents();
                    }
                });
            });
        }

        function openEventModal(eventData = null) {
            const modal = document.getElementById('event-modal');
            const form = document.getElementById('event-form');
            form.reset();
            form.id.value = eventData ? eventData.id : '';
            form.title.value = eventData ? (eventData.title || '') : '';
            form.description.value = eventData ? (eventData.description || '') : '';
            form.location.value = eventData ? (eventData.location || '') : '';
            form.visibility.value = eventData ? (eventData.visibility || 'public') : 'public';
            if (eventData) {
                form.start_date.value = eventData.start_date ? eventData.start_date : '';
                form.end_date.value = eventData.end_date ? eventData.end_date : '';
                document.getElementById('event-modal-title').textContent = 'Modifier l\'événement';
            } else {
                document.getElementById('event-modal-title').textContent = 'Nouvel événement';
            }
            modal.classList.remove('hidden');
        }

        document.getElementById('close-event-modal')?.addEventListener('click', () => document.getElementById('event-modal').classList.add('hidden'));
        document.getElementById('cancel-event')?.addEventListener('click', () => document.getElementById('event-modal').classList.add('hidden'));
        document.getElementById('event-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const isEdit = !!form.id.value;
            const params = new URLSearchParams();
            params.append('action', isEdit ? 'update' : 'create');
            if (isEdit) params.append('id', form.id.value);
            params.append('title', form.title.value.trim());
            params.append('description', form.description.value.trim());
            params.append('location', form.location.value.trim());
            params.append('start_date', form.start_date.value);
            if (form.end_date.value) params.append('end_date', form.end_date.value);
            params.append('visibility', form.visibility.value);
            const res = await fetch('actions/events.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: params.toString() });
            const data = await res.json();
            showNotification(data.success ? 'Enregistré' : (data.message || 'Échec'), data.success ? 'success' : 'error');
            if (data.success) {
                document.getElementById('event-modal').classList.add('hidden');
                loadEvents();
            }
        });

        // ===== Annonces =====
        function renderAnnouncementItem(a) {
            const dateRange = a.publish_date + (a.expire_date ? ' — ' + a.expire_date : '');
            const pinned = a.pinned == 1 ? '<span class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Épinglée</span>' : '';
            const actions = IS_ADMIN ? `
                <div class="flex items-center gap-3 text-sm">
                    <button class="text-blue-600 hover:underline" data-ann-action="edit" data-id="${a.id}">Éditer</button>
                    <button class="text-red-600 hover:underline" data-ann-action="delete" data-id="${a.id}">Supprimer</button>
                </div>` : '';
            return `
                <div class="p-4 flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">${a.title}${pinned}</p>
                        <p class="text-sm text-gray-600">${dateRange} · ${a.visibility === 'private' ? 'Privé' : 'Public'}</p>
                        <p class="mt-1 text-gray-700 whitespace-pre-line">${a.content}</p>
                    </div>
                    ${actions}
                </div>`;
        }

        async function loadAnnouncements() {
            const listEl = document.getElementById('announcements-list');
            if (!listEl) return;
            try {
                const res = await fetch('actions/announcements.php?action=list');
                const data = await res.json();
                if (data.success) {
                    listEl.innerHTML = data.data.length ? data.data.map(renderAnnouncementItem).join('') : '<div class="p-4 text-gray-500">Aucune annonce</div>';
                    if (IS_ADMIN) bindAnnouncementActions();
                } else {
                    listEl.innerHTML = '<div class="p-4 text-red-600">Erreur de chargement</div>';
                }
                feather.replace();
            } catch (e) {
                listEl.innerHTML = '<div class="p-4 text-red-600">Erreur réseau</div>';
            }
        }

        function bindAnnouncementActions() {
            document.querySelectorAll('#announcements-list [data-ann-action]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-id');
                    const action = btn.getAttribute('data-ann-action');
                    if (action === 'edit') {
                        const res = await fetch('actions/announcements.php?action=get&id=' + encodeURIComponent(id));
                        const data = await res.json();
                        if (data.success) openAnnouncementModal(data.data);
                    } else if (action === 'delete') {
                        if (!confirm('Supprimer cette annonce ?')) return;
                        const fd = new URLSearchParams();
                        fd.append('action', 'delete');
                        fd.append('id', id);
                        const delRes = await fetch('actions/announcements.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: fd.toString() });
                        const delData = await delRes.json();
                        showNotification(delData.success ? 'Annonce supprimée' : (delData.message || 'Échec suppression'), delData.success ? 'success' : 'error');
                        if (delData.success) loadAnnouncements();
                    }
                });
            });
        }

        function openAnnouncementModal(data = null) {
            const modal = document.getElementById('announcement-modal');
            const form = document.getElementById('announcement-form');
            if (!modal || !form) return;
            form.reset();
            form.id.value = data ? data.id : '';
            form.title.value = data ? (data.title || '') : '';
            form.content.value = data ? (data.content || '') : '';
            form.visibility.value = data ? (data.visibility || 'public') : 'public';
            document.getElementById('ann-pinned').checked = data ? (String(data.pinned) === '1') : false;
            form.publish_date.value = data && data.publish_date ? data.publish_date : '';
            form.expire_date.value = data && data.expire_date ? data.expire_date : '';
            document.getElementById('announcement-modal-title').textContent = data ? "Modifier l'annonce" : 'Nouvelle annonce';
            modal.classList.remove('hidden');
        }

        async function submitAnnouncementForm(e) {
            e.preventDefault();
            const form = e.target;
            const isEdit = !!form.id.value;
            const params = new URLSearchParams();
            params.append('action', isEdit ? 'update' : 'create');
            if (isEdit) params.append('id', form.id.value);
            params.append('title', form.title.value.trim());
            params.append('content', form.content.value.trim());
            params.append('visibility', form.visibility.value);
            params.append('pinned', document.getElementById('ann-pinned').checked ? '1' : '0');
            params.append('publish_date', form.publish_date.value);
            if (form.expire_date.value) params.append('expire_date', form.expire_date.value);
            try {
                const res = await fetch('actions/announcements.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: params.toString() });
                const data = await res.json();
                showNotification(data.success ? 'Annonce enregistrée' : (data.message || 'Échec'), data.success ? 'success' : 'error');
                if (data.success) {
                    document.getElementById('announcement-modal').classList.add('hidden');
                    loadAnnouncements();
                }
            } catch (e) {
                showNotification('Erreur réseau', 'error');
            }
        }
    </script>
</body>
</html>