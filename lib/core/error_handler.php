<?php
/*
 * Utilidades para el manejo de errores y excepciones en la aplicación.
 * Proporciona controladores globales de errores y excepciones, registro y funciones
 * utilitarias para una gestión integral de errores en toda la aplicación.
 * Autor: José Antonio Cortés Ferre
 */

require_once __DIR__ . '/../../config/paths.php';
require_once getPath('lib/core/exceptions.php');

function ensureLogDirectoryExists() {
    $logDir = dirname(ERROR_LOG_FILE);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
}

/**
 * Log de un error o excepción
 * @param string $message Mensaje de error
 * @param string $level Nivel de log (ERROR, WARNING, INFO, DEBUG)
 * @param Exception $exception Objeto de excepción opcional
 * @param array $context Información de contexto adicional
 */
function logError($message, $level = 'ERROR', $exception = null, $context = []) {
    try {
        ensureLogDirectoryExists();
        
        $logEntry = sprintf(
            "[%s] [%s] %s",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );
        
        if ($exception !== null) {
            $logEntry .= "\nException: " . get_class($exception) . " - " . $exception->getMessage();
            $logEntry .= "\nStack Trace:\n" . $exception->getTraceAsString();
        }
        
        if (!empty($context)) {
            $logEntry .= "\nContext: " . json_encode($context, JSON_PRETTY_PRINT);
        }
        
        $logEntry .= "\n" . str_repeat("-", 80) . "\n";
        
        // Se rota el archivo de log si excede el tamaño máximo
        if (file_exists(ERROR_LOG_FILE) && filesize(ERROR_LOG_FILE) > ERROR_LOG_MAX_SIZE) {
            rotateLogFile();
        }
        
        error_log($logEntry, 3, ERROR_LOG_FILE);
    } catch (Exception $e) {
        // Fallo al registrar el error, se intenta registrar en el log de errores de PHP
        error_log($message . ($exception ? ' - ' . $exception->getMessage() : ''));
    }
}

/**
 * Rota el archivo de log de errores si excede el tamaño máximo
 */
function rotateLogFile() {
    try {
        if (file_exists(ERROR_LOG_FILE)) {
            $timestamp = date('Y-m-d_H-i-s');
            $rotatedName = ERROR_LOG_FILE . '.' . $timestamp;
            rename(ERROR_LOG_FILE, $rotatedName);
            
            cleanupOldLogs();
        }
    } catch (Exception $e) {
        // Fallo al rotar el log, se registra en el log de errores de PHP
        error_log('Failed to rotate error log: ' . $e->getMessage());
    }
}

/**
 * Limpia los archivos de log antiguos, manteniendo solo los 10 más recientes
 */
function cleanupOldLogs() {
    try {
        $logDir = dirname(ERROR_LOG_FILE);
        $pattern = basename(ERROR_LOG_FILE) . '.';
        $files = glob($logDir . DIRECTORY_SEPARATOR . $pattern . '*');
        
        if (count($files) > 10) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $filesToDelete = array_slice($files, 10);
            foreach ($filesToDelete as $file) {
                @unlink($file);
            }
        }
    } catch (Exception $e) {
        // Fallo al limpiar logs antiguos, se registra en el log de errores de PHP
        error_log('Failed to clean up old logs: ' . $e->getMessage());
    }
}

/**
 * Handler global de excepciones no capturadas
 * @param Throwable $exception
 */
function globalExceptionHandler($exception) {
    logError(
        'Uncaught Exception',
        'CRITICAL',
        $exception,
        [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode()
        ]
    );

    displayErrorPage($exception);
}

/**
 * Handler global de errores de PHP
 * @param int $errno Nivel de error
 * @param string $errstr Mensaje de error
 * @param string $errfile Archivo donde ocurrió el error
 * @param int $errline Número de línea donde ocurrió el error
 * @return bool Indica si el error fue manejado
 */
function globalErrorHandler($errno, $errstr, $errfile, $errline) {
    // Si el error está deshabilitado por la configuración de error_reporting, no hacer nada
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorLevels = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'CRITICAL',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CRITICAL',
        E_CORE_WARNING => 'WARNING',
        E_COMPILE_ERROR => 'CRITICAL',
        E_COMPILE_WARNING => 'WARNING',
        E_USER_ERROR => 'ERROR',
        E_USER_WARNING => 'WARNING',
        E_USER_NOTICE => 'NOTICE',
        E_STRICT => 'DEBUG',
        E_DEPRECATED => 'DEBUG',
        E_USER_DEPRECATED => 'DEBUG'
    ];
    
    $level = $errorLevels[$errno] ?? 'ERROR';
    
    logError(
        $errstr,
        $level,
        null,
        [
            'file' => $errfile,
            'line' => $errline,
            'errno' => $errno
        ]
    );
    
    return false;
}

/**
 * Muestra una página de error al usuario
 * @param Throwable $exception
 */
function displayErrorPage($exception) {
    // Previene enviar cabeceras si ya se han enviado
    if (headers_sent()) {
        return;
    }
    
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    
    $isProductionMode = defined('APP_ENV') && APP_ENV === 'production';
    
    include getPath('pages/error_page.php');
}

/**
 * Formatea los errores de validación para su visualización
 * @param array $errors Mensajes de error
 * @return array Array de errores formateados
 */
function formatValidationErrors($errors) {
    $formatted = [];
    
    if (is_array($errors)) {
        foreach ($errors as $error) {
            if (is_string($error)) {
                $formatted[] = $error;
            } elseif (is_array($error)) {
                $formatted = array_merge($formatted, $error);
            }
        }
    }
    
    return array_unique($formatted);
}

/**
 * Determina si un error es recuperable
 * @param Throwable $exception
 * @return bool
 */
function isRecoverableError($exception) {
    return $exception instanceof ValidationException
        || $exception instanceof ResourceNotFoundException
        || $exception instanceof InvalidStateException;
}

/**
 * Obtiene un mensaje de error amigable para el usuario
 * @param Throwable $exception
 * @return string Mensaje de error amigable para el usuario
 */
function getDisplayErrorMessage($exception) {
    if ($exception instanceof AppException) {
        return $exception->getUserMessage();
    }
    
    return 'Ocurrió un error inesperado. Por favor, intente de nuevo.';
}

/**
 * Registra los controladores globales de errores y excepciones
 */
function registerErrorHandlers() {
    set_exception_handler('globalExceptionHandler');
    set_error_handler('globalErrorHandler');
    
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            logError(
                'Fatal Error: ' . $error['message'],
                'CRITICAL',
                null,
                [
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'type' => $error['type']
                ]
            );
        }
    });
}

registerErrorHandlers();
?>
