<?php
/*
 * Aquí se definen las funciones de saneamiento de datos.
 */

function sanitizeName($name) {
    if (is_array($name) || is_object($name)) {
        return '';
    }
    
    if (empty($name)) {
        return '';
    }
    
    $name = trim($name);
    $name = preg_replace('/\s+/', ' ', $name);
    // Reemplazado de FILTRO_SANITIZE_STRING (deprecated) por strip_tags
    $name = strip_tags($name);
    $name = ucwords(strtolower($name));
    
    return $name;
}

function sanitizeEmail($email) {
    if (is_array($email) || is_object($email)) {
        return '';
    }
    
    if (empty($email)) {
        return '';
    }
    
    $email = strtolower(trim($email));
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    return $email;
}

function sanitizeRole($role) {
    if (is_array($role) || is_object($role)) {
        return '';
    }
    
    if (empty($role)) {
        return '';
    }

    $role = strtolower(trim($role));
    
    return $role;
}

function sanitizeUserId($id) {
    if (empty($id)) {
        return 0;
    }
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    return $id;
}

function sanitizeUserData($data) {
    $sanitized = [];
    
    if (isset($data['name'])) {
        $sanitized['name'] = sanitizeName($data['name']);
    }
    
    if (isset($data['email'])) {
        $sanitized['email'] = sanitizeEmail($data['email']);
    }
    
    if (isset($data['role'])) {
        $sanitized['role'] = sanitizeRole($data['role']);
    }
    
    if (isset($data['id'])) {
        $sanitized['id'] = sanitizeUserId($data['id']);
    }

    if (isset($data['password'])) {
        // Only ensure it's a string
        $sanitized['password'] = (string)$data['password'];
    }
    
    return $sanitized;
}

function sanitizeOutput($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


function sanitizeUrl($value) {
    return urlencode($value);
}
?>
