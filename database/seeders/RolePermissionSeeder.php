<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos para el ERP de farmacia
        $permissions = [
            // Gestión de usuarios
            'users.index',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.update',

            // Gestión de clientes
            'clients.index',
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.update',

            // Gestión de productos
            'products.index',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.update',

            // Gestión de inventario
            'inventory.index',
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'inventory.update',

            // Preventas
            'presales.index',
            'presales.view',
            'presales.create',
            'presales.edit',
            'presales.delete',
            'presales.update',

            // Ventas
            'sales.index',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.update',

            // Cuentas por cobrar
            'receivables.index',
            'receivables.view',
            'receivables.create',
            'receivables.edit',
            'receivables.delete',
            'receivables.update',

            // Reportes
            'reports.index',
            'reports.view',
            'reports.export',

            // Configuración del sistema
            'config.index',
            'settings.view',
            'settings.edit',

            // Monitor del sistema
            'system.monitor',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::create(['name' => 'Administrador']);
        $vendedorRole = Role::create(['name' => 'Vendedor']);
        $inventarioRole = Role::create(['name' => 'Inventario']);
        $contadorRole = Role::create(['name' => 'Contador']);

        // Asignar permisos a roles

        // Administrador: todos los permisos
        $adminRole->givePermissionTo(Permission::all());

        // Vendedor: puede ver y manejar clientes, productos, preventas y ventas
        $vendedorRole->givePermissionTo([
            'clients.index', 'clients.view', 'clients.create', 'clients.edit', 'clients.update',
            'products.index', 'products.view',
            'presales.index', 'presales.view', 'presales.create', 'presales.edit', 'presales.update',
            'sales.index', 'sales.view', 'sales.create', 'sales.edit', 'sales.update',
            'inventory.index', 'inventory.view',
            'reports.index', 'reports.view'
        ]);

        // Inventario: maneja productos e inventario
        $inventarioRole->givePermissionTo([
            'products.index', 'products.view', 'products.create', 'products.edit', 'products.update',
            'inventory.index', 'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.update',
            'reports.index', 'reports.view'
        ]);

        // Contador: ve reportes y cuentas por cobrar
        $contadorRole->givePermissionTo([
            'receivables.index', 'receivables.view', 'receivables.create', 'receivables.edit', 'receivables.update',
            'reports.index', 'reports.view', 'reports.export',
            'clients.index', 'clients.view',
            'sales.index', 'sales.view'
        ]);
    }
}