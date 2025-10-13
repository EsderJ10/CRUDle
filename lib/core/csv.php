<?php
// Here are defined the main CSV handling functions

require_once getPath('config/config.php');

function getCSVRecords($filePath = null) {
    if ($filePath === null) {
        $filePath = getPath(DATA_FILE);
    }
    
    $records = [];
    
    if (!file_exists($filePath)) {
        return $records;
    }
    
    $handle = fopen($filePath, 'r');
    if ($handle !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 5) {
                $records[] = $data;
            }
        }
        fclose($handle);
    }
    
    return $records;
}

function writeCSVRecords($records, $filePath = null) {
    if ($filePath === null) {
        $filePath = getPath(DATA_FILE);
    }
    
    // Ensure directory exists
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $handle = fopen($filePath, 'w');
    if ($handle !== FALSE) {
        foreach ($records as $record) {
            fputcsv($handle, $record);
        }
        fclose($handle);
        return true;
    }
    
    return false;
}

function appendToCSV($record, $filePath = null) {
    if ($filePath === null) {
        $filePath = getPath(DATA_FILE);
    }
    
    // Ensure directory exists
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $handle = fopen($filePath, 'a');
    if ($handle !== FALSE) {
        $result = fputcsv($handle, $record);
        fclose($handle);
        return $result !== FALSE;
    }
    
    return false;
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
            continue; // Skip this record (delete it)
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

// Check the status of the CSV file and its directory
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
