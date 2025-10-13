<?php
// User creation page

require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');

// Set page variables for partials
$pageTitle = "Crear Usuario";
$pageHeader = "Crear Nuevo Usuario";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $formData = sanitizeUserData([
        'nombre' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'rol' => $_POST['role'] ?? ''
    ]);
    
    // Validate input data
    $errors = validateUserData($formData);
    
    if (empty($errors)) {
        // Use business logic to create user
        $success = createUser($formData);
        
        // Include header
        include getPath('views/partials/header.php');
        
        if ($success) {
            echo renderMessage("Usuario creado exitosamente.", 'success');
        } else {
            echo renderMessage("Error al crear el usuario.", 'error');
        }
        
        echo '<div class="card text-center">
                <div class="actions">
                    <a href="user_index.php" class="btn btn-primary">Volver a la Lista de Usuarios</a>
                    <a href="user_create.php" class="btn btn-secondary">Crear Otro Usuario</a>
                </div>
              </div>';
        
        // Include footer
        include getPath('views/partials/footer.php');
    } else {
        // Show form with errors
        include getPath('views/partials/header.php');
        
        foreach ($errors as $error) {
            echo renderMessage($error, 'error');
        }
        
        include getPath('views/components/forms/user_form.php');
        
        include getPath('views/partials/footer.php');
    }
} else {
    // Show form
    include getPath('views/partials/header.php');
    include getPath('views/components/forms/user_form.php');
    include getPath('views/partials/footer.php');
}
?>
