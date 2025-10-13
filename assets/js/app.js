// Main application JavaScript entry point
// Manages global functionality and module coordination

/**
 * Main App object for managing global functionality
 */
const CrudApp = {
    // Configuration
    config: {
        debug: false, // Set to true for development
        version: '1.0.0'
    },
    
    // Module registry
    modules: {},
    
    /**
     * Initialize the application
     */
    init() {
        if (this.config.debug) {
            console.log('CRUD App initializing...', this.config);
        }
        
        // Initialize core modules
        this.initGlobalEventListeners();
        this.initUtilities();
        
        if (this.config.debug) {
            console.log('CRUD App initialized successfully');
        }
    },
    
    /**
     * Register a module
     * @param {string} name - Module name
     * @param {Object} module - Module object
     */
    registerModule(name, module) {
        this.modules[name] = module;
        if (this.config.debug) {
            console.log(`Module '${name}' registered`);
        }
    },
    
    /**
     * Get a registered module
     * @param {string} name - Module name
     * @returns {Object|null} Module object or null if not found
     */
    getModule(name) {
        return this.modules[name] || null;
    },
    
    /**
     * Initialize global event listeners
     */
    initGlobalEventListeners() {
        // Global error handler
        window.addEventListener('error', (event) => {
            if (this.config.debug) {
                console.error('Global error:', event.error);
            }
        });
        
        // Global unhandled promise rejection handler
        window.addEventListener('unhandledrejection', (event) => {
            if (this.config.debug) {
                console.error('Unhandled promise rejection:', event.reason);
            }
        });
    },
    
    /**
     * Initialize utility functions
     */
    initUtilities() {
        // Add global utility functions to window if needed
        window.CrudApp = this;
    },
    
    /**
     * Utility function to safely get element by ID
     * @param {string} id - Element ID
     * @returns {HTMLElement|null} Element or null if not found
     */
    getElementById(id) {
        const element = document.getElementById(id);
        if (!element && this.config.debug) {
            console.warn(`Element with ID '${id}' not found`);
        }
        return element;
    },
    
    /**
     * Utility function to show notifications (can be extended)
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning, info)
     */
    showNotification(message, type = 'info') {
        // This can be extended to show toast notifications
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
};

// Initialize the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    CrudApp.init();
});