#!/bin/bash

# Startup script for Laravel Application Manager Docker setup

echo "Starting Laravel Application Manager..."

# Create necessary directories if they don't exist
mkdir -p storage
mkdir -p bootstrap/cache
mkdir -p database
mkdir -p logs
mkdir -p ../applications

# Set proper permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 database/

# Start the containers in detached mode
docker-compose up -d

echo "Laravel Application Manager is starting..."
echo "Access the application at http://localhost"
echo ""
echo "To view logs: docker-compose logs -f"
echo "To stop: docker-compose down"