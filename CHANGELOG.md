# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2025-12-02

### Added
- **Authentication & Security**
  - **Complete Auth System**: Implemented secure Login, Logout, and Session management.
  - **Role-Based Access Control (RBAC)**: Enforced permissions for Admin, Editor, and Viewer roles.
  - **Password Security**: Implemented BCRYPT password hashing with configurable cost.
  - **Secure Sessions**: Added session regeneration, timeout, and secure cookie flags.
- **Production Readiness**
  - Complete translation of all documentation and code to English.
  - Removal of all debug code and development artifacts.
  - Improved error handling for database connections.
- **UI/UX Enhancements**
  - **Toast Notification System**: Implemented a non-blocking, fixed-position notification system to replace browser alerts.
  - **Animations**: Added smooth slide-in and slide-out animations for notifications.
  - **Global App Object**: Exposed `CrudApp` globally (`window.CrudApp`) for better module interoperability.

### Changed
- **Error Handling**: Replaced critical `die()` calls with proper `DatabaseException` handling in `Database` class.
- **Notifications**: 
  - Replaced native JavaScript `alert()` calls with `CrudApp.showNotification()`.
  - Updated `showNotification` to use a dedicated `#toast-container` to prevent layout shifts.
- **Forms**: Updated `accept_invite_form.php` to include necessary JS dependencies (`app.js`).

### Removed
- **Legacy Files**: Removed `ACTIVITY.md` and other development tracking files.
- **Debug Code**: Removed `console.log` and `var_dump` leftovers from production code.

### Migration Guide (v1.2.0 → v2.0.0)

> [!IMPORTANT]
> **Authentication Update**: This version introduces a mandatory login system. Existing users from v1.2.0 will not have passwords set and cannot log in immediately.

#### 1. Database Schema Update
The application includes an auto-migration feature that attempts to update your database schema automatically on the first run. However, for production environments, we recommend verifying or running these SQL commands manually:

```sql
-- Add new columns for Auth and Status
ALTER TABLE users ADD COLUMN status ENUM('active', 'pending', 'inactive') DEFAULT 'pending' AFTER role;
ALTER TABLE users ADD COLUMN invitation_token VARCHAR(64) NULL AFTER status;
ALTER TABLE users ADD COLUMN invitation_expires_at DATETIME NULL AFTER invitation_token;
ALTER TABLE users ADD COLUMN password VARCHAR(255) NULL AFTER avatar_path;
```

#### 2. Accessing Your Account (Admin)
Since existing users have no passwords, you must manually set a password for your admin account directly in the database to gain initial access.

Run this SQL command to set the password `admin123` for user ID 1 (replace with your actual admin ID):

```sql
-- Password hash for 'admin123' (BCRYPT cost 10)
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    status = 'active' 
WHERE id = 1;
```

#### 3. Migrating Other Users
Once logged in as Admin:
1. Go to the Dashboard.
2. You will see existing users with "Pending" status.
3. You can use the **Resend Invite** feature (if email is configured) to send them a link to set their password.
4. Alternatively, you can manually set their passwords in the database using the same method as step 2.

## [1.2.0] - 2025-11-25

### Added

- **Database Support**
  - Migrated from CSV to **MariaDB** for robust data storage.
  - Implemented `Database` class with PDO and Singleton pattern.
  - Automatic schema initialization via Docker (`docker/init.sql`).

- **Security Hardening**
  - **CSRF Protection**: Added `CSRF` class and token validation on all forms.
  - **Secure Sessions**: Implemented `Session` class for session management and "Flash Messages".
  - **Upload Hardening**: Added `.htaccess` to `uploads/` to prevent script execution.

- **Error Handling**
  - **Global Error Handler**: Centralized exception handling for unexpected errors.
  - **User-Friendly Error Page**: Dedicated `error_page.php` for production environments.
  - **Flash Messages**: Replaced URL-based error messages with session-based notifications.

- **Architecture**
  - **Centralized Initialization**: New `config/init.php` to bootstrap the application.
  - **Refactored Controllers**: Simplified logic in all `user_*.php` files.

### Changed

- **Data Layer**: Completely replaced CSV file operations with SQL queries.
- **Configuration**: Updated `config.php` to include database credentials and removed CSV paths.
- **Dependencies**: Added `pdo_mysql` extension to Dockerfile.

### Removed

- **CSV Support**: Removed `lib/core/csv.php` and `CSVException`.
- **Legacy Code**: Removed unused helper functions and redundant try-catch blocks.

### Migration Guide (v1.1.0 → v1.2.0)

> [!WARNING]
> **Breaking Change**: This version migrates from CSV file storage to MariaDB database. Is recommended to follow these steps carefully to preserve your data.

#### For Docker Users (Recommended)

1. **Backup Your Data**
   ```bash
   # Backup existing CSV data
   cp data/usuarios.csv data/usuarios.csv.backup
   
   # Backup avatars
   tar -czf avatars_backup.tar.gz uploads/avatars/
   ```

