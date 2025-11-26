<?php
/*
 * Página para aceptar una invitación y establecer la contraseña.
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/core/validation.php');

$pageTitle = "Aceptar Invitación";
$token = $_GET['token'] ?? '';
$error = null;
$user = null;

try {
    if (empty($token)) {
        throw new InvalidStateException('Token missing', 'El enlace de invitación no es válido.');
    }

    // Verificar token
    $user = getInvitation($token);
    
    if (!$user) {
        throw new InvalidStateException('Invalid token', 'El enlace de invitación no es válido o ha expirado.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password) || strlen($password) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } elseif ($password !== $confirmPassword) {
            $error = "Las contraseñas no coinciden.";
        } else {
            // Activar usuario
            activateUser($token, $password);
            
            // Iniciar sesión automáticamente o redirigir a login
            Session::setFlash('success', 'Cuenta activada exitosamente. Por favor, inicie sesión.');
            header('Location: login.php');
            exit;
        }
    }

} catch (Exception $e) {
    $error = $e instanceof AppException ? $e->getUserMessage() : 'Ocurrió un error inesperado.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CRUDle</title>
    <link rel="stylesheet" href="<?php echo getWebPath('assets/css/styles.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-layout">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-database"></i>
                </div>
                <h1>Bienvenido a CRUDle</h1>
                <p class="auth-subtitle">
                    <?php if ($user): ?>
                        Hola <strong><?php echo htmlspecialchars($user['name']); ?></strong>, configura tu contraseña para continuar.
                    <?php else: ?>
                        Aceptar Invitación
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php if (!$user): ?>
                    <div class="auth-footer">
                        <a href="login.php" class="auth-link">Volver al inicio de sesión</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($user): ?>
                <form method="post" action="accept_invite.php?token=<?php echo htmlspecialchars($token); ?>" class="auth-form">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="disabled-input">
                    </div>

                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" required placeholder="Mínimo 8 caracteres">
                            <button type="button" class="toggle-password" tabindex="-1">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repita la contraseña">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Activar Cuenta
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="<?php echo getWebPath('assets/js/auth.js'); ?>"></script>
</body>
</html>
