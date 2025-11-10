<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'due_date',
        'order_id',
        'sale_id',
        'client_id',
        'client_name',
        'client_tax_id',
        'client_address',
        'created_by',
        'approved_by',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'balance',
        'status',
        'payment_status',
        'payment_method',
        'payment_terms',
        'notes',
        'terms_and_conditions',
        'approved_at',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'payment_status_label',
    ];

    /**
     * Cliente al que pertenece la factura
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Venta relacionada
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Orden relacionada
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Usuario que creó la factura
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que aprobó la factura
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Items de la factura
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Cuentas por cobrar relacionadas
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
     * Scope para facturas pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para facturas pagadas
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope para facturas no pagadas
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope para facturas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('payment_status', ['unpaid', 'partial']);
    }

    /**
     * Scope pagos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Verificar si está vencida
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date < now() && in_array($this->payment_status, ['unpaid', 'partial']);
    }

    /**
     * Días de vencimiento
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    /**
     * Generar número de factura
     */
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = static::latest('id')->first();
        $nextNumber = $lastInvoice ? intval(substr($lastInvoice->invoice_number, 3)) + 1 : 1;
        return 'INV' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Estados disponibles
     */
    public static function getStatuses(): array
    {
        return [
            'draft' => 'Borrador',
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'paid' => 'Pagada',
            'partially_paid' => 'Parcialmente Pagada',
            'overdue' => 'Vencida',
            'cancelled' => 'Cancelada',
        ];
    }

    /**
     * Estados de pago disponibles
     */
    public static function getPaymentStatuses(): array
    {
        return [
            'unpaid' => 'Sin Pagar',
            'partial' => 'Parcial',
            'paid' => 'Pagado',
        ];
    }

    /**
     * Obtener el estado de pago formateado
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        if (!$this->payment_status) {
            return 'N/A';
        }
        $statuses = self::getPaymentStatuses();
        return $statuses[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    /**
     * Verificar si puede ser editada
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar si puede ser aprobada
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si puede ser cancelada
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['cancelled', 'paid']);
    }
}