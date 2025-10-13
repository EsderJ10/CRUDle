<?php
// Business logic for user operations
require_once __DIR__ . '/../../config/paths.php';
require_once getPath('lib/core/csv.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');
require_once getPath('config/config.php');

function getAllUsers() {
    $records = getCSVRecords();
    $users = [];
    
    foreach ($records as $record) {
        if (count($record) >= 5) {
            $users[] = [
                'id' => $record[0],
                'nombre' => $record[1],
                'email' => $record[2],
                'rol' => $record[3],
                'fecha_alta' => $record[4],
                'avatar' => $record[5] ?? null
            ];
        }
    }
    
    return $users;
}

function getUserById($userId) {
    $record = findRecordById($userId);
    
    if ($record && count($record) >= 5) {
        return [
            'id' => $record[0],
            'nombre' => $record[1],
            'email' => $record[2],
            'rol' => $record[3],
            'fecha_alta' => $record[4],
            'avatar' => $record[5] ?? null
        ];
    }
    
    return null;
}

function createUser($formData) {
    $userId = getNextId();
    $data = [
        $userId,
        $formData['nombre'],
        $formData['email'],
        $formData['rol'],
        date(DATE_FORMAT),
        $formData['avatar'] ?? null
    ];
    
    $success = appendToCSV($data);
    return $success ? $userId : false;
}

function updateUser($userId, $formData) {
    // Get current user data to preserve fecha_alta if not provided
    $currentUser = getUserById($userId);
    $fechaAlta = $formData['fecha_alta'] ?? '';
    
    // If fecha_alta is empty but user exists, preserve original or set current date
    if (empty($fechaAlta) && $currentUser) {
        $fechaAlta = !empty($currentUser['fecha_alta']) ? $currentUser['fecha_alta'] : date(DATE_FORMAT);
    } else if (empty($fechaAlta)) {
        // If no current user found and no fecha_alta provided, set current date
        $fechaAlta = date(DATE_FORMAT);
    }
    
    $newRecord = [
        $userId,
        $formData['nombre'],
        $formData['email'],
        $formData['rol'],
        $fechaAlta,
        $formData['avatar'] ?? null
    ];
    
    return updateRecordById($userId, $newRecord);
}

function deleteUserById($userId) {
    return deleteRecordById($userId);
}

function getUserStatistics() {
    $userCount = 0;
    $usersByRole = ['admin' => 0, 'editor' => 0, 'viewer' => 0];
    $recentUsers = [];
    
    $records = getCSVRecords();
    
    foreach ($records as $record) {
        if (count($record) >= 5) {
            $userCount++;
            $role = $record[3];
            if (isset($usersByRole[$role])) {
                $usersByRole[$role]++;
            }
            
            $recentUsers[] = [
                'id' => $record[0],
                'nombre' => $record[1],
                'email' => $record[2],
                'rol' => $record[3],
                'fecha_alta' => $record[4],
                'avatar' => $record[5] ?? null
            ];
        }
    }
    
    // Sort by most recent (assuming higher ID = more recent)
    usort($recentUsers, function($a, $b) {
        return (int)$b['id'] - (int)$a['id'];
    });
    $recentUsers = array_slice($recentUsers, 0, 5); // Keep only last 5
    
    return [
        'userCount' => $userCount,
        'usersByRole' => $usersByRole,
        'recentUsers' => $recentUsers
    ];
}

function handleAvatarUpload($file, $userId = null, $userName = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Create upload directory if it doesn't exist using path config
    $uploadDir = getAvatarPath();
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safeUserName = $userName ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $userName) : 'unknown';
    $filename = 'user_' . ($userId ?: 'temp') . '_' . $safeUserName . '_avatar.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    // Remove existing avatar for this user if updating
    if ($userId) {
        removeExistingUserAvatar($userId);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return getWebUploadPath('avatars/' . $filename); 
    }
    
    return null;
}

function deleteAvatarFile($avatarPath) {
    if ($avatarPath) {
        $filePath = str_replace(getWebPath(''), BASE_PATH, $avatarPath);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
    }
    return false;
}

function removeExistingUserAvatar($userId) {
    // Find and remove any existing avatar for this user
    $avatarDir = getAvatarPath();
    if (is_dir($avatarDir)) {
        $pattern = $avatarDir . 'user_' . $userId . '_*_avatar.*';
        $files = glob($pattern);
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}

function getDefaultAvatar() {
    return getWebPath('assets/images/default-avatar.svg');
}

function checkSystemStatus() {
    return checkCSVStatus();
}
?>
