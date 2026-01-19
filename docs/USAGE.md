# Usage Guide

## Getting Started

### Accessing the Application

1. Open your browser and navigate to `http://server.poudelbijaya.com.np:8000`
2. Register a new account or log in with existing credentials
3. You'll be taken to the dashboard where you can manage applications

### Dashboard Overview

The dashboard provides:
- Total application count
- Deployed application count
- Quick access to create new applications
- List of recent applications with ports and access links

## Application Management

### Creating New Applications

#### From Scratch
1. Click "Create New Application" on the dashboard or applications page
2. Fill in the application name
3. Select the PHP version
4. Click "Create Application"
5. The system will create a basic Laravel application structure

#### From Git Repository
1. Click "Create New Application" 
2. Enter the Git repository URL (e.g., `https://github.com/username/repo.git`)
3. If it's a private repository, enter your personal access token
4. Enter the application name
5. Select the PHP version
6. Click "Create Application"
7. The system will clone the repository and set up the application

### Managing Applications

#### Deploying Applications
1. Go to the Applications list
2. Find the application you want to deploy
3. Click the "Deploy" button
4. The application will be copied to the web root with proper permissions

#### Deleting Applications
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

## Application Setup Process

### After Git Clone

When you create an application from a Git repository, you'll be redirected to the setup page which guides you through:

#### 1. Environment Configuration
- Edit the `.env` file with your application settings
- Configure database connections
- Set application name, environment, debug mode, etc.

#### 2. Application Key Generation
- Generate a new application key with `php artisan key:generate`
- This is required for Laravel's encryption services

#### 3. Dependency Installation
- Run `composer install` to install PHP dependencies
- Run `npm install` to install Node.js dependencies

#### 4. Frontend Asset Building
- Run `npm run build` to build frontend assets
- This compiles CSS, JavaScript, and other frontend resources

#### 5. Database Operations
- Run `php artisan migrate` to run database migrations
- Run `php artisan db:seed` to seed the database with initial data

#### 6. Storage Linking
- Run `php artisan storage:link` to create symbolic links

## Test Dashboard

### Running Individual Tests

The Test Dashboard provides comprehensive testing for all functionality:

#### Repository Cloning Test
- Tests the ability to clone Git repositories
- Verifies key Laravel files are present after cloning

#### Directory Creation Test
- Tests the creation of proper Laravel application structure
- Verifies all necessary directories are created

#### File Verification Test
- Confirms key Laravel files exist after operations
- Checks for composer.json, artisan, app directory, etc.

#### Sudo Integration Test
- Verifies sudo operations work correctly
- Tests system-level operations requiring elevated privileges

#### Permission Management Test
- Tests proper file permissions for web server access
- Verifies storage and cache directories have proper permissions

#### Deployment Flow Test
- Tests the complete application deployment process
- Verifies all steps in the deployment workflow

#### Composer Install Test
- Tests the composer install functionality for Laravel applications
- Verifies dependencies are installed properly

#### Application Start Test
- Tests the application startup functionality on a dedicated port
- Verifies applications can be started on assigned ports

#### Nginx Config Generation Test
- Tests the nginx configuration generation for applications
- Verifies nginx configurations are created properly

#### Database Migration Test
- Tests the database migration functionality for Laravel applications
- Verifies migrations run successfully

#### Cache Clearing Test
- Tests the cache clearing functionality for Laravel applications
- Verifies all Laravel caches can be cleared

#### App Key Generation Test
- Tests the application key generation functionality
- Verifies APP_KEY is properly generated and stored

### Running All Tests
- Click the "Run All Tests" button to execute all tests at once
- View comprehensive results for all functionality

## Port Management

### Automatic Port Assignment

The system automatically assigns ports to applications:
- First application: Port 8000
- Second application: Port 8001
- Third application: Port 8002
- And so on...

### Accessing Applications by Port

Each application is accessible at:
`http://server.poudelbijaya.com.np:PORT`

Where PORT is the assigned port number.

### Port Status Indicators

- **Green indicator**: Application is running on its assigned port
- **Red indicator**: Application is not running
- **"Visit" button**: Direct access to running applications
- **"Start" button**: Start stopped applications

## Security Best Practices

### Authentication

- All management functions require authentication
- Use strong, unique passwords
- Enable two-factor authentication if available

### Sudo Configuration

- Store sudo credentials securely in environment variables
- Use dedicated service accounts for sudo operations
- Limit sudo permissions to necessary commands only

### File Permissions

- Ensure proper file permissions for web server access
- Regularly audit file permissions
- Use appropriate ownership for application files

## Troubleshooting Common Issues

### Application Won't Start

1. Check if the port is already in use:
   ```bash
   sudo netstat -tuln | grep :PORT_NUMBER
   ```

2. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. Verify application files exist and have proper permissions

### Git Clone Fails

1. Verify the repository URL is correct
2. Check that the access token has proper permissions (for private repos)
3. Ensure Git is properly installed and accessible
4. Check network connectivity to the Git server

### Nginx Configuration Issues

1. Verify sudo credentials are properly configured
2. Check nginx service status: `sudo systemctl status nginx`
3. Test nginx configuration: `sudo nginx -t`
4. Check nginx error logs: `sudo tail -f /var/log/nginx/error.log`

### Composer Install Fails

1. Check if Composer is properly installed
2. Verify PHP version requirements in composer.json
3. Check internet connectivity for package downloads
4. Ensure sufficient disk space is available

### Permission Errors

1. Verify sudo credentials in `.env` file
2. Check file ownership and permissions
3. Ensure the web server user has necessary privileges
4. Review sudoers configuration for required commands

## Maintenance Tasks

### Regular Maintenance

- Monitor application logs regularly
- Check disk space in applications directory
- Update system packages periodically
- Backup important configurations

### Performance Monitoring

- Monitor running processes
- Check memory and CPU usage
- Monitor response times
- Review slow queries if using database

### Security Updates

- Keep Laravel framework updated
- Update PHP and system packages
- Rotate access tokens regularly
- Review and update sudo permissions as needed