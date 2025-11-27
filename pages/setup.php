<?php
/*
 * Página de configuración inicial.
 * Permite crear el primer usuario administrador si no existen usuarios.
 */

require_once '../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/core/validation.php');

// Verificar si ya existen usuarios
$users = getAllUsers();
if (!empty($users)) {
    // Si ya hay usuarios, redirigir al login
    header('Location: ' . getWebPath('pages/auth/login.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($password !== $confirmPassword) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            // Crear usuario administrador
            $formData = [
                'nombre' => $name,
                'email' => $email,
                'rol' => 'admin',
                'password' => $password
            ];
            
            createUser($formData);
            
            Session::setFlash('success', 'Administrador creado exitosamente. Por favor inicie sesión.');
            header('Location: ' . getWebPath('pages/auth/login.php'));
            exit;
        } catch (Exception $e) {
            $error = "Error al crear el usuario: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - CRUDle</title>
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
                <p class="auth-subtitle">Configuración Inicial del Sistema</p>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                No se han detectado usuarios en el sistema. Por favor, crea una cuenta de administrador para comenzar.
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="setup.php" class="auth-form">
                <div class="form-group">
                    <label for="name">Nombre Completo</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
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
                    Crear Administrador
                </button>
            </form>
        </div>
    </div>
    <script src="<?php echo getWebPath('assets/js/auth.js'); ?>"></script>
</body>
</html>