2. **Stop Existing Containers**
   ```bash
   docker-compose down
   ```

3. **Pull Latest Code**
   ```bash
   git pull origin main
   # Or download the latest release
   ```

4. **Start New Stack**
   ```bash
   docker-compose up -d
   ```
   The database will be automatically initialized with the schema from `docker/init.sql`.

5. **Migrate CSV Data to Database** (if you have existing users)
   
   Create a migration script `migrate_csv_to_db.php`. An example:
   ```php
   <?php
   require_once 'config/init.php';
   
   $csvFile = 'data/usuarios.csv.backup';
   if (!file_exists($csvFile)) {
       die("No CSV backup found. Skipping migration.\n");
   }
   
   $db = Database::getInstance();
   $handle = fopen($csvFile, 'r');
   
   // Skip header if exists
   fgetcsv($handle);
   
   $count = 0;
   while (($row = fgetcsv($handle)) !== false) {
       if (count($row) >= 5) {
           $sql = "INSERT INTO users (id, name, email, role, created_at, avatar_path) 
                   VALUES (?, ?, ?, ?, ?, ?)";
           $db->query($sql, [
               $row[0], // id
               $row[1], // name
               $row[2], // email
               $row[3], // role
               $row[4], // created_at
               $row[5] ?? null // avatar_path
           ]);
           $count++;
       }
   }
   
   fclose($handle);
   echo "Migrated $count users successfully!\n";
   ```
   
   Run the migration:
   ```bash
   docker exec -it crudle-web php migrate_csv_to_db.php
   ```

6. **Verify Migration**
   - Access the application at `http://localhost:8080`
   - Check that all users are displayed correctly
   - Verify avatars are loading properly

#### For Traditional Installation (XAMPP/Apache)

1. **Backup Your Data** (same as Docker step 1)

2. **Install MariaDB/MySQL**
   - Ensure you have MariaDB or MySQL installed
   - Create a database: `CREATE DATABASE crudle;`
   - Create a user with appropriate permissions

3. **Update Configuration**
   ```bash
   # Edit config/config.php
   # Set your database credentials:
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'crudle');
   define('DB_USER', 'your_user');
   define('DB_PASSWORD', 'your_password');
   ```

4. **Initialize Database Schema**
   ```bash
   mysql -u your_user -p crudle < docker/init.sql
   ```

5. **Migrate CSV Data** (if you have existing users)
   - Use the same migration script from Docker step 5
   - Run: `php migrate_csv_to_db.php`

6. **Update Dependencies**
   - Ensure `pdo_mysql` extension is enabled in `php.ini`
   - Restart Apache

#### Post-Migration Cleanup

Once you've verified everything works:

```bash
# Optional: Remove old CSV file
rm data/usuarios.csv

# Keep the backup for safety
# You can delete it later: rm data/usuarios.csv.backup
```

#### Rollback (if needed)

If you encounter issues and need to rollback to v1.1.0:

```bash
# Stop containers
docker-compose down

# Checkout previous version
git checkout v1.1.0

# Restore CSV backup
cp data/usuarios.csv.backup data/usuarios.csv

# Start old version
docker-compose up -d
```


## [1.1.0] - 2025-11-04

### Added

- **Docker Support** - Full containerization with Docker and Docker Compose
  - `Dockerfile` with PHP 8.1, Apache, and GD extension
  - `docker-compose.yml` for easy container orchestration with automatic build
  - `.dockerignore` to optimize Docker build context
  - `.env` and `.env.example` for environment configuration
  - Apache VirtualHost configuration in `docker/apache-config.conf`
  - One-command deployment: `docker-compose up -d`

- **Environment-Aware Path Configuration**
  - Auto-detection of execution environment (Docker vs local)
  - Automatic `WEB_ROOT` configuration based on environment
  - No manual configuration needed for Docker deployments

- **Avatar Path Normalization**
  - New `normalizeAvatarPath()` function in `lib/helpers/utils.php`
  - Handles avatar paths for both Docker and XAMPP environments
  - Maintains backward compatibility with existing avatar data

- **Documentation & Developer Experience**
  - Updated README with Docker installation instructions
  - Docker section in prerequisites and installation
  - Environment configuration documentation
  - Quick start guide for Docker users

### Fixed

- **Avatar Display Issues**
  - Fixed avatar display for users #9 and #10 (legacy data)
  - Avatar paths now normalize correctly across environments
  - CSS and asset paths work seamlessly in Docker

- **Path Handling**
  - Fixed relative path issues in `lib/helpers/utils.php`
  - Corrected `include_once` statements for better portability
  - Improved path resolution in containerized environment

- **CSS and Asset Loading**
  - CSS files now load correctly in Docker environment
  - Asset paths automatically adapt to environment
  - Smooth theme switching in both environments

### Changed

