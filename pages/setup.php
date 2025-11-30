<?php
require_once __DIR__ . '/../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');

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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación de CRUDle</title>
    <link rel="stylesheet" href="<?php echo getWebPath('assets/css/styles.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="setup-card">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">CRUDle Setup</h1>
            <p class="text-gray-500">Initial configuration assistant</p>
        </div>

        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>"><?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?></div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>"><?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?></div>
            <div class="step <?php echo $step >= 3 ? 'completed' : 'active'; ?>"><?php echo $step >= 3 ? '<i class="fas fa-check"></i>' : '3'; ?></div>
        </div>

        <?php if ($step === 1): ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">System Verification</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <?php foreach ($checks as $check): ?>
                        <div class="check-item">
                            <span><?php echo htmlspecialchars($check['name']); ?></span>
                            <span class="status-icon <?php echo $check['status'] ? 'text-success' : 'text-danger'; ?>">
                                <i class="fas fa-<?php echo $check['status'] ? 'check-circle' : 'times-circle'; ?>"></i>
                            </span>
                        </div>
                        <?php if (!$check['status']): ?>
                            <p class="text-sm text-danger mt-1"><?php echo htmlspecialchars($check['message']); ?></p>
                            <?php if (isset($check['action']) && $check['action'] === 'init_db'): ?>
                                <form method="POST" action="setup.php" class="mt-2">
                                    <input type="hidden" name="action" value="init_db">
                                    <button type="submit" class="btn btn-sm btn-outline">Initialize Database</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex justify-end">
                <?php if ($allChecksPassed): ?>
                    <a href="setup.php?step=2" class="btn btn-primary w-full text-center">Continue <i class="fas fa-arrow-right ml-2"></i></a>
                <?php else: ?>
                    <button disabled class="btn btn-secondary w-full cursor-not-allowed opacity-50">Fix errors to continue</button>
                    <a href="setup.php" class="btn btn-outline mt-2 w-full text-center">Retry</a>
                <?php endif; ?>
            </div>

        <?php elseif ($step === 2): ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Create Admin</h2>
                <?php if ($error): ?>
                    <div class="message message-error mb-4"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="setup.php">
                    <input type="hidden" name="action" value="create_admin">
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" class="form-input w-full" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="form-input w-full" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" class="form-input w-full" required minlength="8">
                    </div>
                    <div class="form-group mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-input w-full" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Create Admin</button>
                </form>
            </div>

        <?php elseif ($step === 3): ?>
            <div class="text-center mb-6">
                <div class="text-success text-5xl mb-4"><i class="fas fa-check-circle"></i></div>
                <h2 class="text-2xl font-bold mb-2">Setup Completed!</h2>
                <p class="text-gray-600 mb-6">The admin user has been created successfully. You have been logged in automatically.</p>
                <a href="../index.php" class="btn btn-primary w-full">Go to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
