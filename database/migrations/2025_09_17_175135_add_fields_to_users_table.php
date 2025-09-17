<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono')->nullable()->after('email');
            $table->string('cedula')->unique()->nullable()->after('telefono');
            $table->text('direccion')->nullable()->after('cedula');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('direccion');
            $table->timestamp('ultimo_acceso')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'cedula', 'direccion', 'estado', 'ultimo_acceso']);
        });
    }
};
