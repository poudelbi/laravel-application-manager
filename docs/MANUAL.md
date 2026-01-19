# Laravel Application Manager - Complete Manual

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [System Architecture](#system-architecture)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Usage Guide](#usage-guide)
7. [API Documentation](#api-documentation)
8. [Troubleshooting](#troubleshooting)
9. [Security Considerations](#security-considerations)
10. [Best Practices](#best-practices)

## Overview

The Laravel Application Manager is a comprehensive web-based tool designed to simplify the management of multiple Laravel applications. It provides an intuitive interface for developers to create, deploy, manage, and monitor Laravel applications with automatic port allocation and configuration management.

The application serves as a centralized hub for managing multiple Laravel projects, allowing developers to quickly spin up new applications, clone existing repositories, and manage their lifecycle from a single dashboard.

## Features

### Core Functionality
- **Git Repository Deployment**: Clone and deploy applications directly from Git repositories (public and private)
- **Automatic Port Allocation**: Assigns unique ports starting from 8000 (app1:8000, app2:8001, etc.)
- **Nginx Configuration Management**: Generate and manage nginx configurations for each application
- **Application Lifecycle Management**: Complete start, stop, update, refresh, and delete functionality
- **Multi-user Authentication**: Secure access with Laravel Breeze authentication
- **Comprehensive Testing**: Built-in test dashboard for all functionality

### Application Management
- **Create Applications**: From scratch or from Git repositories
- **Deploy Applications**: Copy applications to web root for public access
- **Update Applications**: Pull latest changes from Git repositories
- **Refresh Applications**: Clear Laravel caches and configurations
- **Delete Applications**: Complete removal with configuration cleanup

### Site Management
- **Port Assignment**: Automatic sequential port allocation
- **Start/Stop Operations**: Control applications on their assigned ports
- **Direct Access**: Visit applications at their dedicated URLs
- **Status Monitoring**: Real-time running status of applications

### Configuration Management
- **Environment Configuration**: Edit .env files for each application
- **Application Key Generation**: Generate Laravel application keys
- **Dependency Management**: Composer and NPM operations
- **Database Operations**: Migrations and seeding
- **Asset Building**: Frontend asset compilation

## System Architecture

### Directory Structure
```
/var/www/html/master-app/          # Main application (the manager)
/var/www/html/applications/        # Managed applications directory
```

### Key Components
- **Dashboard Controller**: Manages the main dashboard and application overview
- **Application Controller**: Handles application creation, deletion, and setup
- **Sites Controller**: Manages application lifecycle (start, stop, update, etc.)
- **Test Dashboard Controller**: Provides comprehensive testing functionality

### Technology Stack
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Breeze
- **Frontend**: Blade templates with Tailwind CSS
- **Database**: SQLite (default) or MySQL/PostgreSQL
- **Server**: Nginx with PHP-FPM
- **Development Server**: Laravel's built-in development server

## Installation

### Prerequisites
- **Operating System**: Linux (Ubuntu/Debian recommended)
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Git**: Latest version
- **Nginx**: Latest version
- **MySQL/PostgreSQL/SQLite**: For database operations
- **Sudo Access**: For system-level operations

### System Dependencies Installation

```bash
# Update package list
sudo apt update

# Install PHP 8.2 and extensions
sudo apt install php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-json php8.2-xml php8.2-intl php8.2-sqlite3 php8.2-xdebug

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Git
sudo apt install git

# Install Nginx
sudo apt install nginx

# Install Node.js and npm (for frontend operations)
curl -fsSL https://deb.nodesource.com/setup_lts | sudo -E bash -
sudo apt install -y nodejs
```

### Application Installation

#### 1. Clone the Repository
```bash
git clone https://github.com/your-organization/laravel-app-manager.git /var/www/html/master-app
cd /var/www/html/master-app
```

#### 2. Install PHP Dependencies
```bash
composer install
```

#### 3. Configure Environment
```bash
cp .env.example .env
```

Edit the `.env` file with your specific configuration:
```env
APP_NAME=Laravel Application Manager
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/master-app/database/database.sqlite

# Sudo Credentials (for system operations)
SUDO_PASSWORD=your_sudo_password
SUDO_USERNAME=your_sudo_username

# Applications Directory
APPLICATIONS_DIR=/var/www/html/applications

# Cache Configuration
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

#### 4. Generate Application Key
```bash
php artisan key:generate
```

#### 5. Set Up Database
For SQLite (default):
```bash
touch database/database.sqlite
```

For MySQL/PostgreSQL, create the database and update credentials in `.env`.

Run migrations:
```bash
php artisan migrate
```

#### 6. Configure File Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/master-app
sudo chmod -R 755 /var/www/html/master-app
sudo chmod -R 775 /var/www/html/master-app/storage
sudo chmod -R 775 /var/www/html/master-app/bootstrap/cache
```

#### 7. Create Applications Directory
```bash
sudo mkdir -p /var/www/html/applications
sudo chown -R www-data:www-data /var/www/html/applications
sudo chmod -R 775 /var/www/html/applications
```

#### 8. Configure Sudo Permissions
Create a sudoers file to allow the web server to run specific commands:
```bash
sudo visudo -f /etc/sudoers.d/laravel-app-manager
```

Add the following content:
```
www-data ALL=(ALL) NOPASSWD: /usr/bin/git, /bin/rm, /bin/mkdir, /bin/chown, /usr/bin/composer, /usr/bin/npm, /usr/sbin/nginx, /usr/bin/php
```

#### 9. Configure Web Server
Create an Nginx configuration for the application:
```bash
sudo nano /etc/nginx/sites-available/laravel-app-manager
```

Add the following configuration:
```
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/master-app/public;
    index index.php index.html index.htm;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/laravel-app-manager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 10. Start the Application
For development:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

For production, consider using a process manager like Supervisor or systemd.

### Post-Installation Steps

#### 1. Create Admin User
Access the application and register an admin user through the registration form, or create one via artisan:
```bash
php artisan tinker
>>> App\Models\User::create(['name' => 'Admin', 'email' => 'admin@yourdomain.com', 'password' => bcrypt('your_password'), 'email_verified_at' => now()])
```

#### 2. Verify Installation
Navigate to your domain and verify:
- The application loads without errors
- You can access the dashboard
- Authentication works properly
- You can create a test application

#### 3. Configure SSL (Recommended)
For production environments, configure SSL using Let's Encrypt:
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## Configuration

### Environment Variables

The Laravel Application Manager uses several environment variables for configuration. These are defined in the `.env` file.

#### Core Application Settings
```
APP_NAME=Laravel Application Manager
APP_ENV=production
APP_KEY= # Generated with php artisan key:generate
APP_DEBUG=false  # Set to true for development
APP_URL=http://your-domain.com
```

#### Database Configuration
```
DB_CONNECTION=sqlite  # Options: mysql, postgres, sqlite, sqlsrv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=/var/www/html/master-app/database/database.sqlite
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

#### Sudo Configuration
```
SUDO_PASSWORD=your_sudo_password  # Password for sudo operations
SUDO_USERNAME=your_sudo_username  # Username for sudo operations
```

#### Custom Applications Directory
```
APPLICATIONS_DIR=/var/www/html/applications  # Default location for managed applications
```

### Port Configuration

Applications are assigned ports starting from 8000:
- First application: 8000
- Second application: 8001
- Third application: 8002
- And so on...

This can be customized in the `SitesController` by modifying the `$startingPort` property.

### Nginx Configuration

The application generates nginx configurations in:
- **Available sites**: `/etc/nginx/sites-available/`
- **Enabled sites**: `/etc/nginx/sites-enabled/`

## Usage Guide

### Getting Started

#### Accessing the Application
1. Open your browser and navigate to `http://server.poudelbijaya.com.np:8000`
2. Register a new account or log in with existing credentials
3. You'll be taken to the dashboard where you can manage applications

#### Dashboard Overview
The dashboard provides:
- Total application count
- Deployed application count
- Quick access to create new applications
- List of recent applications with ports and access links

### Application Management

#### Creating New Applications

##### From Scratch
1. Click "Create New Application" on the dashboard or applications page
2. Fill in the application name
3. Select the PHP version
4. Click "Create Application"
5. The system will create a basic Laravel application structure

##### From Git Repository
1. Click "Create New Application"
2. Enter the Git repository URL (e.g., `https://github.com/username/repo.git`)
3. If it's a private repository, enter your personal access token
4. Enter the application name
5. Select the PHP version
6. Click "Create Application"
7. The system will clone the repository and set up the application

##### Deploy Official Laravel
1. Click the "Deploy Official Laravel" button on the applications page
2. The system will:
   - Clone the official Laravel repository
   - Run composer install to install dependencies
   - Generate a new application key
   - Create and configure the .env file
   - Set up the SQLite database
   - Run database migrations and seeders
   - Install and build frontend assets (if package.json exists)
   - Set proper file permissions
   - Assign the next available port starting from 8001

#### Managing Applications

##### Deploying Applications
1. Go to the Applications list
2. Find the application you want to deploy
3. Click the "Deploy" button
4. The application will be copied to the web root with proper permissions

##### Deleting Applications
1. Go to the Applications list
2. Find the application you want to delete
3. Click the "Delete" button in the form
4. Confirm the deletion when prompted
5. The application and all its files will be removed

### Site Management

#### Starting Applications
1. Go to the Sites section (`/sites`)
2. Find the application you want to start
3. Click the "Start" button
4. The application will start on its assigned port
5. You can access it at `http://server.poudelbijaya.com.np:PORT`

#### Stopping Applications
1. Go to the Sites section (`/sites`)
2. Find the running application you want to stop
3. Click the "Stop" button
4. The application will be stopped

#### Updating Applications
1. Go to the Sites section (`/sites`)
2. Find the application you want to update
3. Click the "Update" button
4. The system will pull the latest changes from the Git repository
5. Run composer install and npm install if needed

#### Refreshing Applications
1. Go to the Sites section (`/sites`)
2. Find the application you want to refresh
3. Click the "Refresh" button
4. The system will clear Laravel caches (config, cache, view, route)

#### Creating Nginx Configuration
1. Go to the Sites section (`/sites`)
2. Find the application you want to configure
3. Click the "Nginx Config" button
4. The system will generate nginx configuration for the application
5. The configuration will be placed in `/etc/nginx/sites-available/`

### Application Setup Process

#### After Git Clone

When you create an application from a Git repository, you'll be redirected to the setup page which guides you through:

##### 1. Environment Configuration
- Edit the `.env` file with your application settings
- Configure database connections
- Set application name, environment, debug mode, etc.

##### 2. Application Key Generation
- Generate a new application key with `php artisan key:generate`
- This is required for Laravel's encryption services

##### 3. Dependency Installation
- Run `composer install` to install PHP dependencies
- Run `npm install` to install Node.js dependencies

##### 4. Frontend Asset Building
- Run `npm run build` to build frontend assets
- This compiles CSS, JavaScript, and other frontend resources

##### 5. Database Operations
- Run `php artisan migrate` to run database migrations
- Run `php artisan db:seed` to seed the database with initial data

##### 6. Storage Linking
- Run `php artisan storage:link` to create symbolic links

### Test Dashboard

#### Running Individual Tests

The Test Dashboard provides comprehensive testing for all functionality:

##### Repository Cloning Test
- Tests the ability to clone Git repositories
- Verifies key Laravel files are present after cloning

##### Directory Creation Test
- Tests the creation of proper Laravel application structure
- Verifies all necessary directories are created

##### File Verification Test
- Confirms key Laravel files exist after operations
- Checks for composer.json, artisan, app directory, etc.

##### Sudo Integration Test
- Verifies sudo operations work correctly
- Tests system-level operations requiring elevated privileges

##### Permission Management Test
- Tests proper file permissions for web server access
- Verifies storage and cache directories have proper permissions

##### Deployment Flow Test
- Tests the complete application deployment process
- Verifies all steps in the deployment workflow

##### Composer Install Test
- Tests the composer install functionality for Laravel applications
- Verifies dependencies are installed properly

##### Application Start Test
- Tests the application startup functionality on a dedicated port
- Verifies applications can be started on assigned ports

##### Nginx Config Generation Test
- Tests the nginx configuration generation for applications
- Verifies nginx configurations are created properly

##### Database Migration Test
- Tests the database migration functionality for Laravel applications
- Verifies migrations run successfully

##### Cache Clearing Test
- Tests the cache clearing functionality for Laravel applications
- Verifies all Laravel caches can be cleared

##### App Key Generation Test
- Tests the application key generation functionality
- Verifies APP_KEY is properly generated and stored

### Port Management

#### Automatic Port Assignment

The system automatically assigns ports to applications:
- Master application: Port 8000
- First deployed application: Port 8001
- Second deployed application: Port 8002
- Third deployed application: Port 8003
- And so on...

#### Public Accessibility

All deployed applications are configured to be accessible publicly:
- Applications bind to 0.0.0.0 (all interfaces) instead of localhost
- Each application runs on its assigned port and is accessible via the server's domain
- Access deployed applications at: `http://server.poudelbijaya.com.np:PORT`
- Example: First application at `http://server.poudelbijaya.com.np:8001`

#### Accessing Applications by Port

Each application is accessible at:
`http://server.poudelbijaya.com.np:PORT`

Where PORT is the assigned port number.

#### Port Status Indicators

- **Green indicator**: Application is running on its assigned port
- **Red indicator**: Application is not running
- **"Visit" button**: Direct access to running applications
- **"Start" button**: Start stopped applications

## API Documentation

### Authentication

All API endpoints require authentication. The application uses Laravel's built-in authentication system with Laravel Breeze.

### Application Management Endpoints

#### GET /applications
Retrieve a list of all applications.

**Response:**
```json
{
    "applications": [
        {
            "name": "application-name",
            "path": "/var/www/html/applications/application-name",
            "created_at": 1678886400,
            "laravel_version": "^9.0",
            "php_version": "^8.0",
            "port": 8000,
            "is_running": true,
            "url": "http://server.poudelbijaya.com.np:8000"
        }
    ]
}
```

#### POST /applications
Create a new application.

**Parameters:**
- `name` (string, required): The name of the application
- `version` (string, required): PHP version (8.0, 8.1, 8.2, 8.3, 8.4)
- `repo_url` (string, optional): Git repository URL
- `access_token` (string, optional): Personal access token for private repositories

**Response:**
```json
{
    "success": true,
    "message": "Application created successfully",
    "redirect_to": "/applications/{name}/setup"
}
```

#### DELETE /applications/{name}
Delete an application.

**Parameters:**
- `name` (string, required): The name of the application to delete

**Response:**
```json
{
    "success": true,
    "message": "Application deleted successfully"
}
```

#### POST /applications/{name}/deploy
Deploy an application to the web root.

**Parameters:**
- `name` (string, required): The name of the application to deploy

**Response:**
```json
{
    "success": true,
    "message": "Application deployed successfully"
}
```

#### GET /applications/{name}/setup
Get the setup page for an application.

**Response:**
- HTML page with setup form

#### POST /applications/{name}/setup
Store setup configuration for an application.

**Parameters:**
- `environment` (string, optional): Environment configuration
- `generate_key` (boolean, optional): Whether to generate application key
- `php_version` (string, required): PHP version
- `run_commands` (array, optional): Array of commands to run (composer, npm, migrate, seed)

**Response:**
```json
{
    "success": true,
    "message": "Application setup completed successfully"
}
```

#### GET /applications/{name}/nginx-config
Get nginx configuration for an application.

**Response:**
```json
{
    "name": "application-name",
    "config": "server {\n    listen 80;\n    server_name application-name.local;\n    # ... rest of config\n}",
    "path": "/var/www/html/applications/application-name",
    "php_version": "8.1"
}
```

### Site Management Endpoints

#### GET /sites
Retrieve a list of all sites with port assignments.

**Response:**
```json
{
    "sites": [
        {
            "name": "application-name",
            "path": "/var/www/html/applications/application-name",
            "created_at": 1678886400,
            "laravel_version": "^9.0",
            "php_version": "^8.0",
            "is_running": true,
            "port": 8000,
            "url": "http://server.poudelbijaya.com.np:8000"
        }
    ]
}
```

#### POST /sites/{name}/start
Start an application on its assigned port.

**Parameters:**
- `name` (string, required): The name of the application to start

**Response:**
```json
{
    "success": true,
    "message": "Application started successfully on port 8000"
}
```

#### POST /sites/{name}/stop
Stop an application running on its assigned port.

**Parameters:**
- `name` (string, required): The name of the application to stop

**Response:**
```json
{
    "success": true,
    "message": "Application stopped successfully"
}
```

#### POST /sites/{name}/update
Update an application from its Git repository.

**Parameters:**
- `name` (string, required): The name of the application to update

**Response:**
```json
{
    "success": true,
    "message": "Application updated successfully"
}
```

#### POST /sites/{name}/refresh
Refresh an application by clearing caches.

**Parameters:**
- `name` (string, required): The name of the application to refresh

**Response:**
```json
{
    "success": true,
    "message": "Application refreshed successfully"
}
```

#### POST /sites/{name}/nginx-config
Create nginx configuration for an application.

**Parameters:**
- `name` (string, required): The name of the application

**Response:**
```json
{
    "success": true,
    "message": "Nginx configuration created successfully"
}
```

#### DELETE /sites/{name}
Remove nginx configuration for an application (does not delete the application).

**Parameters:**
- `name` (string, required): The name of the application

**Response:**
```json
{
    "success": true,
    "message": "Nginx configuration removed successfully"
}
```

### Test Dashboard Endpoints

#### GET /test-dashboard
Display the test dashboard with available tests.

**Response:**
- HTML page with test dashboard interface

#### POST /test-dashboard/run/{testName}
Run a specific test.

**Parameters:**
- `testName` (string, required): The name of the test to run

Available test names:
- `repository_cloning`
- `directory_creation`
- `file_verification`
- `sudo_integration`
- `permission_management`
- `deployment_flow`
- `composer_install`
- `application_start`
- `nginx_config_generation`
- `database_migration`
- `cache_clearing`
- `app_key_generation`
- `all_tests`

**Response:**
```json
{
    "test_name": "repository_cloning",
    "status": "completed",
    "timestamp": "2026-01-15T16:30:00.000000Z",
    "output": "Repository cloned successfully...",
    "success": true
}
```

### Deploy Official Laravel Endpoint

#### POST /applications/deploy-official-laravel
Deploy the official Laravel application with test migration and seeding.

**Response:**
```json
{
    "success": true,
    "message": "Official Laravel application deployed with test migration and seeding!"
}
```

### Error Responses

All error responses follow this format:
```json
{
    "success": false,
    "message": "Error message describing the issue",
    "error_code": 404
}
```

### HTTP Status Codes

- `200 OK`: Request successful
- `302 Found`: Redirect response (common for authenticated routes)
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Access denied
- `404 Not Found`: Resource not found
- `500 Internal Server Error`: Server error

## Troubleshooting

### Common Issues and Solutions

#### 500 Internal Server Error

##### Issue: Sites page returns 500 error
**Symptoms:** Accessing `/sites` returns a 500 Internal Server Error
**Causes:**
- Timeout when scanning applications directory
- Large composer.json or package.json files
- Permission issues accessing application directories
- Missing sudo permissions

**Solutions:**
1. Check Laravel logs:
   ```bash
   tail -n 50 /var/www/html/master-app/storage/logs/laravel.log
   ```
2. Verify applications directory permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/applications
   sudo chmod -R 755 /var/www/html/applications
   ```
3. Check for large JSON files that might cause timeouts
4. Verify sudo configuration in `.env` file

##### Issue: General 500 errors
**Symptoms:** Any page returns 500 Internal Server Error
**Solutions:**
1. Check file permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/master-app
   sudo chmod -R 755 /var/www/html/master-app
   ```
2. Clear Laravel caches:
   ```bash
   cd /var/www/html/master-app
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

#### Permission Denied Errors

##### Issue: Cannot create directories
**Symptoms:** Error creating application directories
**Causes:** Web server lacks permissions to write to applications directory
**Solutions:**
1. Set proper ownership:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/applications
   ```
2. Set proper permissions:
   ```bash
   sudo chmod -R 775 /var/www/html/applications
   ```

##### Issue: Cannot delete applications
**Symptoms:** Delete operations fail with permission errors
**Causes:** Files owned by root or other user
**Solutions:**
1. Ensure sudo credentials are configured in `.env`:
   ```
   SUDO_PASSWORD=your_password
   SUDO_USERNAME=your_username
   ```
2. Verify sudo permissions for www-data user

#### Git Clone Failures

##### Issue: Git clone fails
**Symptoms:** Error when cloning from Git repository
**Causes:**
- Invalid repository URL
- Insufficient permissions
- Invalid access token
- Network connectivity issues

**Solutions:**
1. Verify repository URL is correct
2. Check access token permissions (for private repos)
3. Test git command manually:
   ```bash
   git clone https://github.com/username/repo.git /tmp/test-clone
   ```
4. Ensure Git is installed: `which git`

#### Port Binding Issues

##### Issue: Cannot start application on assigned port
**Symptoms:** Start operation fails, application not accessible on assigned port
**Causes:**
- Port already in use
- Insufficient privileges
- Firewall blocking

**Solutions:**
1. Check if port is in use:
   ```bash
   sudo netstat -tuln | grep :PORT_NUMBER
   ```
2. Kill any processes using the port:
   ```bash
   sudo lsof -i :PORT_NUMBER -t | xargs kill -9
   ```
3. Verify the application is properly configured to run on the port

#### Nginx Configuration Issues

##### Issue: Cannot create nginx configuration
**Symptoms:** Nginx config creation fails
**Causes:**
- Insufficient sudo permissions
- Nginx not installed
- Configuration file conflicts

**Solutions:**
1. Verify nginx is installed: `which nginx`
2. Check sudo configuration in `.env`
3. Verify nginx configuration directories exist:
   ```bash
   sudo mkdir -p /etc/nginx/sites-available
   sudo mkdir -p /etc/nginx/sites-enabled
   ```
4. Test nginx configuration: `sudo nginx -t`

#### Composer Install Failures

##### Issue: Composer install fails
**Symptoms:** Error during composer install operation
**Causes:**
- PHP version incompatibility
- Network connectivity issues
- Insufficient disk space
- Missing PHP extensions

**Solutions:**
1. Check PHP version compatibility in composer.json
2. Verify required PHP extensions are installed
3. Test composer manually:
   ```bash
   cd /path/to/application
   composer install
   ```
4. Check available disk space: `df -h`

#### Application Still Appears After Deletion

##### Issue: Deleted applications still show in list
**Symptoms:** Application appears in dashboard after deletion
**Causes:**
- Browser caching
- Page not refreshing after redirect
- Incomplete deletion

**Solutions:**
1. Hard refresh the page (Ctrl+F5 or Cmd+Shift+R)
2. Clear browser cache
3. Verify application directory was deleted from filesystem
4. Check that cache is cleared after deletion in controller

#### Sudo Command Issues

##### Issue: Sudo commands fail
**Symptoms:** Operations requiring sudo fail
**Causes:**
- Incorrect sudo password
- Insufficient sudo permissions
- Sudo not configured for www-data user

**Solutions:**
1. Verify sudo password in `.env` file
2. Add proper sudo permissions for www-data user:
   ```bash
   sudo visudo -f /etc/sudoers.d/laravel-app-manager
   ```
   Add:
   ```
   www-data ALL=(ALL) NOPASSWD: /usr/bin/git, /bin/rm, /bin/mkdir, /bin/chown, /usr/bin/composer, /usr/bin/npm, /usr/sbin/nginx, /usr/bin/php
   ```
3. Test sudo command manually:
   ```bash
   echo 'password' | sudo -S whoami
   ```

### Debugging Steps

#### Enable Debug Mode
1. Set `APP_DEBUG=true` in `.env` file
2. Run `php artisan config:cache` to refresh configuration
3. Check detailed error messages

#### Check Logs
1. Laravel logs: `/var/www/html/master-app/storage/logs/laravel.log`
2. Nginx logs: `/var/log/nginx/error.log`
3. System logs: `sudo journalctl -u nginx` or `sudo journalctl -u apache2`

#### Test Individual Components
1. Test file operations manually:
   ```bash
   sudo -u www-data ls -la /var/www/html/applications/
   ```
2. Test Git operations:
   ```bash
   sudo -u www-data git --version
   ```
3. Test Composer:
   ```bash
   sudo -u www-data composer --version
   ```

#### Verify Configuration
1. Check environment file:
   ```bash
   php artisan tinker
   >>> config('app.sudo_password')
   >>> config('app.sudo_username')
   ```
2. Verify directory paths exist and have proper permissions
3. Test connectivity to external services (Git, etc.)

### Performance Issues

#### Slow Page Loads
**Causes:**
- Large number of applications
- Large JSON files in applications
- Network latency

**Solutions:**
1. Optimize file scanning in controllers
2. Implement pagination for large application lists
3. Add file size limits when reading JSON files
4. Use caching for frequently accessed data

#### High Memory Usage
**Solutions:**
1. Increase PHP memory limit in php.ini
2. Optimize JSON file reading with size limits
3. Use streaming for large file operations
4. Implement proper garbage collection

## Security Considerations

### Authentication
- All management functions require authentication
- Uses Laravel Breeze for secure authentication
- Password-protected access to all functionality

### Authorization
- Role-based access control
- Only authenticated users can manage applications
- Proper middleware protection on all routes

### Sudo Operations
- Sudo credentials stored securely in environment
- Commands properly escaped to prevent injection
- Limited sudo permissions for specific operations

### File Operations
- Proper file permissions enforced
- Directory traversal prevention
- Secure file handling with validation

### Input Validation
- All inputs validated and sanitized
- Proper CSRF protection
- Route model binding for security

### Security Best Practices
1. Keep the application updated with security patches
2. Use strong, unique passwords for all accounts
3. Regularly rotate sudo credentials
4. Monitor access logs for suspicious activity
5. Use SSL/TLS for all communications
6. Regularly backup application data and configurations

## Best Practices

### For Administrators
1. Regularly update the Laravel Application Manager
2. Monitor disk space in the applications directory
3. Implement automated backups of application data
4. Regularly review and audit user access
5. Monitor system resources and performance
6. Keep system packages updated

### For Developers
1. Use meaningful application names
2. Maintain clean Git repositories for easy cloning
3. Document application-specific requirements in README files
4. Follow Laravel best practices in managed applications
5. Regularly update dependencies in managed applications
6. Use environment-specific configurations appropriately

### Maintenance Tasks
1. Regularly clear Laravel caches in managed applications
2. Monitor application logs for errors
3. Update PHP and system dependencies
4. Review and clean up unused applications periodically
5. Monitor system resource usage
6. Perform regular security audits