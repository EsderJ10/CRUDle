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
            avatarInput.addEventListener('change', (e) => {
                const removeCheckbox = document.getElementById('remove_avatar');
                if (removeCheckbox && avatarInput.files.length > 0) {
                    // Si el usuario selecciona un archivo, desactiva la opción de eliminación
                    removeCheckbox.checked = false;
                    this.toggleAvatarUpload(removeCheckbox);
                }
                
                // Maneja la previsualización del archivo
                this.handleFileSelect(e);
            });
        }
        
        // Configura el botón de remover archivo de la previsualización
        const removePreviewBtn = document.getElementById('filePreviewRemove');
        if (removePreviewBtn) {
            removePreviewBtn.addEventListener('click', () => {
                this.clearFileInput();
            });
        }
        
        // Configura drag and drop
        const fileUploadLabel = document.getElementById('customFileUpload');
        if (fileUploadLabel) {
            this.initDragAndDrop(fileUploadLabel, avatarInput);
        }
    },
    
    /**
     * Maneja la selección de archivo y muestra la previsualización
     * @param {Event} e - El evento de cambio del input
     */
    handleFileSelect(e) {
        const file = e.target.files[0];
        if (!file) {
            this.clearFileInput();
            return;
        }
        
        // Valida que sea una imagen
        if (!file.type.match('image.*')) {
            alert('Por favor, selecciona solo archivos de imagen.');
            this.clearFileInput();
            return;
        }
        
        // Valida el tamaño (2MB máximo)
        const maxSize = 2 * 1024 * 1024; // 2MB en bytes
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande. El tamaño máximo es 2MB.');
            this.clearFileInput();
            return;
        }
        
        // Actualiza la UI
        const customFileUpload = document.getElementById('customFileUpload');
        const fileTextMain = document.getElementById('fileTextMain');
        const fileTextSub = document.getElementById('fileTextSub');
        
        if (customFileUpload) {
            customFileUpload.classList.add('has-file');
        }
        
        if (fileTextMain) {
            fileTextMain.textContent = 'Archivo seleccionado';
        }
        
        if (fileTextSub) {
            fileTextSub.textContent = file.name;
        }
        
        // Muestra la previsualización
        this.showFilePreview(file);
    },
    
    /**
     * Muestra la previsualización del archivo seleccionado
     * @param {File} file - El archivo a previsualizar
     */
    showFilePreview(file) {
        const preview = document.getElementById('filePreview');
        const previewImage = document.getElementById('filePreviewImage');
        const previewName = document.getElementById('filePreviewName');
        const previewSize = document.getElementById('filePreviewSize');
        
        if (!preview) return;
        
        // Lee el archivo para mostrar la imagen
        const reader = new FileReader();
        reader.onload = (e) => {
            if (previewImage) {
                previewImage.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
        
        // Muestra el nombre y tamaño
        if (previewName) {
            previewName.textContent = file.name;
        }
        
        if (previewSize) {
            const sizeKB = (file.size / 1024).toFixed(2);
            previewSize.textContent = `${sizeKB} KB`;
        }
        
        // Muestra el contenedor de previsualización
        preview.classList.add('show');
    },
    
    /**
     * Limpia el input de archivo y restaura el estado inicial
     */
    clearFileInput() {
        const avatarInput = document.getElementById('avatar');
        const customFileUpload = document.getElementById('customFileUpload');
        const fileTextMain = document.getElementById('fileTextMain');
        const fileTextSub = document.getElementById('fileTextSub');
        const preview = document.getElementById('filePreview');
        
        if (avatarInput) {
            avatarInput.value = '';
        }
        
        if (customFileUpload) {
            customFileUpload.classList.remove('has-file');
        }
        
        if (fileTextMain) {
            fileTextMain.textContent = 'Seleccionar archivo';
        }
        
        if (fileTextSub) {
            fileTextSub.textContent = 'o arrastra y suelta aquí';
        }
        
        if (preview) {
            preview.classList.remove('show');
        }
    },
    
    /**
     * Inicializa el drag and drop para el input de archivo
     * @param {HTMLElement} dropZone - El elemento donde se puede soltar el archivo
     * @param {HTMLElement} input - El input de archivo
     */
    initDragAndDrop(dropZone, input) {
        if (!dropZone || !input) return;
        
        // Previene el comportamiento predeterminado para permitir el drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });
        
        // Agrega efecto visual cuando se arrastra sobre el elemento
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.style.borderColor = 'var(--primary)';
                dropZone.style.background = 'var(--bg-secondary)';
                dropZone.style.transform = 'scale(1.02)';
            });
        });
        
        // Remueve efecto visual cuando se sale del elemento
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.style.borderColor = '';
                dropZone.style.background = '';
                dropZone.style.transform = '';
            });
        });
        
        // Maneja el drop del archivo
        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                // Dispara el evento change manualmente
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
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
        const customFileUpload = document.getElementById('customFileUpload');
        
        if (checkbox.checked) {
            // Si la opción de eliminar está marcada, desactiva la subida de archivos
            if (avatarUploadSection) avatarUploadSection.style.opacity = '0.5';
            if (avatarInput) {
                avatarInput.disabled = true;
                avatarInput.value = ''; // Limpia cualquier archivo seleccionado
            }
            
            // Deshabilita visualmente el área de upload
            if (customFileUpload) {
                customFileUpload.classList.add('disabled');
                customFileUpload.style.pointerEvents = 'none';
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
            
            // Habilita visualmente el área de upload
            if (customFileUpload) {
                customFileUpload.classList.remove('disabled');
                customFileUpload.style.pointerEvents = 'auto';
            }
            
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