<?php
/*
 * En este archivo se definen funciones para validar los datos de entrada del usuario.
 */

require_once getPath('config/config.php');

function validateName($name) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "El nombre es obligatorio.";
        return $errors;
    }
    
    $name = trim($name);
    
    if (strlen($name) < MIN_NAME_LENGTH) {
        $errors[] = "El nombre debe tener al menos " . MIN_NAME_LENGTH . " caracteres.";
    }
    
    if (strlen($name) > MAX_NAME_LENGTH) {
        $errors[] = "El nombre no puede tener más de " . MAX_NAME_LENGTH . " caracteres.";
    }
    
    if (preg_match('/^[0-9]+$/', $name)) {
        $errors[] = "El nombre no puede ser solo números.";
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/', $name)) {
        $errors[] = "El nombre solo puede contener letras y espacios.";
    }
    
    return $errors;
}

function validateEmail($email) {
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "El email es obligatorio.";
        return $errors;
    }
    
    $email = trim($email);
    
    if (strlen($email) > MAX_EMAIL_LENGTH) {
        $errors[] = "El email no puede tener más de " . MAX_EMAIL_LENGTH . " caracteres.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no tiene un formato válido.";
    }
    
    return $errors;
}


function validateAvatar($file) {
    $errors = [];
    
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $errors;
    }
    
    // Se busca cualquier error en la subida del archivo para manejarlo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "* ERROR: Subida fallida del archivo de avatar.";
        return $errors;
    }
    
    // Compprobación de tipos MIME y extensiones permitidas
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = "El avatar debe ser una imagen (JPEG, PNG, GIF, SVG).";
    }
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        $errors[] = "Extensión de archivo no válida. Use: JPG, PNG, GIF, SVG.";
    }
    
    return $errors;
}

function validateUserData($data) {
    $errors = [];
    
    if (isset($data['nombre'])) {
        $errors = array_merge($errors, validateName($data['nombre']));
    }
    
    if (isset($data['email'])) {
        $errors = array_merge($errors, validateEmail($data['email']));
    }
    
    return $errors;
}
?>
