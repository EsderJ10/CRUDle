<?php
/*
 * CSV to Database migration script.
 * Reads data/users.csv and inserts users into the users table.
 */

require_once __DIR__ . '/../config/init.php';
require_once getPath('lib/business/user_operations.php');

$csvFile = getPath('data/users.csv');

if (!file_exists($csvFile)) {
    echo "Error: CSV file not found at $csvFile\n";
    exit(1);
}

echo "Starting migration from $csvFile...\n";

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    $row = 0;
    $successCount = 0;
    $errorCount = 0;
    
    // Detect headers
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
                echo "Row $row skipped: Data incomplete.\n";
                $errorCount++;
                continue;
            }
            
            // Check if email already exists
            $db = Database::getInstance();
            $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                echo "Row $row skipped: Email $email already exists.\n";
                $errorCount++;
                continue;
            }
            
            // Insert user
            // Default password: 12345678
            $formData = [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'password' => '12345678', // This could be changed
                'avatar' => $avatarPath,
                'status' => 'active',
                'created_at' => $createdAt
            ];
            
            createUser($formData);
            echo "User $name ($email) migrated successfully.\n";
            $successCount++;
            
        } catch (Exception $e) {
            echo "Error in row $row: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    fclose($handle);
    
    echo "\nMigration completed.\n";
    echo "Success: $successCount\n";
    echo "Errors/Skipped: $errorCount\n";
    echo "Note: The default password for migrated users is '12345678'.\n";
    
} else {
    echo "Error: Could not open CSV file.\n";
}
?>
