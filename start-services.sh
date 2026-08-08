#!/bin/bash

echo "Running Laravel setup..."

# Generate app key ONLY if not set: regenerating in each deploy invalida
# todas las sesiones y cookies (expulsa a los usuarios logueados)
if [ -z "$APP_KEY" ] && ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    php artisan key:generate --force
    echo "App key generated"
else
    echo "App key already set, skipping generation"
fi

# Test database connection (con reintentos: el Postgres remoto limita las
# conexiones concurrentes y puede estar saturado en el arranque)
echo "Testing database connection..."
CONNECTED=0
for i in 1 2 3 4 5; do
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';" 2>/dev/null; then
        CONNECTED=1
        break
    fi
    echo "Database connection failed (intento $i/5), reintentando en 10 segundos..."
    sleep 10
done
if [ "$CONNECTED" != "1" ]; then
    echo "ERROR: No se pudo conectar a la base de datos después de 5 intentos."
    exit 1
fi

# Run pending migrations only (does NOT delete existing data), con reintentos
echo "Running migrations..."
for i in 1 2 3 4 5; do
    if php artisan migrate --force; then
        break
    fi
    echo "Migration failed (intento $i/5), reintentando en 10 segundos..."
    sleep 10
done

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
