<?php
declare(strict_types=1);

/*
 * Clase para manejar los permisos de los usuarios basados en roles.
 */

class Permissions {
    // Roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_VIEWER = 'viewer';

    // Acciones
    public const USER_CREATE = 'user_create';
    public const USER_READ = 'user_read';
    public const USER_UPDATE = 'user_update';
    public const USER_DELETE = 'user_delete';
    
    /**
     * Verifica si un rol tiene permiso para realizar una acción.
     *
     * @param string $role El rol del usuario.
     * @param string $action La acción a realizar.
     * @return bool True si tiene permiso, false en caso contrario.
     */
    public static function check(string $role, string $action): bool {
        $permissions = [
            self::ROLE_ADMIN => [
                self::USER_CREATE,
                self::USER_READ,
                self::USER_UPDATE,
                self::USER_DELETE,
            ],
            self::ROLE_EDITOR => [
                self::USER_CREATE,
                self::USER_READ,
                self::USER_UPDATE,
            ],
            self::ROLE_VIEWER => [
                self::USER_READ,
            ],
        ];

        if (!isset($permissions[$role])) {
            return false;
        }

        return in_array($action, $permissions[$role]);
    }

    /**
     * Verifica si el usuario actual (en sesión) tiene permiso.
     *
     * @param string $action La acción a realizar.
     * @return bool True si tiene permiso, false en caso contrario.
     */
    public static function checkCurrent(string $action): bool {
        $role = Session::get('user_role');
        if (!$role) {
            return false;
        }

        return self::check($role, $action);
    }
    
    /**
     * Lanza una excepción si el usuario actual no tiene permiso.
     * 
     * @param string $action La acción requerida.
     * @throws AuthException
     */
    public static function require(string $action): void {
        if (!self::checkCurrent($action)) {
            throw new AuthException(
                "User does not have permission for action: $action",
                "No tienes permisos para realizar esta acción."
            );
        }
    }
}
