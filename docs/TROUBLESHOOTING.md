# Troubleshooting Guide

## Common Issues and Solutions

### 500 Internal Server Error

#### Issue: Sites page returns 500 error
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

#### Issue: General 500 errors
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

### Permission Denied Errors

#### Issue: Cannot create directories
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

#### Issue: Cannot delete applications
**Symptoms:** Delete operations fail with permission errors
**Causes:** Files owned by root or other user
**Solutions:**
1. Ensure sudo credentials are configured in `.env`:
   ```
   SUDO_PASSWORD=your_password
   SUDO_USERNAME=your_username
   ```
2. Verify sudo permissions for www-data user

### Git Clone Failures

#### Issue: Git clone fails
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

### Port Binding Issues

#### Issue: Cannot start application on assigned port
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

### Nginx Configuration Issues

#### Issue: Cannot create nginx configuration
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

### Composer Install Failures

#### Issue: Composer install fails
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

### Application Still Appears After Deletion

#### Issue: Deleted applications still show in list
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

### Sudo Command Issues

#### Issue: Sudo commands fail
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

## Debugging Steps

### Enable Debug Mode
1. Set `APP_DEBUG=true` in `.env` file
2. Run `php artisan config:cache` to refresh configuration
3. Check detailed error messages

### Check Logs
1. Laravel logs: `/var/www/html/master-app/storage/logs/laravel.log`
2. Nginx logs: `/var/log/nginx/error.log`
3. System logs: `sudo journalctl -u nginx` or `sudo journalctl -u apache2`

### Test Individual Components
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

### Verify Configuration
1. Check environment file:
   ```bash
   php artisan tinker
   >>> config('app.sudo_password')
   >>> config('app.sudo_username')
   ```
2. Verify directory paths exist and have proper permissions
3. Test connectivity to external services (Git, etc.)

## Performance Issues

### Slow Page Loads
**Causes:**
- Large number of applications
- Large JSON files in applications
- Network latency

**Solutions:**
1. Optimize file scanning in controllers
2. Implement pagination for large application lists
3. Add file size limits when reading JSON files
4. Use caching for frequently accessed data

### High Memory Usage
**Solutions:**
1. Increase PHP memory limit in php.ini
2. Optimize JSON file reading with size limits
3. Use streaming for large file operations
4. Implement proper garbage collection

## Security Issues

### Authentication Problems
**Solutions:**
1. Verify session configuration
2. Check that `.env` file is not publicly accessible
3. Ensure proper HTTPS configuration
4. Test authentication middleware

### Privilege Escalation Concerns
**Solutions:**
1. Limit sudo commands to specific operations only
2. Use dedicated service account for operations
3. Regularly audit sudo logs
4. Implement proper input validation

## System Requirements

### Minimum Requirements
- PHP 8.0+
- Composer
- Git
- Nginx or Apache
- Sudo access for web server user
- At least 2GB RAM for multiple applications

### Recommended Requirements
- PHP 8.1+ with OPcache enabled
- SSD storage for better I/O performance
- 4GB+ RAM for optimal performance
- Dedicated server for production use

## Contact Support

If issues persist after following these troubleshooting steps:

1. Check the application logs for detailed error information
2. Verify all prerequisites are properly installed
3. Ensure all configuration files are properly set up
4. Consult the main documentation for additional information