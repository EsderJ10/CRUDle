<?php
/*
 * Página para editar un usuario.
 * Maneja la visualización del formulario y el procesamiento de datos.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');

Permissions::require(Permissions::USER_UPDATE);

$pageTitle = "Editar Usuario";
$pageHeader = "Editar Usuario";

try {
    // Validar que se proporcione un ID
    // Validar que se proporcione un ID
    if (!isset($_GET['id'])) {
        Session::setFlash('error', 'No se ha proporcionado un ID de usuario.');
        header('Location: user_index.php');
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
        Session::setFlash('error', $e->getUserMessage());
        header('Location: user_index.php');
        exit;
    }
    
    // Procesar formulario POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Error de seguridad: Token CSRF inválido.');
            header('Location: user_edit.php?id=' . $userId);
            exit;
        }

        try {
            // Sanitizar datos
            $formData = sanitizeUserData([
                'nombre' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'rol' => $_POST['role'] ?? '',
                'password' => $_POST['password'] ?? ''
            ]);

            // Security: Only Admins (who can delete) can change roles
            if (!Permissions::checkCurrent(Permissions::USER_DELETE)) {
                $formData['rol'] = $user['rol'];
            }
            
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
                Session::setFlash('success', 'Usuario con ID ' . $userId . ' actualizado exitosamente.');
                header('Location: user_index.php');
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
            
        } catch (AppException $e) {
            // Errores de aplicación conocidos
            Session::setFlash('error', $e->getUserMessage());
            header('Location: user_index.php');
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
    // Error no esperado - Dejar que el Global Handler lo maneje
    throw $e;
}
?>
