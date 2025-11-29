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
     * Verifica si un role tiene permiso para realizar una acción.
     *
     * @param string $role El role del usuario.
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

    private const ROLE_HIERARCHY = [
        self::ROLE_VIEWER => 0,
        self::ROLE_EDITOR => 1,
        self::ROLE_ADMIN => 2,
    ];

    public static function getRoleLevel(string $role): int {
        return self::ROLE_HIERARCHY[$role] ?? -1;
    }

    /**
     * Checks if the current user can assign the target role.
     * Rule:
     * - Admin: Can assign any role.
     * - Editor: Can assign 'viewer' or 'editor'. Cannot assign 'admin'.
     * - Viewer: Cannot assign roles (shouldn't reach here).
     * 
     * @param string $targetRole The role to assign.
     * @return bool True if the current user can assign the target role, false otherwise.
     */
    public static function canAssignRole(string $targetRole): bool {
        $myRole = Session::get('user_role');
        if (!$myRole) return false;

        // Admin can assign anything
        if ($myRole === self::ROLE_ADMIN) {
            return true;
        }

        // Editor can assign viewer or editor, but NOT admin
        if ($myRole === self::ROLE_EDITOR) {
            return in_array($targetRole, [self::ROLE_VIEWER, self::ROLE_EDITOR]);
        }

        return false;
    }

    /**
     * Checks if the current user can edit the target user.
     * Rule:
     * - Admins can edit everyone.
     * - Editors can edit Self and Viewers. Cannot edit other Editors or Admins.
     * - Viewers can edit Self only.
     * 
     * @param array $targetUser The user to edit.
     * @return bool True if the current user can edit the target user, false otherwise.
     */
    public static function canEditUser(array $targetUser): bool {
        $myRole = Session::get('user_role');
        $myId = Session::get('user_id');
        
        if (!$myRole) return false;

        // Admins can edit everyone
        if ($myRole === self::ROLE_ADMIN) {
            return true;
        }

        // Everyone can edit themselves
        if ($targetUser['id'] == $myId) {
            return true;
        }

        // Editors can edit Viewers
        if ($myRole === self::ROLE_EDITOR) {
            return $targetUser['role'] === self::ROLE_VIEWER;
        }

        // Viewers cannot edit anyone else
        return false;
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
        requireLogin();
        if (!self::checkCurrent($action)) {
            throw new AuthException(
                "User does not have permission for action: $action",
                "No tienes permisos para realizar esta acción."
            );
        }
    }
}
