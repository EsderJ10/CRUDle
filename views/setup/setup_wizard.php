<?php
/**
 * View for setup wizard.
 * 
 * Variables expected:
 * - $step
 * - $checks (for step 1)
 * - $allChecksPassed (for step 1)
 * - $error
 * - $success
 */

$step = $step ?? 1;
$checks = $checks ?? [];
$allChecksPassed = $allChecksPassed ?? false;
$error = $error ?? '';
$success = $success ?? '';
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
                <form method="POST" action="setup.php" class="auth-form">
                    <input type="hidden" name="action" value="create_admin">
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" class="form-input w-full" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="form-input w-full" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <?php include getPath('views/components/password_fields.php'); ?>
                    
                    <button type="submit" class="btn btn-primary w-full mt-6">Create Admin</button>
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
    
    <!-- Include auth.js for password toggling -->
    <script src="<?php echo getWebPath('assets/js/auth.js'); ?>"></script>
</body>
</html>
