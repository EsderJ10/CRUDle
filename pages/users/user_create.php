<?php
/*
 * Page to create a new user.
 * Handles form display and data processing.
 * Uses functions from lib/business/user_operations and lib/presentation/user_views modules.
 * Author: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');

Permissions::require(Permissions::USER_CREATE);

$pageTitle = "Invite User";
$pageHeader = "Invite New User";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Security error: Invalid CSRF token.');
            // Reload form
            include getPath('views/partials/header.php');
            $formData = $_POST; // Repopulate
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }

        try {
            // Sanitize input data
            $formData = sanitizeUserData([
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? ''
            ]);
            
            // Validate basic data (name, email)
            $errors = validateUserData($formData);
            
            if (empty($formData['role'])) $errors[] = "Role is required.";
            
            if (!empty($errors)) {
                throw new ValidationException(
                    'Form validation failed',
                    ['general' => $errors],
                    'Please correct the errors in the form.'
                );
            }
            
            // Process avatar if one was uploaded
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                try {
                    $avatarPath = handleAvatarUpload($_FILES['avatar'], null, $formData['name']);
                } catch (Exception $e) {
                    // If avatar fails, warn but continue with invitation
                    Session::setFlash('warning', 'User invited, but there was an error uploading the avatar: ' . $e->getMessage());
                }
            }
            
            // Invite user
            $userId = inviteUser($formData['name'], $formData['email'], $formData['role'], $avatarPath);
            
            // Success - redirect with flash message
            Session::setFlash('success', 'Invitation sent successfully.');
            header('Location: user_index.php');
            exit;
            
        } catch (ValidationException $e) {
            // Show form with errors
            include getPath('views/partials/header.php');
            
            // Show general errors
            $fieldErrors = $e->getErrors();
            foreach ($fieldErrors['general'] ?? [] as $error) {
                echo renderMessage($error, 'error');
            }
            
            // Repopulate form data
            // $formData already has sanitized data
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (AppException $e) {
            // Known application errors (UserOperation, etc.)
            Session::setFlash('error', $e->getUserMessage());
            include getPath('views/partials/header.php');
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
        }
    } else {
        // GET request - show empty form
        include getPath('views/partials/header.php');
        include getPath('views/components/forms/user_form.php');
        include getPath('views/partials/footer.php');
    }
} catch (Exception $e) {
    // Unexpected error (500) - Let Global Handler handle it
    throw $e;
}
?>

