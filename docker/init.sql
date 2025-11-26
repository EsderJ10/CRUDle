CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') NOT NULL,
    status ENUM('active', 'pending', 'inactive') DEFAULT 'pending',
    invitation_token VARCHAR(64) NULL,
    invitation_expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    avatar_path VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NULL
);
