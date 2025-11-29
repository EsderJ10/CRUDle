<?php
declare(strict_types=1);
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
require_once getPath('lib/core/Logger.php');

function getAllUsers() {
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll();
        
        foreach ($users as &$user) {
            $user['avatar'] = normalizeAvatarPath($user['avatar_path']);
            $user['fecha_alta'] = $user['created_at'];
        }
        
        return $users;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error fetching all users: ' . $e->getMessage(),
            'Error fetching user list.',
            0,
            $e
        );
    }
}

function getUserCount() {
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        return (int)$stmt->fetch()['count'];
    } catch (Exception $e) {
        // If table doesn't exist or DB error, return 0 to trigger setup (or handle error)
        // For setup purposes, 0 is safe as it redirects to setup which will do checks
        return 0;
    }
}

function getUserById($userId) {
    try {
        if (empty($userId)) {
            throw new InvalidStateException('Empty user ID provided', 'Invalid user ID.');
        }
        
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            $user['avatar'] = normalizeAvatarPath($user['avatar_path']);
            $user['fecha_alta'] = $user['created_at'];
            return $user;
        }
        
        return null;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error fetching user by ID: ' . $e->getMessage(),
            'Error fetching user data.',
            0,
            $e
        );
    }
}

function createUser($formData) {
    try {
        if (empty($formData)) {
            throw new InvalidStateException('Empty form data provided', 'Invalid form data.');
        }
        
        $db = Database::getInstance();
        
        // Default values
        $password = null;
        if (!empty($formData['password'])) {
            $password = password_hash($formData['password'], PASSWORD_DEFAULT, ['cost' => 10]);
        }

        // If password is provided, default to active. Otherwise pending.
        $defaultStatus = $password ? 'active' : 'pending';
        $status = $formData['status'] ?? $defaultStatus;
        
        $token = $formData['invitation_token'] ?? null;
        $expiresAt = $formData['invitation_expires_at'] ?? null;
        
        $sql = "INSERT INTO users (name, email, role, status, invitation_token, invitation_expires_at, created_at, avatar_path, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $formData['name'],
            $formData['email'],
            $formData['role'],
            $status,
            $token,
            $expiresAt,
            date(DATE_FORMAT),
            $formData['avatar'] ?? null,
            $password
        ];
        
        $db->query($sql, $params);
        return $db->getConnection()->lastInsertId();
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error creating user: ' . $e->getMessage(),
            'Error creating user.',
            0,
            $e
        );
    }
}

