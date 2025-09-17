<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_cliente',
        'nombre_comercial',
        'razon_social',
        'tipo_documento',
        'numero_documento',
        'direccion',
        'ciudad',
        'provincia',
        'codigo_postal',
        'telefono_principal',
        'telefono_secundario',
        'email_principal',
        'email_secundario',
        'contacto_nombre',
        'contacto_cargo',
        'contacto_telefono',
        'limite_credito',
        'dias_credito',
        'tipo_cliente',
        'descuento_general',
        'activo',
        'observaciones',
        'horario_atencion',
        'zona_reparto',
        'configuraciones',
        'ultimo_pedido',
        'total_compras',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'limite_credito' => 'decimal:2',
        'descuento_general' => 'decimal:2',
        'total_compras' => 'decimal:2',
        'activo' => 'boolean',
        'configuraciones' => 'array',
        'ultimo_pedido' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'activo' => true,
        'tipo_documento' => 'RUC',
        'tipo_cliente' => 'regular',
        'limite_credito' => 0,
        'dias_credito' => 0,
        'descuento_general' => 0,
        'total_compras' => 0
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopeByTipoCliente($query, $tipo)
    {
        return $query->where('tipo_cliente', $tipo);
    }

    public function scopeByZona($query, $zona)
    {
        return $query->where('zona_reparto', $zona);
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return $this->nombre_comercial . ' (' . $this->razon_social . ')';
    }

    public function getEstadoCreditoAttribute()
    {
        if ($this->limite_credito <= 0) {
            return 'Sin crédito';
        }

        // Aquí calcularias el saldo actual vs límite
        return 'Con crédito';
    }

    public function getDiasUltimoPedidoAttribute()
    {
        if (!$this->ultimo_pedido) {
            return null;
        }

        return $this->ultimo_pedido->diffInDays(now());
    }

    // Mutators
    public function setCodigoClienteAttribute($value)
    {
        $this->attributes['codigo_cliente'] = strtoupper($value);
    }

    public function setNombreComercialAttribute($value)
    {
        $this->attributes['nombre_comercial'] = ucwords(strtolower($value));
    }

    public function setRazonSocialAttribute($value)
    {
        $this->attributes['razon_social'] = strtoupper($value);
    }

    // Métodos helper
    public static function generateCodigoCliente()
    {
        $currentYear = date('Y');
        $lastPharmacy = static::where('codigo_cliente', 'like', "CLI-%-{$currentYear}")
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(codigo_cliente, "-", 2), "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($lastPharmacy) {
            $lastNumber = (int) explode('-', $lastPharmacy->codigo_cliente)[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return "CLI-{$nextNumber}-{$currentYear}";
    }

    // Relaciones
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function updateTotalCompras()
    {
        // Este método se implementará cuando tengamos el modelo de ventas
        // $this->update(['total_compras' => $this->ventas()->sum('total')]);
    }
}
