<?php
/*
 * Página para listar usuarios.
 * Muestra una tabla con todos los usuarios y opciones para editar o eliminar.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/core/error_handler.php');

$pageTitle = "Gestión de Usuarios";
$pageHeader = "Lista de Usuarios";

try {
    include getPath('views/partials/header.php');
    
    // Mostrar mensajes de éxito o error de operaciones anteriores
    if (isset($_GET['message'])) {
        echo renderMessage($_GET['message'], 'success');
    }
    if (isset($_GET['error'])) {
        echo renderMessage($_GET['error'], 'error');
    }
    
    // Obtener y mostrar usuarios
    try {
        $users = getAllUsers();
        echo renderUserTable($users);
    } catch (CSVException $e) {
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_create.php" class="btn btn-primary">Crear Primer Usuario</a></p>';
    } catch (UserOperationException $e) {
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_create.php" class="btn btn-primary">Crear Primer Usuario</a></p>';
    } catch (Exception $e) {
        echo renderMessage('ERROR: Ocurrió un error al cargar los usuarios. ' . $e->getMessage(), 'error');
        error_log('Error loading users: ' . $e->getMessage());
    }
    
    include getPath('views/partials/footer.php');
} catch (Exception $e) {
    // Error no esperado
    include getPath('views/partials/header.php');
    echo renderMessage('ERROR: Ocurrió un error inesperado. ' . $e->getMessage(), 'error');
    include getPath('views/partials/footer.php');
    error_log('Unexpected error in user_index.php: ' . $e->getMessage());
    exit;
}
?>
