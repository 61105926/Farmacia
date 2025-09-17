#!/bin/bash

# Initialize MySQL
echo "Initializing MySQL..."
service mysql start

# Wait for MySQL to be ready
until mysqladmin ping -h "localhost" --silent; do
    echo "Waiting for MySQL to be ready..."
    sleep 2
done

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS farmacia;"
mysql -e "CREATE USER IF NOT EXISTS 'farmacia'@'localhost' IDENTIFIED BY 'farmacia';"
mysql -e "GRANT ALL PRIVILEGES ON farmacia.* TO 'farmacia'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "MySQL initialized successfully"

# Run Laravel migrations and seeders
echo "Running Laravel setup..."
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force

echo "Laravel setup completed"

# Start Apache in foreground
echo "Starting Apache..."
apache2-foreground