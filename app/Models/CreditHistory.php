<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'amount',
        'balance',
        'description',
        'reference_id',
        'reference_type',
        'changer_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    /**
     * Cliente al que pertenece el historial
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Usuario que realizó el cambio
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changer_id');
    }

    /**
     * Referencia polimórfica (puede ser una venta, pago, etc.)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scope para movimientos de crédito otorgado
     */
    public function scopeCreditGranted($query)
    {
        return $query->where('type', 'credit_granted');
    }

    /**
     * Scope para movimientos de crédito usado
     */
    public function scopeCreditUsed($query)
    {
        return $query->where('type', 'credit_used');
    }

    /**
     * Scope para pagos recibidos
     */
    public function scopePaymentReceived($query)
    {
        return $query->where('type', 'payment_received');
    }

    /**
     * Scope para ajustes de crédito
     */
    public function scopeCreditAdjustment($query)
    {
        return $query->where('type', 'credit_adjustment');
    }

    /**
     * Scope para movimientos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para movimientos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para movimientos cancelados
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
