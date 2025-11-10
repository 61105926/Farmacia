<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first();
        $categories = Category::all();

        $products = [
            [
                'code' => 'PROD001',
                'name' => 'Paracetamol 500mg',
                'description' => 'Analgésico y antipirético',
                'category_id' => $categories->where('name', 'Analgésicos y Antiinflamatorios')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 0.50,
                'sale_price' => 1.20,
                'stock_quantity' => 1000,
                'min_stock' => 100,
                'max_stock' => 2000,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'PROD002',
                'name' => 'Ibuprofeno 400mg',
                'description' => 'Antiinflamatorio no esteroideo',
                'category_id' => $categories->where('name', 'Analgésicos y Antiinflamatorios')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 0.75,
                'sale_price' => 1.80,
                'stock_quantity' => 800,
                'min_stock' => 80,
                'max_stock' => 1500,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'PROD003',
                'name' => 'Amoxicilina 500mg',
                'description' => 'Antibiótico de amplio espectro - Requiere receta médica',
                'category_id' => $categories->where('name', 'Antibióticos')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 2.50,
                'sale_price' => 5.00,
                'stock_quantity' => 500,
                'min_stock' => 50,
                'max_stock' => 1000,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'PROD004',
                'name' => 'Vitamina C 1000mg',
                'description' => 'Suplemento vitamínico',
                'category_id' => $categories->where('name', 'Vitamins y Suplementos')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 1.20,
                'sale_price' => 2.50,
                'stock_quantity' => 600,
                'min_stock' => 60,
                'max_stock' => 1200,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'PROD005',
                'name' => 'Jabón Antibacterial',
                'description' => 'Jabón líquido antibacterial',
                'category_id' => $categories->where('name', 'Higiene Personal')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 1.50,
                'sale_price' => 3.00,
                'stock_quantity' => 400,
                'min_stock' => 40,
                'max_stock' => 800,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'PROD006',
                'name' => 'Shampoo Anticaspa',
                'description' => 'Shampoo para tratamiento de caspa',
                'category_id' => $categories->where('name', 'Cuidado del Cabello')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 2.00,
                'sale_price' => 4.50,
                'stock_quantity' => 300,
                'min_stock' => 30,
                'max_stock' => 600,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'PROD007',
                'name' => 'Aspirina 100mg',
                'description' => 'Analgésico y antiagregante plaquetario',
                'category_id' => $categories->where('name', 'Analgésicos y Antiinflamatorios')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 0.40,
                'sale_price' => 1.00,
                'stock_quantity' => 1200,
                'min_stock' => 120,
                'max_stock' => 2500,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'PROD008',
                'name' => 'Antigripal Completo',
                'description' => 'Medicamento para síntomas de gripe',
                'category_id' => $categories->where('name', 'Antigripales')->first()?->id ?? $categories->first()?->id,
                'cost_price' => 1.80,
                'sale_price' => 3.50,
                'stock_quantity' => 700,
                'min_stock' => 70,
                'max_stock' => 1400,
                'unit_type' => 'unidad',
                'is_active' => true,
                'created_by' => $adminUser->id ?? 1,
            ],
        ];

        foreach ($products as $productData) {
            $productData['slug'] = Str::slug($productData['name']);
            Product::firstOrCreate(
                ['code' => $productData['code']],
                $productData
            );
            $this->command->info("Producto creado: {$productData['name']}");
        }

        $this->command->info('✅ Productos creados exitosamente');
    }
}

