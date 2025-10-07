// Configuration des animations optimisées avec gestion de performance
const ANIMATION_CONFIG = {
    duration: {
        fast: 300,
        normal: 600,
        slow: 1200
    },
    easing: {
        smooth: 'cubic-bezier(0.4, 0, 0.2, 1)',
        bounce: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
        elastic: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)'
    },
    // Gestion de performance selon l'appareil
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    lowEndDevice: navigator.hardwareConcurrency < 4 || navigator.deviceMemory < 4
};

// Cache pour les sélecteurs DOM fréquemment utilisés
const DOM_CACHE = {
    navbar: null,
    counters: null,
    heroSection: null,
    init() {
        this.navbar = document.querySelector('nav');
        this.counters = document.querySelectorAll('[id$="-count"]');
        this.heroSection = document.querySelector('#accueil');
    }
};

// Pool d'objets pour éviter la création/destruction répétée
class ObjectPool {
    constructor(createFn, resetFn, size = 10) {
        this.createFn = createFn;
        this.resetFn = resetFn;
        this.pool = [];
        this.size = size;
        
        // Pré-remplir le pool
        for (let i = 0; i < size; i++) {
            this.pool.push(this.createFn());
        }
    }
    
    get() {
        return this.pool.length > 0 ? this.pool.pop() : this.createFn();
    }
    
    release(obj) {
        if (this.pool.length < this.size) {
            this.resetFn(obj);
            this.pool.push(obj);
        }
    }
}

// Animation des compteurs avec optimisations de performance
function animateCounter(element, target, duration = 2500) {
    // Adaptation de la durée selon les performances
    if (ANIMATION_CONFIG.lowEndDevice) duration *= 0.7;
    if (ANIMATION_CONFIG.reducedMotion) duration = 100;
    
    let start = 0;
    const startTime = performance.now();
    let animationId;
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Utilisation d'une courbe d'animation plus naturelle
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = Math.floor(start + (target * easeOutQuart));
        
        // Optimisation: mettre à jour seulement si la valeur change
        const displayValue = current.toLocaleString();
        if (element.textContent !== displayValue) {
            element.textContent = displayValue;
        }
        
        if (progress < 1) {
            animationId = requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target.toLocaleString();
            // Effet de pulsation uniquement si les animations sont activées
            if (!ANIMATION_CONFIG.reducedMotion) {
                element.style.animation = 'pulse 0.5s ease-in-out';
            }
            // Nettoyer l'animation
            cancelAnimationFrame(animationId);
        }
    }
    
    animationId = requestAnimationFrame(updateCounter);
    
    // Nettoyage automatique en cas d'interruption
    setTimeout(() => {
        if (animationId) {
            cancelAnimationFrame(animationId);
        }
    }, duration + 1000);
}

// Système d'observation avec performance optimisée
const observerOptions = {
    threshold: [0.1, 0.5, 0.8],
    rootMargin: '0px 0px -50px 0px'
};

const performanceObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const target = entry.target;
            
            // Déclencher animations selon le type d'élément
            if (target.classList.contains('fade-in-up')) {
                target.style.opacity = '1';
                target.style.transform = 'translateY(0)';
            }
            
            if (target.classList.contains('slide-in-left')) {
                target.style.opacity = '1';
                target.style.transform = 'translateX(0)';
            }
            
            if (target.classList.contains('slide-in-right')) {
                target.style.opacity = '1';
                target.style.transform = 'translateX(0)';
            }
            
            if (target.classList.contains('bounce-in')) {
                target.style.opacity = '1';
                target.style.transform = 'scale(1) rotate(0deg)';
            }
            
            // Animation des compteurs avec délais échelonnés
            if (target.id === 'members-count') {
                setTimeout(() => animateCounter(target, 150), 200);
            } else if (target.id === 'projects-count') {
                setTimeout(() => animateCounter(target, 45), 400);
            } else if (target.id === 'events-count') {
                setTimeout(() => animateCounter(target, 20), 600);
            } else if (target.id === 'awards-count') {
                setTimeout(() => animateCounter(target, 8), 800);
            }
            
            // Désactiver l'observation après l'animation
            performanceObserver.unobserve(target);
        }
    });
}, observerOptions);

// Gestion avancée du parallax avec performance optimisée
let ticking = false;
let lastScrollY = 0;
const SCROLL_THRESHOLD = 2; // Seuil minimum pour déclencher les calculs

