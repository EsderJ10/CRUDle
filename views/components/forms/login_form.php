<?php
/**
 * Login form component.
 * 
 * Variables expected:
 * - $error
 */

$error = $error ?? null;
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