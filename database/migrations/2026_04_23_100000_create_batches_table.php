<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('batch_number', 100);
            $table->integer('initial_quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->date('expiry_date')->nullable();
            $table->date('entry_date');
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->string('supplier', 200)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'depleted', 'expired'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'status', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
