#!/bin/bash

# Initialize MariaDB
echo "Initializing MariaDB..."

# Configure MariaDB to bind to all interfaces
echo "[mysqld]
bind-address = 0.0.0.0
socket = /var/run/mysqld/mysqld.sock
port = 3306" >> /etc/mysql/mariadb.conf.d/50-server.cnf

# Create socket directory
mkdir -p /var/run/mysqld
chown mysql:mysql /var/run/mysqld

# Start MariaDB
service mariadb start

# Wait for MariaDB to be ready
until mysqladmin ping -h "127.0.0.1" --silent; do
    echo "Waiting for MariaDB to be ready..."
    sleep 2
done

# Create database and user
mysql -h 127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS farmacia;"
mysql -h 127.0.0.1 -e "CREATE USER IF NOT EXISTS 'farmacia'@'%' IDENTIFIED BY 'farmacia';"
mysql -h 127.0.0.1 -e "GRANT ALL PRIVILEGES ON farmacia.* TO 'farmacia'@'%';"
mysql -h 127.0.0.1 -e "FLUSH PRIVILEGES;"

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