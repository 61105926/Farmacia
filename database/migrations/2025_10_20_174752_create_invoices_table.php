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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            // Relación con pedido
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');

            // Cliente
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->string('client_name');
            $table->string('client_tax_id')->nullable();
            $table->text('client_address')->nullable();

            // Usuario
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // Montos
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);

            // Estado
            $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('draft');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            // Método de pago
            $table->string('payment_method')->nullable();
            $table->text('payment_terms')->nullable();

            // Información adicional
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();

            // Tracking
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
