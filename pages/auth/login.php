<?php
require_once '../../config/init.php';
require_once getPath('views/user_views.php');

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
            Session::setFlash('success', 'Welcome, ' . Session::get('user_name'));
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
include getPath('views/components/forms/login_form.php');
include getPath('views/partials/footer.php');
?>