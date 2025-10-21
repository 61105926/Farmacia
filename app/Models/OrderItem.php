<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_code',
        'product_name',
        'product_description',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
        'quantity_delivered',
        'quantity_cancelled',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity_delivered' => 'integer',
        'quantity_cancelled' => 'integer',
    ];

    // Relaciones
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getQuantityPendingAttribute(): int
    {
        return $this->quantity - $this->quantity_delivered - $this->quantity_cancelled;
    }

    public function getIsFullyDeliveredAttribute(): bool
    {
        return $this->quantity_delivered >= $this->quantity;
    }

    public function getIsPartiallyDeliveredAttribute(): bool
    {
        return $this->quantity_delivered > 0 && $this->quantity_delivered < $this->quantity;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->quantity_cancelled >= $this->quantity;
    }

    // Métodos
    public function calculateSubtotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountAmount = $subtotal * ($this->discount_percentage / 100);
        $taxAmount = ($subtotal - $discountAmount) * ($this->tax_rate / 100);
        $total = $subtotal - $discountAmount + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public function deliver(int $quantity): void
    {
        $this->increment('quantity_delivered', $quantity);
    }

    public function cancel(int $quantity): void
    {
        $this->increment('quantity_cancelled', $quantity);
    }
}