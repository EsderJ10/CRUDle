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
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/core/error_handler.php');

$pageTitle = "Crear Usuario";
$pageHeader = "Crear Nuevo Usuario";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Sanitizar datos de entrada
            $formData = sanitizeUserData([
                'nombre' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'rol' => $_POST['role'] ?? ''
            ]);
            
            // Validar datos
            $errors = validateUserData($formData);
            
            // Validar avatar si se proporciona
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
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
            
            // Crear usuario
            $userId = createUser($formData);
            
            // Procesar avatar si se proporcionó
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                try {
                    $avatarPath = handleAvatarUpload($_FILES['avatar'], $userId, $formData['nombre']);
                    if ($avatarPath) {
                        $formData['avatar'] = $avatarPath;
                        updateUser($userId, $formData);
                    }
                } catch (AvatarException $e) {
                    // Avatar upload failed, but user was created successfully
                    // Log this but don't fail the entire operation
                    error_log('Avatar upload failed for user ' . $userId . ': ' . $e->getMessage());
                    // User is still created without avatar
                }
            }
            
            // Éxito - mostrar mensaje de confirmación
            include getPath('views/partials/header.php');
            echo renderMessage("* Usuario creado exitosamente.", 'success');
            
            echo '<div class="card text-center">
                    <div class="actions">
                        <a href="user_index.php" class="btn btn-primary">Volver a la Lista de Usuarios</a>
                        <a href="user_create.php" class="btn btn-secondary">Crear Otro Usuario</a>
                    </div>
                  </div>';
            
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (ValidationException $e) {
            // Mostrar formulario con errores
            include getPath('views/partials/header.php');
            
            // Mostrar errores generales
            $fieldErrors = $e->getErrors();
            foreach ($fieldErrors['general'] ?? [] as $error) {
                echo renderMessage($error, 'error');
            }
            
            // Repoblar datos del formulario
            $user = $formData ?? [];
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (CSVException $e) {
            include getPath('views/partials/header.php');
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
            echo '<p><a href="user_create.php">Volver al formulario</a></p>';
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (UserOperationException $e) {
            include getPath('views/partials/header.php');
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
            echo '<p><a href="user_create.php">Volver al formulario</a></p>';
            include getPath('views/partials/footer.php');
            exit;
        }
    } else {
        // GET request - mostrar formulario vacío
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
    error_log('Unexpected error in user_create.php: ' . $e->getMessage());
    exit;
}
?>

