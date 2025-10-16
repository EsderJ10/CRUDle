<?php

require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');

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
    
    if (isset($_FILES['avatar'])) {
        $avatarErrors = validateAvatar($_FILES['avatar']);
        $errors = array_merge($errors, $avatarErrors);
    }
    
    if (empty($errors)) {
        $userId = createUser($formData);
        
        if ($userId) {
            // Handle avatar upload with proper naming now that we have user ID
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarPath = handleAvatarUpload($_FILES['avatar'], $userId, $formData['nombre']);
                if ($avatarPath) {
                    // Update user record with avatar path
                    $formData['avatar'] = $avatarPath;
                    updateUser($userId, $formData);
                }
            }
            $success = true;
        } else {
            $success = false;
        }
        
        // Include header
        include getPath('views/partials/header.php');
        
        if ($success) {
            echo renderMessage("Usuario creado exitosamente.", 'success');
        } else {
            echo renderMessage("Error al crear el usuario.", 'error');
            // Clean up avatar file if user creation failed
            if ($avatarPath) {
                deleteAvatarFile($avatarPath);
            }
        }
        
        echo '<div class="card text-center">
                <div class="actions">
                    <a href="user_index.php" class="btn btn-primary">Volver a la Lista de Usuarios</a>
                    <a href="user_create.php" class="btn btn-secondary">Crear Otro Usuario</a>
                </div>
              </div>';
        
        include getPath('views/partials/footer.php');
    } else {
        include getPath('views/partials/header.php');
        
        foreach ($errors as $error) {
            echo renderMessage($error, 'error');
        }
        
        include getPath('views/components/forms/user_form.php');
        
        include getPath('views/partials/footer.php');
    }
} else {
    include getPath('views/partials/header.php');
    include getPath('views/components/forms/user_form.php');
    include getPath('views/partials/footer.php');
}
?>
