<?php
/*
 * Page to accept an invitation and set the password.
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/core/validation.php');

$pageTitle = "Accept Invitation";
$token = $_GET['token'] ?? '';
$error = null;
$user = null;

try {
    if (empty($token)) {
        throw new InvalidStateException('Token missing', 'Invalid invitation link.');
    }

    // Verify token
    $user = getInvitation($token);
    
    if (!$user) {
        throw new InvalidStateException('Invalid token', 'Invitation link is invalid or has expired.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password) || strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            // Process avatar if one was uploaded
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Use the user ID since we have it available in $user['id']
                    $avatarPath = handleAvatarUpload($_FILES['avatar'], $user['id'], $user['name']);
                } catch (Exception $e) {
                    Logger::warning('Avatar upload failed during activation', ['error' => $e->getMessage()]);
                }
            }

            // Activate user
            activateUser($token, $password, $avatarPath);
            
            // Automatically login or redirect to login
            Session::setFlash('success', 'Account activated successfully. You can now login.');
            header('Location: login.php');
            exit;
        }
    }

} catch (Exception $e) {
    $error = $e instanceof AppException ? $e->getUserMessage() : 'An unexpected error occurred.';
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
                <h1>Welcome to CRUDle</h1>
                <p class="auth-subtitle">
                    <?php if ($user): ?>
                        Hello <strong><?php echo htmlspecialchars($user['name']); ?></strong>, set your password to continue.
                    <?php else: ?>
                        Accept Invitation
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
                        <a href="login.php" class="auth-link">Back to Login</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($user): ?>
                <form method="post" action="accept_invite.php?token=<?php echo htmlspecialchars($token); ?>" class="auth-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="disabled-input">
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" required placeholder="Minimum 8 characters">
                            <button type="button" class="toggle-password" tabindex="-1">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="avatar">Avatar (Optional)</label>
                        <div class="avatar-upload-section" id="avatarUploadSection">
                            <label for="avatar" class="custom-file-upload" id="customFileUpload">
                                <span class="file-icon fas fa-upload"></span>
                                <span class="file-text">
                                    <span class="file-text-main" id="fileTextMain">Select file</span>
                                    <span class="file-text-sub" id="fileTextSub">or drag and drop here</span>
                                </span>
                            </label>
                            <input type="file" 
                                   id="avatar" 
                                   name="avatar" 
                                   accept="image/jpeg,image/png,image/gif">
                            <div class="file-preview" id="filePreview">
                                <img src="" alt="Preview" class="file-preview-image" id="filePreviewImage">
                                <div class="file-preview-info">
                                    <div class="file-preview-name" id="filePreviewName"></div>
                                    <div class="file-preview-size" id="filePreviewSize"></div>
                                </div>
                                <button type="button" class="file-preview-remove" id="filePreviewRemove">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <small class="text-neutral-600">
                                Allowed formats: JPG, PNG, GIF. Max size: 2MB.
                            </small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Activate Account
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="<?php echo getWebPath('assets/js/user-form.js'); ?>"></script>
    <script src="<?php echo getWebPath('assets/js/auth.js'); ?>"></script>
</body>
</html>
