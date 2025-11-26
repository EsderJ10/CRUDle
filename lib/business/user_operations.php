<?php
/*
 * Funciones para la lógica de negocio relacionada con los usuarios.
 * Maneja operaciones CRUD, validación y procesamiento de datos.
 * Utiliza la clase Database para la persistencia.
 * También maneja la carga y eliminación de avatares de usuario.
 * Autor: José Antonio Cortés Ferre
 */

require_once __DIR__ . '/../../config/init.php';
require_once getPath('lib/core/Database.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');
require_once getPath('lib/core/Mailer.php');

function getAllUsers() {
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll();
        
        foreach ($users as &$user) {
            $user['avatar'] = normalizeAvatarPath($user['avatar_path']);
            $user['nombre'] = $user['name'];
            $user['rol'] = $user['role'];
            $user['fecha_alta'] = $user['created_at'];
        }
        
        return $users;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error fetching all users: ' . $e->getMessage(),
            'Error al obtener la lista de usuarios.',
            0,
            $e
        );
    }
}

function getUserById($userId) {
    try {
        if (empty($userId)) {
            throw new InvalidStateException('Empty user ID provided', 'El ID de usuario no es válido.');
        }
        
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            $user['avatar'] = normalizeAvatarPath($user['avatar_path']);
            $user['nombre'] = $user['name'];
            $user['rol'] = $user['role'];
            $user['fecha_alta'] = $user['created_at'];
            return $user;
        }
        
        return null;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error fetching user by ID: ' . $e->getMessage(),
            'Error al obtener los datos del usuario.',
            0,
            $e
        );
    }
}

function createUser($formData) {
    try {
        if (empty($formData)) {
            throw new InvalidStateException('Empty form data provided', 'Los datos del formulario no son válidos.');
        }
        
        $db = Database::getInstance();
        $sql = "INSERT INTO users (name, email, role, created_at, avatar_path, password) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $formData['nombre'],
            $formData['email'],
            $formData['rol'],
            date(DATE_FORMAT),
            $formData['avatar'] ?? null,
            password_hash($formData['password'], PASSWORD_DEFAULT)
        ];
        
        $db->query($sql, $params);
        return $db->getConnection()->lastInsertId();
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error creating user: ' . $e->getMessage(),
            'Error al crear el usuario.',
            0,
            $e
        );
    }
}

function updateUser($userId, $formData) {
    try {
        if (empty($userId) || empty($formData)) {
            throw new InvalidStateException('Invalid data provided', 'Datos inválidos.');
        }
        
        $db = Database::getInstance();
        
        $stmt = $db->query("SELECT id FROM users WHERE id = ?", [$userId]);
        if (!$stmt->fetch()) {
            throw new ResourceNotFoundException('User not found: ' . $userId, 'El usuario no existe.');
        }
        
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, avatar_path = ?";
        $params = [
            $formData['nombre'],
            $formData['email'],
            $formData['rol'],
            $formData['avatar'] ?? null
        ];

        if (!empty($formData['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($formData['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;
        
        $db->query($sql, $params);
        return true;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error updating user: ' . $e->getMessage(),
            'Error al actualizar el usuario.',
            0,
            $e
        );
    }
}

function deleteUserById($userId) {
    try {
        if (empty($userId)) {
            throw new InvalidStateException('Empty user ID provided', 'El ID de usuario no es válido.');
        }
        
        $db = Database::getInstance();
        $db->query("DELETE FROM users WHERE id = ?", [$userId]);
        
        return true;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error deleting user: ' . $e->getMessage(),
            'Error al eliminar el usuario.',
            0,
            $e
        );
    }
}

function getUserStatistics() {
    try {
        $db = Database::getInstance();
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch()['count'];
        
        $stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $roles = $stmt->fetchAll();
        $usersByRole = ['admin' => 0, 'editor' => 0, 'viewer' => 0];
        foreach ($roles as $row) {
            $usersByRole[$row['role']] = $row['count'];
        }
        
        $stmt = $db->query("SELECT * FROM users ORDER BY id DESC LIMIT 5");
        $recentUsers = $stmt->fetchAll();
        foreach ($recentUsers as &$user) {
            $user['avatar'] = normalizeAvatarPath($user['avatar_path']);
            $user['nombre'] = $user['name'];
            $user['rol'] = $user['role'];
            $user['fecha_alta'] = $user['created_at'];
        }
        
        return [
            'userCount' => $userCount,
            'usersByRole' => $usersByRole,
            'recentUsers' => $recentUsers
        ];
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error calculating statistics: ' . $e->getMessage(),
            'Error al calcular las estadísticas.',
            0,
            $e
        );
    }
}

