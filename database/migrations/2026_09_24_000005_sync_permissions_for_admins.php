<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Crea los permisos que usa el sistema y que falten en la base de datos,
     * y asigna todos los permisos a los roles de administrador.
     */
    public function up(): void
    {
        $permissions = [
            'clients.create',
            'clients.delete',
            'clients.edit',
            'clients.export',
            'clients.index',
            'clients.update',
            'clients.view',
            'config.index',
            'inventory.create',
            'inventory.delete',
            'inventory.edit',
            'inventory.index',
            'inventory.update',
            'inventory.view',
            'payments.approve',
            'payments.cancel',
            'presales.approve',
            'presales.cancel',
            'presales.convert',
            'presales.create',
            'presales.delete',
            'presales.edit',
            'presales.index',
            'presales.update',
            'presales.view',
            'products.create',
            'products.delete',
            'products.edit',
            'products.index',
            'products.update',
            'products.view',
            'receivables.create',
            'receivables.delete',
            'receivables.edit',
            'receivables.index',
            'receivables.update',
            'receivables.view',
            'reports.export',
            'reports.index',
            'reports.view',
            'sales.cancel',
            'sales.complete',
            'sales.create',
            'sales.delete',
            'sales.edit',
            'sales.index',
            'sales.invoice',
            'sales.payment',
            'sales.update',
            'sales.view',
            'settings.edit',
            'settings.view',
            'system.monitor',
            'users.create',
            'users.delete',
            'users.edit',
            'users.index',
            'users.update',
            'users.view',
        ];

        foreach ($permissions as $name) {
            $exists = DB::table('permissions')
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name'       => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $adminRoleIds = DB::table('roles')
            ->whereIn('name', ['super-admin', 'Administrador', 'administrador', 'ADMINISTRADOR', 'admin', 'Admin'])
            ->pluck('id');

        $permissionIds = DB::table('permissions')->where('guard_name', 'web')->pluck('id');

        foreach ($adminRoleIds as $roleId) {
            $assigned = DB::table('role_has_permissions')
                ->where('role_id', $roleId)
                ->pluck('permission_id')
                ->all();

            $missing = $permissionIds->diff($assigned)
                ->map(fn ($permissionId) => ['permission_id' => $permissionId, 'role_id' => $roleId])
                ->values()
                ->all();

            if (!empty($missing)) {
                DB::table('role_has_permissions')->insert($missing);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        //
    }
};
