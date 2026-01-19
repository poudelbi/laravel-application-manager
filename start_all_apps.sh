#!/bin/bash

echo "Starting all Laravel applications on their assigned ports..."

# Get all applications and sort them
APPS=($(ls -1d /var/www/html/applications/*/ | sort))

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
        if [ $? -eq 0 ]; then
            echo "  - Successfully started on port $port"
        else
            echo "  - Failed to start on port $port"
        fi
    fi
    
    INDEX=$((INDEX + 1))
done

echo ""
echo "Applications are now accessible at:"
INDEX=0
for app_path in "${APPS[@]}"; do
    app_name=$(basename "$app_path")
    port=$((8001 + INDEX))
    echo "  - http://server.poudelbijaya.com.np:$port/ ($app_name)"
    INDEX=$((INDEX + 1))
done