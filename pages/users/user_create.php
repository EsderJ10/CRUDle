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

Permissions::require(Permissions::USER_CREATE);

$pageTitle = "Invite User";
$pageHeader = "Invite New User";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Security error: Invalid CSRF token.');
            // Recargar formulario
            include getPath('views/partials/header.php');
            $formData = $_POST; // Repoblar
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }

        try {
            // Sanitizar datos de entrada
            $formData = sanitizeUserData([
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? ''
            ]);
            
            // Validar datos básicos (nombre, email, role)
            // Usamos validateUserData pero ignoramos password y avatar
            $errors = [];
            if (empty($formData['name'])) $errors[] = "Name is required.";
            if (empty($formData['email'])) $errors[] = "Email is required.";
            if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
            if (empty($formData['role'])) $errors[] = "Role is required.";
            
            if (!empty($errors)) {
                throw new ValidationException(
                    'Form validation failed',
                    ['general' => $errors],
                    'Please correct the errors in the form.'
                );
            }
            
            // Procesar avatar si se subió uno
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                try {
                    $avatarPath = handleAvatarUpload($_FILES['avatar'], null, $formData['name']);
                } catch (Exception $e) {
                    // Si falla el avatar, advertimos pero continuamos con la invitación
                    Session::setFlash('warning', 'User invited, but there was an error uploading the avatar: ' . $e->getMessage());
                }
            }
            
            // Invitar usuario
            $userId = inviteUser($formData['name'], $formData['email'], $formData['role'], $avatarPath);
            
            // Éxito - redirigir con mensaje flash
            Session::setFlash('success', 'Invitation sent successfully.');
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
            // $formData ya tiene los datos sanitizados
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (AppException $e) {
            // Errores de aplicación conocidos (UserOperation, etc.)
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

