<?php
/*
 * Página para mostrar la información detallada de un usuario.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

    require_once '../../config/paths.php';
    require_once getPath('config/config.php');
    require_once getPath('lib/business/user_operations.php');
    require_once getPath('lib/presentation/user_views.php');

    $pageTitle = "Información del Usuario";
    $pageHeader = "Detalles del Usuario";

    if (!isset($_GET['id'])) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: No se ha proporcionado un ID de usuario.', 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }

    $userId = $_GET['id'];
    $file = getPath(DATA_FILE);
    
    if (!file_exists($file)) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: El archivo de usuarios no existe.', 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }
    
    $user = getUserById($userId);
    
    if ($user === null) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: Usuario con ID ' . $userId . ' no encontrado.', 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }

    include getPath('views/partials/header.php');
    
    echo renderUserInfo($user);

    include getPath('views/partials/footer.php');
?>
