<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'business_name',
        'trade_name',
        'tax_id',
        'client_type',
        'category',
        'status',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'website',
        'price_list_id',
        'default_discount',
        'payment_term_id',
        'credit_limit',
        'credit_days',
        'salesperson_id',
        'collector_id',
        'zone',
        'visit_day',
        'visit_frequency',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'default_discount' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
    ];

    /**
     * Lista de precios asignada
     */
    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    /**
     * Condición de pago
     */
    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    /**
     * Vendedor/Preventista asignado
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * Cobrador asignado
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    /**
     * Usuario que creó el cliente
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Contactos del cliente
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * Contacto principal
     */
    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    /**
     * Direcciones de entrega
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    /**
     * Dirección predeterminada
     */
    public function defaultAddress()
    {
        return $this->hasOne(ClientAddress::class)->where('is_default', true);
    }

    /**
     * Preventas del cliente
     */
    public function presales(): HasMany
    {
        return $this->hasMany(Presale::class);
    }

    /**
     * Ventas del cliente
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Órdenes del cliente
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Facturas del cliente
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Cuentas por cobrar
     */
    public function receivables(): HasMany
    {
        return $this->hasMany(Receivable::class);
    }

    /**
     * Pagos recibidos
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Historial de crédito del cliente
     */
    public function creditHistory(): HasMany
    {
        return $this->hasMany(CreditHistory::class);
    }

    /**
     * Calcular saldo pendiente
     */
    public function getPendingBalanceAttribute(): float
    {
        return $this->receivables()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('balance');
    }

    /**
     * Calcular crédito disponible
     */
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->pending_balance);
    }

    /**
     * Verificar si tiene facturas vencidas
     */
    public function hasOverdueInvoices(): bool
    {
        return $this->receivables()
            ->where('status', 'overdue')
            ->exists();
    }

    /**
     * Verificar si excede límite de crédito
     */
    public function exceedsCreditLimit(): bool
    {
        return $this->pending_balance > $this->credit_limit;
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para clientes bloqueados
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Scope por tipo de cliente
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('client_type', $type);
    }

    /**
     * Scope por categoría
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope por vendedor
     */
    public function scopeBySalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope por cobrador
     */
    public function scopeByCollector($query, int $collectorId)
    {
        return $query->where('collector_id', $collectorId);
    }

    /**
     * Scope por zona
     */
    public function scopeInZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Scope búsqueda
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('business_name', 'like', "%{$search}%")
              ->orWhere('trade_name', 'like', "%{$search}%")
              ->orWhere('tax_id', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }
}
