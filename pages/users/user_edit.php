<?php
    // It will receive the user id and it will show the user information in a form to edit it. It will be always called from user_index.php.
    // After edition, it will save the changes in the CSV file and redirect to user_index.php.

    // Include business logic and presentation
    require_once '../../config/paths.php';
    require_once getPath('config/config.php');
    require_once getPath('lib/business/user_operations.php');
    require_once getPath('lib/presentation/user_views.php');

    // Set page variables for partials
    $pageTitle = "Editar Usuario";
    $pageHeader = "Editar Usuario";

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

    // If the request is POST, it means the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formData = [
            'nombre' => $_POST['nombre'] ?? '',
            'email' => $_POST['email'] ?? '',
            'rol' => $_POST['rol'] ?? '',
            'fecha_alta' => $_POST['fecha_alta'] ?? ''
        ];

        // Use business logic to update user
        $success = updateUser($userId, $formData);
        
        if ($success) {
            // Redirect to user_index.php with success message
            header('Location: user_index.php?message=' . urlencode('Usuario con ID ' . $userId . ' actualizado exitosamente.'));
            exit;
        } else {
            // Redirect to user_index.php with error message
            header('Location: user_index.php?error=' . urlencode('ERROR al actualizar el usuario con ID ' . $userId . '.'));
            exit;
        }
    }
    
    // If the user was found, display the edit form (only for GET requests)
    if ($user !== null) {
        include getPath('views/partials/header.php');
        echo renderEditForm($user);
        include getPath('views/partials/footer.php');
    }
?>
