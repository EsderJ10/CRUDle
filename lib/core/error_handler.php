<?php
/*
 * Utilities for error and exception handling in the application.
 * Provides global error and exception handlers, logging, and utility functions
 * for comprehensive error management throughout the application.
 * Author: José Antonio Cortés Ferre
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
 * Log an error or exception
 * @param string $message Error message
 * @param string $level Log level (ERROR, WARNING, INFO, DEBUG)
 * @param Exception $exception Optional exception object
 * @param array $context Additional context information
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
        
        // Rotate log file if it exceeds max size
        if (file_exists(ERROR_LOG_FILE) && filesize(ERROR_LOG_FILE) > ERROR_LOG_MAX_SIZE) {
            rotateLogFile();
        }
        
        error_log($logEntry, 3, ERROR_LOG_FILE);
    } catch (Exception $e) {
        // Failed to log error, try logging to PHP error log
        error_log($message . ($exception ? ' - ' . $exception->getMessage() : ''));
    }
}

/**
 * Rotates the error log file if it exceeds max size
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
        // Failed to rotate log, log to PHP error log
        error_log('Failed to rotate error log: ' . $e->getMessage());
    }
}

/**
 * Cleans up old log files, keeping only the 10 most recent
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
        // Failed to clean up old logs, log to PHP error log
        error_log('Failed to clean up old logs: ' . $e->getMessage());
    }
}

/**
 * Global uncaught exception handler
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
 * Global PHP error handler
 * @param int $errno Error level
 * @param string $errstr Error message
 * @param string $errfile File where error occurred
 * @param int $errline Line number where error occurred
 * @return bool Indicates if error was handled
 */
function globalErrorHandler($errno, $errstr, $errfile, $errline) {
    // If error is disabled by error_reporting configuration, do nothing
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
 * Displays an error page to the user
 * @param Throwable $exception
 */
function displayErrorPage($exception) {
    // Prevent sending headers if already sent
    if (headers_sent()) {
        return;
    }
    
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    
    $isProductionMode = defined('APP_ENV') && APP_ENV === 'production';
    
    include getPath('pages/error_page.php');
}

/**
 * Formats validation errors for display
 * @param array $errors Error messages
 * @return array Array of formatted errors
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
 * Determines if an error is recoverable
 * @param Throwable $exception
 * @return bool
 */
function isRecoverableError($exception) {
    return $exception instanceof ValidationException
        || $exception instanceof ResourceNotFoundException
        || $exception instanceof InvalidStateException;
}

/**
 * Gets a user-friendly error message
 * @param Throwable $exception
 * @return string User-friendly error message
 */
function getDisplayErrorMessage($exception) {
    if ($exception instanceof AppException) {
        return $exception->getUserMessage();
    }
    
    return 'Ocurrió un error inesperado. Por favor, intente de nuevo.';
}

/**
 * Registers global error and exception handlers
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
