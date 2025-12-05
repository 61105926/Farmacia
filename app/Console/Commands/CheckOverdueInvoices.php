<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-overdue-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar facturas vencidas y próximas a vencer y crear notificaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando facturas vencidas y próximas a vencer...');

        $today = Carbon::today();
        $days7 = $today->copy()->addDays(7);
        $days3 = $today->copy()->addDays(3);

        // Facturas vencidas (no pagadas) - incluye las que vencen hoy
        $overdueInvoices = Invoice::whereNotNull('due_date')
            ->where('due_date', '<=', $today) // <= para incluir las que vencen hoy
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('balance', '>', 0)
            ->with('client')
            ->orderBy('due_date', 'asc')
            ->get();

        // Facturas próximas a vencer (7 días) - excluye las que vencen hoy
        $upcomingInvoices = Invoice::whereNotNull('due_date')
            ->where('due_date', '>', $today) // > para excluir las que vencen hoy
            ->where('due_date', '<=', $days7)
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('balance', '>', 0)
            ->with('client')
            ->orderBy('due_date', 'asc')
            ->get();

        // Obtener usuarios a notificar
        $notifyUsers = collect();
        
        // Intentar obtener usuarios por roles (pueden no existir todos)
        $rolesToCheck = [
            'super-admin',
            'Administrador',
            'administrador',
            'cobrador',
            'Contador',
            'contabilidad'
        ];
        
        foreach ($rolesToCheck as $roleName) {
            try {
                $users = User::role($roleName)->get();
                $notifyUsers = $notifyUsers->merge($users);
            } catch (\Exception $e) {
                // El rol no existe, continuar
                continue;
            }
        }
        
        $notifyUsers = $notifyUsers->unique('id');

        if ($notifyUsers->isEmpty()) {
            $this->warn('No hay usuarios para notificar.');
            return 0;
        }

        $notifiedCount = 0;
        $todayStr = $today->format('Y-m-d');

        // Notificar facturas vencidas
        foreach ($overdueInvoices as $invoice) {
            // Calcular días vencidos correctamente
            // diffInDays sin segundo parámetro siempre da positivo, necesitamos calcular manualmente
            $daysOverdue = $today->diffInDays($invoice->due_date);
            
            // Si la fecha de vencimiento es hoy, considerar como 1 día vencido
            if ($invoice->due_date->isSameDay($today)) {
                $daysOverdue = 1;
            }
            
            // Verificar si ya se notificó hoy sobre esta factura
            $alreadyNotified = DB::table('notifications')
                ->where('type', 'error')
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->invoice_id', $invoice->id)
                ->whereJsonContains('data->check_date', $todayStr)
                ->whereJsonContains('data->type', 'overdue')
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $clientName = $invoice->client_name ?? ($invoice->client->name ?? 'N/A');
            
            // Mensaje más claro
            if ($daysOverdue == 1) {
                $message = "Factura {$invoice->invoice_number} vencida HOY - Cliente: {$clientName} - Saldo: $" . number_format($invoice->balance, 2);
            } else {
                $message = "Factura {$invoice->invoice_number} vencida hace {$daysOverdue} día(s) - Cliente: {$clientName} - Saldo: $" . number_format($invoice->balance, 2);
            }

            foreach ($notifyUsers as $user) {
                NotificationHelper::create(
                    $user,
                    'Factura Vencida',
                    $message,
                    'error',
                    '/cuentas-por-cobrar?filter=overdue',
                    [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'client_id' => $invoice->client_id,
                        'client_name' => $clientName,
                        'balance' => $invoice->balance,
                        'due_date' => $invoice->due_date->format('Y-m-d'),
                        'days_overdue' => $daysOverdue,
                        'check_date' => $todayStr,
                        'type' => 'overdue',
                    ]
                );
            }

            $notifiedCount++;
        }

        // Notificar facturas próximas a vencer
        foreach ($upcomingInvoices as $invoice) {
            // Calcular días hasta el vencimiento (positivo si está en el futuro)
            $daysUntilDue = $today->diffInDays($invoice->due_date, false);
            
            // Si la factura vence hoy o ya venció, no debería estar aquí, pero por si acaso
            if ($daysUntilDue <= 0) {
                continue; // Ya se maneja en facturas vencidas
            }
            
            // Solo notificar si faltan 3 días o menos
            if ($daysUntilDue > 3) {
                continue;
            }

            // Verificar si ya se notificó hoy sobre esta factura
            $alreadyNotified = DB::table('notifications')
                ->where('type', 'warning')
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->invoice_id', $invoice->id)
                ->whereJsonContains('data->check_date', $todayStr)
                ->whereJsonContains('data->type', 'upcoming')
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $clientName = $invoice->client_name ?? ($invoice->client->name ?? 'N/A');
            
            // Mensaje más claro según los días
            if ($daysUntilDue == 1) {
                $message = "Factura {$invoice->invoice_number} vence MAÑANA - Cliente: {$clientName} - Saldo: $" . number_format($invoice->balance, 2);
            } else {
                $message = "Factura {$invoice->invoice_number} vence en {$daysUntilDue} día(s) - Cliente: {$clientName} - Saldo: $" . number_format($invoice->balance, 2);
            }

            foreach ($notifyUsers as $user) {
                NotificationHelper::create(
                    $user,
                    'Factura Próxima a Vencer',
                    $message,
                    'warning',
                    '/cuentas-por-cobrar?filter=upcoming',
                    [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'client_id' => $invoice->client_id,
                        'client_name' => $clientName,
                        'balance' => $invoice->balance,
                        'due_date' => $invoice->due_date->format('Y-m-d'),
                        'days_until_due' => $daysUntilDue,
                        'check_date' => $todayStr,
                        'type' => 'upcoming',
                    ]
                );
            }

            $notifiedCount++;
        }

        $this->info("✓ Notificaciones creadas para {$notifiedCount} factura(s).");
        return 0;
    }
}
