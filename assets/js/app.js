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
        // Debug mode disabled for production

        this.initGlobalEventListeners();

        // Initialization complete
    },

    /**
     * Registers a module in the application
     * @param {string} name - Module name
     * @param {Object} module - Module object
     */
    registerModule(name, module) {
        this.modules[name] = module;

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
            this.showNotification(event.message, 'danger');
        });

        window.addEventListener('unhandledrejection', (event) => {
            this.showNotification(event.reason, 'danger');
        });
    },

    /**
     * Utility function to safely get an element by ID
     * @param {string} id - Element ID
     * @returns {HTMLElement|null} Element or null if not found
     */
    getElementById(id) {
        const element = document.getElementById(id);

        return element;
    },

    /**
     * Utility function to show notifications
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning, info)
     */
    showNotification(message, type = 'info') {
        const uploadSection = document.querySelector('.avatar-upload-section');
        if (!uploadSection) return;

        let cssClass = 'message-info';
        if (type === 'success') cssClass = 'message-success';
        if (type === 'danger' || type === 'error') cssClass = 'message-error';
        if (type === 'warning') cssClass = 'message-warning';

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${cssClass}`;
        messageDiv.textContent = message;

        uploadSection.insertBefore(messageDiv, uploadSection.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentElement) {
                messageDiv.style.transition = 'opacity 0.5s';
                messageDiv.style.opacity = '0';
                setTimeout(() => messageDiv.remove(), 500);
            }
        }, 5000);
    }
};

// Expose CrudApp globally
window.CrudApp = CrudApp;

// Initialize the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    CrudApp.init();
});