<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'payment_date',
        'client_id',
        'invoice_id',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'bank_name',
        'account_number',
        'check_number',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Cliente al que pertenece el pago
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Factura relacionada
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Usuario que registró el pago
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que aprobó el pago
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope por método de pago
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope por rango de fechas
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope por cliente
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope para pagos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para pagos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para pagos cancelados
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Generar número de pago
     */
    public static function generatePaymentNumber(): string
    {
        $lastPayment = static::latest('id')->first();
        $nextNumber = $lastPayment ? intval(substr($lastPayment->payment_number, 3)) + 1 : 1;
        return 'PAG' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Métodos de pago disponibles
     */
    public static function getPaymentMethods(): array
    {
        return [
            'cash' => 'Efectivo',
            'transfer' => 'Transferencia',
            'check' => 'Cheque',
            'credit_card' => 'Tarjeta de Crédito',
            'debit_card' => 'Tarjeta de Débito',
            'other' => 'Otro',
        ];
    }

    /**
     * Estados disponibles
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'Pendiente',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            'rejected' => 'Rechazado',
        ];
    }

    /**
     * Verificar si puede ser aprobado
     */
    public function getCanApproveAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si puede ser cancelado
     */
    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, ['pending', 'completed']);
    }

    /**
     * Aprobar el pago
     */
    public function approve(User $user): bool
    {
        if (!$this->can_approve) {
            return false;
        }

        DB::beginTransaction();
        try {
            $this->update([
                'status' => 'completed',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Actualizar saldo de la factura
            if ($this->invoice_id) {
                $this->updateInvoiceBalance();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancelar el pago
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->can_cancel) {
            return false;
        }

        DB::beginTransaction();
        try {
            $this->update([
                'status' => 'cancelled',
                'notes' => $this->notes . ($reason ? "\nMotivo de cancelación: " . $reason : ''),
            ]);

            // Actualizar saldo de la factura
            if ($this->invoice_id && $this->status === 'completed') {
                $this->updateInvoiceBalance();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar saldo de la factura
     */
    protected function updateInvoiceBalance(): void
    {
        if (!$this->invoice_id) {
            return;
        }

        $invoice = $this->invoice;
        $totalPaid = $invoice->payments()->completed()->sum('amount');
        $balance = $invoice->total - $totalPaid;

        $paymentStatus = 'unpaid';
        if ($balance <= 0) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        $invoice->update([
            'paid_amount' => $totalPaid,
            'balance' => max(0, $balance),
            'payment_status' => $paymentStatus,
            'paid_at' => $balance <= 0 ? now() : null,
        ]);
    }
}