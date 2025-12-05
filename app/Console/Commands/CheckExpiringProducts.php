<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckExpiringProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-expiring-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar productos próximos a vencer y crear notificaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando productos próximos a vencer...');

        $today = Carbon::today();
        $days30 = $today->copy()->addDays(30);
        $days15 = $today->copy()->addDays(15);
        $days7 = $today->copy()->addDays(7);

        // Productos que vencen en los próximos 30 días
        $expiringProducts = Product::whereNotNull('expiry_date')
            ->where('is_active', true)
            ->where('expiry_date', '>=', $today)
            ->where('expiry_date', '<=', $days30)
            ->where('stock_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        if ($expiringProducts->isEmpty()) {
            $this->info('No hay productos próximos a vencer.');
            return 0;
        }

        // Obtener administradores y usuarios de inventario
        $notifyUsers = collect();
        
        // Intentar obtener usuarios por roles (pueden no existir todos)
        $rolesToCheck = [
            'super-admin',
            'Administrador', 
            'administrador',
            'bodeguero',
            'Inventario'
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

        foreach ($expiringProducts as $product) {
            $daysUntilExpiry = $today->diffInDays($product->expiry_date, false);
            
            // Determinar el tipo de notificación según los días restantes
            $type = 'info';
            $urgency = '';
            
            if ($daysUntilExpiry <= 7) {
                $type = 'error';
                $urgency = 'CRÍTICO';
            } elseif ($daysUntilExpiry <= 15) {
                $type = 'warning';
                $urgency = 'URGENTE';
            } else {
                $type = 'warning';
                $urgency = 'ATENCIÓN';
            }

            // Verificar si ya se notificó hoy sobre este producto
            $alreadyNotified = DB::table('notifications')
                ->where('type', $type)
                ->whereDate('created_at', $today)
                ->whereJsonContains('data->product_id', $product->id)
                ->whereJsonContains('data->check_date', $todayStr)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $message = "{$product->name} vence en {$daysUntilExpiry} día(s) - Stock: {$product->stock_quantity} unidades";
            if ($product->code) {
                $message .= " (Código: {$product->code})";
            }

            // Crear notificación para todos los usuarios relevantes
            foreach ($notifyUsers as $user) {
                NotificationHelper::create(
                    $user,
                    "{$urgency}: Producto Próximo a Vencer",
                    $message,
                    $type,
                    '/productos/' . $product->id,
                    [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'expiry_date' => $product->expiry_date->format('Y-m-d'),
                        'days_until_expiry' => $daysUntilExpiry,
                        'stock_quantity' => $product->stock_quantity,
                        'check_date' => $todayStr,
                    ]
                );
            }

            $notifiedCount++;
        }

        $this->info("✓ Notificaciones creadas para {$notifiedCount} producto(s) próximos a vencer.");
        return 0;
    }
}
