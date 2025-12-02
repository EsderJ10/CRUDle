/*
 * User Form Module
 * Handles form interactivity: avatar (upload, removal) and validation
 * Real validation is done in PHP. JavaScript only handles UX and interactivity
 * Author: José Antonio Cortés Ferre.
 */

(function () {
    if (window.UserFormModule) return;

    const UserFormModule = {
        /**
         * Initializes all user form functionality
         */
        init() {
            this.initAvatarHandling();
        },

        /**
         * Initializes avatar handling (upload and removal)
         * Sets up listeners for removal checkbox and file input
         * Synchronizes states between both controls to improve UX
         */
        initAvatarHandling() {
            const removeAvatarCheckbox = document.getElementById('remove_avatar');
            if (removeAvatarCheckbox) {
                // Initialize form state on page load
                this.toggleAvatarUpload(removeAvatarCheckbox);

                // Set up listener for checkbox changes
                removeAvatarCheckbox.addEventListener('change', () => {
                    this.toggleAvatarUpload(removeAvatarCheckbox);
                });
            }

            // Add handler for file input for better UX
            const avatarInput = document.getElementById('avatar');
            if (avatarInput) {
                avatarInput.addEventListener('change', (e) => {
                    const removeCheckbox = document.getElementById('remove_avatar');
                    if (removeCheckbox && avatarInput.files.length > 0) {
                        // If user selects a file, disable removal option
                        removeCheckbox.checked = false;
                        this.toggleAvatarUpload(removeCheckbox);
                    }

                    // Handle file preview
                    this.handleFileSelect(e);
                });
            }

            // Set up remove file preview button
            const removePreviewBtn = document.getElementById('filePreviewRemove');
            if (removePreviewBtn) {
                removePreviewBtn.addEventListener('click', () => {
                    this.clearFileInput();
                });
            }

            // Set up drag and drop
            const fileUploadLabel = document.getElementById('customFileUpload');
            if (fileUploadLabel) {
                this.initDragAndDrop(fileUploadLabel, avatarInput);
            }
        },

        /**
         * Handles file selection and shows preview
         * @param {Event} e - Input change event
         */
        handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) {
                this.clearFileInput();
                return;
            }

            // Validate it is an image
            if (!file.type.match('image.*')) {
                window.CrudApp.showNotification('Please select only image files.', 'danger');
                this.clearFileInput();
                return;
            }

            // Validate size (2MB max)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                window.CrudApp.showNotification('File is too large. Maximum size is 2MB.', 'danger');
                this.clearFileInput();
                return;
            }

            // Update UI
            const customFileUpload = document.getElementById('customFileUpload');
            const fileTextMain = document.getElementById('fileTextMain');
            const fileTextSub = document.getElementById('fileTextSub');

            if (customFileUpload) {
                customFileUpload.classList.add('has-file');
            }

            if (fileTextMain) {
                fileTextMain.textContent = 'File selected';
            }

            if (fileTextSub) {
                fileTextSub.textContent = file.name;
            }

            // Show preview
            this.showFilePreview(file);
        },

        /**
         * Shows selected file preview
         * @param {File} file - File to preview
         */
        showFilePreview(file) {
            const preview = document.getElementById('filePreview');
            const previewImage = document.getElementById('filePreviewImage');
            const previewName = document.getElementById('filePreviewName');
            const previewSize = document.getElementById('filePreviewSize');

            if (!preview) return;

            // Read file to show image
            const reader = new FileReader();
            reader.onload = (e) => {
                if (previewImage) {
                    previewImage.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);

            // Show name and size
            if (previewName) {
                previewName.textContent = file.name;
            }

            if (previewSize) {
                const sizeKB = (file.size / 1024).toFixed(2);
                previewSize.textContent = `${sizeKB} KB`;
            }

            // Show preview container
            preview.classList.add('show');
        },

        /**
         * Clears file input and restores initial state
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
                fileTextMain.textContent = 'Select file';
            }

            if (fileTextSub) {
                fileTextSub.textContent = 'or drag and drop here';
            }

            if (preview) {
                preview.classList.remove('show');
            }
        },

        /**
         * Initializes drag and drop for file input
         * @param {HTMLElement} dropZone - Drop zone element
         * @param {HTMLElement} input - File input
         */
        initDragAndDrop(dropZone, input) {
            if (!dropZone || !input) return;

            // Prevent default behavior to allow drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            // Add visual effect when dragging over element
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.style.borderColor = 'var(--primary)';
                    dropZone.style.background = 'var(--bg-secondary)';
                    dropZone.style.transform = 'scale(1.02)';
                });
            });

            // Remove visual effect when leaving element
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.style.borderColor = '';
                    dropZone.style.background = '';
                    dropZone.style.transform = '';
                });
            });

            // Handle file drop
            dropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            });
        },

        /**
         * Toggles visibility and state of avatar upload area
         * Controls form opacity, input state, and visual warnings
         * Also applies visual effects to current avatar (desaturated, opacity)
         * @param {HTMLElement} checkbox - Avatar removal checkbox
         */
        toggleAvatarUpload(checkbox) {
            const avatarUploadSection = document.getElementById('avatarUploadSection');
            const avatarInput = document.getElementById('avatar');
            const removeWarning = document.getElementById('removeAvatarWarning');
            const currentAvatar = document.querySelector('.current-avatar img');
            const customFileUpload = document.getElementById('customFileUpload');

            if (checkbox.checked) {
                // If remove option is checked, disable file upload
                if (avatarUploadSection) avatarUploadSection.style.opacity = '0.5';
                if (avatarInput) {
                    avatarInput.disabled = true;
                    avatarInput.value = ''; // Clear any selected file
                }

                // Visually disable upload area
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
                // If remove option is not checked, enable file upload
                if (avatarUploadSection) avatarUploadSection.style.opacity = '1';
                if (avatarInput) avatarInput.disabled = false;

                // Visually enable upload area
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

    // Module initialization on DOM load
    document.addEventListener('DOMContentLoaded', () => {
        UserFormModule.init();

        if (window.CrudApp) {
            window.CrudApp.registerModule('userForm', UserFormModule);
        }
    });

    window.UserFormModule = UserFormModule;
})();