/*
 * Punto de entrada en JavaScript para la aplicación CRUD.
 * Este archivo inicializa la aplicación y configura los módulos necesarios.
 * Autor: José Antonio Cortés Ferre.
 */
const CrudApp = {
    config: {
        debug: false,
        version: '1.1.0'
    },
    
    modules: {},
    
    /**
     * Inicializa la aplicación CRUD
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
     * Registra un módulo en la aplicación
     * @param {string} name - Nombre del módulo
     * @param {Object} module - Objeto del módulo
     */
    registerModule(name, module) {
        this.modules[name] = module;
        if (this.config.debug) {
            console.log(`Module '${name}' registered.`);
        }
    },
    
    /**
     * Obtiene un módulo registrado
     * @param {string} name - Nombre del módulo
     * @returns {Object|null} Objeto del módulo o null si no se encuentra
     */
    getModule(name) {
        return this.modules[name] || null;
    },
    
    /**
     * Inicializa los listeners de eventos globales
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
     * Función de utilidad para obtener un elemento de forma segura por ID
     * @param {string} id - Elemento ID
     * @returns {HTMLElement|null} Elemento o null si no se encuentra
     */
    getElementById(id) {
        const element = document.getElementById(id);
        if (!element && this.config.debug) {
            console.warn(`Element with ID '${id}' not found`);
        }
        return element;
    },
    
    /**
     * Función de utilidad para mostrar notificaciones
     * @param {string} message - Mensaje de la notificación
     * @param {string} type - Tipo de notificación (success, error, warning, info)
     */
    showNotification(message, type = 'info') {
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
};

// Initialize the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    CrudApp.init();
});