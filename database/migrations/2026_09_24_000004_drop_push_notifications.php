<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Se quitaron las notificaciones push: borrar su tabla y las claves VAPID
     */
    public function up(): void
    {
        Schema::dropIfExists('push_subscriptions');

        foreach (['vapid_public_key', 'vapid_private_key'] as $column) {
            if (Schema::hasColumn('system_settings', $column)) {
                Schema::table('system_settings', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        //
    }
};
