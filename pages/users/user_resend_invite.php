<?php
/*
 * Script to resend invitation to a user.
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');

requireLogin();

// Check if admin
if (!isAdmin()) {
    Session::setFlash('error', 'Access denied.');
    header('Location: user_index.php');
    exit;
}

$userId = $_GET['id'] ?? null;

if (!$userId) {
    Session::setFlash('error', 'User ID not specified.');
    header('Location: user_index.php');
    exit;
}

// Validate CSRF
if (!isset($_GET['csrf_token']) || !CSRF::validate($_GET['csrf_token'])) {
    Session::setFlash('error', 'Security error: Invalid CSRF token.');
    header('Location: user_index.php');
    exit;
}

try {
    resendInvitation($userId);
    Session::setFlash('success', 'Invitation resent successfully.');
} catch (AppException $e) {
    Session::setFlash('error', $e->getUserMessage());
} catch (Exception $e) {
    Session::setFlash('error', 'Unexpected error resending invitation.');
}

header('Location: user_index.php');
exit;
?>