- **Path Configuration** (`config/paths.php`)
  - Now auto-detects environment from `APP_ENV` variable
  - `WEB_ROOT` automatically set based on Docker/local detection
  - More flexible and maintainable architecture

- **User Operations** (`lib/business/user_operations.php`)
  - All user retrieval functions now normalize avatar paths
  - `getAllUsers()` - normalizes avatars in user list
  - `getUserById()` - normalizes avatar for single user
  - `getUserStatistics()` - normalizes avatars in recent users

- **Installation Process**
  - Docker is now the recommended quick-start option
  - Traditional XAMPP setup still fully supported
  - Clearer, more organized installation instructions

### Technical Details

- **Environment Variables**
  - `APP_ENV` - Controls environment detection (development/production)
  - `WEB_PORT` - Configurable port for Docker (default: 8080)
  - `DOCKER_CONTAINER` - Optional flag for container detection

- **Docker Network**
  - Created `crudle-network` bridge for future multi-container setup
  - Prepares foundation for database and cache services

- **Volume Mounts**
  - `./data` → `/var/www/html/data` - CSV database persistence
  - `./uploads` → `/var/www/html/uploads` - Avatar storage
  - `./logs` → `/var/www/html/logs` - Error logs

### Dependencies

- No new PHP dependencies added
- Docker-only requirement (optional)
- Backward compatible with v1.0.0 deployments

### Migration Guide

**From v1.0.0 to v1.1.0:**

1. Pull latest code
2. Existing XAMPP installations work without changes
3. To use Docker: `docker-compose up -d`
4. Avatar paths automatically normalize on load

---

## [1.0.0] - 2025-10-31

### Added

- **Core CRUD Operations**
  - Create new users with validation
  - Read user list and individual user details
  - Update user information
  - Delete users with confirmation

- **User Management Features**
  - User information: Name, Email, Role, Registration Date
  - Avatar upload with validation (JPEG, PNG, GIF, SVG, max 2MB)
  - User roles: Admin, Editor, Viewer
  - Avatar management (upload, view, delete)

- **Dashboard**
  - Real-time user statistics
  - Users by role breakdown
  - Recent user activity (latest 5 users)
  - Quick action buttons

- **User Interface**
  - Responsive design (mobile, tablet, desktop)
  - Dark/Light theme toggle with persistent storage
  - Smooth page transitions and animations
  - Success/error messages with visual feedback
  - Confirmation dialogs for sensitive operations

- **Data Validation & Security**
  - Server-side input validation
  - XSS protection with output escaping
  - Data sanitization
  - File upload validation
  - CSV data integrity checks

- **Error Handling**
  - Comprehensive error handling system
  - Custom exception classes
  - Error logging to file
  - User-friendly error messages
  - Error page with diagnostics

- **Architecture & Code Organization**
  - MVC-inspired architecture
  - Clean separation of concerns (presentation, business, core, data layers)
  - Modular JavaScript with ES6 modules
  - Reusable components and utilities
  - Well-documented code

- **Development Features**
  - Git version control
  - Organized project structure
  - Comprehensive README
  - Technical documentation
  - TODO tracking

### Technical Stack

- **Backend:** PHP 8.1+
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Data Storage:** CSV file-based
- **Server:** Apache/XAMPP

### Project Status

- Initial stable release
- Production-ready code quality
- CSV storage suitable for small to medium datasets
- Foundation for future database migration

---

## Version History Summary

| Version | Date | Type | Focus |
|---------|------|------|-------|
| 1.1.0 | 2025-11-04 | Minor | Docker support & path normalization |
| 1.0.0 | 2025-10-31 | Major | Initial stable release |

---

## Future Roadmap

Planned for future releases:

- [ ] **v1.2.0** - Database migration
  - Migrate from CSV to database
  - Data migration script
  - Backward compatibility layer

- [ ] **v2.0.0** - User authentication and authorization
  - User login system
  - Session management
  - Role-based access control
  - Password hashing and security

- [ ] **v2.1.0** - Enhanced search and filtering
  - Search users by name/email
  - Filter by role
  - Pagination for large datasets

- [ ] **v2.2.0** - Advanced features
  - User activity logging
  - Bulk operations
  - Import/export (CSV, JSON)

- [ ] **v3.0.0** - API and testing
  - RESTful API endpoints
  - Unit and integration tests
  - API documentation

---

**Version Strategy:**
- **v1.x** - CSV-based, file storage
- **v2.0** - Database backend (breaking change)
- **v3.0** - API layer and modern architecture

---

## Support

For issues, questions, or contributions:

- Report bugs: [GitHub Issues](https://github.com/EsderJ10/CRUDle/issues)
- Star the project on GitHub
- Read the documentation in `docs/` directory
- Check FAQ in README.md

---

## License

Copyright © 2025 José Antonio Cortés Ferre

Licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

**Last Updated:** November 4, 2025
