<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Deja como condiciones de pago activas solo Efectivo, Crédito y Transferencia
     * (definidas en config/payment_terms.php). Las demás se desactivan, no se borran,
     * para no romper clientes que ya las tengan asignadas.
     */
    public function up(): void
    {
        $terms = config('payment_terms.terms', []);
        $codes = [];

        foreach ($terms as $term) {
            $codes[] = $term['code'];

            $values = [
                'name' => $term['name'],
                'days' => $term['days'],
                'description' => $term['description'] ?? null,
                'is_default' => $term['is_default'] ?? false,
                'is_active' => true,
                'updated_at' => now(),
            ];

            // Reusar una condición existente con el mismo código o nombre
            $existing = DB::table('payment_terms')
                ->where('code', $term['code'])
                ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($term['name'])])
                ->orderByRaw('code = ? DESC', [$term['code']])
                ->first();

            if ($existing) {
                DB::table('payment_terms')->where('id', $existing->id)
                    ->update($values + ['code' => $term['code']]);
            } else {
                DB::table('payment_terms')->insert($values + [
                    'code' => $term['code'],
                    'created_at' => now(),
                ]);
            }
        }

        if (!empty($codes)) {
            DB::table('payment_terms')->whereNotIn('code', $codes)
                ->update(['is_active' => false, 'is_default' => false, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Sin reversión: solo sincroniza datos
    }
};
