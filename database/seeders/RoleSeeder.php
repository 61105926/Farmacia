<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'super-admin' => 'Super Administrador - Acceso total al sistema',
            'administrador' => 'Administrador del sistema con permisos limitados',
            'vendedor-preventas' => 'Preventista que registra pedidos en campo',
            'vendedor-ventas' => 'Vendedor que procesa pedidos y genera facturas',
            'cobrador' => 'Responsable de cobranza y cuentas por cobrar',
            'bodeguero' => 'Encargado de inventario y almacén',
            'contabilidad' => 'Personal de contabilidad y finanzas',
            'auditor' => 'Auditor del sistema (solo lectura)',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );

            $this->command->info("Rol creado: {$name}");
        }

        $this->command->info('✅ Roles creados exitosamente');
    }
}
