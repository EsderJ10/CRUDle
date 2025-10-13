<?php
// Here are all the main validation functions for the forms

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
    
    // Avatar is optional, so if no file is uploaded, that's fine
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $errors;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error al subir el archivo de avatar.";
        return $errors;
    }
    
    // Check file size (max 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB in bytes
    if ($file['size'] > $maxSize) {
        $errors[] = "El avatar no puede ser mayor a 2MB.";
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = "El avatar debe ser una imagen (JPEG, PNG, GIF, SVG).";
    }
    
    // Check file extension
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
