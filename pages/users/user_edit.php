<?php
    // It will receive the user id and it will show the user information in a form to edit it. It will be always called from user_index.php.
    // After edition, it will save the changes in the CSV file and redirect to user_index.php.

    // Include business logic and presentation
    require_once '../../config/paths.php';
    require_once getPath('config/config.php');
    require_once getPath('lib/business/user_operations.php');
    require_once getPath('lib/presentation/user_views.php');
    require_once getPath('lib/core/validation.php');
    require_once getPath('lib/core/sanitization.php');

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
        $formData = sanitizeUserData([
            'nombre' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'rol' => $_POST['role'] ?? '',
            'fecha_alta' => $_POST['fecha_alta'] ?? ''
        ]);

        // Validate input data
        $errors = validateUserData($formData);
        
        // Validate avatar if uploaded
        if (isset($_FILES['avatar'])) {
            $avatarErrors = validateAvatar($_FILES['avatar']);
            $errors = array_merge($errors, $avatarErrors);
        }
        
        if (empty($errors)) {
            // Handle avatar upload
            $newAvatarPath = null;
            $oldAvatarPath = $user['avatar'];
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $newAvatarPath = handleAvatarUpload($_FILES['avatar'], $userId, $formData['nombre']);
                if ($newAvatarPath) {
                    $formData['avatar'] = $newAvatarPath;
                    // Old avatar is automatically removed by removeExistingUserAvatar in handleAvatarUpload
                } else {
                    $formData['avatar'] = $oldAvatarPath; // Keep old avatar if upload failed
                }
            } else {
                // No new avatar uploaded, keep the existing one
                $formData['avatar'] = $oldAvatarPath;
            }
            
            $success = updateUser($userId, $formData);
            
            if ($success) {
                // Redirect to user_index.php with success message
                header('Location: user_index.php?message=' . urlencode('Usuario con ID ' . $userId . ' actualizado exitosamente.'));
                exit;
            } else {
                // If update failed and we uploaded a new avatar, clean it up
                if ($newAvatarPath && $newAvatarPath !== $oldAvatarPath) {
                    deleteAvatarFile($newAvatarPath);
                }
                // Redirect to user_index.php with error message
                header('Location: user_index.php?error=' . urlencode('ERROR al actualizar el usuario con ID ' . $userId . '.'));
                exit;
            }
        } else {
            // There are validation errors, display the form with errors
            include getPath('views/partials/header.php');
            
            // Display all validation errors
            foreach ($errors as $error) {
                echo renderMessage($error, 'error');
            }
            
            // Re-populate the form with submitted data (but keep the original user data for non-submitted fields)
            $user['nombre'] = $formData['nombre'];
            $user['email'] = $formData['email'];
            $user['rol'] = $formData['rol'];
            
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }
    }
    
    // If the user was found, display the edit form (only for GET requests)
    if ($user !== null) {
        include getPath('views/partials/header.php');
        include getPath('views/components/forms/user_form.php');
        include getPath('views/partials/footer.php');
    }
?>
