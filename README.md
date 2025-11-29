# CRUDle

<p align="center">
<img src="public/banner.png" alt="CRUDle Banner" width="100%">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-2.0.0-blue.svg?style=flat-square" alt="Version">
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=flat-square" alt="License">
</p>

<p align="center">
<strong>A modern prototype of an user management system built with Vanilla PHP.</strong>
</p>

<p align="center">
<a href="#-quick-start"><strong>🚀 Quick Start</strong></a> ·
<a href="#-tech-stack"><strong>🛠️ Tech Stack</strong></a> ·
<a href="#-architecture"><strong>📂 Structure</strong></a>
</p>

## 📸 Interface Preview

<table align="center">
<tr>
<td align="center"><strong>☀️ Light Theme</strong></td>
<td align="center"><strong>🌑 Dark Theme</strong></td>
</tr>
<tr>
<td><img src="public/screenshots/dashboard-light.png" alt="Light Mode" width="100%"></td>
<td><img src="public/screenshots/dashboard-dark.png" alt="Dark Mode" width="100%"></td>
</tr>
</table>
<p align="center"><em>Fully responsive mobile-first design included.</em></p>

## 🚀 Quick Start

Get the application running in under 2 minutes using Docker.

### 1. Clone the repo

```bash
git clone https://github.com/EsderJ10/CRUDle.git
```

### 2. Start the container

```bash
cd CRUDle
docker-compose up -d
```

> You can access the app at: http://localhost:8080

<details>
<summary><b>🔧 Manual / XAMPP Installation Instructions</b></summary>

If you prefer not to use Docker:

- Clone the repo: `git clone https://github.com/EsderJ10/CRUDle.git`
- Web Server: Point Apache/Nginx to the CRUDle directory.
- Database: Create a DB named crudle and import docker/init.sql.
- Config: Copy .env.example to .env and update your DB credentials.
- Permissions: Ensure uploads/ and logs/ are writable.

</details>

## ✨ Key Features

Without relying on external frameworks, this project demonstrates some concepts as:

- 🔐 Security First: Implements CSRF tokens, SQL Injection protection (PDO), and XSS sanitization.
- 🎨 Modern UX: Real-time dashboard, persistent Dark/Light mode, and smooth CSS transitions.
- 🏗️ MVC Architecture: Strict separation of Business Logic, Data Access, and Views.
- 🖼️ Media Handling: Secure avatar upload validation (MIME type checks) and storage.

## 🛠️ Tech Stack

<p align="center">
<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
<img src="https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB">
<img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
<img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
<img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
<img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
</p> 

## 🏗️ Architecture

CRUDle follows a MVC-inspired structure.

<details>
<summary><b>📂 View Directory Structure</b></summary>

```php
CRUDle/
├── assets/          # Static assets (CSS, JS, Images)
├── config/          # Environment & Database configuration
├── lib/             # Core Framework
│   ├── business/    # Business Layer (Business Logic)
│   ├── core/        # Core Layer (DB Singleton, Sesion, Security)
│   ├── helpers/     # Utilities & Enums
│   └── presentation/ # Presentation Layer (Views)
├── pages/           # Route Controllers
│   ├── auth/        # Authentication (Login, Register, Reset Password)
│   └── users/       # Users (Create, Read, Update, Delete)
├── views/           # UI Components & Partials
│   ├── components/  # UI Components (forms)
│   └── partials/    # UI Partials (headers, footers, etc.)
├── docker/          # Container configuration
└── index.php        # Application Entry Point
```

</details>

## 📄 License & Author

Distributed under the MIT License.

<div align="center">
<strong>Made with ❤️ and ☕ by <a href="https://github.com/EsderJ10">EsderJ10</a></strong>
<br>
<sub>If you find this project useful, please consider giving it a ⭐ on GitHub!</sub>
</div>