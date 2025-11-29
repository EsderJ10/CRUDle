<?php
/*
 * Script de migración de CSV a Base de Datos.
 * Lee data/users.csv e inserta los usuarios en la tabla users.
 */

require_once __DIR__ . '/../config/init.php';
require_once getPath('lib/business/user_operations.php');

$csvFile = getPath('data/users.csv');

if (!file_exists($csvFile)) {
    echo "Error: No se encontró el archivo CSV en $csvFile\n";
    exit(1);
}

echo "Iniciando migración desde $csvFile...\n";

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    $row = 0;
    $successCount = 0;
    $errorCount = 0;
    
    // Detectar encabezados
    $headers = fgetcsv($handle, 1000, ",");
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row++;
        
        try {
            $name = $data[1] ?? '';
            $email = $data[2] ?? '';
            $role = $data[3] ?? 'viewer';
            $createdAt = $data[4] ?? date('Y-m-d H:i:s');
            $avatarPath = $data[5] ?? null;
            
            if (empty($name) || empty($email)) {
                echo "Fila $row saltada: Datos incompletos.\n";
                $errorCount++;
                continue;
            }
            
            // Verificar si el email ya existe
            $db = Database::getInstance();
            $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                echo "Fila $row saltada: El email $email ya existe.\n";
                $errorCount++;
                continue;
            }
            
            // Insertar usuario
            // Contraseña por defecto: 12345678
            $formData = [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'password' => '12345678',
                'avatar' => $avatarPath,
                'status' => 'active',
                'created_at' => $createdAt
            ];
            
            createUser($formData);
            echo "Usuario $name ($email) migrado exitosamente.\n";
            $successCount++;
            
        } catch (Exception $e) {
            echo "Error en fila $row: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    fclose($handle);
    
    echo "\nMigración completada.\n";
    echo "Exitosos: $successCount\n";
    echo "Errores/Saltados: $errorCount\n";
    echo "Nota: La contraseña por defecto para los usuarios migrados es '12345678'.\n";
    
} else {
    echo "Error: No se pudo abrir el archivo CSV.\n";
}
?>
