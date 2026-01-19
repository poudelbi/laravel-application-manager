#!/bin/bash

# Build script for Laravel Application Manager Docker setup

echo "Building Laravel Application Manager Docker containers..."

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

# Build the containers
docker-compose build

echo "Build completed! To start the application, run:"
echo "  docker-compose up -d"