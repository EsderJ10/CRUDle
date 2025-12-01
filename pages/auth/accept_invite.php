<?php
/*
 * Page to accept an invitation and set the password.
 */

require_once '../../config/init.php';

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

// Include the view
include getPath('views/components/forms/auth/accept_invite_form.php');
?>
