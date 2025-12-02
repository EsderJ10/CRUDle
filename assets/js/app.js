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
        // Get or create toast container
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        let cssClass = 'message-info';
        if (type === 'success') cssClass = 'message-success';
        if (type === 'danger' || type === 'error') cssClass = 'message-error';
        if (type === 'warning') cssClass = 'message-warning';

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${cssClass}`;
        messageDiv.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.style.cssText = `
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-55%);
            background: none;
            border: none;
            color: inherit;
            opacity: 0.6;
            cursor: pointer;
            padding: 5px;
        `;
        closeBtn.onclick = () => removeToast(messageDiv);
        messageDiv.appendChild(closeBtn);
        messageDiv.style.paddingRight = '40px';

        container.appendChild(messageDiv);

        function removeToast(element) {
            element.style.animation = 'toastSlideOut 0.5s ease-out forwards';
            setTimeout(() => element.remove(), 500);
        }

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentElement) {
                removeToast(messageDiv);
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