FROM ubuntu:22.04

LABEL maintainer="Laravel Application Manager"

# Prevent interactive prompts during package installation
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    nginx \
    sudo \
    gnupg \
    lsb-release \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    && rm -rf /var/lib/apt/lists/*

# Install PHP 8.2 with required extensions
RUN apt-get update && apt-get install -y \
    php8.2 \
    php8.2-cli \
    php8.2-common \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-redis \
    php8.2-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP
RUN echo "upload_max_filesize = 100M" >> /etc/php/8.2/cli/php.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.2/cli/php.ini && \
    echo "memory_limit = 512M" >> /etc/php/8.2/cli/php.ini

RUN echo "upload_max_filesize = 100M" >> /etc/php/8.2/fpm/php.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.2/fpm/php.ini && \
    echo "memory_limit = 512M" >> /etc/php/8.2/fpm/php.ini

# Enable PHP-FPM
RUN sed -i 's/listen = .*/listen = 0.0.0.0:9000/' /etc/php/8.2/fpm/pool.d/www.conf

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html/master-app

# Copy application files
COPY . /var/www/html/master-app

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/master-app && \
    chmod -R 755 /var/www/html/master-app && \
    chmod -R 775 /var/www/html/master-app/storage && \
    chmod -R 775 /var/www/html/master-app/bootstrap/cache

# Create applications directory
RUN mkdir -p /var/www/html/applications && \
    chown -R www-data:www-data /var/www/html/applications

# Configure sudo for www-data user (needed for application management)
RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

# Create supervisord configuration
RUN mkdir -p /var/log/supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy and make init script executable
COPY docker/init.sh /init.sh
RUN chmod +x /init.sh

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php artisan tinker --execute="echo 'OK';" || exit 1

ENTRYPOINT ["/init.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]