<?php
/*
 * Funciones auxiliares para la gestión de usuarios.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/paths.php';

require_once getPath('lib/helpers/enums.php');
require_once getPath('lib/core/csv.php');

function getRoles() {
    return array_map(fn($role) => $role->value, Role::cases());    
}

function getID() {
    return getNextId();
}

/**
 * Normaliza la ruta del avatar para que funcione tanto en Docker como en XAMPP
 * Elimina /CRUDle/ del path si existe y añade el WEB_ROOT correcto
 */
function normalizeAvatarPath($avatarPath) {
    if (empty($avatarPath)) {
        return null;
    }
    
    // Si la ruta ya tiene /CRUDle/, la quitamos
    $cleanPath = str_replace('/CRUDle/', '/', $avatarPath);
    
    // Aseguramos que empiece con /
    if (substr($cleanPath, 0, 1) !== '/') {
        $cleanPath = '/' . $cleanPath;
    }
    
    // Añadimos el WEB_ROOT correcto según el entorno
    return WEB_ROOT . $cleanPath;
}
?>
