# Docker Setup for Laravel Application Manager

This document describes how to set up and run the Laravel Application Manager using Docker.

## Prerequisites

- Docker Engine (version 20.10 or later)
- Docker Compose (version 2.0 or later)
- Git

## Quick Start

1. Clone the repository (if not already done):
```bash
git clone <repository-url>
cd master-app
```

2. Create a `.env` file with your configuration:
```bash
cp .env.docker .env
```

3. Edit the `.env` file to add your specific configuration, especially:
   - `GITHUB_TOKEN`: Your GitHub Personal Access Token
   - `SUDO_PASSWORD`: Password for sudo operations
   - `SUDO_USERNAME`: Username for sudo operations

4. Build and start the services:
```bash
docker-compose up -d
```

5. The application will be available at `http://localhost`

## Docker Architecture

The setup consists of the following services:

- **app**: Main Laravel application container with PHP-FPM
- **nginx**: Web server and reverse proxy
- **redis**: Cache and queue backend
- **db**: SQLite database container

## Service Configuration

### Laravel Application Container
- Built from Ubuntu 22.04 with PHP 8.2
- Includes Composer and Node.js for dependency management
- Runs PHP-FPM, queue workers, and cron jobs via Supervisor
- Exposes port 9000 internally

### Nginx Container
- Serves as the main web server
- Reverse proxies to the Laravel application
- Serves static files directly
- Exposes ports 80 and 443

### Redis Container
- Provides caching and queue functionality
- Persists data in named volume

### Database Container
- SQLite database for application data
- Persists data in named volume

## Volumes

- `./storage` → `/var/www/html/master-app/storage` (Laravel storage)
- `./bootstrap/cache` → `/var/www/html/master-app/bootstrap/cache` (Bootstrap cache)
- `./database` → `/var/www/html/master-app/database` (Database files)
- `../applications` → `/var/www/html/applications` (Managed applications directory)

## Environment Variables

The following environment variables can be configured in the `.env` file:

- `GITHUB_TOKEN`: GitHub Personal Access Token for repository cloning
- `SUDO_PASSWORD`: Password for sudo operations
- `SUDO_USERNAME`: Username for sudo operations
- `APP_ENV`: Application environment (production, development, etc.)
- `APP_DEBUG`: Enable/disable debug mode

## Useful Commands

### Build and run
```bash
docker-compose up -d
```

### View logs
```bash
docker-compose logs -f
```

### Execute commands in the app container
```bash
docker-compose exec app php artisan <command>
docker-compose exec app composer <command>
docker-compose exec app npm <command>
```

### Stop and remove containers
```bash
docker-compose down
```

### Rebuild containers
```bash
docker-compose build --no-cache
docker-compose up -d
```

## Development Notes

- The application manages other Laravel applications in the `../applications` directory
- The sudo functionality is configured to work within the container environment
- File permissions are handled to ensure proper access for the web server
- The application is optimized for production use with opcode caching and other optimizations

## Troubleshooting

### If the application fails to start:
1. Check the logs: `docker-compose logs app`
2. Ensure all required environment variables are set
3. Verify that the database file has proper permissions

### If database migrations fail:
1. Check that the database container is running: `docker-compose ps`
2. Manually run migrations: `docker-compose exec app php artisan migrate`

### If frontend assets don't load:
1. Check that the nginx container is running
2. Verify that the public directory is properly mounted
3. Rebuild assets: `docker-compose exec app npm run build`