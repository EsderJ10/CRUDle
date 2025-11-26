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
        this.initProfileDropdown();
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

        this.restoreSidebarState(sidebar, body);

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function (e) {
                e.preventDefault();
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
                // Guarda el nuevo estado
                DashboardModule.saveSidebarState(sidebar);
            });
        }

        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function (e) {
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
            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                body.classList.remove('sidebar-mobile-open');
            });
        }
    },

    /**
     * Guarda el estado del sidebar de escritorio en localStorage
     * @param {HTMLElement} sidebar - Elemento de la barra lateral
     */
    saveSidebarState(sidebar) {
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
    },

    /**
     * Restaura el estado guardado del sidebar de escritorio desde localStorage
     * Se aplican AMBAS clases: .collapsed en el sidebar y .sidebar-collapsed en el body
     * @param {HTMLElement} sidebar - Elemento de la barra lateral
     * @param {HTMLElement} body - Elemento body
     */
    restoreSidebarState(sidebar, body) {
        if (!sidebar || !body) return;

        const savedState = localStorage.getItem('sidebarCollapsed');
        const isCollapsed = savedState === 'true';

        // Aplicar/remover .collapsed en el sidebar
        sidebar.classList.toggle('collapsed', isCollapsed);

        // Aplicar/remover .sidebar-collapsed en el body (necesario para el main-wrapper)
        body.classList.toggle('sidebar-collapsed', isCollapsed);
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
    },

    /**
     * Inicializa el dropdown del perfil de usuario
     */
    initProfileDropdown() {
        const userProfile = document.getElementById('userProfileDropdown');

        if (userProfile) {
            // Toggle dropdown on click
            userProfile.addEventListener('click', (e) => {
                e.stopPropagation();
                userProfile.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userProfile.contains(e.target)) {
                    userProfile.classList.remove('active');
                }
            });

            // Prevent closing when clicking inside the dropdown menu (except for links)
            const dropdownMenu = userProfile.querySelector('.profile-dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // If a link was clicked, allow propagation or handle navigation
                    if (e.target.tagName === 'A' || e.target.closest('a')) {
                        userProfile.classList.remove('active');
                    }
                });
            }
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