<?php
/*
 * En este archivo se definen funciones para validar los datos de entrada del usuario.
 */

require_once getPath('config/config.php');
require_once getPath('lib/core/exceptions.php');

function validateName($name) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
        return $errors;
    }
    
    $name = trim($name);
    
    if (strlen($name) < MIN_NAME_LENGTH) {
        $errors[] = "Name must be at least " . MIN_NAME_LENGTH . " characters long.";
    }
    
    if (strlen($name) > MAX_NAME_LENGTH) {
        $errors[] = "Name cannot exceed " . MAX_NAME_LENGTH . " characters.";
    }
    
    if (preg_match('/^[0-9]+$/', $name)) {
        $errors[] = "Name cannot contain only numbers.";
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/', $name)) {
        $errors[] = "Name can only contain letters and spaces.";
    }
    
    return $errors;
}

function validateEmail($email) {
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required.";
        return $errors;
    }
    
    $email = trim($email);
    
    if (strlen($email) > MAX_EMAIL_LENGTH) {
        $errors[] = "Email cannot exceed " . MAX_EMAIL_LENGTH . " characters.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
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
                UPLOAD_ERR_INI_SIZE => 'File is too large (server limit).',
                UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit).',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
            ];
            
            $errorMessage = $uploadErrors[$file['error']] ?? 'Unknown error uploading file.';
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
                ['avatar' => ['File is too large (max 2MB).']],
                'Image file is too large.'
            );
        }
        
        // Comprobar que el archivo no esté vacío
        if ($file['size'] <= 0) {
            throw new ValidationException(
                'Avatar file is empty',
                ['avatar' => ['File is empty.']],
                'Image file is empty.'
            );
        }
        
        // Verificar que sea un archivo temporal válido
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new FileUploadException(
                'Invalid uploaded file: ' . $file['tmp_name'],
                'File does not appear to be a valid uploaded file.'
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
                    ['avatar' => ['Could not determine file type.']],
                    'File type could not be verified.'
                );
            }
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new ValidationException(
                    'Invalid MIME type: ' . $mimeType,
                    ['avatar' => ['Avatar must be an image (JPEG, PNG, GIF, SVG).']],
                    'Invalid file type.'
                );
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ValidationException(
                'MIME type verification failed: ' . $e->getMessage(),
                ['avatar' => ['Error verifying file type.']],
                'Error validating image.',
                0,
                $e
            );
        }
        
        // Verificar extensión del archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            throw new ValidationException(
                'Invalid file extension: ' . $extension,
                ['avatar' => ['Invalid file extension. Use: JPG, PNG, GIF, SVG.']],
                'Invalid file extension.'
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
            'Error validating profile image.',
            0,
            $e
        );
    }
}

function validateUserData($data) {
    $errors = [];
    
    if (isset($data['name'])) {
        $errors = array_merge($errors, validateName($data['name']));
    }
    
    if (isset($data['email'])) {
        $errors = array_merge($errors, validateEmail($data['email']));
    }
    
    return $errors;
}
?>
