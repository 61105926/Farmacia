<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear permiso config.system si no existe
        $permissionId = DB::table('permissions')
            ->where('name', 'config.system')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name'       => 'config.system',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Asignar a todos los roles de administrador
        $adminRoles = DB::table('roles')
            ->whereIn('name', ['super-admin', 'Administrador', 'administrador', 'admin', 'Admin'])
            ->pluck('id');

        foreach ($adminRoles as $roleId) {
            $exists = DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->exists();

            if (!$exists) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id'       => $roleId,
                ]);
            }
        }

        // Limpiar cache de permisos de Spatie
        DB::table('cache')->where('key', 'like', '%spatie.permission.cache%')->delete();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'config.system')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
