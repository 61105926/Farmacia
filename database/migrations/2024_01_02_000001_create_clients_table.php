<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('business_name');
            $table->string('trade_name')->nullable();
            $table->string('tax_id', 50)->unique();
            $table->enum('client_type', ['pharmacy', 'chain', 'hospital', 'clinic', 'other']);
            $table->enum('category', ['A', 'B', 'C'])->default('B');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->default('Bolivia');
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Foreign keys to tables that will be created later
            $table->unsignedBigInteger('price_list_id')->nullable();
            $table->decimal('default_discount', 5, 2)->default(0.00);
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->integer('credit_days')->default(0);

            $table->foreignId('salesperson_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('collector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('zone', 100)->nullable();
            $table->enum('visit_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->enum('visit_frequency', ['weekly', 'biweekly', 'monthly'])->default('weekly');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('client_type');
            $table->index('salesperson_id');
            $table->index('collector_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
