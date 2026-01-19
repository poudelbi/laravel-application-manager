#!/bin/bash

# Initialize SQLite database if it doesn't exist
DB_FILE="/var/www/html/master-app/database/database.sqlite"

if [ ! -f "$DB_FILE" ]; then
    echo "Creating SQLite database file..."
    touch "$DB_FILE"
    chmod 664 "$DB_FILE"
    chown www-data:www-data "$DB_FILE"
    echo "Database file created at $DB_FILE"
else
    echo "Database file already exists at $DB_FILE"
fi