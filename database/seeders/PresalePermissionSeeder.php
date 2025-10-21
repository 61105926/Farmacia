<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class PresalePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos de preventas
        $permissions = [
            'presales.view',
            'presales.create',
            'presales.update',
            'presales.delete',
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

        $this->command->info('✅ Permisos de preventas creados exitosamente');
    }
}
