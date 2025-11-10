<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Presale;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first();
        $clients = Client::all();
        $products = Product::all();
        $presales = Presale::where('status', 'converted')->get();

        if ($clients->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️  No hay clientes o productos. Ejecuta primero ClientSeeder y ProductSeeder.');
            return;
        }

        $saleCounter = Sale::count();
        $paymentMethods = ['cash', 'credit', 'transfer'];

        // Crear ventas con diferentes estados
        $sales = [
            // Venta completada (efectivo)
            [
                'code' => 'VEN' . str_pad($saleCounter + 1, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'presale_id' => null,
                'status' => 'completed',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'delivery_date' => Carbon::now()->subDays(2),
                'notes' => 'Venta completada en efectivo',
                'created_by' => $adminUser->id,
                'completed_at' => Carbon::now()->subDays(2),
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 25, 'unit_price' => 1.20, 'discount' => 0],
                    ['product_id' => $products->random()->id, 'quantity' => 20, 'unit_price' => 1.80, 'discount' => 5],
                ],
            ],
            // Venta completada (crédito)
            [
                'code' => 'VEN' . str_pad($saleCounter + 2, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'presale_id' => null,
                'status' => 'completed',
                'payment_method' => 'credit',
                'payment_status' => 'pending',
                'delivery_date' => Carbon::now()->subDays(1),
                'notes' => 'Venta a crédito',
                'created_by' => $adminUser->id,
                'completed_at' => Carbon::now()->subDays(1),
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 40, 'unit_price' => 2.50, 'discount' => 3],
                    ['product_id' => $products->random()->id, 'quantity' => 30, 'unit_price' => 1.80, 'discount' => 0],
                ],
            ],
            // Venta desde preventa convertida
            [
                'code' => 'VEN' . str_pad($saleCounter + 3, 6, '0', STR_PAD_LEFT),
                'client_id' => $presales->first()?->client_id ?? $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'presale_id' => $presales->first()?->id,
                'status' => 'completed',
                'payment_method' => 'transfer',
                'payment_status' => 'paid',
                'delivery_date' => Carbon::now()->subDays(3),
                'notes' => 'Venta generada desde preventa convertida',
                'created_by' => $adminUser->id,
                'completed_at' => Carbon::now()->subDays(3),
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 50, 'unit_price' => 1.20, 'discount' => 5],
                    ['product_id' => $products->random()->id, 'quantity' => 40, 'unit_price' => 2.50, 'discount' => 0],
                ],
            ],
            // Venta en borrador
            [
                'code' => 'VEN' . str_pad($saleCounter + 4, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'presale_id' => null,
                'status' => 'draft',
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'delivery_date' => Carbon::now()->addDays(5),
                'notes' => 'Venta en borrador',
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 15, 'unit_price' => 3.00, 'discount' => 0],
                    ['product_id' => $products->random()->id, 'quantity' => 10, 'unit_price' => 2.50, 'discount' => 2],
                ],
            ],
            // Venta cancelada
            [
                'code' => 'VEN' . str_pad($saleCounter + 5, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'presale_id' => null,
                'status' => 'cancelled',
                'payment_method' => 'credit',
                'payment_status' => 'pending',
                'delivery_date' => Carbon::now()->addDays(7),
                'notes' => 'Venta cancelada',
                'created_by' => $adminUser->id,
                'cancelled_at' => Carbon::now()->subDays(1),
                'cancelled_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 20, 'unit_price' => 1.80, 'discount' => 0],
                ],
            ],
            // Venta reciente completada
            [
                'code' => 'VEN' . str_pad($saleCounter + 6, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients->random()->id,
                'salesperson_id' => $adminUser->id,
                'presale_id' => null,
                'status' => 'completed',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'delivery_date' => Carbon::now(),
                'notes' => 'Venta reciente completada',
                'created_by' => $adminUser->id,
                'completed_at' => Carbon::now(),
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 35, 'unit_price' => 1.20, 'discount' => 3],
                    ['product_id' => $products->random()->id, 'quantity' => 25, 'unit_price' => 2.50, 'discount' => 5],
                    ['product_id' => $products->random()->id, 'quantity' => 20, 'unit_price' => 1.80, 'discount' => 0],
                ],
            ],
        ];

        foreach ($sales as $saleData) {
            $items = $saleData['items'];
            unset($saleData['items']);

            // Calcular totales
            $subtotal = 0;
            $totalDiscount = 0;

            foreach ($items as $itemData) {
                $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                $itemDiscount = $itemSubtotal * ($itemData['discount'] / 100);
                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;
            }

            $saleData['subtotal'] = $subtotal;
            $saleData['total_discount'] = $totalDiscount;
            $saleData['total'] = $subtotal - $totalDiscount;

            $sale = Sale::create($saleData);

            // Crear items de la venta
            foreach ($items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                    $itemDiscount = $itemSubtotal * ($itemData['discount'] / 100);
                    $itemTotal = $itemSubtotal - $itemDiscount;

                    SaleItem::create([
                        'sale_id' => $sale->id,
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

            $this->command->info("Venta creada: {$sale->code} - Estado: {$sale->status} - Pago: {$sale->payment_status}");
        }

        $this->command->info('✅ Ventas creadas exitosamente');
    }
}

