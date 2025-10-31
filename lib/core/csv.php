<?php
/*
 * Funciones para manejar operaciones con archivos CSV.
 */

require_once getPath('config/config.php');
require_once getPath('lib/core/exceptions.php');

function getCSVRecords($filePath = null) {
    if ($filePath === null) {
        $filePath = getPath(DATA_FILE);
    }
    
    $records = [];
    
    if (!file_exists($filePath)) {
        return $records;
    }
    
    try {
        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            throw new CSVException(
                'Unable to open CSV file for reading: ' . $filePath,
                'Error al acceder al archivo de datos.'
            );
        }
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Se comprueba que la fila tenga al menos 5 columnas (id, nombre, email, rol, fecha_alta)
            if (count($data) >= 5) {
                $records[] = $data;
            }
        }
        
        fclose($handle);
    } catch (CSVException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new CSVException(
            'CSV reading error: ' . $e->getMessage(),
            'Error al leer el archivo de datos.',
            0,
            $e
        );
    }
    
    return $records;
}
function writeCSVRecords($records, $filePath = null) {
    if ($filePath === null) {
        $filePath = getPath(DATA_FILE);
    }
    
    try {
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                throw new CSVException(
                    'Unable to create CSV directory: ' . $dir,
                    'Error al crear el directorio de datos.'
                );
            }
        }
        
        $handle = @fopen($filePath, 'w');
        if ($handle === FALSE) {
            throw new CSVException(
                'Unable to open CSV file for writing: ' . $filePath,
                'Error al escribir en el archivo de datos.'
            );
        }
        
        foreach ($records as $record) {
            if (fputcsv($handle, $record) === FALSE) {
                fclose($handle);
                throw new CSVException(
                    'Error writing record to CSV file',
                    'Error al guardar datos en el archivo.'
                );
            }
        }
        
        fclose($handle);
        return true;
    } catch (CSVException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new CSVException(
            'CSV writing error: ' . $e->getMessage(),
            'Error al guardar el archivo de datos.',
            0,
            $e
        );
    }
}
function appendToCSV($record, $filePath = null) {
    if ($filePath === null) {
        $filePath = getPath(DATA_FILE);
    }
    
    try {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                throw new CSVException(
                    'Unable to create CSV directory: ' . $dir,
                    'Error al crear el directorio de datos.'
                );
            }
        }
        
        $handle = @fopen($filePath, 'a');
        if ($handle === FALSE) {
            throw new CSVException(
                'Unable to open CSV file for appending: ' . $filePath,
                'Error al acceder al archivo de datos.'
            );
        }
        
        if (fputcsv($handle, $record) === FALSE) {
            fclose($handle);
            throw new CSVException(
                'Error appending record to CSV file',
                'Error al guardar los datos.'
            );
        }
        
        fclose($handle);
        return true;
    } catch (CSVException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new CSVException(
            'CSV append error: ' . $e->getMessage(),
            'Error al guardar el archivo de datos.',
            0,
            $e
        );
    }
}

function findRecordById($id, $filePath = null) {
    $records = getCSVRecords($filePath);
    
    foreach ($records as $record) {
        if (isset($record[0]) && $record[0] == $id) {
            return $record;
        }
    }
    
    return null;
}


function updateRecordById($id, $newRecord, $filePath = null) {
    $records = getCSVRecords($filePath);
    $updated = false;
    
    for ($i = 0; $i < count($records); $i++) {
        if (isset($records[$i][0]) && $records[$i][0] == $id) {
            $records[$i] = $newRecord;
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        return writeCSVRecords($records, $filePath);
    }
    
    return false;
}

function deleteRecordById($id, $filePath = null) {
    $records = getCSVRecords($filePath);
    $filteredRecords = [];
    $found = false;
    
    foreach ($records as $record) {
        if (isset($record[0]) && $record[0] == $id) {
            $found = true;
            continue; // Salta el registro a eliminar
        }
        $filteredRecords[] = $record;
    }
    
    if ($found) {
        return writeCSVRecords($filteredRecords, $filePath);
    }
    
    return false;
}

function getNextId($filePath = null) {
    $records = getCSVRecords($filePath);
    $maxId = 0;
    
    foreach ($records as $record) {
        if (isset($record[0]) && is_numeric($record[0])) {
            $maxId = max($maxId, (int)$record[0]);
        }
    }
    
    return $maxId + 1;
}

// Comprueba el estado del archivo CSV (existencia, permisos, etc.)
function checkCSVStatus($filePath = null) {
    if ($filePath === null) {
        $filePath = getPath(DATA_FILE);
    }
    
    $dir = dirname($filePath);
    
    return [
        'fileExists' => file_exists($filePath),
        'dirExists' => is_dir($dir),
        'dirWritable' => is_writable($dir),
        'fileWritable' => file_exists($filePath) ? is_writable($filePath) : is_writable($dir),
        'filePath' => $filePath
    ];
}
?>
