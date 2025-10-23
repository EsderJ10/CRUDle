/*
 * Módulo del Formulario de Usuario
 * Maneja la interactividad del formulario: avatar (subida, eliminación) y validación
 * La validación real se realiza en PHP. JavaScript solo maneja la experiencia de usuario e interactividad
 * Autor: José Antonio Cortés Ferre.
 */

const UserFormModule = {
    /**
     * Inicializa toda la funcionalidad del formulario de usuario
     */
    init() {
        this.initAvatarHandling();
        this.initFormValidation();
    },
    
    /**
     * Inicializa el manejo del avatar (subida y eliminación)
     * Configura los listeners para el checkbox de eliminación y el input de archivo
     * Sincroniza los estados entre ambos controles para mejorar la UX
     */
    initAvatarHandling() {
        const removeAvatarCheckbox = document.getElementById('remove_avatar');
        if (removeAvatarCheckbox) {
            // Inicializa el estado del formulario en la carga de la página
            this.toggleAvatarUpload(removeAvatarCheckbox);
            
            // Configura el listener para cambios en el checkbox
            removeAvatarCheckbox.addEventListener('change', () => {
                this.toggleAvatarUpload(removeAvatarCheckbox);
            });
        }
        
        // Agrega un handler para el input de archivo para mejor UX
        const avatarInput = document.getElementById('avatar');
        if (avatarInput) {
            avatarInput.addEventListener('change', () => {
                const removeCheckbox = document.getElementById('remove_avatar');
                if (removeCheckbox && avatarInput.files.length > 0) {
                    // Si el usuario selecciona un archivo, desactiva la opción de eliminación
                    removeCheckbox.checked = false;
                    this.toggleAvatarUpload(removeCheckbox);
                }
            });
        }
    },
    
    /**
     * Alterna la visibilidad y estado del área de subida de avatar
     * Controla la opacidad del formulario, estado del input y avisos visuales
     * También aplica efectos visuales al avatar actual (desaturado, opacidad)
     * @param {HTMLElement} checkbox - El checkbox de eliminación de avatar
     */
    toggleAvatarUpload(checkbox) {
        const avatarUploadSection = document.getElementById('avatarUploadSection');
        const avatarInput = document.getElementById('avatar');
        const removeWarning = document.getElementById('removeAvatarWarning');
        const currentAvatar = document.querySelector('.current-avatar img');
        
        if (checkbox.checked) {
            // Si la opción de eliminar está marcada, desactiva la subida de archivos
            if (avatarUploadSection) avatarUploadSection.style.opacity = '0.5';
            if (avatarInput) {
                avatarInput.disabled = true;
                avatarInput.value = ''; // Limpia cualquier archivo seleccionado
            }
            
            if (removeWarning) {
                removeWarning.style.display = 'block';
            }
            
            if (currentAvatar) {
                currentAvatar.style.opacity = '0.4';
                currentAvatar.style.filter = 'grayscale(100%)';
            }
        } else {
            // Si la opción de eliminar no está marcada, habilita la subida de archivos
            if (avatarUploadSection) avatarUploadSection.style.opacity = '1';
            if (avatarInput) avatarInput.disabled = false;
            
            if (removeWarning) {
                removeWarning.style.display = 'none';
            }
            
            if (currentAvatar) {
                currentAvatar.style.opacity = '1';
                currentAvatar.style.filter = 'none';
            }
        }
    },
};

// Inicialización del módulo al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    UserFormModule.init();
    
    // Registra el módulo con la aplicación principal si está disponible
    if (window.CrudApp) {
        window.CrudApp.registerModule('userForm', UserFormModule);
    }
});

window.UserFormModule = UserFormModule;