<?php
// Data sanitization functions

function sanitizeName($name) {
    if (empty($name)) {
        return '';
    }
    
    $name = trim($name);
    $name = preg_replace('/\s+/', ' ', $name);
    
    // Capitalize first letter of each word
    $name = ucwords(strtolower($name));
    
    return $name;
}

function sanitizeEmail($email) {
    if (empty($email)) {
        return '';
    }
    
    $email = strtolower(trim($email));
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    return $email;
}

function sanitizeRole($role) {
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
    
    if (isset($data['nombre'])) {
        $sanitized['nombre'] = sanitizeName($data['nombre']);
    }
    
    if (isset($data['email'])) {
        $sanitized['email'] = sanitizeEmail($data['email']);
    }
    
    if (isset($data['rol'])) {
        $sanitized['rol'] = sanitizeRole($data['rol']);
    }
    
    if (isset($data['id'])) {
        $sanitized['id'] = sanitizeUserId($data['id']);
    }
    
    return $sanitized;
}

function sanitizeOutput($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


function sanitizeUrl($value) {
    return urlencode($value);
}

function sanitizeForCSV($value) {
    // Remove or escape characters that could break CSV format
    $value = str_replace(["\r", "\n"], ' ', $value);
    $value = trim($value);
    
    return $value;
}
?>
