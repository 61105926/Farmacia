<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * invoice_items.quantity era integer pero recibe cantidades decimales
     * copiadas de sale_items.quantity (decimal 10,3). PostgreSQL rechaza
     * "1.000" en columnas enteras, lo que impedía generar facturas.
     */
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
};
