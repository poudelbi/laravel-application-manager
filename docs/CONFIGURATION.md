# Configuration Guide

## Environment Variables

The Laravel Application Manager uses several environment variables for configuration. These are defined in the `.env` file.

### Core Application Settings

```
APP_NAME=Laravel Application Manager
APP_ENV=production
APP_KEY= # Generated with php artisan key:generate
APP_DEBUG=false  # Set to true for development
APP_URL=http://your-domain.com
```

### Database Configuration

```
DB_CONNECTION=sqlite  # Options: mysql, postgres, sqlite, sqlsrv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=/var/www/html/master-app/database/database.sqlite
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

### Sudo Configuration

```
SUDO_PASSWORD=your_sudo_password  # Password for sudo operations
SUDO_USERNAME=your_sudo_username  # Username for sudo operations
```

### Cache and Session Settings

```
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
```

## Application Directory Structure

### Default Locations

- **Applications Directory**: `/var/www/html/applications/`
- **Master Application**: `/var/www/html/master-app/`
- **Web Root**: `/var/www/html/`

### Customizing Applications Directory

You can customize the applications directory by modifying the configuration in `config/app.php`:

```php
'applications_dir' => env('APPLICATIONS_DIR', '/var/www/html/applications'),
```

Then set the `APPLICATIONS_DIR` in your `.env` file:

```
APPLICATIONS_DIR=/custom/path/to/applications
```

## Port Configuration

### Default Port Range

Applications are assigned ports starting from 8000:
- First application: 8000
- Second application: 8001
- Third application: 8002
- And so on...

### Customizing Starting Port

To change the starting port, you would need to modify the `SitesController`:

```php
protected $startingPort = 9000;  // Change from 8000 to your preferred starting port
```

## Nginx Configuration

### Default Nginx Setup

The application generates nginx configurations in:
- **Available sites**: `/etc/nginx/sites-available/`
- **Enabled sites**: `/etc/nginx/sites-enabled/`

### Custom Nginx Configuration

You can customize the nginx configuration template by modifying the `generateNginxConfig` method in `SitesController`:

```php
private function generateNginxConfig($appName, $appPath, $domainName)
{
    $config = "server {\n";
    $config .= "    listen 80;\n";
    $config .= "    server_name {$domainName};\n";
    $config .= "    root {$appPath}/public;\n";
    $config .= "    index index.php index.html index.htm;\n\n";
    // ... rest of configuration
}
```

## Authentication Configuration

### Laravel Breeze Settings

The application uses Laravel Breeze for authentication. Configuration can be found in:
- `config/auth.php` - Authentication settings
- `app/Models/User.php` - User model
- `database/migrations/` - User table migrations

### User Registration

By default, user registration is enabled. To disable it, modify the routes in `routes/web.php`:

```php
// Remove or comment out the registration routes
// Route::get('/register', [EmailVerificationPromptController::class, '__invoke'])->name('register');
```

## File Permissions

### Recommended Permissions

- **Applications Directory**: `775` (rwx for owner and group, rx for others)
- **Application Files**: `664` (rw for owner and group, r for others)
- **Application Directories**: `775` (rwx for owner and group, rx for others)

### Sudo Permissions

For the application to work properly with sudo, ensure the web server user (typically `www-data`) has sudo access to specific commands:

```
www-data ALL=(ALL) NOPASSWD: /usr/bin/git, /bin/rm, /bin/mkdir, /bin/chown, /usr/bin/composer, /usr/bin/npm, /usr/sbin/nginx, /usr/bin/php
```

## Security Configuration

### HTTPS Configuration

For production environments, configure HTTPS in your nginx configuration:

```
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # ... rest of configuration
}
```

### Rate Limiting

The application uses Laravel's built-in rate limiting. Configure in `app/Http/Kernel.php`:

```php
'web' => [
    // ... other middleware
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### CORS Configuration

If you need to configure CORS, update `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    // ... other CORS settings
];
```

## Performance Configuration

### Caching Configuration

For better performance, consider using Redis or Memcached instead of file-based caching:

```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Queue Configuration

For background processing, configure a queue driver:

```
QUEUE_CONNECTION=database  # or redis, sqs, etc.
```

### Optimizations

Run these commands after configuration for better performance:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Debugging Configuration

### Debug Mode

For development, enable debug mode:

```
APP_DEBUG=true
```

### Logging Configuration

Configure logging in `config/logging.php`:

```php
'default' => env('LOG_CHANNEL', 'stack'),
```

### Debugbar Configuration

The application includes Laravel Debugbar for development:

```
DEBUGBAR_ENABLED=true
```