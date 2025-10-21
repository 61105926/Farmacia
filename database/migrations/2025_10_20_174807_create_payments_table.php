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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('payment_number')->unique();
            $table->date('payment_date');

            // Cliente y factura
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('restrict');

            // Montos
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('COP');

            // Método de pago
            $table->enum('payment_method', ['cash', 'transfer', 'check', 'credit_card', 'debit_card', 'other'])->default('cash');
            $table->string('payment_reference')->nullable()->comment('Número de referencia/transacción');

            // Información bancaria (si aplica)
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('check_number')->nullable();

            // Estado
            $table->enum('status', ['pending', 'completed', 'cancelled', 'rejected'])->default('completed');

            // Tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Información adicional
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
