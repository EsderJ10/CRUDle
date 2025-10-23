// Dashboard Module
// Handles sidebar navigation, theme toggle, and page transitions

const DashboardModule = {
    /**
     * Initialize dashboard functionality
     */
    init() {
        this.initSidebar();
        this.initNavigation();
        this.initPageTransitions();
    },
    
    /**
     * Initialize sidebar functionality
     */
    initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const body = document.body;
        
        // Desktop sidebar toggle
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
            });
        }
        
        // Mobile sidebar toggle
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
        
        // Close mobile sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                body.classList.remove('sidebar-mobile-open');
            });
        }
    },
    
    /**
     * Initialize navigation highlighting
     */
    initNavigation() {
        const currentPath = window.location.pathname.toLowerCase();
        const navLinks = document.querySelectorAll('.nav-link');
        
        // First, remove all active classes
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.parentElement) {
                link.parentElement.classList.remove('active');
            }
        });
        
        // Find the best match and set only one as active
        let activeLink = null;
        let matchPriority = 0;
        
        navLinks.forEach((link) => {
            const page = link.getAttribute('data-page');
            
            // Determine match priority (higher number = better match)
            let priority = 0;
            
            if (currentPath.includes('user_create.php') && page === 'create') {
                priority = 5; // Most specific match
            } else if (currentPath.includes('user_edit.php') && page === 'users') {
                priority = 4; // Specific match
            } else if (currentPath.includes('user_info.php') && page === 'users') {
                priority = 4; // Specific match
            } else if (currentPath.includes('user_delete.php') && page === 'users') {
                priority = 4; // Specific match
            } else if (currentPath.includes('user_index.php') && page === 'users') {
                priority = 4; // Specific match
            } else if (currentPath.includes('index.php') && page === 'dashboard') {
                priority = 3; // Dashboard match
            } else if (currentPath.endsWith('/') && page === 'dashboard') {
                priority = 2; // Root path
            } else if (currentPath.includes('user_') && page === 'users') {
                priority = 1; // Generic user page match
            }
            
            if (priority > matchPriority) {
                matchPriority = priority;
                activeLink = link;
            }
        });
        
        // Set only the best match as active
        if (activeLink) {
            activeLink.classList.add('active');
            if (activeLink.parentElement) {
                activeLink.parentElement.classList.add('active');
            }
        }
    },
    
    /**
     * Initialize page transitions
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    DashboardModule.init();
    
    // Register with main app if available
    if (window.CrudApp) {
        window.CrudApp.registerModule('dashboard', DashboardModule);
    }
});