<?php
/**
 * View for accepting invitation.
 * 
 * Variables expected:
 * - $pageTitle
 * - $user
 * - $token
 * - $error
 */

// Ensure variables are set
$pageTitle = $pageTitle ?? 'Accept Invitation';
$user = $user ?? null;
$token = $token ?? '';
$error = $error ?? null;
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

                    <?php include getPath('views/components/password_fields.php'); ?>

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
