# Moodle CodeRunner Academy - Project Overview

## Executive Summary

This is a **Moodle 4.0 Learning Management System** enhanced with the **CodeRunner plugin** for automated code execution and assessment. It's designed to provide educators with a secure, scalable platform for teaching programming through hands-on code challenges with real-time execution and instant feedback.

---

## Project Objectives

### Primary Goals

1. **Secure Code Execution Environment**
   - Execute student code safely in isolated containers
   - Prevent malicious code from harming the system
   - Maintain system stability during code testing

2. **Automated Code Assessment**
   - Run test cases against student submissions
   - Provide instant feedback on code correctness
   - Generate detailed grading reports

3. **Scalable Deployment**
   - Run locally for development and testing
   - Deploy to cloud (Render.com) for production
   - Support multiple concurrent users

4. **Educational Excellence**
   - Enable interactive programming education
   - Support multiple programming languages (Python, PHP, JavaScript, C)
   - Provide immediate learning feedback

---

## Project Motive

### Why This Project?

**Problem Being Solved:**
- Traditional programming courses lack real-time code execution capabilities
- Manual code review is time-consuming for instructors
- Students need immediate feedback to learn programming effectively
- Scaling programming education requires automated assessment

**Solution Provided:**
- CodeRunner plugin turns Moodle into an **intelligent code testing platform**
- Automatic evaluation of student code against test cases
- Secure sandboxed execution preventing system compromise
- Supports asynchronous and real-time feedback loops
- Enables educators to teach programming at scale

---

## Technology Stack

### Core Technologies

| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| **LMS** | Moodle | 4.0 | Learning management system foundation |
| **Code Plugin** | CodeRunner | Latest | Automated code execution & grading |
| **PHP** | PHP | 8.2 | Backend server-side language |
| **Database** | PostgreSQL / MariaDB | 15 / 10.4 | Data persistence |
| **Web Server** | Apache | 2.4 | HTTP server |
| **Containerization** | Docker | Latest | Consistent deployment environments |
| **Deployment** | Render.com | - | Cloud hosting platform |

### Key Features & Extensions

1. **Database Abstraction**
   - Support for PostgreSQL (production)
   - Support for MariaDB/MySQL (local development)
   - Automatic database type detection

2. **Security Features**
   - Secure code submission handler with validation
   - Code audit for detecting security issues
   - SQL injection prevention through parameterized queries
   - CSRF protection and session management

3. **Programming Languages**
   - **PHP** - Primary language for server-side testing
   - **Python** - Data processing and algorithms
   - **JavaScript** - Client-side and Node.js code
   - **C** - Systems programming challenges

4. **Code Validation**
   - Syntax checking before execution
   - Pattern detection for suspicious code
   - Audit logging of all submissions

---

## Architecture

### System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   Student/Instructor                      │
│                  (Web Browser Access)                     │
└──────────────────────┬──────────────────────────────────┘
                       │
                       │ HTTPS/HTTP
                       │
┌─────────────────────────────────────────────────────────┐
│                  Reverse Proxy Layer                      │
│              (Render/Local Apache)                        │
└──────────────────────┬──────────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ▼              ▼              ▼
   ┌─────────┐   ┌─────────┐   ┌──────────┐
   │ Moodle  │   │ CodeRunner   │ PHP API  │
   │  Core   │   │  Plugin      │ Handlers │
   └────┬────┘   └──────┬──────┘ └────┬─────┘
        │               │             │
        └───────┬───────┼─────────────┘
                │       │
         ┌──────▼───────▼──────┐
         │  Session Management │
         │  User Authentication │
         │  Grade Storage      │
         └────────┬────────────┘
                  │
        ┌─────────▼──────────┐
        │   PostgreSQL (Render)
        │   MariaDB (Local)
        └────────────────────┘
