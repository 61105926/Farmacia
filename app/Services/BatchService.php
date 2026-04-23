<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class BatchService
{
    /**
     * Descuenta stock de lotes en orden FIFO.
     * Retorna el batch_id del primer lote usado (para registrar en el item).
     *
     * @return array ['batch_id' => int|null, 'deductions' => [[batch_id, qty], ...]]
     */
    public function deductFifo(int $productId, int|float $quantity, string $notes = ''): array
    {
        $remaining = $quantity;
        $deductions = [];
        $primaryBatchId = null;

        $batches = Batch::activeFifo($productId)->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $take = min($batch->remaining_quantity, $remaining);
            $newRemaining = $batch->remaining_quantity - $take;

            $batch->remaining_quantity = $newRemaining;
            $batch->status = $newRemaining <= 0 ? 'depleted' : 'active';
            $batch->save();

            $deductions[] = ['batch_id' => $batch->id, 'quantity' => $take];
            if ($primaryBatchId === null) {
                $primaryBatchId = $batch->id;
            }

            $remaining -= $take;
        }

        // Si no había lotes suficientes, igualmente descontamos (stock general)
        return [
            'batch_id'   => $primaryBatchId,
            'deductions' => $deductions,
        ];
    }

    /**
     * Crea un lote nuevo al ingresar stock (compra/entrada).
     */
    public function createBatch(int $productId, int $quantity, string $batchNumber, ?string $expiryDate, string $entryDate, ?float $costPrice = null, ?string $supplier = null, ?string $notes = null): Batch
    {
        return Batch::create([
            'product_id'         => $productId,
            'batch_number'       => $batchNumber,
            'initial_quantity'   => $quantity,
            'remaining_quantity' => $quantity,
            'expiry_date'        => $expiryDate,
            'entry_date'         => $entryDate,
            'cost_price'         => $costPrice,
            'supplier'           => $supplier,
            'notes'              => $notes,
            'status'             => 'active',
            'created_by'         => Auth::id(),
        ]);
    }

    /**
     * Retorna los lotes activos de un producto con info resumida para el frontend.
     */
    public function getActiveBatchesForProduct(int $productId): array
    {
        return Batch::activeFifo($productId)->get()->map(function ($b) {
            return [
                'id'                 => $b->id,
                'batch_number'       => $b->batch_number,
                'remaining_quantity' => $b->remaining_quantity,
                'expiry_date'        => $b->expiry_date?->format('Y-m-d'),
                'entry_date'         => $b->entry_date->format('Y-m-d'),
                'supplier'           => $b->supplier,
            ];
        })->toArray();
    }
}
