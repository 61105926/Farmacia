<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'product_id', 'batch_number', 'initial_quantity', 'remaining_quantity',
        'expiry_date', 'entry_date', 'cost_price', 'supplier', 'notes',
        'status', 'created_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'entry_date'  => 'date',
        'cost_price'  => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Lotes activos para un producto en orden FIFO (más antiguo primero)
    public function scopeActiveFifo($query, int $productId)
    {
        return $query->where('product_id', $productId)
                     ->where('status', 'active')
                     ->where('remaining_quantity', '>', 0)
                     ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')
                     ->orderBy('id', 'asc');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsLowAttribute(): bool
    {
        return $this->remaining_quantity > 0 && $this->remaining_quantity <= ($this->initial_quantity * 0.1);
    }
}