```

### File Structure

```
moodle/
├── admin/                    # Moodle administration interface
├── lib/                      # Core Moodle libraries
├── public/                   # Web-accessible files
│   ├── index.php            # Moodle entry point
│   ├── config.php           # Database & environment config
│   └── jobe/                # Code execution API endpoints
├── tests_scripts/           # Testing and validation scripts
│   ├── pgsql_verification.php
│   ├── db_connectivity_test.php
│   └── simple_db_test.php
├── question/                # Question types & CodeRunner
├── CodeValidator.php        # Syntax validation
├── CodeAudit.php           # Security code analysis
├── SecureCodeSubmissionHandler.php  # Secure submission handling
├── config.php              # Main configuration (auto-detection)
├── Dockerfile              # Docker image definition
├── docker-compose.yml      # Local development setup
├── render.yaml             # Render deployment config
└── README.md               # Basic documentation
```

---

## Deployment Options

### Option 1: Local Development (Windows XAMPP)

**Environment**: Windows with XAMPP Apache & MariaDB

**Setup:**
```bash
# Database: MariaDB on localhost:3306
# Web Server: Apache on localhost:80
# Moodle URL: http://localhost/public
# Database: moodle (user: root, password: empty)
```

**Configuration in config.php:**
```php
$CFG->wwwroot = 'http://localhost/public';
$CFG->dbtype = 'mariadb';
$CFG->dbhost = 'localhost';
```

### Option 2: Docker Local Development

**Environment**: Docker containers (PHP + Apache + MariaDB)

**Setup:**
```bash
docker-compose up -d
# Moodle URL: http://localhost:8080
# Database: MariaDB in container
```

**Configuration:**
```php
$CFG->wwwroot = 'http://localhost:8080';
$CFG->dbtype = 'mariadb';
$CFG->dbhost = 'mysql';  # Docker service name
```

### Option 3: Render Cloud Deployment

**Environment**: Render.com with PostgreSQL managed database

**Setup:**
```bash
git push origin main  # Trigger Render webhook
# Moodle URL: https://coderunner-academy.onrender.com
# Database: PostgreSQL on Render (from DATABASE_URL env var)
```

**Configuration:**
```php
$CFG->wwwroot = 'http://coderunner-academy.onrender.com';
$CFG->dbtype = 'pgsql';
$CFG->dbhost = getenv('DATABASE_URL');  # Auto-detected
```

---

## Key Components Explained

### 1. Moodle Core (LMS)
- User authentication and authorization
- Course structure and content management
- Grade book and assessment
- Reporting and analytics

### 2. CodeRunner Plugin
- Code submission interface
- Test case execution
- Automated grading
- Feedback generation

### 3. Database Layer
- **PostgreSQL**: Production database (Render)
- **MariaDB/MySQL**: Development database (Local)
- Automatic detection and configuration

### 4. Security Components

#### SecureCodeSubmissionHandler.php
- Validates all code submissions
- Detects suspicious patterns
- Logs audit trail
- Prevents code injection

#### CodeValidator.php
- Checks PHP syntax
- Validates bracket/quote balance
- Detects parsing errors

#### CodeAudit.php
- Scans for dangerous functions (eval, exec, system)
- Detects shell metacharacters
- Language-specific security checks

### 5. Code Execution Engine
- Isolated execution environment
- Test case management
- Result collection and grading
- Real-time feedback

---

## Installation & Setup

### Prerequisites

**Local Development:**
- Windows XAMPP (Apache + PHP 8.2 + MariaDB)
- OR Docker Desktop

**Production:**
- Render.com account
- GitHub repository access
- PostgreSQL database (Render managed)

### Quick Start

#### 1. Local Setup (Windows)
```bash
# Clone repository
git clone https://github.com/kiran2k5-ai/Optimized-Compilation.git
cd moodle

# Run diagnostic test
php public/simple_db_test.php

# Open in browser
# http://localhost/public/

# Complete Moodle installation wizard
```

#### 2. Docker Setup
```bash
# Build and run
docker-compose up -d

# Access at
# http://localhost:8080/public/

# Check logs
docker-compose logs -f moodle
```

#### 3. Render Deployment
```bash
# Push to GitHub (triggers automatic build)
git push origin main

# Visit deployment URL
# https://coderunner-academy.onrender.com

# Monitor deployment
# Render Dashboard → Logs
```

---

## Configuration Details

### config.php - Smart Auto-Detection

The configuration file automatically detects the environment:

```php
// 1. Detects if running in Docker
$is_docker = is_dir('/var/moodledata');

// 2. Detects database type
if ($is_docker && getenv('DATABASE_URL')) {
    // Render PostgreSQL
    $CFG->dbtype = 'pgsql';
    // Parse DATABASE_URL for connection details
} else if ($is_docker) {
    // Docker local MariaDB
    $CFG->dbtype = 'mariadb';
} else {
    // Windows XAMPP MariaDB
    $CFG->dbtype = 'mariadb';
}

// 3. Configures appropriate PHP settings
$CFG->wwwroot = 'http://' . $_SERVER['HTTP_HOST'];
$CFG->sessioncookieinsecure = true;  // Allow HTTP
$CFG->reverseproxy = false;
$CFG->sslproxy = false;
```

### Docker Configuration (Dockerfile)

```dockerfile
FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql pdo_pgsql pgsql \
    mysqli gd intl xml zip mbstring soap

# Configure PHP for Moodle
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/moodle.ini

