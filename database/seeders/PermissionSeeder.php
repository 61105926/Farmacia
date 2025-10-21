<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'clients' => 'Clientes',
            'products' => 'Productos',
            'inventory' => 'Inventario',
            'presales' => 'Preventas',
            'sales' => 'Ventas',
            'receivables' => 'Cuentas por Cobrar',
            'reports' => 'Reportes',
            'config' => 'Configuración',
            'audit' => 'Auditoría',
        ];

        $actions = [
            'index' => 'Ver listado',
            'view' => 'Ver detalle',
            'create' => 'Crear',
            'update' => 'Editar',
            'delete' => 'Eliminar',
        ];

        // Crear permisos por módulo y acción
        foreach ($modules as $moduleSlug => $moduleName) {
            foreach ($actions as $actionSlug => $actionName) {
                $permissionName = "{$moduleSlug}.{$actionSlug}";

                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);

                $this->command->info("Permiso creado: {$permissionName}");
            }
        }

        // Permisos especiales
        $specialPermissions = [
            'clients.export' => 'Exportar clientes',
            'products.import' => 'Importar productos',
            'inventory.transfer' => 'Transferir inventario',
            'presales.approve' => 'Aprobar preventas',
            'presales.convert' => 'Convertir preventa a venta',
            'sales.invoice' => 'Facturar ventas',
            'receivables.collect' => 'Registrar cobros',
            'reports.financial' => 'Ver reportes financieros',
            'config.system' => 'Configuración del sistema',
        ];

        foreach ($specialPermissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            $this->command->info("Permiso especial creado: {$name}");
        }

        // Asignar todos los permisos al rol super-admin
        $superAdmin = Role::findByName('super-admin');
        $superAdmin->givePermissionTo(Permission::all());

        $this->command->info('✅ Permisos creados y asignados exitosamente');
    }
}
