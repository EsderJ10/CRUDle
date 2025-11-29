<?php
/*
 * Funciones auxiliares para la gestión de usuarios.
 * Autor: José Antonio Cortés Ferre
 */

require_once __DIR__ . '/../../config/paths.php';

require_once getPath('lib/helpers/enums.php');

function getRoles() {
    return array_map(fn($role) => $role->value, Role::cases());    
}


/**
 * Normaliza la ruta del avatar para que funcione tanto en Docker como en XAMPP
 * Elimina /CRUDle/ del path si existe y añade el WEB_ROOT correcto
 */
function normalizeAvatarPath($avatarPath) {
    if (empty($avatarPath)) {
        return null;
    }
    
    // If it's just a filename (no slashes), prepend the standard path
    if (strpos($avatarPath, '/') === false) {
        return WEB_ROOT . '/uploads/avatars/' . $avatarPath;
    }
    
    // Legacy handling: If it already has /CRUDle/, remove it to re-add correct WEB_ROOT
    $cleanPath = str_replace('/CRUDle/', '/', $avatarPath);
    
    // Ensure it starts with /
    if (substr($cleanPath, 0, 1) !== '/') {
        $cleanPath = '/' . $cleanPath;
    }
    
    // Add correct WEB_ROOT
    return WEB_ROOT . $cleanPath;
}
?>
