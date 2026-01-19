#!/bin/bash

# Laravel Application Manager - Startup Script
# This script starts all Laravel applications on their assigned ports

echo "Laravel Application Manager - Starting All Applications"

# Define the applications directory
APPS_DIR="/var/www/html/applications"

# Check if applications directory exists
if [ ! -d "$APPS_DIR" ]; then
    echo "Applications directory does not exist: $APPS_DIR"
    exit 1
fi

# Get all applications and sort them
APPS=($(ls -1d "$APPS_DIR"/*/ 2>/dev/null | sort))

if [ ${#APPS[@]} -eq 0 ]; then
    echo "No applications found in $APPS_DIR"
    exit 0
fi

echo "Found ${#APPS[@]} applications to start..."

INDEX=0
for app_path in "${APPS[@]}"; do
    app_name=$(basename "$app_path")
    port=$((8001 + INDEX))
    
    echo "Starting application: $app_name on port: $port"
    
    # Check if port is already in use
    if lsof -i :$port >/dev/null 2>&1; then
        echo "  - Port $port is already in use, skipping..."
    else
        # Start the application on its assigned port
        cd "$app_path" && php artisan serve --host=0.0.0.0 --port=$port > /dev/null 2>&1 &
        START_PID=$!
        
        # Brief pause to allow server to start
        sleep 2
        
        # Verify the server is running
        if lsof -i :$port >/dev/null 2>&1; then
            echo "  - Successfully started on port $port (PID: $START_PID)"
        else
            echo "  - Failed to start on port $port"
        fi
    fi
    
    INDEX=$((INDEX + 1))
done

echo ""
echo "Applications are now running and accessible at:"
INDEX=0
for app_path in "${APPS[@]}"; do
    app_name=$(basename "$app_path")
    port=$((8001 + INDEX))
    echo "  - http://server.poudelbijaya.com.np:$port/ ($app_name)"
    INDEX=$((INDEX + 1))
done

echo ""
echo "Laravel Application Manager startup completed successfully!"