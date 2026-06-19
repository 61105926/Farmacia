#!/bin/bash

echo "Running Laravel setup..."

# Generate app key if not set
php artisan key:generate --force
echo "App key generated"

# Test database connection
echo "Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';"

# Run pending migrations only (does NOT delete existing data)
echo "Running migrations..."
php artisan migrate --force

# Storage symlink
echo "Creating storage link..."
php artisan storage:link --force

# Run seeders only if no users exist (first deploy)
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "No users found, running initial seeders..."
    php artisan db:seed --class=RolePermissionSeeder --force
    php artisan db:seed --class=AdminUserSeeder --force
    echo "Seeders completed"
else
    echo "Users already exist ($USER_COUNT), skipping seeders"
fi

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "Laravel setup completed"

# Start Apache
echo "Starting Apache..."
apache2-foreground
