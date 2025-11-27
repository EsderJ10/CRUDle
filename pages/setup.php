<?php
require_once __DIR__ . '/../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');

// LOCKING: If users exist, setup is disabled.
if (getUserCount() > 0) {
    header('Location: auth/login.php');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Handle Form Submission (Step 2 -> 3)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_admin') {
    // RACE CONDITION CHECK
    if (getUserCount() > 0) {
        header('Location: auth/login.php');
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        if (empty($name) || empty($email) || empty($password)) {
            throw new ValidationException('Todos los campos son obligatorios.');
        }

        if ($password !== $confirm_password) {
            throw new ValidationException('Las contraseñas no coinciden.');
        }

        if (strlen($password) < 8) {
            throw new ValidationException('La contraseña debe tener al menos 8 caracteres.');
        }

        // Create Admin User
        $formData = [
            'name' => $name,
            'email' => $email,
            'role' => 'admin',
            'password' => $password,
            'avatar' => null
        ];

        $userId = createUser($formData);
        
        // Auto-login
        login($email, $password);

        // Redirect to Step 3
        header('Location: setup.php?step=3');
        exit;

    } catch (Exception $e) {
        $error = $e instanceof AppException ? $e->getUserMessage() : $e->getMessage();
        $step = 2; // Stay on step 2
    }
}

// System Checks for Step 1
$checks = [];
if ($step === 1) {
    // DB Connection
    $dbStatus = checkSystemStatus();
    $checks[] = [
        'name' => 'Conexión a Base de Datos',
        'status' => $dbStatus['status'] === 'OK',
        'message' => $dbStatus['message']
    ];

    // Write Permissions
    $uploadDir = getAvatarPath();
    $isWritable = is_writable(dirname($uploadDir)) || (file_exists($uploadDir) && is_writable($uploadDir));
    // Try to create if not exists
    if (!file_exists($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
        $isWritable = is_writable($uploadDir);
    }
    
    $checks[] = [
        'name' => 'Permisos de Escritura (Uploads)',
        'status' => $isWritable,
        'message' => $isWritable ? 'Directorio escribible' : 'No se puede escribir en ' . $uploadDir
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
    <style>
        body { background-color: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .setup-card { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 500px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 2rem; position: relative; }
        .step-indicator::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 2px; background: #e5e7eb; z-index: 0; transform: translateY(-50%); }
        .step { width: 30px; height: 30px; border-radius: 50%; background: #e5e7eb; color: #6b7280; display: flex; align-items: center; justify-content: center; font-weight: 600; position: relative; z-index: 1; }
        .step.active { background: #2563eb; color: white; }
        .step.completed { background: #10b981; color: white; }
        .check-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border-bottom: 1px solid #f3f4f6; }
        .check-item:last-child { border-bottom: none; }
        .status-icon { font-size: 1.25rem; }
        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Instalación de CRUDle</h1>
            <p class="text-gray-500">Asistente de configuración inicial</p>
        </div>

        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>"><?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?></div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>"><?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?></div>
            <div class="step <?php echo $step >= 3 ? 'completed' : 'active'; ?>"><?php echo $step >= 3 ? '<i class="fas fa-check"></i>' : '3'; ?></div>
        </div>

        <?php if ($step === 1): ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Verificación del Sistema</h2>
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
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex justify-end">
                <?php if ($allChecksPassed): ?>
                    <a href="setup.php?step=2" class="btn btn-primary w-full text-center">Continuar <i class="fas fa-arrow-right ml-2"></i></a>
                <?php else: ?>
                    <button disabled class="btn btn-secondary w-full cursor-not-allowed opacity-50">Corregir errores para continuar</button>
                    <a href="setup.php" class="btn btn-outline mt-2 w-full text-center">Reintentar</a>
                <?php endif; ?>
            </div>

        <?php elseif ($step === 2): ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Crear Administrador</h2>
                <?php if ($error): ?>
                    <div class="message message-error mb-4"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="setup.php">
                    <input type="hidden" name="action" value="create_admin">
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                        <input type="text" name="name" class="form-input w-full" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                        <input type="email" name="email" class="form-input w-full" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input type="password" name="password" class="form-input w-full" required minlength="8">
                    </div>
                    <div class="form-group mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" class="form-input w-full" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Crear Administrador</button>
                </form>
            </div>

        <?php elseif ($step === 3): ?>
            <div class="text-center mb-6">
                <div class="text-success text-5xl mb-4"><i class="fas fa-check-circle"></i></div>
                <h2 class="text-2xl font-bold mb-2">¡Instalación Completada!</h2>
                <p class="text-gray-600 mb-6">El usuario administrador ha sido creado exitosamente. Ya has iniciado sesión automáticamente.</p>
                <a href="../index.php" class="btn btn-primary w-full">Ir al Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
