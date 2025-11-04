# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

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
