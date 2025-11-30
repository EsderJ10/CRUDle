<?php
/* 
 * Custom Exception Classes
 * Defines specific exceptions used in the CRUDle application.
 * Author: José Antonio Cortés Ferre
 * 
 */

/**
 * Base Application Exception
 * All custom exceptions inherit from this
 */
class AppException extends Exception {
    protected $userMessage = 'An unexpected error occurred. Please try again.';
    
    /**
     * Constructor
     * @param string $message Technical error message for logging
     * @param string $userMessage User-friendly error message
     * @param int $code Error code
     * @param Throwable $previous Previous exception for chaining
     */
    public function __construct(
        $message = '',
        $userMessage = '',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        if (!empty($userMessage)) {
            $this->userMessage = $userMessage;
        }
    }
    
    public function getUserMessage() {
        return $this->userMessage;
    }
}


/**
 * Validation Exception
 * Thrown when input validation fails
 */
class ValidationException extends AppException {
    private $errors = [];
    
    public function __construct(
        $message = 'Validation failed',
        $errors = [],
        $userMessage = 'The provided data is not valid.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
        $this->errors = $errors;
    }
    
    public function getErrors() {
        return $this->errors;
    }

    public function hasFieldError($field) {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }
    
    public function getFieldErrors($field) {
        return $this->errors[$field] ?? [];
    }
}

/**
 * File Upload Exception
 * Thrown when file upload operations fail
 */
class FileUploadException extends AppException {
    public function __construct(
        $message = 'File upload failed',
        $userMessage = 'Error uploading file. Please try again.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Avatar Exception
 * Thrown when avatar operations fail (upload, delete, processing)
 */
class AvatarException extends AppException {
    public function __construct(
        $message = 'Avatar operation failed',
        $userMessage = 'Error processing avatar. Please try again.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * User Operation Exception
 * Thrown when user CRUD operations fail
 */
class UserOperationException extends AppException {
    public function __construct(
        $message = 'User operation failed',
        $userMessage = 'Error performing user operation. Please try again.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Resource Not Found Exception
 * Thrown when requested resource (user, file, etc.) is not found
 */
class ResourceNotFoundException extends AppException {
    public function __construct(
        $message = 'Resource not found',
        $userMessage = 'The requested resource does not exist.',
        $code = 404,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Invalid State Exception
 * Thrown when an operation cannot be performed due to current state
 */
class InvalidStateException extends AppException {
    public function __construct(
        $message = 'Invalid state',
        $userMessage = 'Operation cannot be performed due to current state.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Authentication Exception
 * Thrown when authentication operations fail
 */
class AuthException extends AppException {
    public function __construct(
        $message = 'Authentication failed',
        $userMessage = 'Authentication failed.',
        $code = 401,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}
?>