function updateUser($userId, $formData) {
    try {
        if (empty($userId) || empty($formData)) {
            throw new InvalidStateException('Invalid data provided', 'Invalid data.');
        }
        
        $db = Database::getInstance();
        
        $stmt = $db->query("SELECT id FROM users WHERE id = ?", [$userId]);
        if (!$stmt->fetch()) {
            throw new ResourceNotFoundException('User not found: ' . $userId, 'User not found.');
        }
        
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, avatar_path = ?";
        $params = [
            $formData['name'],
            $formData['email'],
            $formData['role'],
            $formData['avatar'] ?? null
        ];

        if (!empty($formData['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($formData['password'], PASSWORD_DEFAULT, ['cost' => 10]);
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;
        
        $db->query($sql, $params);
        return true;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error updating user: ' . $e->getMessage(),
            'Error updating user.',
            0,
            $e
        );
    }
}

function deleteUserById($userId) {
    if (empty($userId)) {
        throw new InvalidStateException('Empty user ID provided', 'Invalid user ID.');
    }

    // Check if trying to delete self
    $currentUserId = Session::get('user_id');
    if ($userId == $currentUserId) {
        throw new UserOperationException(
            'Attempt to delete own account',
            'You cannot delete your own account.'
        );
    }

    // Check if it's the last user
    if (getUserCount() <= 1) {
        throw new UserOperationException(
            'Attempt to delete the last user',
            'Cannot delete the last user in the system.'
        );
    }

    try {
        $db = Database::getInstance();
        
        $stmt = $db->query("DELETE FROM users WHERE id = ?", [$userId]);
        
        // Check if the user actually existed
        if ($stmt->rowCount() === 0) {
            throw new UserOperationException(
                'User not found: ' . $userId,
                'User not found.'
            );
        }
        
        return true;

    } catch (Exception $e) {
        throw new UserOperationException(
            'DB Error deleting user: ' . $e->getMessage(),
            'System error while deleting user.',
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
            'Error calculating statistics.',
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
                'Error processing file.'
            );
        }
        
        $uploadDir = getAvatarPath();
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true)) {
                throw new AvatarException(
                    'Unable to create avatar directory: ' . $uploadDir,
                    'Error creating avatar directory.'
                );
            }
        }
        
        if (!is_writable($uploadDir)) {
            throw new AvatarException(
                'Avatar directory is not writable: ' . $uploadDir,
                'No permission to save image.'
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
                Logger::warning('Avatar cleanup warning', ['error' => $e->getMessage()]);
            }
        }
        
        // Se mueve el archivo subido a la ubicación deseada
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new AvatarException(
                'Failed to move uploaded file to: ' . $targetPath,
                'Error saving profile image.'
            );
        }
        
        return $filename;
    } catch (AvatarException $e) {
        throw $e;
    } catch (FileUploadException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new AvatarException(
            'Avatar upload error: ' . $e->getMessage(),
            'Error processing profile image.',
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
        
        // If it's just a filename, construct the full path
        if (strpos($avatarPath, '/') === false) {
            $filePath = getAvatarPath() . $avatarPath;
        } else {
            // Legacy: try to resolve from web path
            $filePath = str_replace(getWebPath(''), BASE_PATH, $avatarPath);
        }
        
        if (!file_exists($filePath)) {
            // Si el fichero no existe, no hay nada que eliminar
            return true;
        }
        
        if (!is_file($filePath)) {
            throw new AvatarException(
                'Avatar path is not a file: ' . $filePath,
                'Invalid avatar path.'
            );
        }
        
        if (!is_writable($filePath)) {
            throw new AvatarException(
                'Avatar file is not writable: ' . $filePath,
                'No permission to delete image.'
            );
        }
        
        if (!unlink($filePath)) {
            throw new AvatarException(
                'Failed to delete avatar file: ' . $filePath,
                'Error deleting profile image.'
            );
        }
        
        return true;
    } catch (AvatarException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new AvatarException(
            'Avatar deletion error: ' . $e->getMessage(),
            'Error deleting profile image.',
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
                        'Error deleting old image.'
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
            'Error cleaning up previous avatars.',
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
        return ['status' => 'ERROR', 'message' => 'Failed to connect to database'];
    }
}

function checkDatabaseSchema() {
    try {
        $db = Database::getInstance();
        // Check if table exists
        $db->query("SELECT 1 FROM users LIMIT 1");
        
        // Check for status column
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'status'");
        if (!$stmt->fetch()) {
            return ['status' => 'WARNING', 'message' => 'Table exists but missing columns (status)'];
        }

        // Check for invitation_token column
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'invitation_token'");
        if (!$stmt->fetch()) {
            return ['status' => 'WARNING', 'message' => 'Table exists but missing columns (invitation_token)'];
        }

        // Check for invitation_expires_at column
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'invitation_expires_at'");
        if (!$stmt->fetch()) {
            return ['status' => 'WARNING', 'message' => 'Table exists but missing columns (invitation_expires_at)'];
        }
        
        // Check for password column
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'password'");
        if (!$stmt->fetch()) {
            return ['status' => 'WARNING', 'message' => 'Table exists but missing columns (password)'];
        }

        // Check for avatar_path column
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'avatar_path'");
        if (!$stmt->fetch()) {
            return ['status' => 'WARNING', 'message' => 'Table exists but missing columns (avatar_path)'];
        }
        
        return ['status' => 'OK', 'message' => 'Database schema correct'];
    } catch (Exception $e) {
        return ['status' => 'ERROR', 'message' => 'Table "users" does not exist'];
    }
}

function initializeDatabase() {
    try {
        $db = Database::getInstance();
        
        // Create table if not exists
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            role ENUM('admin', 'editor', 'viewer') NOT NULL,
            status ENUM('active', 'pending', 'inactive') DEFAULT 'pending',
            invitation_token VARCHAR(64) NULL,
            invitation_expires_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            avatar_path VARCHAR(255) DEFAULT NULL,
            password VARCHAR(255) NULL
        )";
        $db->query($sql);
        
        // Check for missing columns and add them
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'status'");
        if (!$stmt->fetch()) {
            $db->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'pending', 'inactive') DEFAULT 'pending' AFTER role");
        }
        
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'invitation_token'");
        if (!$stmt->fetch()) {
            $db->query("ALTER TABLE users ADD COLUMN invitation_token VARCHAR(64) NULL AFTER status");
        }
        
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'invitation_expires_at'");
        if (!$stmt->fetch()) {
            $db->query("ALTER TABLE users ADD COLUMN invitation_expires_at DATETIME NULL AFTER invitation_token");
        }

        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'avatar_path'");
        if (!$stmt->fetch()) {
            $db->query("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) DEFAULT NULL AFTER created_at");
        }

        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'password'");
        if (!$stmt->fetch()) {
            $db->query("ALTER TABLE users ADD COLUMN password VARCHAR(255) NULL AFTER avatar_path");
        }

        return true;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error initializing database: ' . $e->getMessage(),
            'Error initializing database.',
            0,
            $e
        );
    }
}
function inviteUser($name, $email, $role, $avatarPath = null) {
    try {
        if (empty($name) || empty($email) || empty($role)) {
            throw new InvalidStateException('Missing required fields', 'Missing required fields.');
        }

        $db = Database::getInstance();
        
        // Check if email already exists
        $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$email]);
        if ($stmt->fetch()) {
            throw new UserOperationException('Email already exists', 'Email already registered.');
        }

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $formData = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'avatar' => $avatarPath,
            'status' => 'pending',
            'invitation_token' => $token,
            'invitation_expires_at' => $expiresAt
        ];

        $userId = createUser($formData);

        // Send invitation email
        sendInvitationEmail($email, $name, $token);

        return $userId;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error inviting user: ' . $e->getMessage(),
            'Error inviting user.',
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
            throw new ResourceNotFoundException('User not found', 'User not found.');
        }

        if ($user['status'] !== 'pending') {
            throw new InvalidStateException('User is not pending', 'User is not in pending status.');
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
            'Error resending invitation.',
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
            'Error verifying invitation.',
            0,
            $e
        );
    }
}

