// Variables globales
let sidebarCollapsed = false;
let isMobile = false;
let sidebar;
let toggleButton;
let mobileMenuButton;
let sidebarOverlay;
let logoText;
let navTexts;

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
    if (!sidebar || !sidebarOverlay) return;
    
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
    if (!sidebar || !logoText || !navTexts || !toggleButton) return;
    
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
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Fermer sidebar mobile
function closeMobileSidebar() {
    if (isMobile && sidebar && sidebarOverlay) {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Initialiser sidebar
function initializeSidebar() {
    if (!sidebar) return;
    
    checkIfMobile();
    
    if (isMobile) {
        sidebar.classList.add('fixed', 'top-0', 'left-0', 'h-full', '-translate-x-full');
        sidebar.classList.remove('w-16');
        sidebar.classList.add('w-64');
        if (logoText) logoText.style.display = 'block';
        if (navTexts) navTexts.forEach(text => text.style.display = 'block');
        sidebarCollapsed = false;
    } else {
        sidebar.classList.remove('fixed', 'top-0', 'left-0', 'h-full', '-translate-x-full');
        if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Changer d'onglet
function switchTab(tabName) {
    // Vérifier si on est sur une page de dashboard
    if (!sidebar) return;
    
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
        const pageTitle = document.getElementById('page-title');
        const pageDescription = document.getElementById('page-description');
        if (pageTitle) pageTitle.textContent = config.title;
        if (pageDescription) pageDescription.textContent = config.description;
    }
    
    if (isMobile) {
        closeMobileSidebar();
    }
    
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
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

// Dashboard JavaScript Functions
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les éléments DOM après le chargement de la page
    sidebar = document.getElementById('sidebar');
    toggleButton = document.getElementById('toggle-sidebar');
    mobileMenuButton = document.getElementById('mobile-menu-button');
    sidebarOverlay = document.getElementById('sidebar-overlay');
    logoText = document.getElementById('logo-text');
    navTexts = document.querySelectorAll('.nav-text');
    
    // Vérifier si on est sur une page de dashboard (avec sidebar)
    if (!sidebar) {
        console.log('Dashboard elements not found, skipping dashboard initialization');
        return;
    }
    
    // Initialiser Feather Icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    initializeSidebar();
    
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');
            if (tabName) {
                switchTab(tabName);
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
    
    // Ne pas appeler switchTab si on n'est pas sur une page de dashboard
    if (sidebar) {
        switchTab('dashboard');
    }
    
    // Ne pas exécuter ces fonctions si on n'est pas sur une page de dashboard
    if (sidebar) {
        setTimeout(() => {
            showNotification('Dashboard Club Pro chargé avec succès !', 'success');
        }, 1000);
        
        // Initialiser le tableau de bord
        initDashboard();
        
        // Ajouter des écouteurs d'événements
        addEventListeners();
    }
});

// Supprimé le deuxième DOMContentLoaded pour éviter les conflits

function initDashboard() {
    if (!sidebar) return;
    
    console.log('Dashboard initialized');
    
    // Ajouter des animations de carte
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('card-animation');
        }, index * 100);
    });
    
    // Mettre à jour la date/heure si l'élément existe
    updateDateTime();
    setInterval(updateDateTime, 1000);
}

function initTabs() {
    const navItems = document.querySelectorAll('.nav-item');
    const tabContents = document.querySelectorAll('.tab-content');
    
    navItems.forEach(navItem => {
        navItem.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Retirer la classe active de tous les éléments de navigation et contenus d'onglets
            navItems.forEach(item => item.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Ajouter la classe active à l'élément de navigation cliqué
            this.classList.add('active');
            
            // Afficher le contenu de l'onglet correspondant
            const targetTab = this.getAttribute('data-tab') || this.getAttribute('href')?.substring(1);
            if (targetTab) {
                const targetContent = document.getElementById(targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            }
        });
    });
}

function addEventListeners() {
    // Ajouter des gestionnaires de clic pour les boutons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Ajouter un état de chargement
            this.classList.add('loading');
            setTimeout(() => {
                this.classList.remove('loading');
            }, 1000);
        });
    });
    
    // Validation de formulaire
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function updateDateTime() {
    const dateTimeElement = document.getElementById('current-datetime');
    if (dateTimeElement) {
        const now = new Date();
        dateTimeElement.textContent = now.toLocaleString();
    }
}

function validateForm(form) {
    const required = form.querySelectorAll('[required]');
    let isValid = true;
    
    required.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}


function refreshData() {
    // Ajouter un appel AJAX pour rafraîchir les données du tableau de bord
    console.log('Refreshing dashboard data...');
    showNotification('Data refreshed successfully', 'success');
}