function updateParallax() {
    const scrolled = window.pageYOffset;
    
    // Optimisation: ne calculer que si le scroll a suffisamment changé
    if (Math.abs(scrolled - lastScrollY) < SCROLL_THRESHOLD) {
        ticking = false;
        return;
    }
    
    lastScrollY = scrolled;
    
    // Batch DOM reads et writes pour éviter le layout thrashing
    const elements = document.querySelectorAll('.parallax-element, .float');
    const transforms = [];
    
    // Phase de lecture
    elements.forEach((element, index) => {
        const speed = 0.2 + (index * 0.05);
        const yPos = scrolled * speed;
        transforms.push({ element, yPos });
    });
    
    // Phase d'écriture
    transforms.forEach(({ element, yPos }) => {
        // Utiliser transform3d pour l'accélération GPU
        element.style.transform = `translate3d(0, ${yPos}px, 0)`;
    });
    
    ticking = false;
}

// Gestionnaire d'événements avec throttling optimisé
function requestTick() {
    if (!ticking && !ANIMATION_CONFIG.reducedMotion) {
        requestAnimationFrame(updateParallax);
        ticking = true;
    }
}

// Système de particules interactives
class ParticleSystem {
    constructor(container, count = 50) {
        this.container = container;
        this.particles = [];
        this.createParticles(count);
        this.animate();
    }
    
    createParticles(count) {
        for (let i = 0; i < count; i++) {
            const particle = document.createElement('div');
            particle.className = 'absolute w-1 h-1 bg-white rounded-full opacity-30';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 5 + 's';
            particle.style.animationDuration = (3 + Math.random() * 4) + 's';
            
            this.particles.push({
                element: particle,
                x: Math.random() * window.innerWidth,
                y: Math.random() * window.innerHeight,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5
            });
            
            this.container.appendChild(particle);
        }
    }
    
    animate() {
        this.particles.forEach(particle => {
            particle.x += particle.vx;
            particle.y += particle.vy;
            
            // Rebond sur les bords
            if (particle.x < 0 || particle.x > window.innerWidth) particle.vx *= -1;
            if (particle.y < 0 || particle.y > window.innerHeight) particle.vy *= -1;
            
            particle.element.style.left = particle.x + 'px';
            particle.element.style.top = particle.y + 'px';
        });
        
        requestAnimationFrame(() => this.animate());
    }
}

// Gestionnaire de thème dynamique
class ThemeManager {
    constructor() {
        this.currentTheme = 'light';
        this.initializeTheme();
    }
    
    initializeTheme() {
        // Détection du thème préféré de l'utilisateur
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            this.currentTheme = 'dark';
        }
        this.applyTheme();
    }
    
    applyTheme() {
        document.body.setAttribute('data-theme', this.currentTheme);
        
        // Adaptation des couleurs selon le thème
        const root = document.documentElement;
        if (this.currentTheme === 'dark') {
            root.style.setProperty('--text-primary', '#ffffff');
            root.style.setProperty('--bg-primary', '#1a1a1a');
        } else {
            root.style.setProperty('--text-primary', '#000000');
            root.style.setProperty('--bg-primary', '#ffffff');
        }
    }
    
    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme();
    }
}

// Système de notifications toast
class ToastManager {
    constructor() {
        this.container = this.createContainer();
    }
    
    createContainer() {
        const container = document.createElement('div');
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
        return container;
    }
    
