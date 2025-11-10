<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
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

        // Función auxiliar para generar número de factura
        $invoiceCounter = Invoice::count();
        
        // Crear facturas con diferentes estados
        $invoices = [
            // Factura pagada completamente
            [
                'invoice_number' => 'INV' . str_pad($invoiceCounter + 1, 6, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays(45),
                'due_date' => Carbon::now()->subDays(15),
                'client_id' => $clients->random()->id,
                'client_name' => $clients->random()->business_name,
                'client_tax_id' => $clients->random()->tax_id,
                'client_address' => $clients->random()->address,
                'subtotal' => 500.00,
                'discount_amount' => 25.00,
                'tax_amount' => 90.25,
                'total' => 565.25,
                'paid_amount' => 565.25,
                'balance' => 0.00,
                'status' => 'approved',
                'payment_status' => 'paid',
                'payment_method' => 'transfer',
                'payment_terms' => '30 días',
                'approved_by' => $adminUser->id,
                'approved_at' => Carbon::now()->subDays(44),
                'paid_at' => Carbon::now()->subDays(20),
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 50, 'unit_price' => 10.00],
                ],
            ],
            // Factura parcialmente pagada
            [
                'invoice_number' => 'INV' . str_pad($invoiceCounter + 2, 6, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays(30),
                'due_date' => Carbon::now()->subDays(0),
                'client_id' => $clients->random()->id,
                'client_name' => $clients->random()->business_name,
                'client_tax_id' => $clients->random()->tax_id,
                'client_address' => $clients->random()->address,
                'subtotal' => 1200.00,
                'discount_amount' => 60.00,
                'tax_amount' => 216.60,
                'total' => 1356.60,
                'paid_amount' => 678.30,
                'balance' => 678.30,
                'status' => 'approved',
                'payment_status' => 'partial',
                'payment_method' => 'credit',
                'payment_terms' => '30 días',
                'approved_by' => $adminUser->id,
                'approved_at' => Carbon::now()->subDays(29),
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 100, 'unit_price' => 12.00],
                ],
            ],
            // Factura vencida sin pagar
            [
                'invoice_number' => 'INV' . str_pad($invoiceCounter + 3, 6, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays(45),
                'due_date' => Carbon::now()->subDays(15),
                'client_id' => $clients->random()->id,
                'client_name' => $clients->random()->business_name,
                'client_tax_id' => $clients->random()->tax_id,
                'client_address' => $clients->random()->address,
                'subtotal' => 800.00,
                'discount_amount' => 40.00,
                'tax_amount' => 144.40,
                'total' => 904.40,
                'paid_amount' => 0.00,
                'balance' => 904.40,
                'status' => 'approved',
                'payment_status' => 'unpaid',
                'payment_method' => 'credit',
                'payment_terms' => '30 días',
                'approved_by' => $adminUser->id,
                'approved_at' => Carbon::now()->subDays(44),
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 80, 'unit_price' => 10.00],
                ],
            ],
            // Factura pendiente de pago (no vencida)
            [
                'invoice_number' => 'INV' . str_pad($invoiceCounter + 4, 6, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(20),
                'client_id' => $clients->random()->id,
                'client_name' => $clients->random()->business_name,
                'client_tax_id' => $clients->random()->tax_id,
                'client_address' => $clients->random()->address,
                'subtotal' => 1500.00,
                'discount_amount' => 75.00,
                'tax_amount' => 270.75,
                'total' => 1695.75,
                'paid_amount' => 0.00,
                'balance' => 1695.75,
                'status' => 'approved',
                'payment_status' => 'unpaid',
                'payment_method' => 'credit',
                'payment_terms' => '30 días',
                'approved_by' => $adminUser->id,
                'approved_at' => Carbon::now()->subDays(9),
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 150, 'unit_price' => 10.00],
                ],
            ],
            // Factura reciente parcialmente pagada
            [
                'invoice_number' => 'INV' . str_pad($invoiceCounter + 5, 6, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(25),
                'client_id' => $clients->random()->id,
                'client_name' => $clients->random()->business_name,
                'client_tax_id' => $clients->random()->tax_id,
                'client_address' => $clients->random()->address,
                'subtotal' => 2000.00,
                'discount_amount' => 100.00,
                'tax_amount' => 361.00,
                'total' => 2261.00,
                'paid_amount' => 1130.50,
                'balance' => 1130.50,
                'status' => 'approved',
                'payment_status' => 'partial',
                'payment_method' => 'credit',
                'payment_terms' => '30 días',
                'approved_by' => $adminUser->id,
                'approved_at' => Carbon::now()->subDays(4),
                'created_by' => $adminUser->id,
                'items' => [
                    ['product_id' => $products->random()->id, 'quantity' => 200, 'unit_price' => 10.00],
                ],
            ],
        ];

        foreach ($invoices as $invoiceData) {
            $items = $invoiceData['items'];
            unset($invoiceData['items']);

            $invoice = Invoice::create($invoiceData);

            // Crear items de la factura
            foreach ($items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $subtotal = $itemData['quantity'] * $itemData['unit_price'];
                    $taxRate = 19.00;
                    $taxAmount = $subtotal * ($taxRate / 100);
                    $total = $subtotal + $taxAmount;
                    
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'product_description' => $product->description,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'subtotal' => $subtotal,
                        'total' => $total,
                    ]);
                }
            }

            $this->command->info("Factura creada: {$invoice->invoice_number} - Estado: {$invoice->payment_status}");
        }

        $this->command->info('✅ Facturas creadas exitosamente');
    }
}

