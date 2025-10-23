/*
 * Módulo del Dashboard
 * Maneja la navegación de la barra lateral, el cambio de tema y las transiciones de página
 * Autor: José Antonio Cortés Ferre.
 */

const DashboardModule = {
    /**
     * Inicializa la funcionalidad del dashboard
     */
    init() {
        this.initSidebar();
        this.initNavigation();
        this.initPageTransitions();
    },
    
    /**
     * Inicializa la funcionalidad de la barra lateral
     */
    initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const body = document.body;
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
            });
        }
        
        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('mobile-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('active');
                }
                body.classList.toggle('sidebar-mobile-open');
            });
        }
        
        // Cierra la barra lateral móvil al hacer clic en el overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                body.classList.remove('sidebar-mobile-open');
            });
        }
    },
    
    /**
     * Inicializa la funcionalidad de resaltado de navegación
     */
    initNavigation() {
        const currentPath = window.location.pathname.toLowerCase();
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.parentElement) {
                link.parentElement.classList.remove('active');
            }
        });
        
        let activeLink = null;
        let matchPriority = 0;
        
        /* Se determina el mejor enlace coincidente basado en la ruta actual
         * Es decir, se da prioridad a las coincidencias más específicas
         * Esto sirve para que, por ejemplo, en user_edit.php se resalte "Usuarios" en lugar de "Dashboard"
         */

        navLinks.forEach((link) => {
            const page = link.getAttribute('data-page');
            
            let priority = 0;
            
            if (currentPath.includes('user_create.php') && page === 'create') {
                priority = 5;
            } else if (currentPath.includes('user_edit.php') && page === 'users') {
                priority = 4;
            } else if (currentPath.includes('user_info.php') && page === 'users') {
                priority = 4;
            } else if (currentPath.includes('user_delete.php') && page === 'users') {
                priority = 4;
            } else if (currentPath.includes('user_index.php') && page === 'users') {
                priority = 4;
            } else if (currentPath.includes('index.php') && page === 'dashboard') {
                priority = 3;
            } else if (currentPath.endsWith('/') && page === 'dashboard') {
                priority = 2;
            } else if (currentPath.includes('user_') && page === 'users') {
                priority = 1;
            }
            
            if (priority > matchPriority) {
                matchPriority = priority;
                activeLink = link;
            }
        });
        
        if (activeLink) {
            activeLink.classList.add('active');
            if (activeLink.parentElement) {
                activeLink.parentElement.classList.add('active');
            }
        }
    },
    
    /**
     * Inicializa las transiciones de página
     */
    initPageTransitions() {
        const pageContent = document.querySelector('.page-content');
        if (pageContent) {
            pageContent.style.opacity = '0';
            pageContent.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                pageContent.style.transition = 'all 0.3s ease';
                pageContent.style.opacity = '1';
                pageContent.style.transform = 'translateY(0)';
            }, 100);
        }
    }
};

// Inicialización del módulo al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    DashboardModule.init();
    
    if (window.CrudApp) {
        window.CrudApp.registerModule('dashboard', DashboardModule);
    }
});