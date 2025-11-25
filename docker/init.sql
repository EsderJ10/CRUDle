CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') NOT NULL,
    created_at DATETIME NOT NULL,
    avatar_path VARCHAR(255) DEFAULT NULL
);
