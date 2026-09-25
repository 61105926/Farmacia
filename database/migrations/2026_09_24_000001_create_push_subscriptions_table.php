<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('endpoint');
                $table->string('endpoint_hash', 64)->unique();
                $table->string('public_key');
                $table->string('auth_token');
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });
        }

        // Claves VAPID del servidor (se generan solas la primera vez)
        Schema::table('system_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('system_settings', 'vapid_public_key')) {
                $table->text('vapid_public_key')->nullable();
            }
            if (!Schema::hasColumn('system_settings', 'vapid_private_key')) {
                $table->text('vapid_private_key')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');

        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['vapid_public_key', 'vapid_private_key']);
        });
    }
};
