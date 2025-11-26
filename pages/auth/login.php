<?php
require_once '../../config/init.php';
require_once getPath('lib/business/auth_operations.php');

$pageTitle = "Iniciar Sesión - CRUDle";
$pageHeader = "Iniciar Sesión";

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    header('Location: ' . getWebPath('index.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        if (login($email, $password)) {
            Session::setFlash('success', 'Bienvenido de nuevo, ' . $_SESSION['user_name']);
            header('Location: ' . getWebPath('index.php'));
            exit;
        }
    } catch (AuthException $e) {
        $error = $e->getUserMessage();
    } catch (Exception $e) {
        $error = 'Ocurrió un error inesperado.';
        error_log($e->getMessage());
    }
}

include getPath('views/partials/header.php');
?>

<div class="card page-transition" style="max-width: 400px; margin: 2rem auto;">
    <h2 class="text-center mb-6">Acceder</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="form">
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" class="form-control" required 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-actions mt-6">
            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
        </div>
    </form>
</div>

<?php include getPath('views/partials/footer.php'); ?>
