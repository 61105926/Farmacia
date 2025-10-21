<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presale extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'client_id',
        'salesperson_id',
        'subtotal',
        'total_discount',
        'total',
        'notes',
        'delivery_date',
        'status',
        'created_by',
        'confirmed_at',
        'confirmed_by',
        'converted_at',
        'converted_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'confirmed_at' => 'datetime',
        'converted_at' => 'datetime',
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
     * Relación con el creador
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con quien confirmó
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Relación con quien convirtió
     */
    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    /**
     * Relación con los items de la preventa
     */
    public function items(): HasMany
    {
        return $this->hasMany(PresaleItem::class);
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
     * Verificar si la preventa puede ser editada
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar si la preventa puede ser confirmada
     */
    public function canBeConfirmed(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar si la preventa puede ser convertida a venta
     */
    public function canBeConverted(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Obtener el estado formateado
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'confirmed' => 'Confirmada',
            'converted' => 'Convertida',
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
            'confirmed' => 'blue',
            'converted' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
