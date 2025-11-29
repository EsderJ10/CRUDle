document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initPasswordValidation();
});

/**
 * Handles password visibility toggling with accessibility support.
 */
function initPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', (e) => {
            // Prevent form submission if button is inside a form
            e.preventDefault();

            // Find the input relative to the button wrapper or previous sibling
            // Using logic that handles slightly different HTML structures safely
            const wrapper = button.closest('.password-input-wrapper');
            const input = wrapper ? wrapper.querySelector('input') : button.previousElementSibling;
            const icon = button.querySelector('i');

            if (!input) return;

            const isPassword = input.type === 'password';

            // Toggle Type
            input.type = isPassword ? 'text' : 'password';

            // Update Icon
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');

            // Accessibility: Tell screen readers the state changed
            button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', isPassword);

            // UX: Keep focus on the input so typing isn't interrupted
            input.focus();
        });
    });
}

/**
 * Real-time password matching validation.
 * "Don't wait for the click to tell me I'm wrong."
 */
function initPasswordValidation() {
    const form = document.querySelector('.auth-form');
    if (!form) return;

    const passInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');

    if (!passInput || !confirmInput) return;

    // Helper to show/hide error
    const updateStatus = () => {
        const val1 = passInput.value;
        const val2 = confirmInput.value;

        // Remove previous validation classes
        confirmInput.classList.remove('is-valid', 'is-invalid');

        if (val2 === '') {
            // Do nothing if empty
            return;
        }

        if (val1 === val2) {
            confirmInput.classList.add('is-valid');
            confirmInput.setCustomValidity(''); // Valid
        } else {
            confirmInput.classList.add('is-invalid');
            confirmInput.setCustomValidity('Passwords do not match'); // Invalid
        }
    };

    // Listen to input events for real-time feedback
    confirmInput.addEventListener('input', updateStatus);
    passInput.addEventListener('input', () => {
        // Only validate match if user has already typed in confirm box
        if (confirmInput.value !== '') updateStatus();
    });

    // Final safety check on submit
    form.addEventListener('submit', (e) => {
        if (passInput.value !== confirmInput.value) {
            e.preventDefault();
            // Shake animation or focus logic could go here
            confirmInput.focus();
            confirmInput.classList.add('shake-animation'); // Optional CSS class

            // Remove animation class after it plays so it can be triggered again
            setTimeout(() => {
                confirmInput.classList.remove('shake-animation');
            }, 500);
        }
    });
}
