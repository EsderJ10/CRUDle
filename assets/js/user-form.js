// User Form Module
// Handles avatar removal and upload interactions

const UserFormModule = {
    /**
     * Initialize user form functionality
     */
    init() {
        this.initAvatarHandling();
        this.initFormValidation();
    },
    
    /**
     * Initialize avatar handling functionality
     */
    initAvatarHandling() {
        const removeAvatarCheckbox = document.getElementById('remove_avatar');
        if (removeAvatarCheckbox) {
            // Initialize the state on page load
            this.toggleAvatarUpload(removeAvatarCheckbox);
            
            // Set up event listener for changes
            removeAvatarCheckbox.addEventListener('change', () => {
                this.toggleAvatarUpload(removeAvatarCheckbox);
            });
        }
        
        // Add file input change handler for better UX
        const avatarInput = document.getElementById('avatar');
        if (avatarInput) {
            avatarInput.addEventListener('change', () => {
                const removeCheckbox = document.getElementById('remove_avatar');
                if (removeCheckbox && avatarInput.files.length > 0) {
                    // If user selects a file, uncheck remove avatar
                    removeCheckbox.checked = false;
                    this.toggleAvatarUpload(removeCheckbox);
                }
            });
        }
    },
    
    /**
     * Toggles avatar upload section based on remove checkbox state
     * @param {HTMLElement} checkbox - The remove avatar checkbox
     */
    toggleAvatarUpload(checkbox) {
        const avatarUploadSection = document.getElementById('avatarUploadSection');
        const avatarInput = document.getElementById('avatar');
        const removeWarning = document.getElementById('removeAvatarWarning');
        const currentAvatar = document.querySelector('.current-avatar img');
        
        if (checkbox.checked) {
            // If remove avatar is checked, disable file upload and show warning
            if (avatarUploadSection) avatarUploadSection.style.opacity = '0.5';
            if (avatarInput) {
                avatarInput.disabled = true;
                avatarInput.value = ''; // Clear any selected file
            }
            
            if (removeWarning) {
                removeWarning.style.display = 'block';
            }
            
            // Add visual indication that avatar will be removed
            if (currentAvatar) {
                currentAvatar.style.opacity = '0.4';
                currentAvatar.style.filter = 'grayscale(100%)';
            }
        } else {
            // If remove avatar is unchecked, enable file upload and hide warning
            if (avatarUploadSection) avatarUploadSection.style.opacity = '1';
            if (avatarInput) avatarInput.disabled = false;
            
            if (removeWarning) {
                removeWarning.style.display = 'none';
            }
            
            // Remove visual indication
            if (currentAvatar) {
                currentAvatar.style.opacity = '1';
                currentAvatar.style.filter = 'none';
            }
        }
    },
    
    /**
     * Initialize form validation (can be extended)
     */
    initFormValidation() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (event) => {
                // Add custom validation logic here if needed
                return this.validateForm(form);
            });
        }
    },
    
    /**
     * Validate form before submission
     * @param {HTMLFormElement} form - The form element
     * @returns {boolean} Whether the form is valid
     */
    validateForm(form) {
        // Add custom validation logic here
        // For now, just return true to allow normal form submission
        return true;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    UserFormModule.init();
    
    // Register with main app if available
    if (window.CrudApp) {
        window.CrudApp.registerModule('userForm', UserFormModule);
    }
});

// Export for global access if needed
window.UserFormModule = UserFormModule;