function activateUser($token, $password, $avatarPath = null) {
    try {
        $user = getInvitation($token);
        if (!$user) {
            throw new InvalidStateException('Invalid or expired token', 'Invitation link is invalid or has expired.');
        }

        $db = Database::getInstance();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

        // Update user: set password, activate, clear token, set avatar
        $sql = "UPDATE users SET password = ?, status = 'active', invitation_token = NULL, invitation_expires_at = NULL, avatar_path = ? WHERE id = ?";
        $db->query($sql, [$hashedPassword, $avatarPath, $user['id']]);

        return true;
    } catch (Exception $e) {
        throw new UserOperationException(
            'Error activating user: ' . $e->getMessage(),
            'Error activating account.',
            0,
            $e
        );
    }
}

function sendInvitationEmail($email, $name, $token) {
    $invitePath = getWebPath("pages/auth/accept_invite.php?token=" . $token);
    $inviteLink = APP_URL . $invitePath;
    
    $subject = "Invitation to CRUDle";
    $body = "
    <html>
    <body style='font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
            <h2 style='color: #2563eb;'>Welcome to CRUDle</h2>
            <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>You have been invited to join the CRUDle platform. To activate your account and set your password, please click the link below:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . $inviteLink . "' style='background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Accept Invitation</a>
            </p>
            <p>This link will expire in 48 hours.</p>
            <p style='font-size: 12px; color: #666;'>If you were not expecting this invitation, you can ignore this email.</p>
        </div>
    </body>
    </html>
    ";

    $mailer = new Mailer();
    if ($mailer->send($email, $subject, $body)) {
        return true;
    } else {
        // Fallback logging if email fails
        Logger::error('FAILED TO SEND EMAIL', ['email' => $email, 'link' => $inviteLink]);
        return false;
    }
}
?>