function handleAvatarUpload($file, $userId = null, $userName = null) {
    try {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new FileUploadException(
                'File upload error: ' . $file['error'],
                'Error al procesar el archivo.'
            );
        }
        
        $uploadDir = getAvatarPath();
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true)) {
                throw new AvatarException(
                    'Unable to create avatar directory: ' . $uploadDir,
                    'Error al crear el directorio de avatares.'
                );
            }
        }
        
        if (!is_writable($uploadDir)) {
            throw new AvatarException(
                'Avatar directory is not writable: ' . $uploadDir,
                'No hay permisos para guardar la imagen.'
            );
        }
        
        // Se genera un nombre para el archivo con el nombre del usuario
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeUserName = $userName ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $userName) : 'unknown';
        $filename = 'user_' . ($userId ?: 'temp') . '_' . $safeUserName . '_avatar.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if ($userId) {
            try {
                removeExistingUserAvatar($userId);
            } catch (Exception $e) {
                // Se hace un log del error pero no se detiene el proceso de subida
                error_log('Avatar cleanup warning: ' . $e->getMessage());
            }
        }
        
        // Se mueve el archivo subido a la ubicación deseada
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new AvatarException(
                'Failed to move uploaded file to: ' . $targetPath,
                'Error al guardar la imagen de perfil.'
            );
        }
        
        return getWebUploadPath('avatars/' . $filename);
    } catch (AvatarException $e) {
        throw $e;
    } catch (FileUploadException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new AvatarException(
            'Avatar upload error: ' . $e->getMessage(),
            'Error al procesar la imagen de perfil.',
            0,
            $e
        );
    }
}

function deleteAvatarFile($avatarPath) {
    try {
        if (empty($avatarPath)) {
            return true;
        }
        
        $filePath = str_replace(getWebPath(''), BASE_PATH, $avatarPath);
        
        if (!file_exists($filePath)) {
            // Si el fichero no existe, no hay nada que eliminar
            return true;
        }
        
        if (!is_file($filePath)) {
            throw new AvatarException(
                'Avatar path is not a file: ' . $filePath,
                'La ruta del avatar no es válida.'
            );
        }
        
        if (!is_writable($filePath)) {
            throw new AvatarException(
                'Avatar file is not writable: ' . $filePath,
                'No hay permisos para eliminar la imagen.'
            );
        }
        
        if (!unlink($filePath)) {
            throw new AvatarException(
                'Failed to delete avatar file: ' . $filePath,
                'Error al eliminar la imagen de perfil.'
            );
        }
        
        return true;
    } catch (AvatarException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new AvatarException(
            'Avatar deletion error: ' . $e->getMessage(),
            'Error al eliminar la imagen de perfil.',
            0,
            $e
        );
    }
}

function removeExistingUserAvatar($userId) {
    try {
        if (empty($userId)) {
            return true;
        }
        
        $avatarDir = getAvatarPath();
        if (!is_dir($avatarDir)) {
            return true;
        }
        
        $pattern = $avatarDir . 'user_' . $userId . '_*_avatar.*';
        $files = glob($pattern);
        
        if (empty($files)) {
            return true;
        }
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                if (!unlink($file)) {
                    throw new AvatarException(
                        'Failed to delete avatar file: ' . $file,
                        'Error al eliminar una imagen antigua.'
                    );
                }
            }
        }
        
        return true;
    } catch (AvatarException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new AvatarException(
            'Error removing existing avatar: ' . $e->getMessage(),
            'Error al limpiar avatares anteriores.',
            0,
            $e
        );
    }
}

function getDefaultAvatar() {
    return getWebPath('assets/images/default-avatar.svg');
}

