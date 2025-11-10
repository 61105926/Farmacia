<?php

namespace Database\Seeders;

use App\Models\Receivable;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReceivableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first();
        $invoices = Invoice::whereIn('payment_status', ['unpaid', 'partial'])->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('⚠️  No hay facturas sin pagar. Ejecuta primero InvoiceSeeder.');
            return;
        }

        foreach ($invoices as $invoice) {
            // Solo crear receivables para facturas que tienen saldo pendiente
            if ($invoice->balance > 0) {
                $status = 'pending';
                
                // Si está vencida, cambiar estado
                if ($invoice->due_date && $invoice->due_date < now()) {
                    $status = 'overdue';
                } elseif ($invoice->paid_amount > 0) {
                    $status = 'partial';
                }

                Receivable::firstOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                        'client_id' => $invoice->client_id,
                    ],
                    [
                        'client_id' => $invoice->client_id,
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->total,
                        'balance' => $invoice->balance,
                        'due_date' => $invoice->due_date ?? $invoice->invoice_date->addDays(30),
                        'status' => $status,
                        'notes' => "Cuenta por cobrar generada automáticamente para factura {$invoice->invoice_number}",
                        'created_by' => $adminUser->id ?? 1,
                    ]
                );

                $this->command->info("Cuenta por cobrar creada para factura: {$invoice->invoice_number} - Estado: {$status}");
            }
        }

        $this->command->info('✅ Cuentas por cobrar creadas exitosamente');
    }
}

