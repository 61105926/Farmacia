<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presale_items', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')
                ->constrained('batches')->nullOnDelete();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')
                ->constrained('batches')->nullOnDelete();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')
                ->constrained('batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('presale_items', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};
