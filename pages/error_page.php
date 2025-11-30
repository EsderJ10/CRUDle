<?php
/*
 * Error page.
 * Displays an error page with exception details.
 *
 * Parameters:
 * - $exception: The Throwable exception object
 * - $isProductionMode: Boolean indicating production environment
 */

$message = $isProductionMode 
    ? 'Ocurrió un error inesperado. Por favor, intente de nuevo más tarde.'
    : htmlspecialchars($exception->getMessage());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="<?php echo getWebPath('assets/css/error.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="error-container">
        <div class="error-icon fas fa-exclamation-triangle"></div>
        <h1 class="error-title">Error</h1>
        <p class="error-message"><?php echo $message; ?></p>
        
        <?php if (!$isProductionMode): ?>
            <div class="error-code">
                <strong>Technical details:</strong>
                Type: <?php echo htmlspecialchars($exception::class); ?><br>
                File: <?php echo htmlspecialchars($exception->getFile()); ?><br>
                Line: <?php echo htmlspecialchars((string)$exception->getLine()); ?><br>
                <strong>Message:</strong>
                <?php echo htmlspecialchars($exception->getMessage()); ?>
            </div>
        <?php endif; ?>
        <a href="<?php echo getWebPath('index.php'); ?>" class="btn">Return to Home</a>
    </div>
</body>
</html>
