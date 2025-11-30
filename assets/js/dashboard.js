/*
 * Dashboard Module
 * Handles sidebar navigation, theme toggling, and page transitions
 * Author: José Antonio Cortés Ferre.
 */

const DashboardModule = {
    /**
     * Initializes dashboard functionality
     */
    init() {
        this.initSidebar();
        this.initNavigation();
        this.initPageTransitions();
        this.initProfileDropdown();
    },

    /**
     * Initializes sidebar functionality
     */
    initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const body = document.body;

        this.restoreSidebarState(sidebar, body);

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', (e) => {
                e.preventDefault();
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
                // Save new state
                DashboardModule.saveSidebarState(sidebar);
            });
        }

        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', (e) => {
                e.preventDefault();
                sidebar.classList.toggle('mobile-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('active');
                }
                body.classList.toggle('sidebar-mobile-open');
            });
        }

        // Close mobile sidebar when clicking on overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                body.classList.remove('sidebar-mobile-open');
            });
        }
    },

    /**
     * Saves desktop sidebar state in localStorage
     * @param {HTMLElement} sidebar - Sidebar element
     */
    saveSidebarState(sidebar) {
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
    },

    /**
     * Restores saved desktop sidebar state from localStorage
     * Both classes are applied: .collapsed on sidebar and .sidebar-collapsed on body
     * @param {HTMLElement} sidebar - Sidebar element
     * @param {HTMLElement} body - Body element
     */
    restoreSidebarState(sidebar, body) {
        if (!sidebar || !body) return;

        const savedState = localStorage.getItem('sidebarCollapsed');
        const isCollapsed = savedState === 'true';

        // Apply/remove .collapsed on sidebar
        sidebar.classList.toggle('collapsed', isCollapsed);

        // Apply/remove .sidebar-collapsed on body (needed for main-wrapper)
        body.classList.toggle('sidebar-collapsed', isCollapsed);
    },

    /**
     * Initializes navigation highlighting functionality
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

        /* Determine the best matching link based on current path.
         * Priority is given to more specific matches.
         * This ensures that e.g. in user_edit.php, "Users" is highlighted instead of "Dashboard"
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
     * Initializes page transitions
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
     * Initializes user profile dropdown
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

// Module initialization on DOM load
document.addEventListener('DOMContentLoaded', () => {
    DashboardModule.init();

    if (window.CrudApp) {
        window.CrudApp.registerModule('dashboard', DashboardModule);
    }
});