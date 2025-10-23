<?php
/*
 * Página para crear un nuevo usuario.
 * Maneja la visualización del formulario y el procesamiento de datos.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');

$pageTitle = "Crear Usuario";
$pageHeader = "Crear Nuevo Usuario";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = sanitizeUserData([
        'nombre' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'rol' => $_POST['role'] ?? ''
    ]);
    
    $errors = validateUserData($formData);
    
    if (isset($_FILES['avatar'])) {
        $avatarErrors = validateAvatar($_FILES['avatar']);
        $errors = array_merge($errors, $avatarErrors);
    }
    
    if (empty($errors)) {
        $userId = createUser($formData);
        
        if ($userId) {
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarPath = handleAvatarUpload($_FILES['avatar'], $userId, $formData['nombre']);
                if ($avatarPath) {
                    $formData['avatar'] = $avatarPath;
                    updateUser($userId, $formData);
                }
            }
            $success = true;
        } else {
            $success = false;
        }
        
        include getPath('views/partials/header.php');
        
        if ($success) {
            echo renderMessage("* Usuario creado exitosamente.", 'success');
        } else {
            echo renderMessage("* ERROR: Creación de usuario fallida.", 'error');
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
