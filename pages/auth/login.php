<?php
require_once '../../config/init.php';
require_once getPath('lib/business/auth_operations.php');
require_once getPath('lib/presentation/user_views.php');

$pageTitle = "Login - CRUDle";
$pageHeader = "Login";

// If already logged in, redirect to dashboard
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
            Session::setFlash('success', 'Welcome back, ' . $_SESSION['user_name']);
            header('Location: ' . getWebPath('index.php'));
            exit;
        }
    } catch (AuthException $e) {
        $error = $e->getUserMessage();
    } catch (Exception $e) {
        $error = 'An unexpected error occurred.';
        error_log($e->getMessage());
    }
}

include getPath('views/partials/header.php');
?>

<div class="card page-transition" style="max-width: 400px; margin: 2rem auto;">
    <h2 class="text-center mb-6">Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="form">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-actions mt-6">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>
    </form>
</div>

<?php include getPath('views/partials/footer.php'); ?>
