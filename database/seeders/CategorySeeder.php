<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Medicamentos',
                'description' => 'Medicamentos con y sin receta médica',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Analgésicos y Antiinflamatorios',
                'description' => 'Medicamentos para dolor e inflamación',
                'is_active' => true,
                'sort_order' => 2,
                'parent_name' => 'Medicamentos',
            ],
            [
                'name' => 'Antibióticos',
                'description' => 'Antibióticos de uso común',
                'is_active' => true,
                'sort_order' => 3,
                'parent_name' => 'Medicamentos',
            ],
            [
                'name' => 'Antigripales',
                'description' => 'Medicamentos para resfriados y gripe',
                'is_active' => true,
                'sort_order' => 4,
                'parent_name' => 'Medicamentos',
            ],
            [
                'name' => 'Vitamins y Suplementos',
                'description' => 'Vitaminas, minerales y suplementos nutricionales',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Higiene Personal',
                'description' => 'Productos de higiene y cuidado personal',
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Cuidado del Cabello',
                'description' => 'Shampoos, acondicionadores y tratamientos capilares',
                'is_active' => true,
                'sort_order' => 21,
                'parent_name' => 'Higiene Personal',
            ],
            [
                'name' => 'Cuidado Dental',
                'description' => 'Pasta dental, cepillos y enjuagues bucales',
                'is_active' => true,
                'sort_order' => 22,
                'parent_name' => 'Higiene Personal',
            ],
            [
                'name' => 'Cuidado de la Piel',
                'description' => 'Cremas, lociones y productos para el cuidado de la piel',
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'name' => 'Antisépticos',
                'description' => 'Alcohol, agua oxigenada y otros antisépticos',
                'is_active' => true,
                'sort_order' => 40,
            ],
            [
                'name' => 'Material Médico',
                'description' => 'Gasas, vendas, algodón y material médico',
                'is_active' => true,
                'sort_order' => 50,
            ],
            [
                'name' => 'Accesorios',
                'description' => 'Termómetros, mascarillas y otros accesorios',
                'is_active' => true,
                'sort_order' => 60,
            ],
            [
                'name' => 'Infantil',
                'description' => 'Productos específicos para bebés y niños',
                'is_active' => true,
                'sort_order' => 70,
            ],
            [
                'name' => 'Cuidado Femenino',
                'description' => 'Productos de higiene íntima y cuidado femenino',
                'is_active' => true,
                'sort_order' => 80,
            ],
            [
                'name' => 'Otros',
                'description' => 'Otros productos farmacéuticos y de farmacia',
                'is_active' => true,
                'sort_order' => 99,
            ],
        ];

        // Primero creamos las categorías principales (sin parent)
        foreach ($categories as $categoryData) {
            if (!isset($categoryData['parent_name'])) {
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($categoryData['name'])],
                    [
                        'name' => $categoryData['name'],
                        'description' => $categoryData['description'] ?? null,
                        'is_active' => $categoryData['is_active'] ?? true,
                        'sort_order' => $categoryData['sort_order'] ?? 0,
                    ]
                );

                $this->command->info("Categoría creada: {$category->name}");
            }
        }

        // Ahora creamos las categorías hijas (con parent)
        foreach ($categories as $categoryData) {
            if (isset($categoryData['parent_name'])) {
                $parent = Category::where('name', $categoryData['parent_name'])->first();
                
                if ($parent) {
                    $category = Category::firstOrCreate(
                        ['slug' => Str::slug($categoryData['name'])],
                        [
                            'name' => $categoryData['name'],
                            'description' => $categoryData['description'] ?? null,
                            'parent_id' => $parent->id,
                            'is_active' => $categoryData['is_active'] ?? true,
                            'sort_order' => $categoryData['sort_order'] ?? 0,
                        ]
                    );

                    $this->command->info("Subcategoría creada: {$category->name} (padre: {$parent->name})");
                }
            }
        }

        $this->command->info('✅ Categorías creadas exitosamente');
    }
}

