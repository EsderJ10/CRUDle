<?php
/*
 * En este archivo se definen funciones para validar los datos de entrada del usuario.
 */

require_once getPath('config/config.php');
require_once getPath('lib/core/exceptions.php');

function validateName($name) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "El nombre es obligatorio.";
        return $errors;
    }
    
    $name = trim($name);
    
    if (strlen($name) < MIN_NAME_LENGTH) {
        $errors[] = "El nombre debe tener al menos " . MIN_NAME_LENGTH . " caracteres.";
    }
    
    if (strlen($name) > MAX_NAME_LENGTH) {
        $errors[] = "El nombre no puede tener más de " . MAX_NAME_LENGTH . " caracteres.";
    }
    
    if (preg_match('/^[0-9]+$/', $name)) {
        $errors[] = "El nombre no puede ser solo números.";
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/', $name)) {
        $errors[] = "El nombre solo puede contener letras y espacios.";
    }
    
    return $errors;
}

function validateEmail($email) {
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "El email es obligatorio.";
        return $errors;
    }
    
    $email = trim($email);
    
    if (strlen($email) > MAX_EMAIL_LENGTH) {
        $errors[] = "El email no puede tener más de " . MAX_EMAIL_LENGTH . " caracteres.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no tiene un formato válido.";
    }
    
    return $errors;
}


function validateAvatar($file) {
    $errors = [];
    
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $errors;
    }
    
    try {
        // Se busca cualquier error en la subida del archivo para manejarlo
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande (límite del servidor).',
                UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande (límite del formulario).',
                UPLOAD_ERR_PARTIAL => 'El archivo no se subió completamente.',
                UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
                UPLOAD_ERR_NO_TMP_DIR => 'No hay directorio temporal en el servidor.',
                UPLOAD_ERR_CANT_WRITE => 'No se puede escribir el archivo en el servidor.',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo.'
            ];
            
            $errorMessage = $uploadErrors[$file['error']] ?? 'Error desconocido al subir el archivo.';
            throw new FileUploadException(
                'Upload error code: ' . $file['error'],
                '* ERROR: ' . $errorMessage
            );
        }
        
        // Comprobar el tamaño del archivo (máximo 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            throw new ValidationException(
                'Avatar file exceeds maximum size',
                ['avatar' => ['El archivo es demasiado grande (máximo 2MB).']],
                'El archivo de imagen es demasiado grande.'
            );
        }
        
        // Comprobar que el archivo no esté vacío
        if ($file['size'] <= 0) {
            throw new ValidationException(
                'Avatar file is empty',
                ['avatar' => ['El archivo está vacío.']],
                'El archivo de imagen está vacío.'
            );
        }
        
        // Verificar que sea un archivo temporal válido
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new FileUploadException(
                'Invalid uploaded file: ' . $file['tmp_name'],
                'El archivo no parece ser un archivo subido válido.'
            );
        }
        
        // Comprobar tipos MIME y extensiones permitidas
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        
        // Verificar MIME type
        try {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                throw new Exception('finfo_open failed');
            }
            
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($mimeType === false) {
                throw new ValidationException(
                    'Could not determine MIME type',
                    ['avatar' => ['No se puede determinar el tipo de archivo.']],
                    'El tipo de archivo no se pudo verificar.'
                );
            }
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new ValidationException(
                    'Invalid MIME type: ' . $mimeType,
                    ['avatar' => ['El avatar debe ser una imagen (JPEG, PNG, GIF, SVG).']],
                    'El tipo de archivo no es válido.'
                );
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ValidationException(
                'MIME type verification failed: ' . $e->getMessage(),
                ['avatar' => ['Error al verificar el tipo de archivo.']],
                'Error al validar la imagen.',
                0,
                $e
            );
        }
        
        // Verificar extensión del archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            throw new ValidationException(
                'Invalid file extension: ' . $extension,
                ['avatar' => ['Extensión de archivo no válida. Use: JPG, PNG, GIF, SVG.']],
                'La extensión del archivo no es válida.'
            );
        }
        
        return $errors;
    } catch (ValidationException $e) {
        throw $e;
    } catch (FileUploadException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new FileUploadException(
            'Avatar validation error: ' . $e->getMessage(),
            'Error al validar la imagen de perfil.',
            0,
            $e
        );
    }
}

function validateUserData($data) {
    $errors = [];
    
    if (isset($data['nombre'])) {
        $errors = array_merge($errors, validateName($data['nombre']));
    }
    
    if (isset($data['email'])) {
        $errors = array_merge($errors, validateEmail($data['email']));
    }
    
    return $errors;
}
?>
