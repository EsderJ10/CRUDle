<?php
/*
 * Página para crear un nuevo usuario.
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

requireLogin();

$pageTitle = "Crear Usuario";
$pageHeader = "Crear Nuevo Usuario";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Error de seguridad: Token CSRF inválido.');
            // Recargar formulario
            include getPath('views/partials/header.php');
            $user = $_POST; // Repoblar
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }

        try {
            // Sanitizar datos de entrada
            $formData = sanitizeUserData([
                'nombre' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'rol' => $_POST['role'] ?? '',
                'password' => $_POST['password'] ?? ''
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
            
            // Éxito - redirigir con mensaje flash
            Session::setFlash('success', 'Usuario creado exitosamente.');
            header('Location: user_index.php');
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
            
        } catch (AppException $e) {
            // Errores de aplicación conocidos (CSV, UserOperation, etc.)
            Session::setFlash('error', $e->getUserMessage());
            include getPath('views/partials/header.php');
            include getPath('views/components/forms/user_form.php');
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
    // Error no esperado (500) - Dejar que el Global Handler lo maneje
    throw $e;
}
?>

