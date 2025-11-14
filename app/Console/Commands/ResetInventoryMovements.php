<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class ResetInventoryMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:reset-movements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pone en 0 todos los campos previous_stock y new_stock de los movimientos de inventario';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('¿Estás seguro de que deseas poner en 0 todos los movimientos de inventario? Esta acción no se puede deshacer.')) {
            $this->info('Operación cancelada.');
            return 0;
        }

        try {
            $this->info('Actualizando movimientos de inventario...');
            
            $updated = DB::table('inventories')
                ->update([
                    'previous_stock' => 0,
                    'new_stock' => 0,
                    'updated_at' => now(),
                ]);

            $this->info("✅ Se actualizaron {$updated} movimientos de inventario.");
            $this->info('Todos los campos previous_stock y new_stock han sido puestos en 0.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al actualizar movimientos: ' . $e->getMessage());
            return 1;
        }
    }
}
