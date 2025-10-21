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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Tipo de movimiento
            $table->enum('movement_type', ['purchase', 'sale', 'return', 'adjustment', 'transfer', 'damage', 'expiry'])->comment('Tipo de movimiento');
            $table->enum('transaction_type', ['in', 'out'])->comment('Entrada o salida');

            // Cantidades
            $table->integer('quantity')->comment('Cantidad del movimiento');
            $table->integer('previous_stock')->default(0)->comment('Stock anterior');
            $table->integer('new_stock')->default(0)->comment('Nuevo stock');

            // Referencia
            $table->string('reference_type')->nullable()->comment('Tipo de documento (order, invoice, etc)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID del documento');
            $table->string('reference_number')->nullable()->comment('Número de referencia');

            // Usuario y fecha
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->date('movement_date');

            // Información adicional
            $table->decimal('unit_cost', 10, 2)->nullable()->comment('Costo unitario');
            $table->decimal('total_cost', 12, 2)->nullable()->comment('Costo total');
            $table->text('notes')->nullable();
            $table->string('batch_number')->nullable()->comment('Número de lote');
            $table->date('expiry_date')->nullable()->comment('Fecha de vencimiento');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
