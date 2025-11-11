<?php

namespace Database\Seeders;

use App\Models\PriceList;
use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen listas de precios
        if (PriceList::count() > 0) {
            $this->command->info('Ya existen listas de precios. Omitiendo seeder.');
            return;
        }

        $priceLists = [
            [
                'name' => 'Lista General',
                'code' => 'GEN',
                'description' => 'Lista de precios general para todos los clientes',
                'type' => 'fixed',
                'adjustment_value' => 0.00,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Lista Mayorista',
                'code' => 'MAY',
                'description' => 'Lista de precios con descuento para mayoristas',
                'type' => 'percentage',
                'adjustment_value' => 15.00, // 15% de descuento
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Lista Minorista',
                'code' => 'MIN',
                'description' => 'Lista de precios para clientes minoristas',
                'type' => 'fixed',
                'adjustment_value' => 0.00,
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($priceLists as $list) {
            PriceList::create($list);
        }

        $this->command->info('✅ Listas de precios creadas exitosamente.');
    }
}

