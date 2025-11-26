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

requireAdmin();

$pageTitle = "Invitar Usuario";
$pageHeader = "Invitar Nuevo Usuario";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Error de seguridad: Token CSRF inválido.');
            // Recargar formulario
            include getPath('views/partials/header.php');
            $formData = $_POST; // Repoblar
            include getPath('views/components/forms/invite_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }

        try {
            // Sanitizar datos de entrada
            $formData = sanitizeUserData([
                'nombre' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'rol' => $_POST['role'] ?? ''
            ]);
            
            // Validar datos básicos (nombre, email, rol)
            // Usamos validateUserData pero ignoramos password y avatar
            $errors = [];
            if (empty($formData['nombre'])) $errors[] = "El nombre es obligatorio.";
            if (empty($formData['email'])) $errors[] = "El email es obligatorio.";
            if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";
            if (empty($formData['rol'])) $errors[] = "El rol es obligatorio.";
            
            if (!empty($errors)) {
                throw new ValidationException(
                    'Form validation failed',
                    ['general' => $errors],
                    'Por favor, corrija los errores en el formulario.'
                );
            }
            
            // Invitar usuario
            $userId = inviteUser($formData['nombre'], $formData['email'], $formData['rol']);
            
            // Éxito - redirigir con mensaje flash
            Session::setFlash('success', 'Invitación enviada exitosamente.');
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
            include getPath('views/components/forms/invite_form.php');
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (AppException $e) {
            // Errores de aplicación conocidos (UserOperation, etc.)
            Session::setFlash('error', $e->getUserMessage());
            include getPath('views/partials/header.php');
            include getPath('views/components/forms/invite_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }
    } else {
        // GET request - mostrar formulario vacío
        include getPath('views/partials/header.php');
        include getPath('views/components/forms/invite_form.php');
        include getPath('views/partials/footer.php');
    }
} catch (Exception $e) {
    // Error no esperado (500) - Dejar que el Global Handler lo maneje
    throw $e;
}
?>

