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
?>
