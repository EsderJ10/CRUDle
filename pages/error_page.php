<?php
/*
 * Página de error.
 * Muestra una página de error con detalles de la excepción
 *
 * Parámetros:
 * - $exception: El objeto de excepción Throwable
 * - $isProductionMode: Booleano que indica el entorno de producción
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
                <strong>Detalles técnicos:</strong><br>
                Tipo: <?php echo htmlspecialchars($exception::class); ?><br>
                Archivo: <?php echo htmlspecialchars($exception->getFile()); ?><br>
                Línea: <?php echo htmlspecialchars((string)$exception->getLine()); ?><br>
                <br>
                <strong>Mensaje:</strong><br>
                <?php echo htmlspecialchars($exception->getMessage()); ?>
            </div>
        <?php endif; ?>
        
        <a href="<?php echo getWebPath('index.php'); ?>" class="btn">Volver a Inicio</a>
    </div>
</body>
</html>
