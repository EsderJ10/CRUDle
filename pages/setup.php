<?php
require_once __DIR__ . '/../config/init.php';

if (getUserCount() > 0) {
    header('Location: auth/login.php');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_admin') {
    if (getUserCount() > 0) {
        header('Location: auth/login.php');
        exit;
    }

    require_once getPath('lib/core/sanitization.php');
    
    $name = sanitizeName($_POST['name'] ?? '');
    $email = sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        if (empty($name) || empty($email) || empty($password)) {
            throw new ValidationException('All fields are required.');
        }

        if ($password !== $confirm_password) {
            throw new ValidationException('Passwords do not match.');
        }

        if (strlen($password) < 8) {
            throw new ValidationException('Password must be at least 8 characters long.');
        }

        $db = Database::getInstance();
        
        // Check count to avoid race conditions
        if (getUserCount() > 0) {
            header('Location: auth/login.php');
            exit;
        }

        // Reset IDs if this is the first user
        $db->query("TRUNCATE TABLE users");

        $formData = [
            'name' => $name,
            'email' => $email,
            'role' => 'admin',
            'password' => $password,
            'avatar' => null,
            'status' => 'active'
        ];

        $userId = createUser($formData);
        
        // Auto-login
        try {
            login($email, $password);
        } catch (Exception $e) {
            header('Location: auth/login.php?setup_success=1');
            exit;
        }

        header('Location: setup.php?step=3');
        exit;

    } catch (Exception $e) {
        $error = $e instanceof AppException ? $e->getUserMessage() : $e->getMessage();
        $step = 2;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'init_db') {
    try {
        initializeDatabase();
        $success = 'Database initialized successfully.';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// System Checks
$checks = [];
if ($step === 1) {
    // DB Connection
    $dbStatus = checkSystemStatus();
    $checks[] = [
        'name' => 'Database Connection',
        'status' => $dbStatus['status'] === 'OK',
        'message' => $dbStatus['message']
    ];

    // Schema Check
    if ($dbStatus['status'] === 'OK') {
        $schemaStatus = checkDatabaseSchema();
        $checks[] = [
            'name' => 'Database Schema',
            'status' => $schemaStatus['status'] === 'OK',
            'message' => $schemaStatus['message'],
            'action' => $schemaStatus['status'] !== 'OK' ? 'init_db' : null
        ];
    }

    // Write Permissions
    $uploadDir = getAvatarPath();
    
    // Try to create if not exists
    if (!file_exists($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    
    $testFile = $uploadDir . 'perm_test.tmp';
    $isWritable = @file_put_contents($testFile, 'test') !== false;
    if ($isWritable) {
        @unlink($testFile);
    }
    
    $debugInfo = '';
    if (!$isWritable) {
        $currentUser = get_current_user();
        $processUser = posix_getpwuid(posix_geteuid())['name'];
        $debugInfo = " (Path: $uploadDir, User: $processUser)";
    }
    
    $checks[] = [
        'name' => 'Write Permissions (Uploads)',
        'status' => $isWritable,
        'message' => $isWritable ? 'Directory is writable' : 'Cannot write to the upload directory' . $debugInfo
    ];

    $allChecksPassed = !in_array(false, array_column($checks, 'status'));
}

// Include the view
include getPath('views/setup/setup_wizard.php');
?>
