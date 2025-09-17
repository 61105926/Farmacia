<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@farmacia.com',
            'password' => Hash::make('admin123'),
            'telefono' => '123-456-7890',
            'cedula' => '12345678901',
            'direccion' => 'Oficina Principal',
            'estado' => 'activo',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('Administrador');
    }
}