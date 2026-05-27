<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('SISPANDO');
            $table->string('logo_path')->nullable();
            $table->string('logo_icon_path')->nullable();
            $table->timestamps();
        });

        // Insert default row
        DB::table('system_settings')->insert([
            'site_name'      => 'SISPANDO',
            'logo_path'      => null,
            'logo_icon_path' => null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
