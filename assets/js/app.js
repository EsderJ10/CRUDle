/*
 * JavaScript entry point for the CRUD application.
 * This file initializes the application and configures necessary modules.
 * Author: José Antonio Cortés Ferre.
 */
const CrudApp = {
    config: {
        debug: false,
        version: '1.1.0'
    },

    modules: {},

    /**
     * Initializes the CRUD application
     */
    init() {
        if (this.config.debug) {
            console.log('CRUDle initializing...', this.config);
        }

        this.initGlobalEventListeners();

        if (this.config.debug) {
            console.log('CRUDle initialized successfully');
        }
    },

    /**
     * Registers a module in the application
     * @param {string} name - Module name
     * @param {Object} module - Module object
     */
    registerModule(name, module) {
        this.modules[name] = module;
        if (this.config.debug) {
            console.log(`Module '${name}' registered.`);
        }
    },

    /**
     * Gets a registered module
     * @param {string} name - Module name
     * @returns {Object|null} Module object or null if not found
     */
    getModule(name) {
        return this.modules[name] || null;
    },

    /**
     * Initializes global event listeners
     */
    initGlobalEventListeners() {
        window.addEventListener('error', (event) => {
            if (this.config.debug) {
                console.error('Global error:', event.error);
            }
        });

        window.addEventListener('unhandledrejection', (event) => {
            if (this.config.debug) {
                console.error('Unhandled promise rejection:', event.reason);
            }
        });
    },

    /**
     * Utility function to safely get an element by ID
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
     * Utility function to show notifications
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning, info)
     */
    showNotification(message, type = 'info') {
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
};

// Initialize the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    CrudApp.init();
});