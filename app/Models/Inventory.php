<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'movement_type',
        'transaction_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'reference_type',
        'reference_id',
        'reference_number',
        'created_by',
        'movement_date',
        'unit_cost',
        'total_cost',
        'notes',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'quantity' => 'integer',
        'previous_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    // Relaciones
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    // Scopes
    public function scopeEntries($query)
    {
        return $query->where('transaction_type', 'in');
    }

    public function scopeExits($query)
    {
        return $query->where('transaction_type', 'out');
    }

    public function scopeByMovementType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Accessors
    public function getMovementTypeLabelAttribute(): string
    {
        $labels = [
            'purchase' => 'Compra',
            'sale' => 'Venta',
            'return' => 'Devolución',
            'adjustment' => 'Ajuste',
            'transfer' => 'Transferencia',
            'damage' => 'Daño',
            'expiry' => 'Vencimiento',
        ];

        return $labels[$this->movement_type] ?? $this->movement_type;
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        return $this->transaction_type === 'in' ? 'Entrada' : 'Salida';
    }

    // Métodos estáticos
    public static function getMovementTypes(): array
    {
        return [
            'purchase' => 'Compra',
            'sale' => 'Venta',
            'return' => 'Devolución',
            'adjustment' => 'Ajuste',
            'transfer' => 'Transferencia',
            'damage' => 'Daño',
            'expiry' => 'Vencimiento',
        ];
    }

    public static function getTransactionTypes(): array
    {
        return [
            'in' => 'Entrada',
            'out' => 'Salida',
        ];
    }
}
