<?php

namespace App\Console\Commands;

use App\Helpers\NotificationHelper;
use App\Models\User;
use Illuminate\Console\Command;

class CreateSampleNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:create-samples {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear notificaciones de ejemplo para probar el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuario con ID {$userId} no encontrado.");
                return 1;
            }
            $users = collect([$user]);
        } else {
            $users = User::where('status', 'active')->take(5)->get();
            if ($users->isEmpty()) {
                $this->error('No hay usuarios activos en el sistema.');
                return 1;
            }
        }

        $this->info("Creando notificaciones de ejemplo para {$users->count()} usuario(s)...");

        foreach ($users as $user) {
            // Notificación de éxito - Nueva venta
            NotificationHelper::success(
                $user,
                'Nueva Venta Realizada',
                "Se ha registrado una nueva venta por $2,500.00",
                '/ventas'
            );

            // Notificación de advertencia - Stock bajo
            NotificationHelper::warning(
                $user,
                'Stock Bajo',
                'Paracetamol 500mg tiene solo 10 unidades en stock',
                '/productos'
            );

            // Notificación de error - Factura vencida
            NotificationHelper::error(
                $user,
                'Factura Vencida',
                'La factura #FAC-001234 está vencida desde hace 5 días',
                '/cuentas-por-cobrar'
            );

            // Notificación informativa - Nuevo pedido
            NotificationHelper::info(
                $user,
                'Nuevo Pedido Recibido',
                'Cliente ABC Farmacia acaba de realizar un pedido por $1,800.00',
                '/preventas'
            );

            $this->line("  ✓ Notificaciones creadas para: {$user->name}");
        }

        $this->info("\n¡Notificaciones de ejemplo creadas exitosamente!");
        $this->line("Puedes verlas en el navbar del sistema.");
        
        return 0;
    }
}
