<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first();
        $invoices = Invoice::whereIn('payment_status', ['partial', 'paid'])->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('⚠️  No hay facturas con pagos. Ejecuta primero InvoiceSeeder.');
            return;
        }

        $paymentMethods = ['cash', 'transfer', 'check', 'credit_card', 'debit_card', 'other'];
        $banks = ['Banco Pichincha', 'Banco Guayaquil', 'Banco del Pacífico', 'Banco Produbanco'];

        foreach ($invoices as $invoice) {
            if ($invoice->paid_amount > 0) {
                // Si está completamente pagada, crear un solo pago
                if ($invoice->payment_status === 'paid') {
                    Payment::firstOrCreate(
                        [
                            'invoice_id' => $invoice->id,
                            'client_id' => $invoice->client_id,
                        ],
                        [
                            'payment_number' => Payment::generatePaymentNumber(),
                            'payment_date' => $invoice->paid_at ?? $invoice->invoice_date->addDays(rand(5, 25)),
                            'client_id' => $invoice->client_id,
                            'invoice_id' => $invoice->id,
                            'amount' => $invoice->total,
                            'currency' => 'USD',
                            'payment_method' => in_array($invoice->payment_method ?? '', $paymentMethods) ? ($invoice->payment_method ?? $paymentMethods[array_rand($paymentMethods)]) : $paymentMethods[array_rand($paymentMethods)],
                            'payment_reference' => 'REF-' . strtoupper(uniqid()),
                            'bank_name' => in_array($invoice->payment_method ?? '', ['transfer', 'check']) ? $banks[array_rand($banks)] : null,
                            'status' => 'completed',
                            'created_by' => $adminUser->id ?? 1,
                            'approved_by' => $adminUser->id ?? 1,
                            'approved_at' => $invoice->paid_at ?? now(),
                            'notes' => "Pago completo de factura {$invoice->invoice_number}",
                        ]
                    );
                } else {
                    // Si está parcialmente pagada, crear un pago parcial
                    Payment::firstOrCreate(
                        [
                            'invoice_id' => $invoice->id,
                            'client_id' => $invoice->client_id,
                        ],
                        [
                            'payment_number' => Payment::generatePaymentNumber(),
                            'payment_date' => $invoice->invoice_date->addDays(rand(5, 20)),
                            'client_id' => $invoice->client_id,
                            'invoice_id' => $invoice->id,
                            'amount' => $invoice->paid_amount,
                            'currency' => 'USD',
                            'payment_method' => in_array($invoice->payment_method ?? '', $paymentMethods) ? ($invoice->payment_method ?? $paymentMethods[array_rand($paymentMethods)]) : $paymentMethods[array_rand($paymentMethods)],
                            'payment_reference' => 'REF-' . strtoupper(uniqid()),
                            'bank_name' => in_array($invoice->payment_method ?? '', ['transfer', 'check']) ? $banks[array_rand($banks)] : null,
                            'status' => 'completed',
                            'created_by' => $adminUser->id ?? 1,
                            'approved_by' => $adminUser->id ?? 1,
                            'approved_at' => $invoice->invoice_date->addDays(rand(5, 20)),
                            'notes' => "Pago parcial de factura {$invoice->invoice_number}",
                        ]
                    );
                }

                $this->command->info("Pago creado para factura: {$invoice->invoice_number} - Monto: {$invoice->paid_amount}");
            }
        }

        $this->command->info('✅ Pagos creados exitosamente');
    }
}

