<?php
/*
 * Helper functions for user management.
 * Author: José Antonio Cortés Ferre
 */

require_once __DIR__ . '/../../config/paths.php';

require_once getPath('lib/helpers/enums.php');

function getRoles() {
    return array_map(fn($role) => $role->value, Role::cases());    
}


/**
 * Normalizes the avatar path to work in both Docker and XAMPP
 * Removes /CRUDle/ from the path if it exists and adds the correct WEB_ROOT
 */
function normalizeAvatarPath($avatarPath) {
    if (empty($avatarPath)) {
        return null;
    }
    
    // If it's just a filename (no slashes), prepend the standard path
    if (strpos($avatarPath, '/') === false) {
        return WEB_ROOT . '/uploads/avatars/' . $avatarPath;
    }
    
    // If it already has /CRUDle/, remove it to re-add correct WEB_ROOT
    $cleanPath = str_replace('/CRUDle/', '/', $avatarPath);
    
    // Ensure it starts with /
    if (substr($cleanPath, 0, 1) !== '/') {
        $cleanPath = '/' . $cleanPath;
    }
    
    // Add correct WEB_ROOT
    return WEB_ROOT . $cleanPath;
}
?>
