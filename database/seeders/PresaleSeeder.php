<?php

namespace Database\Seeders;

use App\Models\Presale;
use App\Models\PresaleItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PresaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first();
        $clients = Client::all();
        $products = Product::all();

        if ($clients->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️  No hay clientes o productos. Ejecuta primero ClientSeeder y ProductSeeder.');
            return;
        }

        $presaleCounter = Presale::count();

        // Crear preventas con diferentes estados
        $presales = [
            // Preventa en borrador
            [
                'code' => 'PRE' . str_pad($presaleCounter + 1, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'status' => 'draft',
                'delivery_date' => Carbon::now()->addDays(7),
                'notes' => 'Preventa pendiente de confirmación',
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 20, 'unit_price' => 1.20, 'discount' => 0],
                    ['product_id' => $products->random()->id, 'quantity' => 15, 'unit_price' => 1.80, 'discount' => 5],
                ],
            ],
            // Preventa confirmada
            [
                'code' => 'PRE' . str_pad($presaleCounter + 2, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'status' => 'confirmed',
                'delivery_date' => Carbon::now()->addDays(5),
                'notes' => 'Preventa confirmada, lista para convertir',
                'created_by' => $adminUser->id,
                'confirmed_by' => $adminUser->id,
                'confirmed_at' => Carbon::now()->subDays(2),
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 30, 'unit_price' => 2.50, 'discount' => 3],
                    ['product_id' => $products->random()->id, 'quantity' => 25, 'unit_price' => 1.80, 'discount' => 0],
                ],
            ],
            // Preventa convertida a venta
            [
                'code' => 'PRE' . str_pad($presaleCounter + 3, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'status' => 'converted',
                'delivery_date' => Carbon::now()->subDays(3),
                'notes' => 'Preventa convertida exitosamente',
                'created_by' => $adminUser->id,
                'confirmed_by' => $adminUser->id,
                'confirmed_at' => Carbon::now()->subDays(10),
                'converted_by' => $adminUser->id,
                'converted_at' => Carbon::now()->subDays(5),
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 50, 'unit_price' => 1.20, 'discount' => 5],
                    ['product_id' => $products->random()->id, 'quantity' => 40, 'unit_price' => 2.50, 'discount' => 0],
                ],
            ],
            // Preventa cancelada
            [
                'code' => 'PRE' . str_pad($presaleCounter + 4, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'status' => 'cancelled',
                'delivery_date' => Carbon::now()->addDays(10),
                'notes' => 'Preventa cancelada por el cliente',
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 10, 'unit_price' => 3.50, 'discount' => 0],
                ],
            ],
            // Preventa confirmada reciente
            [
                'code' => 'PRE' . str_pad($presaleCounter + 5, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'status' => 'confirmed',
                'delivery_date' => Carbon::now()->addDays(3),
                'notes' => 'Preventa confirmada recientemente',
                'created_by' => $adminUser->id,
                'confirmed_by' => $adminUser->id,
                'confirmed_at' => Carbon::now()->subDays(1),
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 35, 'unit_price' => 1.80, 'discount' => 2],
                    ['product_id' => $products->random()->id, 'quantity' => 20, 'unit_price' => 2.50, 'discount' => 5],
                    ['product_id' => $products->random()->id, 'quantity' => 15, 'unit_price' => 3.00, 'discount' => 0],
                ],
            ],
        ];

        foreach ($presales as $presaleData) {
            $items = $presaleData['items'];
            unset($presaleData['items']);

            // Calcular totales
            $subtotal = 0;
            $totalDiscount = 0;

            foreach ($items as $itemData) {
                $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                $itemDiscount = $itemSubtotal * ($itemData['discount'] / 100);
                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;
            }

            $presaleData['subtotal'] = $subtotal;
            $presaleData['total_discount'] = $totalDiscount;
            $presaleData['total'] = $subtotal - $totalDiscount;

            $presale = Presale::create($presaleData);

            // Crear items de la preventa
            foreach ($items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                    $itemDiscount = $itemSubtotal * ($itemData['discount'] / 100);
                    $itemTotal = $itemSubtotal - $itemDiscount;

                    PresaleItem::create([
                        'presale_id' => $presale->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount' => $itemData['discount'],
                        'subtotal' => $itemSubtotal,
                        'discount_amount' => $itemDiscount,
                        'total' => $itemTotal,
                    ]);
                }
            }

            $this->command->info("Preventa creada: {$presale->code} - Estado: {$presale->status}");
        }

        $this->command->info('✅ Preventas creadas exitosamente');
    }
}

