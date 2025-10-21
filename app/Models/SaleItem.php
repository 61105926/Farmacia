<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'subtotal',
        'discount_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con la venta
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcular el subtotal del item
     */
    public function calculateSubtotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Calcular el descuento del item
     */
    public function calculateDiscount(): float
    {
        return $this->calculateSubtotal() * ($this->discount / 100);
    }

    /**
     * Calcular el total del item
     */
    public function calculateTotal(): float
    {
        return $this->calculateSubtotal() - $this->calculateDiscount();
    }

    /**
     * Actualizar totales del item
     */
    public function updateTotals(): void
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->discount_amount = $this->calculateDiscount();
        $this->total = $this->calculateTotal();
        $this->save();
    }
}
