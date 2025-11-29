<?php
/*
 * Página para mostrar la información detallada de un usuario.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');

$pageTitle = "Información del Usuario";
$pageHeader = "Detalles del Usuario";

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
    
    // Cargar usuario
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
    }catch (UserOperationException $e) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }
    
    // Mostrar información del usuario
    include getPath('views/partials/header.php');
    echo renderUserInfo($user);
    include getPath('views/partials/footer.php');
    
} catch (Exception $e) {
    // Error no esperado
    include getPath('views/partials/header.php');
    echo renderMessage('ERROR: Ocurrió un error inesperado. ' . $e->getMessage(), 'error');
    echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
    include getPath('views/partials/footer.php');
    error_log('Unexpected error in user_info.php: ' . $e->getMessage());
    exit;
}
?>
