<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Nombre sucursal/punto
            $table->text('address');
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('reference')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('delivery_hours')->nullable();
            $table->text('special_instructions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('client_id');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
