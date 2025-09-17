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
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Gestión de clientes
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            // Gestión de productos
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Gestión de inventario
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',

            // Preventas
            'presales.view',
            'presales.create',
            'presales.edit',
            'presales.delete',

            // Ventas
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',

            // Cuentas por cobrar
            'receivables.view',
            'receivables.create',
            'receivables.edit',
            'receivables.delete',

            // Reportes
            'reports.view',
            'reports.export',

            // Configuración del sistema
            'settings.view',
            'settings.edit',
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
            'clients.view', 'clients.create', 'clients.edit',
            'products.view',
            'presales.view', 'presales.create', 'presales.edit',
            'sales.view', 'sales.create', 'sales.edit',
            'inventory.view',
            'reports.view'
        ]);

        // Inventario: maneja productos e inventario
        $inventarioRole->givePermissionTo([
            'products.view', 'products.create', 'products.edit',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'reports.view'
        ]);

        // Contador: ve reportes y cuentas por cobrar
        $contadorRole->givePermissionTo([
            'receivables.view', 'receivables.create', 'receivables.edit',
            'reports.view', 'reports.export',
            'clients.view',
            'sales.view'
        ]);
    }
}