<?php
/**
 * Logger Class
 * Handles application logging with different severity levels.
 */

class Logger {
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const DEBUG = 'DEBUG';

    private static $logFile;

    /**
     * Initialize the logger with a log file path.
     */
    public static function init() {
        if (!defined('LOGS_PATH')) {
            // Fallback if constant not defined
            self::$logFile = __DIR__ . '/../../logs/app.log';
        } else {
            self::$logFile = LOGS_PATH . 'app.log';
        }
    }

    /**
     * Log a message.
     * 
     * @param string $level Severity level
     * @param string $message Log message
     * @param array $context Optional context data
     */
    public static function log($level, $message, array $context = []) {
        if (!self::$logFile) {
            self::init();
        }

        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$date] [$level] $message$contextStr" . PHP_EOL;

        // Write to file
        error_log($logEntry, 3, self::$logFile);
        
        // Also write to standard error log for Docker/Server logs
        error_log("[$level] $message");
    }

    public static function info($message, array $context = []) {
        self::log(self::INFO, $message, $context);
    }

    public static function warning($message, array $context = []) {
        self::log(self::WARNING, $message, $context);
    }

    public static function error($message, array $context = []) {
        self::log(self::ERROR, $message, $context);
    }

    public static function debug($message, array $context = []) {
        // Only log debug if not in production or specifically enabled
        if (getenv('APP_ENV') !== 'production') {
            self::log(self::DEBUG, $message, $context);
        }
    }
}
?>
