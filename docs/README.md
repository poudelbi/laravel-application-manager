# Laravel Application Manager Documentation

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Usage](#usage)
6. [API Endpoints](#api-endpoints)
7. [Troubleshooting](#troubleshooting)
8. [Security](#security)

## Overview

The Laravel Application Manager is a comprehensive tool for managing multiple Laravel applications. It provides a web-based interface to create, deploy, manage, and monitor Laravel applications with automatic port allocation and configuration management.

## Features

### Core Functionality
- **Git Repository Deployment**: Clone and deploy applications from Git repositories
- **Automatic Port Allocation**: Assigns unique ports starting from 8000 (app1:8000, app2:8001, etc.)
- **Nginx Configuration Management**: Generate and manage nginx configurations for each application
- **Application Lifecycle Management**: Start, stop, update, refresh, and delete applications
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

## Installation

### Prerequisites
- PHP 8.0 or higher
- Composer
- Git
- Nginx
- MySQL/PostgreSQL/SQLite
- Sudo access (for system operations)

### Setup Steps
1. Clone the repository
2. Install dependencies with `composer install`
3. Set up environment variables in `.env`
4. Run migrations with `php artisan migrate`
5. Start the development server with `php artisan serve`

### Environment Configuration
```env
APP_NAME=Laravel Application Manager
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Sudo credentials for system operations
SUDO_PASSWORD=your_sudo_password
SUDO_USERNAME=your_sudo_username

# Database configuration
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

## Configuration

### Sudo Configuration
The application requires sudo credentials to perform system-level operations such as:
- Creating/deleting application directories
- Managing nginx configurations
- Starting/stopping application processes
- Setting file permissions

Set these in your `.env` file:
```
SUDO_PASSWORD=your_password
SUDO_USERNAME=your_username
```

### Applications Directory
By default, applications are stored in `/var/www/html/applications/`. This can be customized in the configuration.

### Port Allocation
Applications are assigned ports starting from 8000. The first application gets port 8000, the second gets 8001, and so on.

## Usage

### Creating Applications

#### From Scratch
1. Navigate to the Applications section
2. Click "Create New Application"
3. Enter application name and PHP version
4. The system will create a basic Laravel application structure

#### From Git Repository
1. Navigate to the Applications section
2. Enter repository URL and access token (if private)
3. Enter application name and PHP version
4. The system will clone the repository and set up the application

### Managing Applications

#### Deploying Applications
1. Go to the Applications list
2. Click "Deploy" for the application you want to deploy
3. The application will be copied to the web root with proper permissions

#### Starting Applications
1. Go to the Sites section
2. Find your application in the list
3. Click "Start" to start the application on its assigned port
4. Access the application at `http://server.domain.com:PORT`

#### Stopping Applications
1. Go to the Sites section
2. Find your application in the list
3. Click "Stop" to stop the application

#### Updating Applications
1. Go to the Sites section
2. Find your application in the list
3. Click "Update" to pull latest changes from Git repository

#### Refreshing Applications
1. Go to the Sites section
2. Find your application in the list
3. Click "Refresh" to clear Laravel caches

### Site Management

#### Nginx Configuration
1. Go to the Sites section
2. Find your application in the list
3. Click "Nginx Config" to generate nginx configuration
4. The configuration will be created in `/etc/nginx/sites-available/`

#### Accessing Applications
- Applications are accessible at `http://server.domain.com:PORT`
- Each application gets a unique port based on its position in the list
- Running applications show a "Visit" button for direct access

### Testing Functionality

#### Test Dashboard
The Test Dashboard provides comprehensive testing for all functionality:
- Repository Cloning Test
- Directory Creation Test
- File Verification Test
- Sudo Integration Test
- Permission Management Test
- Deployment Flow Test
- Composer Install Test
- Application Start Test
- Nginx Config Generation Test
- Database Migration Test
- Cache Clearing Test
- App Key Generation Test

## API Endpoints

### Applications
- `GET /applications` - List all applications
- `POST /applications` - Create new application
- `DELETE /applications/{name}` - Delete application
- `POST /applications/{name}/deploy` - Deploy application
- `GET /applications/{name}/setup` - Setup application
- `POST /applications/{name}/setup` - Store application setup
- `GET /applications/{name}/nginx-config` - Get nginx configuration

### Sites
- `GET /sites` - List all sites with port assignments
- `POST /sites/{name}/start` - Start application on assigned port
- `POST /sites/{name}/stop` - Stop application on assigned port
- `POST /sites/{name}/update` - Update application from Git
- `POST /sites/{name}/refresh` - Refresh application caches
- `POST /sites/{name}/nginx-config` - Generate nginx configuration
- `DELETE /sites/{name}` - Remove nginx configuration
- `POST /applications/deploy-official-laravel` - Deploy official Laravel application

### Test Dashboard
- `GET /test-dashboard` - View test dashboard
- `POST /test-dashboard/run/{testName}` - Run specific test

## Troubleshooting

### Common Issues

#### Permission Errors
- Ensure sudo credentials are properly configured
- Verify the web server has appropriate permissions
- Check file ownership and permissions in the applications directory

#### Port Conflicts
- Applications use sequential ports starting from 8000
- If a port is already in use, the system will try the next available port
- Check for other services running on the expected ports

#### Git Clone Failures
- Verify repository URL is correct
- Ensure access token has appropriate permissions
- Check network connectivity to the Git server

#### Nginx Configuration Issues
- Verify sudo credentials for nginx operations
- Check nginx service is running
- Ensure proper permissions for nginx configuration directories

### Debugging Tips
1. Check Laravel logs in `storage/logs/laravel.log`
2. Verify sudo commands work manually from command line
3. Ensure all required commands (git, composer, php, nginx) are available
4. Check file permissions in the applications directory

## Security

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