<?php
/* 
 * Clases de Excepciones Personalizadas
 * Se definen las excepciones específicas utilizadas en la aplicación CRUDle.
 * Autor: José Antonio Cortés Ferre
 * 
 */

/**
 * Excepción Base de la Aplicación
 * Todas las excepciones personalizadas heredan de esta
 */
class AppException extends Exception {
    protected $userMessage = 'Ocurrió un error inesperado. Por favor, intente de nuevo.';
    
    /**
     * Constructor
     * @param string $message Mensaje de error técnico para registro
     * @param string $userMessage Mensaje de error amigable para el usuario
     * @param int $code Código de error
     * @param Throwable $previous Excepción previa para encadenamiento
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
 * Excepción de operación CSV
 * Se lanza cuando hay errores al manipular archivos CSV
 */
class CSVException extends AppException {
    public function __construct(
        $message = 'CSV operation failed',
        $userMessage = 'Error al acceder al archivo de datos. Por favor, intente de nuevo.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Excepción de Validación
 * Se lanza cuando falla la validación de entrada
 */
class ValidationException extends AppException {
    private $errors = [];
    
    public function __construct(
        $message = 'Validation failed',
        $errors = [],
        $userMessage = 'Los datos proporcionados no son válidos.',
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
 * Excepción de carga de archivos
 * Se lanza cuando fallan las operaciones de carga de archivos
 */
class FileUploadException extends AppException {
    public function __construct(
        $message = 'File upload failed',
        $userMessage = 'Error al procesar el archivo. Por favor, intente de nuevo.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Excepción de Avatar
 * Se lanza cuando fallan las operaciones de avatar (carga, eliminación, procesamiento)
 */
class AvatarException extends AppException {
    public function __construct(
        $message = 'Avatar operation failed',
        $userMessage = 'Error al procesar la imagen de perfil. Por favor, intente de nuevo.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Excepción de operación de Usuario
 * Se lanza cuando fallan las operaciones CRUD de usuario
 */
class UserOperationException extends AppException {
    public function __construct(
        $message = 'User operation failed',
        $userMessage = 'Error al realizar la operación. Por favor, intente de nuevo.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Excepción de Recurso No Encontrado
 * Se lanza cuando el recurso solicitado (usuario, archivo, etc.) no se encuentra
 */
class ResourceNotFoundException extends AppException {
    public function __construct(
        $message = 'Resource not found',
        $userMessage = 'El recurso solicitado no existe.',
        $code = 404,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}

/**
 * Excepción de Estado Inválido
 * Se lanza cuando no se puede realizar una operación debido al estado actual
 */
class InvalidStateException extends AppException {
    public function __construct(
        $message = 'Invalid state',
        $userMessage = 'No se puede realizar esta operación en el estado actual.',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $previous);
    }
}
?>
