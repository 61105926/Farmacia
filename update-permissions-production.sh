#!/bin/bash

echo "========================================="
echo "  ACTUALIZACIÓN DE PERMISOS PRODUCCIÓN"
echo "========================================="
echo ""
echo "ℹ️  Este script actualizará los permisos sin borrar datos existentes"
echo ""

# Limpiar caché
echo "📦 Limpiando caché..."
php artisan config:clear
php artisan cache:clear

# Ejecutar solo el seeder de permisos
echo ""
echo "🔐 Actualizando permisos..."
php artisan db:seed --class=RolePermissionSeeder --force

echo ""
echo "🔑 Limpiando caché de permisos..."
php artisan permission:cache-reset

echo ""
echo "✅ Permisos actualizados exitosamente"
echo ""
echo "📋 Permisos ahora disponibles:"
echo "   ✓ users.index, users.view, users.create, users.edit, users.update, users.delete"
echo "   ✓ clients.index, clients.view, clients.create, clients.edit, clients.update, clients.delete"
echo "   ✓ products.index, products.view, products.create, products.edit, products.update, products.delete"
echo "   ✓ inventory.index, inventory.view, inventory.create, inventory.edit, inventory.update, inventory.delete"
echo "   ✓ presales.index, presales.view, presales.create, presales.edit, presales.update, presales.delete"
echo "   ✓ sales.index, sales.view, sales.create, sales.edit, sales.update, sales.delete"
echo "   ✓ receivables.index, receivables.view, receivables.create, receivables.edit, receivables.update, receivables.delete"
echo "   ✓ reports.index, reports.view, reports.export"
echo "   ✓ config.index, settings.view, settings.edit"
echo "   ✓ system.monitor"
echo ""
echo "⚠️  NOTA: Si ya existían permisos duplicados, pueden aparecer errores."
echo "   Esto es normal. Los nuevos permisos se habrán creado correctamente."
echo ""
