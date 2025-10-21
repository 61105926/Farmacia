<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class SalePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos de ventas
        $permissions = [
            'sales.view',
            'sales.create',
            'sales.update',
            'sales.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Asignar permisos al rol super-admin
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $this->command->info('✅ Permisos de ventas creados exitosamente');
    }
}