    show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type} transform translate-x-full`;
        toast.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-${this.getIcon(type)}"></i>
                <span>${message}</span>
                <button class="ml-4 text-gray-500 hover:text-gray-700" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        this.container.appendChild(toast);
        
        // Animation d'entrée
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        }, 100);
        
        // Auto-suppression
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    
    getIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Initialisation au chargement de la page avec optimisations
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le cache DOM
    DOM_CACHE.init();
    
    // Initialisation des gestionnaires
    const themeManager = new ThemeManager();
    const toastManager = new ToastManager();
    
    // Masquer le loader et afficher le contenu
    function showMainContent() {
        const loader = document.getElementById('performance-loader');
        const mainContent = document.getElementById('main-content');
        
        if (loader && mainContent) {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
                mainContent.style.display = 'block';
                // Déclencher les animations d'entrée
                document.body.classList.add('loaded');
            }, 300);
        }
    }
    
    // Préparation des éléments pour les animations (optimisée)
    const elementsToAnimate = [
        { selector: '.fade-in-up', style: { opacity: '0', transform: 'translateY(50px)' } },
        { selector: '.slide-in-left', style: { opacity: '0', transform: 'translateX(-100px)' } },
        { selector: '.slide-in-right', style: { opacity: '0', transform: 'translateX(100px)' } },
        { selector: '.bounce-in', style: { opacity: '0', transform: 'scale(0.3) rotate(-10deg)' } }
    ];
    
    // Utiliser un DocumentFragment pour les modifications DOM groupées
    elementsToAnimate.forEach(({ selector, style }) => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            if (element && element.nodeType === Node.ELEMENT_NODE) {
                // Batch les styles pour éviter le reflow
                Object.assign(element.style, style, {
                    transition: ANIMATION_CONFIG.reducedMotion ? 'none' : 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)'
                });
                performanceObserver.observe(element);
            }
        });
    });
    
    // Observer les compteurs avec vérification optimisée
    if (DOM_CACHE.counters) {
        DOM_CACHE.counters.forEach(counter => {
            if (counter && counter.nodeType === Node.ELEMENT_NODE) {
                performanceObserver.observe(counter);
            }
        });
    }
    
    // Smooth scroll optimisé
    document.querySelectorAll('nav a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
                
                // Feedback visuel
                toastManager.show(`Navigation vers ${targetId.substring(1)}`, 'info', 2000);
            }
        });
    });
    
    // Gestion avancée de la navbar avec optimisations
    let lastScrollTop = 0;
    let navbarTimer = null;
    
    // Utiliser un throttle plus efficace pour le scroll
    const handleScroll = () => {
        if (navbarTimer) return;
        
        navbarTimer = setTimeout(() => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (DOM_CACHE.navbar) {
                // Batch les modifications de style
                const styles = {};
                
                // Effet glassmorphism progressif
                if (scrollTop > 100) {
                    styles.backdropFilter = 'blur(20px)';
                    styles.background = 'rgba(255, 255, 255, 0.1)';
                } else {
                    styles.backdropFilter = 'blur(10px)';
                    styles.background = 'rgba(255, 255, 255, 0.05)';
                }
                
                // Auto-hide navbar on scroll down (seulement si pas réduit motion)
                if (!ANIMATION_CONFIG.reducedMotion) {
                    if (scrollTop > lastScrollTop && scrollTop > 200) {
                        styles.transform = 'translateY(-100%)';
                    } else {
                        styles.transform = 'translateY(0)';
                    }
                }
                
                // Appliquer tous les styles en une fois
                Object.assign(DOM_CACHE.navbar.style, styles);
            }
            
            lastScrollTop = scrollTop;
            requestTick(); // Parallax
            navbarTimer = null;
        }, 16); // ~60fps
    };
    
    window.addEventListener('scroll', handleScroll, { passive: true });
    
    // Animation des boutons avec feedback haptic (si supporté)
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function(e) {
            // Effet de ripple
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
            
            // Feedback haptic si supporté
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        });
    });
    
    // Gestion du formulaire de contact avec validation avancée
    const contactForm = document.querySelector('form');
    if (contactForm && contactForm.nodeType === Node.ELEMENT_NODE) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            try {
                // Validation des champs
                const inputs = this.querySelectorAll('input, textarea');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (input.hasAttribute('required') && !input.value.trim()) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    toastManager.show('Veuillez remplir tous les champs obligatoires', 'error');
                    return;
                }
                
                const emailInput = this.querySelector('input[type="email"]');
                if (emailInput && emailInput.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                    toastManager.show('Adresse email invalide', 'error');
                    return;
                }
                
                // Animation de chargement
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    const originalText = submitButton.innerHTML;
                    
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
                    submitButton.disabled = true;
                    
                    // Simulation d'envoi
                    setTimeout(() => {
                        submitButton.innerHTML = '<i class="fas fa-check mr-2"></i>Message envoyé !';
                        submitButton.classList.add('bg-green-600');
                        toastManager.show('Message envoyé avec succès !', 'success');
                        
                        setTimeout(() => {
                            submitButton.innerHTML = originalText;
                            submitButton.classList.remove('bg-green-600');
                            submitButton.disabled = false;
                            this.reset();
                        }, 2000);
                    }, 1500);
                }
            } catch (error) {
                console.log('Form handling error:', error);
                toastManager.show('Erreur lors de l\'envoi', 'error');
            }
        });
    }
    
    // Système de particules pour le hero (conditionnel selon les performances)
    if (DOM_CACHE.heroSection && DOM_CACHE.heroSection.nodeType === Node.ELEMENT_NODE) {
        try {
            // Réduire le nombre de particules sur les appareils moins performants
            const particleCount = ANIMATION_CONFIG.lowEndDevice ? 15 : 30;
            if (!ANIMATION_CONFIG.reducedMotion) {
                new ParticleSystem(DOM_CACHE.heroSection, particleCount);
            }
        } catch (error) {
            console.log('Particle system skipped');
        }
    }
    
    // Afficher le contenu principal après initialisation
    setTimeout(showMainContent, 100);
    
    // Performance monitoring
    if ('performance' in window) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                try {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData && perfData.loadEventEnd && perfData.fetchStart) {
                        const loadTime = Math.round(perfData.loadEventEnd - perfData.fetchStart);
                        if (loadTime > 0) {
                            console.log(`Site chargé en ${loadTime}ms`);
                        }
                    }
                } catch (error) {
                    console.log('Performance monitoring skipped');
                }
            }, 100);
        });
    }
});

// Optimisation pour les animations CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Le reste du code a été remplacé par la version moderne ci-dessus