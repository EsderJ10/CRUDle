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
                'fecha_alta' => $record[4]
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
            'fecha_alta' => $record[4]
        ];
    }
    
    return null;
}

function createUser($formData) {
    $data = [
        getNextId(),
        $formData['nombre'],
        $formData['email'],
        $formData['rol'],
        date(DATE_FORMAT)
    ];
    
    return appendToCSV($data);
}

function updateUser($userId, $formData) {
    $newRecord = [
        $userId,
        $formData['nombre'],
        $formData['email'],
        $formData['rol'],
        $formData['fecha_alta']
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
            
            // Store for recent users
            $recentUsers[] = [
                'id' => $record[0],
                'nombre' => $record[1],
                'email' => $record[2],
                'rol' => $record[3],
                'fecha_alta' => $record[4]
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

function checkSystemStatus() {
    return checkCSVStatus();
}
?>
