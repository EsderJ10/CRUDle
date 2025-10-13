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