function checkSystemStatus() {
    try {
        Database::getInstance();
        return ['status' => 'OK', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        return ['status' => 'ERROR', 'message' => 'Database connection failed'];
    }
}
function inviteUser($name, $email, $role) {
    try {
        if (empty($name) || empty($email) || empty($role)) {
            throw new InvalidStateException('Missing required fields', 'Faltan datos requeridos.');
        }

        $db = Database::getInstance();
        
        // Check if email already exists
        $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$email]);
        if ($stmt->fetch()) {
            throw new UserOperationException('Email already exists', 'El correo electrónico ya está registrado.');
        }

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $sql = "INSERT INTO users (name, email, role, status, invitation_token, invitation_expires_at, created_at, password) VALUES (?, ?, ?, 'pending', ?, ?, ?, NULL)";
        $params = [
            $name,
            $email,
            $role,
            $token,
            $expiresAt,
            date(DATE_FORMAT)
        ];

        $db->query($sql, $params);
        $userId = $db->getConnection()->lastInsertId();

        // Send invitation email
        sendInvitationEmail($email, $name, $token);

        return $userId;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error inviting user: ' . $e->getMessage(),
            'Error al invitar al usuario.',
            0,
            $e
        );
    }
}

function resendInvitation($userId) {
    try {
        $db = Database::getInstance();
        
        // Get user details
        $stmt = $db->query("SELECT name, email, status FROM users WHERE id = ?", [$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new ResourceNotFoundException('User not found', 'Usuario no encontrado.');
        }

        if ($user['status'] !== 'pending') {
            throw new InvalidStateException('User is not pending', 'El usuario no está en estado pendiente.');
        }

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $sql = "UPDATE users SET invitation_token = ?, invitation_expires_at = ? WHERE id = ?";
        $db->query($sql, [$token, $expiresAt, $userId]);

        // Send new email
        sendInvitationEmail($user['email'], $user['name'], $token);

        return true;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error resending invitation: ' . $e->getMessage(),
            'Error al reenviar la invitación.',
            0,
            $e
        );
    }
}

function getInvitation($token) {
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users WHERE invitation_token = ? AND status = 'pending'", [$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        // Check expiration
        if (strtotime($user['invitation_expires_at']) < time()) {
            return null; // Token expired
        }

        return $user;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error fetching invitation: ' . $e->getMessage(),
            'Error al verificar la invitación.',
            0,
            $e
        );
    }
}

function activateUser($token, $password) {
    try {
        $user = getInvitation($token);
        if (!$user) {
            throw new InvalidStateException('Invalid or expired token', 'El enlace de invitación no es válido o ha expirado.');
        }

        $db = Database::getInstance();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update user: set password, activate, clear token
        $sql = "UPDATE users SET password = ?, status = 'active', invitation_token = NULL, invitation_expires_at = NULL WHERE id = ?";
        $db->query($sql, [$hashedPassword, $user['id']]);

        return true;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error activating user: ' . $e->getMessage(),
            'Error al activar la cuenta.',
            0,
            $e
        );
    }
}

function sendInvitationEmail($email, $name, $token) {
    $invitePath = getWebPath("pages/auth/accept_invite.php?token=" . $token);
    $inviteLink = APP_URL . $invitePath;
    
    $subject = "Invitación a CRUDle";
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
            <h2 style='color: #2563eb;'>Bienvenido a CRUDle</h2>
            <p>Hola <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Has sido invitado a unirte a la plataforma CRUDle. Para activar tu cuenta y establecer tu contraseña, por favor haz clic en el siguiente enlace:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . $inviteLink . "' style='background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Aceptar Invitación</a>
            </p>
            <p>Este enlace expirará en 48 horas.</p>
            <p style='font-size: 12px; color: #666;'>Si no esperabas esta invitación, puedes ignorar este correo.</p>
        </div>
    </body>
    </html>
    ";

    $mailer = new Mailer();
    if ($mailer->send($email, $subject, $body)) {
        return true;
    } else {
        // Fallback logging if email fails
        error_log("FAILED TO SEND EMAIL to $email. Link: $inviteLink");
        return false;
    }
}
?>
