#!/bin/bash

echo "========================================="
echo "  REINICIO DE BASE DE DATOS PRODUCCIÓN"
echo "========================================="
echo ""
echo "⚠️  ADVERTENCIA: Este script eliminará TODOS los datos actuales"
echo "    y recreará la base de datos desde cero."
echo ""
read -p "¿Estás seguro de continuar? (escribe 'SI' para confirmar): " confirmacion

if [ "$confirmacion" != "SI" ]; then
    echo "❌ Operación cancelada"
    exit 1
fi

echo ""
echo "🔄 Iniciando reinicio de base de datos..."
echo ""

# Limpiar caché de configuración
echo "📦 Limpiando caché de Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Eliminar y recrear base de datos
echo ""
echo "🗑️  Eliminando base de datos existente..."
php artisan db:wipe --force

echo ""
echo "🔧 Ejecutando migraciones..."
php artisan migrate:fresh --force

echo ""
echo "🌱 Ejecutando seeders..."
php artisan db:seed --force

echo ""
echo "🔑 Limpiando caché de permisos..."
php artisan permission:cache-reset

echo ""
echo "✅ Base de datos reiniciada exitosamente"
echo ""
echo "📋 Usuario admin por defecto:"
echo "   Email: admin@farmacia.com"
echo "   Password: admin123"
echo ""
echo "⚠️  IMPORTANTE: Cambia la contraseña del admin inmediatamente"
echo ""
