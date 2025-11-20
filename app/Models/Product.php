<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'category_id',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'min_stock',
        'max_stock',
        'unit_type',
        'is_active',
        'notes',
        'expiry_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'expiry_date' => 'date',
    ];

    // Relaciones
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(Inventory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    // Accessors
    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock_quantity <= 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock_quantity <= $this->min_stock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    public function getStockStatusTextAttribute()
    {
        switch ($this->stock_status) {
            case 'out_of_stock':
                return 'Sin Stock';
            case 'low_stock':
                return 'Stock Bajo';
            case 'in_stock':
                return 'En Stock';
            default:
                return 'Desconocido';
        }
    }

    // Métodos
    public static function generateCode()
    {
        $lastProduct = static::latest('id')->first();
        $nextId = $lastProduct ? $lastProduct->id + 1 : 1;
        return 'PROD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    public function updateStock($quantity, $type = 'set', $notes = null)
    {
        $currentStock = $this->stock_quantity;
        $newStock = $currentStock;

        switch ($type) {
            case 'add':
                $newStock = $currentStock + $quantity;
                break;
            case 'subtract':
                $newStock = max(0, $currentStock - $quantity);
                break;
            case 'set':
                $newStock = $quantity;
                break;
        }

        $this->update(['stock_quantity' => $newStock]);

        // Registrar movimiento si existe la tabla
        if (\Schema::hasTable('stock_movements')) {
            \DB::table('stock_movements')->insert([
                'product_id' => $this->id,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $currentStock,
                'new_stock' => $newStock,
                'notes' => $notes,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $newStock;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // Generar código si no existe
            if (empty($product->code)) {
                $product->code = static::generateCode();
            }
            
            // Generar slug SIEMPRE si no existe (obligatorio en la base de datos)
            if (empty($product->slug) && !empty($product->name)) {
                $slug = Str::slug($product->name);
                $slugCount = static::where('slug', 'like', $slug . '%')->count();
                if ($slugCount > 0) {
                    $slug = $slug . '-' . ($slugCount + 1);
                }
                $product->slug = $slug;
            } elseif (empty($product->slug)) {
                // Si no hay nombre, generar slug basado en código
                $slug = Str::slug($product->code ?? 'product-' . time());
                $product->slug = $slug;
            }
        });
        
        static::updating(function ($product) {
            // Actualizar slug si cambió el nombre
            if ($product->isDirty('name') && empty($product->slug)) {
                $slug = Str::slug($product->name);
                $slugCount = static::where('slug', 'like', $slug . '%')
                    ->where('id', '!=', $product->id)
                    ->count();
                if ($slugCount > 0) {
                    $slug = $slug . '-' . ($slugCount + 1);
                }
                $product->slug = $slug;
            }
        });
    }
}
