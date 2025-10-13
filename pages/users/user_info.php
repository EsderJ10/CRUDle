<?php
    // It will display the user information in a table format. It will receive the user id. It will be always called from user_index.php.

    // Include business logic and presentation
    require_once '../../config/paths.php';
    require_once getPath('config/config.php');
    require_once getPath('lib/business/user_operations.php');
    require_once getPath('lib/presentation/user_views.php');

    // Set page variables for partials
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
    
    // Use business logic to get user
    $user = getUserById($userId);
    
    if ($user === null) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: Usuario con ID ' . $userId . ' no encontrado.', 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }

    // Include header
    include getPath('views/partials/header.php');
    
    // Use presentation layer to render user information
    echo renderUserInfo($user);

    // Include footer
    include getPath('views/partials/footer.php');
?>
