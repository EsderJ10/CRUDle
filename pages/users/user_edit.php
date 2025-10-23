<?php
/*
 * Página para editar un usuario.
 * Maneja la visualización del formulario y el procesamiento de datos.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

    require_once '../../config/paths.php';
    require_once getPath('config/config.php');
    require_once getPath('lib/business/user_operations.php');
    require_once getPath('lib/presentation/user_views.php');
    require_once getPath('lib/core/validation.php');
    require_once getPath('lib/core/sanitization.php');

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
    
    $user = getUserById($userId);
    
    if ($user === null) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: Usuario con ID ' . $userId . ' no encontrado.', 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }

    // Si la petición es POST, procesar el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formData = sanitizeUserData([
            'nombre' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'rol' => $_POST['role'] ?? '',
            'fecha_alta' => $_POST['fecha_alta'] ?? ''
        ]);
        
        $removeAvatar = isset($_POST['remove_avatar']) && $_POST['remove_avatar'] == '1';
        $errors = validateUserData($formData);
        
        if ($removeAvatar && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $errors[] = "No puedes eliminar y subir un avatar al mismo tiempo. Elige solo una opción.";
        }
    
        if (!$removeAvatar && isset($_FILES['avatar'])) {
            $avatarErrors = validateAvatar($_FILES['avatar']);
            $errors = array_merge($errors, $avatarErrors);
        }
        
        if (empty($errors)) {
            $newAvatarPath = null;
            $oldAvatarPath = $user['avatar'];
            
            if ($removeAvatar) {
                // El usuario ha optado por eliminar el avatar existente
                if ($oldAvatarPath) {
                    deleteAvatarFile($oldAvatarPath);
                }
                $formData['avatar'] = null;
            } else if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                // El usuario está subiendo un nuevo avatar
                $newAvatarPath = handleAvatarUpload($_FILES['avatar'], $userId, $formData['nombre']);
                if ($newAvatarPath) {
                    $formData['avatar'] = $newAvatarPath;
                    // El avatar antiguo se elimina automáticamente en removeExistingUserAvatar en handleAvatarUpload
                } else {
                    $formData['avatar'] = $oldAvatarPath; // Se mantiene el antiguo si la subida falla
                }
            } else {
                // No changes to avatar, keep the existing one
                $formData['avatar'] = $oldAvatarPath;
            }

            $success = updateUser($userId, $formData);
            
            if ($success) {
                header('Location: user_index.php?message=' . urlencode('Usuario con ID ' . $userId . ' actualizado exitosamente.'));
                exit;
            } else {
                if ($newAvatarPath && $newAvatarPath !== $oldAvatarPath) {
                    deleteAvatarFile($newAvatarPath);
                }
                header('Location: user_index.php?error=' . urlencode('ERROR al actualizar el usuario con ID ' . $userId . '.'));
                exit;
            }
        } else {
            include getPath('views/partials/header.php');
            
            foreach ($errors as $error) {
                echo renderMessage($error, 'error');
            }
            
            $user['nombre'] = $formData['nombre'];
            $user['email'] = $formData['email'];
            $user['rol'] = $formData['rol'];
            
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }
    }

    // Si se entra por GET (se encuentra el usuario), mostrar el formulario con los datos actuales del usuario
    if ($user !== null) {
        include getPath('views/partials/header.php');
        include getPath('views/components/forms/user_form.php');
        include getPath('views/partials/footer.php');
    }
?>
