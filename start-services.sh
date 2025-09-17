#!/bin/bash

# Initialize MariaDB
echo "Initializing MariaDB..."
service mariadb start

# Wait for MariaDB to be ready
until mysqladmin ping -h "localhost" --silent; do
    echo "Waiting for MariaDB to be ready..."
    sleep 2
done

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS farmacia;"
mysql -e "CREATE USER IF NOT EXISTS 'farmacia'@'localhost' IDENTIFIED BY 'farmacia';"
mysql -e "GRANT ALL PRIVILEGES ON farmacia.* TO 'farmacia'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "MariaDB initialized successfully"

# Run Laravel migrations and seeders
echo "Running Laravel setup..."
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force

echo "Laravel setup completed"

# Start Apache in foreground
echo "Starting Apache..."
apache2-foreground