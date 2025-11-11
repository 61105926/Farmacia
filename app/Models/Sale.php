<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'invoice_number',
        'client_id',
        'salesperson_id',
        'presale_id',
        'subtotal',
        'total_discount',
        'total',
        'payment_method',
        'payment_status',
        'notes',
        'delivery_date',
        'status',
        'created_by',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con el cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación con el vendedor
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * Relación con la preventa origen
     */
    public function presale(): BelongsTo
    {
        return $this->belongsTo(Presale::class);
    }

    /**
     * Relación con el creador
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con quien canceló
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Relación con los items de la venta
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Relación con la factura generada
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por vendedor
     */
    public function scopeBySalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope para filtrar por cliente
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope para filtrar por método de pago
     */
    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope para filtrar por estado de pago
     */
    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Verificar si la venta puede ser editada
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar si la venta puede ser completada
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar si la venta puede ser cancelada
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'completed']);
    }

    /**
     * Obtener el estado formateado
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener el color del estado
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtener el método de pago formateado
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Efectivo',
            'credit' => 'Crédito',
            'transfer' => 'Transferencia',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener el estado de pago formateado
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'Pagado',
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
            default => 'Desconocido',
        };
    }
}
