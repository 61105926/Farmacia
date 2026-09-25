<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('system_settings', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('system_settings', 'phone')) {
                $table->string('phone', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['address', 'phone']);
        });
    }
};
