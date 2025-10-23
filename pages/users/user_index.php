<?php
/*
 * Página para listar usuarios.
 * Muestra una tabla con todos los usuarios y opciones para editar o eliminar.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');

$pageTitle = "Gestión de Usuarios";
$pageHeader = "Lista de Usuarios";

include getPath('views/partials/header.php');

if (isset($_GET['message'])) {
    echo renderMessage($_GET['message'], 'success');
}
if (isset($_GET['error'])) {
    echo renderMessage($_GET['error'], 'error');
}

$users = getAllUsers();

echo renderUserTable($users);

include getPath('views/partials/footer.php');
?>
