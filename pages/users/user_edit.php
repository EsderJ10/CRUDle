<?php
/*
 * Page to edit a user.
 * Handles form display and data processing.
 * Uses functions from lib/business/user_operations and lib/presentation/user_views modules.
 * Author: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');
require_once getPath('views/user_views.php');
require_once getPath('lib/core/validation.php');
require_once getPath('lib/core/sanitization.php');

Permissions::require(Permissions::USER_UPDATE);

$pageTitle = "Edit User";
$pageHeader = "Edit User";

try {
    // Validate that an ID is provided
    if (!isset($_GET['id'])) {
        Session::setFlash('error', 'No user ID provided.');
        header('Location: user_index.php');
        exit;
    }
    
    $userId = $_GET['id'];
    
    // Load existing user
    try {
        $user = getUserById($userId);
    } catch (Exception $e) {
        Session::setFlash('error', 'Error loading user: ' . $e->getMessage());
        header('Location: user_index.php');
        exit;
    }
    if (!$user) {
        Session::setFlash('error', 'User not found.');
        header('Location: user_index.php');
        exit;
    }

    // Check if user has permission to edit this specific target user
    if (!Permissions::canEditUser($user)) {
        Session::setFlash('error', 'You do not have permission to edit this user.');
        header('Location: user_index.php');
        exit;
    }
    
    // Process POST form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Security error: Invalid CSRF token.');
            header('Location: user_edit.php?id=' . $userId);
            exit;
        }

        try {
            // Sanitize data
            $formData = sanitizeUserData([
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? '',
                'password' => $_POST['password'] ?? ''
            ]);

            // If editing self, FORCE role to remain unchanged.
            if ($userId == Session::get('user_id')) {
                $formData['role'] = $user['role'];
            } else {
                // Validate role assignment permission
                if (!Permissions::canAssignRole($formData['role'])) {
                    throw new ValidationException(
                        'Permission denied',
                        ['role' => ['You do not have permission to assign the selected role.']],
                        'Permission error.'
                    );
                }
            }
            
            // Validate basic data
            $errors = validateUserData($formData);
            
            // Validate conflicts between remove and upload
            $removeAvatar = isset($_POST['remove_avatar']) && $_POST['remove_avatar'] == '1';
            if ($removeAvatar && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $errors[] = "You cannot remove and upload an avatar at the same time. Please choose only one option.";
            }
            
            // Validate avatar if provided
            if (!$removeAvatar && isset($_FILES['avatar'])) {
                try {
                    $avatarErrors = validateAvatar($_FILES['avatar']);
                    $errors = array_merge($errors, $avatarErrors);
                } catch (ValidationException $e) {
                    $errors = array_merge($errors, $e->getErrors()['avatar'] ?? [$e->getUserMessage()]);
                } catch (FileUploadException $e) {
                    $errors[] = $e->getUserMessage();
                }
            }
            
            if (!empty($errors)) {
                throw new ValidationException(
                    'Form validation failed',
                    ['general' => $errors],
                    'Please correct the errors in the form.'
                );
            }
            
            // Handle avatar
            $newAvatarPath = null;
            $oldAvatarPath = $user['avatar'];
            
            if ($removeAvatar) {
                // User opted to remove existing avatar
                if ($oldAvatarPath) {
                    try {
                        deleteAvatarFile($oldAvatarPath);
                    } catch (AvatarException $e) {
                        error_log('Avatar deletion failed: ' . $e->getMessage());
                        // Do not fail the entire operation for this
                    }
                }
                $formData['avatar'] = null;
            } else if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                // User is uploading a new avatar
                try {
                    $newAvatarPath = handleAvatarUpload($_FILES['avatar'], $userId, $formData['name']);
                    if ($newAvatarPath) {
                        $formData['avatar'] = $newAvatarPath;
                        // Old avatar is automatically deleted in handleAvatarUpload
                    } else {
                        $formData['avatar'] = $oldAvatarPath; // Keep old one if upload fails
                    }
                } catch (AvatarException $e) {
                    error_log('Avatar upload failed: ' . $e->getMessage());
                    // Keep old avatar
                    $formData['avatar'] = $oldAvatarPath;
                }
            } else {
                // No changes to avatar
                $formData['avatar'] = $oldAvatarPath;
            }
            
            // Update user
            $success = updateUser($userId, $formData);
            
            if ($success) {
                Session::setFlash('success', 'User ID ' . $userId . ' updated successfully.');
                header('Location: user_index.php');
                exit;
            } else {
                throw new UserOperationException(
                    'Failed to update user',
                    'Error updating user.'
                );
            }
            
        } catch (ValidationException $e) {
            // Show form with errors
            include getPath('views/partials/header.php');
            
            $fieldErrors = $e->getErrors();
            foreach ($fieldErrors['general'] ?? [] as $error) {
                echo renderMessage($error, 'error');
            }
            
            // Update form data
            $user['name'] = $formData['name'];
            $user['email'] = $formData['email'];
            $user['role'] = $formData['role'];
            
            include getPath('views/components/forms/user_form.php');
            include getPath('views/partials/footer.php');
            exit;
            
        } catch (AppException $e) {
            // Known application errors
            Session::setFlash('error', $e->getUserMessage());
            header('Location: user_index.php');
            exit;
        }
    }
    
    // GET request - show form with user data
    if ($user !== null) {
        include getPath('views/partials/header.php');
        // Filter available roles based on permissions
        $availableRoles = [];
        foreach (Role::cases() as $role) {
            if (Permissions::canAssignRole($role->value)) {
                $availableRoles[$role->value] = $role->label();
            }
        }

        // Pass available roles to the view
        include getPath('views/components/forms/user_form.php');
        
        include getPath('views/partials/footer.php');
    }
    
} catch (Exception $e) {
    // Unexpected error - Let Global Handler handle it
    throw $e;
}
?>