# Configure PostgreSQL
RUN echo "pgsql.allow_persistent = On" >> /usr/local/etc/php/conf.d/moodle.ini
RUN echo "pgsql.max_persistent = -1" >> /usr/local/etc/php/conf.d/moodle.ini
```

---

## Security Implementation

### Authentication & Authorization
- Moodle user account system
- Role-based access control (RBAC)
- Admin, Instructor, Student roles
- Session management

### Data Protection
- Code submissions stored securely
- Grades encrypted and backed up
- User data access logging
- GDPR compliance considerations

### Code Execution Safety
- Sandboxed execution environment
- Resource limits (CPU, memory)
- Timeout enforcement
- Network isolation

### Audit Trail
- All submissions logged
- Suspicious patterns flagged
- Admin review capability
- Compliance reporting

---

## Testing & Validation

### Included Test Scripts

1. **simple_db_test.php**
   - Tests database connectivity
   - Shows table count
   - Verifies user credentials

2. **db_connectivity_test.php**
   - Extended connectivity testing
   - Connection pooling verification
   - Performance metrics

3. **pgsql_verification.php**
   - Checks PostgreSQL extensions
   - Tests native pg_connect()
   - Validates PDO connections

### Running Tests Locally
```bash
# Test database connection
php public/simple_db_test.php

# Test PostgreSQL extensions
php tests_scripts/pgsql_verification.php

# Run unit tests (if available)
phpunit --bootstrap public/config.php tests/
```

---

## Deployment Checklist

### Before Production Deployment

- [ ] Database configured and accessible
- [ ] PHP extensions installed (pgsql, pdo_pgsql)
- [ ] SSL/TLS certificates valid (if using HTTPS)
- [ ] Moodle config.php correctly configured
- [ ] All code changes committed to git
- [ ] Database backup created
- [ ] Security audit completed
- [ ] Test cases executed
- [ ] Performance load testing passed

### Post-Deployment Verification

- [ ] Moodle login page loads
- [ ] Database connection successful
- [ ] CodeRunner plugin functional
- [ ] Code execution working
- [ ] Grades being saved correctly
- [ ] SSL certificate valid (if HTTPS)
- [ ] Logs being generated
- [ ] Backups running automatically

---

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check DATABASE_URL environment variable
- Verify PostgreSQL is running
- Check credentials in config.php
- Run: `php public/simple_db_test.php`

**CodeRunner Not Working**
- Verify PHP extensions: pdo_pgsql, pgsql
- Check permission on /var/moodledata
- Review Apache error logs
- Check CodeRunner plugin is enabled

**SSL/TLS Errors**
- Ensure wwwroot matches deployment domain
- Check reverse proxy configuration
- Verify certificate validity
- Clear browser cache and cookies

**Performance Issues**
- Check database query performance
- Monitor PHP memory usage
- Review code submission size limits
- Optimize course content

---

## Maintenance

### Regular Tasks

**Daily:**
- Monitor application logs
- Check system resource usage
- Review user activity logs

**Weekly:**
- Database backup verification
- Security vulnerability scanning
- Performance metrics review

**Monthly:**
- Database optimization (VACUUM/ANALYZE)
- Log rotation and archival
- Certificate renewal check
- Dependency updates

**Quarterly:**
- Security audit
- Code review
- Performance optimization
- Backup restoration testing

---

## Support & Documentation

### Documentation Files
- **README.md** - Basic setup instructions
- **config.php** - Configuration with inline comments
- **Dockerfile** - Docker image definition
- **render.yaml** - Render deployment configuration
- **docker-compose.yml** - Local development environment

### External Resources
- [Moodle Documentation](https://docs.moodle.org/)
- [CodeRunner Plugin](https://github.com/trampgeek/moodle-qtype_coderunner)
- [Render.com Docs](https://render.com/docs)
- [Docker Documentation](https://docs.docker.com/)

---

## License & Attribution

- **Moodle**: GNU GPL v3
- **CodeRunner Plugin**: GNU GPL v3
- **Custom Code**: Licensed as per project requirements

---

## Version History

### Current Version: 4.0 with CodeRunner

**Latest Changes:**
- PostgreSQL support for Render deployment
- Secure code submission handling
- Code audit and validation
- Multi-environment configuration
- Docker containerization
- Automated deployment pipeline

---

## Conclusion

This Moodle installation with CodeRunner transforms learning management into an interactive programming education platform. By combining the power of Moodle's LMS capabilities with CodeRunner's automated assessment features, educators can deliver engaging programming courses with real-time feedback and scalable deployment options.

The system is designed for:
- ✅ Security: Sandboxed execution prevents malicious code
- ✅ Scalability: Handles growing student populations
- ✅ Flexibility: Works locally or in the cloud
- ✅ Accessibility: Web-based interface for students and instructors
- ✅ Reliability: Automated testing and continuous integration

**Ready for production deployment and educational use.**
