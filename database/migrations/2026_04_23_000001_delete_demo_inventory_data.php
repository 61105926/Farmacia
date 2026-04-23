<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('inventories')
            ->where('notes', 'like', 'Movimiento de demostración%')
            ->delete();

        DB::table('products')
            ->whereIn('code', ['PARA001', 'IBUP002', 'AMOX003'])
            ->whereIn('name', ['Paracetamol 500mg', 'Ibuprofeno 400mg', 'Amoxicilina 500mg'])
            ->delete();
    }

    public function down(): void {}
};
