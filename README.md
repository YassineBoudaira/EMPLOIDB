# 🚀 EMPLOIDB - Job Portal Management System

[![PHP Version](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-green.svg)](https://mysql.com)
[![Security](https://img.shields.io/badge/Security-Enterprise%20Grade-red.svg)](https://owasp.org)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## 📋 Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Security Features](#security-features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Backup & Restore](#backup--restore)
- [Contributing](#contributing)
- [License](#license)

---

## 🎯 Overview

**EMPLOIDB** is a comprehensive job portal management system built with PHP and MySQL. It provides a secure, user-friendly platform for job seekers and employers to connect, with advanced security features and a modern user interface.

### 🏆 Key Highlights
- **Enterprise-Grade Security** - Argon2ID password hashing, SQL injection protection, XSS protection
- **User-Friendly Interface** - Modern, responsive design with intuitive navigation
- **Comprehensive Admin Panel** - Full administrative control over users, jobs, and content
- **Secure File Upload System** - Protected file uploads with validation and sanitization
- **Backup & Restore System** - Complete version control with easy restoration

---

## ✨ Features

### 👥 User Management
- **User Registration & Authentication** - Secure signup and login system
- **Role-Based Access Control** - Admin and regular user roles
- **Profile Management** - Complete user profile customization
- **Password Management** - Secure password change functionality

### 💼 Job Management
- **Job Posting** - Create and manage job announcements
- **Job Search & Filtering** - Advanced search with multiple criteria
- **Application System** - Secure job application submission
- **File Upload Support** - CV and document upload functionality

### 🛠️ Admin Features
- **User Management** - Complete user administration
- **Content Management** - Manage jobs, categories, and content
- **System Monitoring** - Error logging and system health
- **Security Dashboard** - Security status and monitoring

### 🔒 Security Features
- **Argon2ID Password Hashing** - Industry-standard password security
- **SQL Injection Protection** - Prepared statements for all database queries
- **XSS Protection** - Output escaping and sanitization
- **CSRF Protection** - Cross-site request forgery protection
- **Secure File Uploads** - MIME type validation and secure naming
- **Session Security** - Secure session management with timeout

---

## 🔐 Security Features

### Password Security
- **Argon2ID Hashing** - Latest password hashing algorithm
- **Password Strength Validation** - Minimum 8 characters required
- **Secure Password Change** - Verified current password requirement

### Database Security
- **Prepared Statements** - All queries use parameterized statements
- **Input Sanitization** - Comprehensive input cleaning and validation
- **Error Handling** - Secure error messages without information disclosure
- **Connection Security** - PDO with secure configuration

### Session Security
- **Secure Cookies** - HttpOnly, Secure, SameSite attributes
- **Session Regeneration** - Periodic session ID regeneration
- **Timeout Handling** - Automatic session timeout
- **Cleanup** - Proper session destruction on logout

### File Upload Security
- **MIME Type Validation** - Only allowed file types accepted
- **Size Limits** - Configurable file size restrictions
- **Secure Filenames** - Generated filenames prevent path traversal
- **Directory Validation** - Upload directory security checks

---

## 🚀 Installation

### Prerequisites
- **PHP 7.4 or higher**
- **MySQL 5.7 or higher**
- **Web Server** (Apache/Nginx)
- **Composer** (for dependency management)

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/EMPLOIDB.git
cd EMPLOIDB
```

### Step 2: Database Setup
```sql
-- Create database
CREATE DATABASE emploi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import database structure (if available)
mysql -u root -p emploi < database/schema.sql
```

### Step 3: Configuration
1. Copy the configuration template:
```bash
cp include/config.example.php include/config.php
```

2. Edit `include/config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'emploi');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 4: Set Permissions
```bash
# Set proper permissions for upload directories
chmod 755 upload/
chmod 755 fichiers/
chmod 755 logs/
```

### Step 5: Web Server Configuration
Configure your web server to point to the project directory and ensure PHP is properly configured.

---

## ⚙️ Configuration

### Database Configuration
Edit `include/config.php` to configure your database connection:

```php
// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'emploi');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_EXPIRY', 1800); // 30 minutes

// File upload settings
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx']);
```

### Security Configuration
The system includes comprehensive security settings that can be customized in `include/config.php`:

- **Error Reporting** - Configure for development/production
- **Security Headers** - XSS protection, content type options
- **Session Security** - Cookie settings and timeout
- **File Upload Limits** - Size and type restrictions

---

## 📖 Usage

### Default Login Credentials

#### Admin Access
- **Username:** `admin`
- **Password:** `admin123`
- **Role:** Administrator

#### Regular User Access
- **Username:** `user`
- **Password:** `user123`
- **Role:** User

### Getting Started

1. **Access the Application**
   - Navigate to your web server URL
   - You'll be redirected to the login page

2. **First Login**
   - Use the default credentials above
   - Change your password immediately after login

3. **Admin Panel**
   - Access full administrative features
   - Manage users, jobs, and system settings

4. **User Features**
   - Browse job listings
   - Apply for positions
   - Manage your profile

### Key Features Usage

#### Job Management
- **Create Jobs:** Admin can create new job postings
- **Edit Jobs:** Modify existing job details
- **Delete Jobs:** Remove outdated positions
- **Search Jobs:** Advanced filtering and search

#### User Management
- **User Registration:** New users can sign up
- **Profile Updates:** Users can modify their profiles
- **Password Changes:** Secure password management
- **Admin Control:** Complete user administration

#### File Management
- **Secure Uploads:** CV and document uploads
- **File Validation:** Type and size checking
- **Secure Storage:** Protected file storage

---

## 📚 API Documentation

### Authentication Endpoints

#### Login
```http
POST /login.php
Content-Type: application/x-www-form-urlencoded

user=username&pass=password&csrf_token=token
```

#### Logout
```http
GET /logout.php
```

#### Register
```http
POST /signup.php
Content-Type: application/x-www-form-urlencoded

user=username&pass=password&email=email&csrf_token=token
```

### Job Management Endpoints

#### Get Jobs
```http
GET /index.php
```

#### Get Job Details
```http
GET /annoncedetaile.php?ida=job_id
```

#### Search Jobs
```http
GET /filtrage.php?idd=domain_id&idv=city_id
```

### Security Notes
- All endpoints require CSRF token validation
- Input validation is enforced on all parameters
- Output is properly escaped to prevent XSS
- Session management is required for authenticated endpoints

---

## 🔄 Backup & Restore

The project includes a comprehensive backup and restore system with multiple versions:

### Available Versions
- **Version 1.0.0.1** - Initial project state
- **Version 1.0.0.2** - Enhanced security and functionality

### Restore Commands

#### PowerShell (Windows)
```powershell
# Restore to version 1.0.0.1
.\restore_v1.0.0.1.ps1

# Restore to version 1.0.0.2
.\restore_v1.0.0.2.ps1
```

#### Batch (Windows)
```batch
# Restore to version 1.0.0.1
restore_v1.0.0.1.bat

# Restore to version 1.0.0.2
restore_v1.0.0.2.bat
```

#### Manual Restore
1. Delete current files (except backup folders and .git)
2. Copy files from desired backup folder to root directory

### Backup Features
- **Automatic Current State Backup** - Before restoration
- **Git Repository Preservation** - .git folder never deleted
- **Timestamped Backups** - Unique backup names
- **Error Handling** - Clear error messages

---

## 🤝 Contributing

We welcome contributions to improve EMPLOIDB! Please follow these guidelines:

### Development Setup
1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes
4. Test thoroughly
5. Commit with descriptive messages
6. Push to your branch
7. Create a Pull Request

### Code Standards
- **PHP Standards:** Follow PSR-12 coding standards
- **Security:** All code must pass security review
- **Documentation:** Update documentation for new features
- **Testing:** Include tests for new functionality

### Commit Message Format
```
type(scope): description

[optional body]

[optional footer]
```

Examples:
```
feat(auth): add two-factor authentication
fix(security): resolve SQL injection vulnerability
docs(readme): update installation instructions
```

### Pull Request Guidelines
- **Clear Description** - Explain what the PR does
- **Security Review** - Ensure no security vulnerabilities
- **Testing** - Include test cases
- **Documentation** - Update relevant docs

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

### License Summary
- **Commercial Use** - ✅ Allowed
- **Modification** - ✅ Allowed
- **Distribution** - ✅ Allowed
- **Private Use** - ✅ Allowed
- **Liability** - ❌ No liability
- **Warranty** - ❌ No warranty

---

## 🆘 Support

### Getting Help
- **Documentation** - Check this README and project docs
- **Issues** - Report bugs via GitHub Issues
- **Security** - Report security issues privately
- **Community** - Join our community discussions

### Common Issues
- **Login Problems** - Check credentials and database connection
- **File Upload Issues** - Verify directory permissions
- **Database Errors** - Check configuration and MySQL status
- **Security Warnings** - Review security settings

### Contact Information
- **Project Maintainer** - [Your Name](mailto:your.email@example.com)
- **GitHub Issues** - [Report Issues](https://github.com/yourusername/EMPLOIDB/issues)
- **Security Contact** - [Security Issues](mailto:security@example.com)

---

## 🏆 Acknowledgments

- **OWASP** - Security guidelines and best practices
- **PHP Community** - Language and framework support
- **MySQL Team** - Database system
- **Contributors** - All project contributors

---

## 📊 Project Statistics

- **Version:** 1.0.0.2
- **Last Updated:** December 8, 2025
- **Security Level:** Enterprise Grade
- **Compatibility:** PHP 7.4+, MySQL 5.7+
- **License:** MIT

---

**⭐ Star this repository if you find it helpful!**

**🔒 Security-focused job portal management system for modern web applications.**