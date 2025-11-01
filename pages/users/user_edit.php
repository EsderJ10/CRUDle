<?php
/*
 * Página para editar un usuario.
 * Maneja la visualización del formulario y el procesamiento de datos.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/paths.php';
require_once getPath('config/config.php');
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/core/error_handler.php');

$pageTitle = "Editar Usuario";
$pageHeader = "Editar Usuario";

try {
    // Validar que se proporcione un ID
    if (!isset($_GET['id'])) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: No se ha proporcionado un ID de usuario.', 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }
    
    $userId = $_GET['id'];
    
    // Cargar usuario existente
    try {
        $user = getUserById($userId);
        
        if ($user === null) {
            throw new ResourceNotFoundException(
                'User not found: ' . $userId,
                'El usuario no existe.'
            );
        }
    } catch (ResourceNotFoundException $e) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    } catch (CSVException $e) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }
    
    // Procesar formulario POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Sanitizar datos
            $formData = sanitizeUserData([
                'nombre' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'rol' => $_POST['role'] ?? ''
            ]);
            
            // Validar datos básicos
            $errors = validateUserData($formData);
            
            // Validar conflictos entre remove y upload
            $removeAvatar = isset($_POST['remove_avatar']) && $_POST['remove_avatar'] == '1';
            if ($removeAvatar && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $errors[] = "No puedes eliminar y subir un avatar al mismo tiempo. Elige solo una opción.";
            }
            
            // Validar avatar si se proporciona
            if (!$removeAvatar && isset($_FILES['avatar'])) {
                try {
                    $avatarErrors = validateAvatar($_FILES['avatar']);
                    $errors = array_merge($errors, $avatarErrors);
                } catch (ValidationException $e) {
                    $errors = array_merge($errors, $e->getErrors()['avatar'] ?? [$e->getUserMessage()]);
                } catch (FileUploadException $e) {
                    $errors[] = $e->getUserMessage();
                }
            }
            
            if (!empty($errors)) {
                throw new ValidationException(
                    'Form validation failed',
                    ['general' => $errors],
                    'Por favor, corrija los errores en el formulario.'
                );
            }
            
            // Manejar avatar
            $newAvatarPath = null;
            $oldAvatarPath = $user['avatar'];
            
            if ($removeAvatar) {
                // Usuario optó por eliminar el avatar existente
                if ($oldAvatarPath) {
                    try {
                        deleteAvatarFile($oldAvatarPath);
                    } catch (AvatarException $e) {
                        error_log('Avatar deletion failed: ' . $e->getMessage());
                        // No fallar la operación entera por esto
                    }
                }
                $formData['avatar'] = null;
            } else if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                // Usuario está subiendo un nuevo avatar
                try {
                    $newAvatarPath = handleAvatarUpload($_FILES['avatar'], $userId, $formData['nombre']);
                    if ($newAvatarPath) {
                        $formData['avatar'] = $newAvatarPath;
                        // El avatar antiguo se elimina automáticamente en handleAvatarUpload
                    } else {
                        $formData['avatar'] = $oldAvatarPath; // Mantener el antiguo si la subida falla
                    }
                } catch (AvatarException $e) {
                    error_log('Avatar upload failed: ' . $e->getMessage());
                    // Mantener el avatar antiguo
                    $formData['avatar'] = $oldAvatarPath;
                }
            } else {
                // Sin cambios al avatar
                $formData['avatar'] = $oldAvatarPath;
            }
            
            // Actualizar usuario
            $success = updateUser($userId, $formData);
            
            if ($success) {
                header('Location: user_index.php?message=' . urlencode('Usuario con ID ' . $userId . ' actualizado exitosamente.'));
                exit;
            } else {
                throw new UserOperationException(
                    'Failed to update user',
                    'Error al actualizar el usuario.'
                );
            }
            
        } catch (ValidationException $e) {
            // Mostrar formulario con errores
            include getPath('views/partials/header.php');
            
            $fieldErrors = $e->getErrors();
            foreach ($fieldErrors['general'] ?? [] as $error) {
                echo renderMessage($error, 'error');
            }
            
            // Actualizar datos del formulario
            $user['nombre'] = $formData['nombre'];
            $user['email'] = $formData['email'];
            $user['rol'] = $formData['rol'];
            
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (ResourceNotFoundException $e) {
            include getPath('views/partials/header.php');
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
            echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (CSVException $e) {
            include getPath('views/partials/header.php');
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
            echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (UserOperationException $e) {
            include getPath('views/partials/header.php');
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
            echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
            include getPath('views/partials/footer.php');
            exit;
        }
    }
    
    // GET request - mostrar formulario con datos del usuario
    if ($user !== null) {
        include getPath('views/partials/header.php');
        include getPath('views/components/forms/user_form.php');
        include getPath('views/partials/footer.php');
    }
    
} catch (Exception $e) {
    // Error no esperado
    include getPath('views/partials/header.php');
    echo renderMessage('ERROR: Ocurrió un error inesperado. ' . $e->getMessage(), 'error');
    echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
    include getPath('views/partials/footer.php');
    error_log('Unexpected error in user_edit.php: ' . $e->getMessage());
    exit;
}
?>
