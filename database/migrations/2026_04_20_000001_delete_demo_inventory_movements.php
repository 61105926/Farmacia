<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $demoNotes = [
            'Compra inicial de producto',
            'Venta a cliente',
            'Reposición de stock',
            'Ajuste por inventario físico',
        ];

        DB::table('stock_movements')->whereIn('notes', $demoNotes)->delete();
        DB::table('inventories')->whereIn('notes', $demoNotes)->delete();
    }

    public function down(): void
    {
        // No reversible
    }
};
