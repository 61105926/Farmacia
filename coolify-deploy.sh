#!/bin/bash

# Script de despliegue para Coolify
# Este script se ejecuta después del build en Coolify

set -e

echo "🚀 Iniciando despliegue en Coolify..."

# Esperar a que la base de datos esté lista
echo "⏳ Esperando conexión a la base de datos..."
until php artisan db:show &> /dev/null; do
    echo "   Esperando MySQL..."
    sleep 2
done

echo "✅ Base de datos conectada"

# Ejecutar migraciones frescas con seeders
echo "📦 Ejecutando migraciones frescas y seeders..."
php artisan migrate:fresh --seed --force

# Limpiar y optimizar caché
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "⚡ Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Establecer permisos
echo "🔐 Estableciendo permisos..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "✅ Despliegue completado exitosamente!"

