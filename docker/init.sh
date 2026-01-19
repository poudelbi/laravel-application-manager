#!/bin/bash

set -e

echo "Starting Laravel Application Manager initialization..."

# Initialize SQLite database file
echo "Initializing database file..."
sh /var/www/html/master-app/docker/init-db.sh

# Change to application directory
cd /var/www/html/master-app

# Install PHP dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing PHP dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Install Node dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing Node dependencies..."
    npm install
fi

# Generate application key if not exists
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Build frontend assets
echo "Building frontend assets..."
npm run build

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Set proper permissions
chown -R www-data:www-data /var/www/html/master-app/storage
chown -R www-data:www-data /var/www/html/master-app/bootstrap/cache
chown -R www-data:www-data /var/www/html/applications

echo "Initialization completed!"

# Start supervisord (this should be the main process)
exec "$@"