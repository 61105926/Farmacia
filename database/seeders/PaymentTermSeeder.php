<?php

namespace Database\Seeders;

use App\Models\PaymentTerm;
use Illuminate\Database\Seeder;

class PaymentTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener las condiciones de pago desde la configuración
        $terms = config('payment_terms.terms', []);

        foreach ($terms as $term) {
            PaymentTerm::updateOrCreate(
                ['code' => $term['code']],
                [
                    'name' => $term['name'],
                    'days' => $term['days'],
                    'description' => $term['description'] ?? null,
                    'is_default' => $term['is_default'] ?? false,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Condiciones de pago sincronizadas desde configuración');
    }
}
