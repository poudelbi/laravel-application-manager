# Installation Guide

## Prerequisites

Before installing the Laravel Application Manager, ensure your system meets the following requirements:

- **Operating System**: Linux (Ubuntu/Debian recommended)
- **PHP**: 8.0 or higher
- **Composer**: Latest version
- **Git**: Latest version
- **Nginx**: Latest version
- **MySQL/PostgreSQL/SQLite**: For database operations
- **Sudo Access**: For system-level operations

## System Dependencies

Install the required system dependencies:

```bash
# Update package list
sudo apt update

# Install PHP and extensions
sudo apt install php8.4 php8.4-cli php8.4-common php8.4-mysql php8.4-zip php8.4-gd php8.4-mbstring php8.4-curl php8.4-xml php8.4-bcmath php8.4-json php8.4-xml php8.4-intl php8.4-sqlite3 php8.4-xdebug

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

## Application Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-organization/laravel-app-manager.git /var/www/html/master-app
cd /var/www/html/master-app
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the example environment file and configure your settings:

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

# Cache Configuration
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Redis Configuration (if using Redis)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Set Up Database

For SQLite (default):
```bash
touch database/database.sqlite
```

For MySQL/PostgreSQL, create the database and update credentials in `.env`.

Run migrations:
```bash
php artisan migrate
```

### 6. Configure File Permissions

```bash
sudo chown -R www-data:www-data /var/www/html/master-app
sudo chmod -R 755 /var/www/html/master-app
sudo chmod -R 775 /var/www/html/master-app/storage
sudo chmod -R 775 /var/www/html/master-app/bootstrap/cache
```

### 7. Create Applications Directory

```bash
sudo mkdir -p /var/www/html/applications
sudo chown -R www-data:www-data /var/www/html/applications
sudo chmod -R 775 /var/www/html/applications
```

### 8. Configure Sudo Permissions

Create a sudoers file to allow the web server to run specific commands:

```bash
sudo visudo -f /etc/sudoers.d/laravel-app-manager
```

Add the following content:
```
www-data ALL=(ALL) NOPASSWD: /usr/bin/git, /bin/rm, /bin/mkdir, /bin/chown, /usr/bin/composer, /usr/bin/npm, /usr/sbin/nginx, /usr/bin/php
```

### 9. Configure Web Server

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
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
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

### 10. Start the Application

For development:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

For production, consider using a process manager like Supervisor or systemd.

## Post-Installation Steps

### 1. Create Admin User

Access the application and register an admin user through the registration form, or create one via artisan:

```bash
php artisan tinker
>>> App\Models\User::create(['name' => 'Admin', 'email' => 'admin@yourdomain.com', 'password' => bcrypt('your_password'), 'email_verified_at' => now()])
```

### 2. Verify Installation

Navigate to your domain and verify:
- The application loads without errors
- You can access the dashboard
- Authentication works properly
- You can create a test application

### 3. Configure SSL (Recommended)

For production environments, configure SSL using Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## Troubleshooting Installation

### Common Issues

#### Composer Installation Fails
- Ensure PHP and required extensions are installed
- Check internet connectivity
- Verify Composer is properly installed

#### Permission Errors
- Verify file ownership is set to www-data
- Check that sudo permissions are properly configured
- Ensure the applications directory has proper permissions

#### Database Connection Errors
- Verify database credentials in .env
- Check that the database service is running
- Ensure the database file has proper permissions (for SQLite)

#### Nginx Configuration Issues
- Verify nginx configuration syntax with `sudo nginx -t`
- Check that the site is enabled in sites-enabled
- Ensure PHP-FPM is properly configured