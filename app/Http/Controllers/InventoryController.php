<?php

namespace App\Http\Controllers;

use App\Helpers\InertiaHelper;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\BatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Usar distinct para evitar duplicados
        $query = Inventory::with(['product', 'creator'])->distinct();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->get('movement_type'));
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->get('transaction_type'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('movement_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('movement_date', '<=', $request->get('date_to'));
        }

        // Ordenar por fecha y ID para evitar duplicados en la paginación
        $movements = $query->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Estadísticas
        $stats = [
            'totalProducts' => Product::count(),
            'entriesToday' => Inventory::where('transaction_type', 'in')
                ->whereDate('movement_date', today())
                ->sum('quantity'),
            'exitsToday' => Inventory::where('transaction_type', 'out')
                ->whereDate('movement_date', today())
                ->sum('quantity'),
            'lowStock' => Product::whereColumn('stock_quantity', '<=', 'min_stock')
                ->where('stock_quantity', '>', 0)
                ->count(),
        ];

        return Inertia::render('Inventory/Index', [
            'movements' => InertiaHelper::sanitizeData($movements),
            'products' => Product::active()->get(['id', 'name', 'code', 'description']),
            'movementTypes' => Inventory::getMovementTypes(),
            'transactionTypes' => Inventory::getTransactionTypes(),
            'stats' => $stats,
            'filters' => $request->only([
                'search', 'movement_type', 'transaction_type',
                'product_id', 'date_from', 'date_to'
            ]),
        ]);
    }

    public function movements(Request $request)
    {
        // Usar distinct para evitar duplicados
        $query = Inventory::with(['product', 'creator'])->distinct();

        // Filtros específicos para movimientos
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('movement_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('movement_date', '<=', $request->get('date_to'));
        }

        // Ordenar por fecha y ID para evitar duplicados en la paginación
        $movements = $query->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Inventory/Movements', [
            'movements' => $movements,
            'products' => Product::active()->get(['id', 'name', 'code', 'description']),
            'filters' => $request->only(['product_id', 'date_from', 'date_to']),
        ]);
    }

    public function stock(Request $request)
    {
        // Obtener stock actual por producto
        $query = Product::with([
            'category',
            'batches' => fn($q) => $q->where('status', 'active')->where('remaining_quantity', '>', 0)->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')->limit(1),
        ]);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('stock_status')) {
            $status = $request->get('stock_status');
            if ($status === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'min_stock');
            } elseif ($status === 'out') {
                $query->where('stock_quantity', 0);
            }
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Inventory/Stock', [
            'products' => $products,
            'categories' => ProductCategory::active()->get(),
            'filters' => $request->only(['search', 'category_id', 'stock_status']),
        ]);
    }

    public function create(Request $request)
    {
        try {
            // Verificar si la tabla existe
            if (!Schema::hasTable('products')) {
                return Inertia::render('Inventory/Create', [
                    'products' => [],
                    'movementTypes' => Inventory::getMovementTypes(),
                    'transactionTypes' => Inventory::getTransactionTypes(),
                    'error' => 'La tabla de productos no existe. Por favor, crea las tablas primero.'
                ]);
            }

            // Cargar productos activos, si no hay, cargar todos
            $products = Product::active()
                ->select('id', 'name', 'code', 'description', 'stock_quantity', 'cost_price', 'sale_price')
                ->orderBy('name', 'asc')
                ->get();

            if ($products->isEmpty()) {
                $products = Product::select('id', 'name', 'code', 'description', 'stock_quantity', 'cost_price', 'sale_price')
                    ->orderBy('name', 'asc')
                    ->get();
            }

            return Inertia::render('Inventory/Create', [
                'products'            => InertiaHelper::sanitizeData($products),
                'movementTypes'       => Inventory::getMovementTypes(),
                'transactionTypes'    => Inventory::getTransactionTypes(),
                'selectedProductId'   => $request->integer('product') ?: null,
                'selectedBatchNumber' => $request->string('batch') ?: null,
            ]);
            
        } catch (\Exception $e) {
            return Inertia::render('Inventory/Create', [
                'products' => [],
                'movementTypes' => Inventory::getMovementTypes(),
                'transactionTypes' => Inventory::getTransactionTypes(),
                'error' => 'Error al cargar productos: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Preprocesar datos antes de validar
            $data = $request->all();
            
            // Convertir valores vacíos a null para campos opcionales
            if (isset($data['expiry_date']) && $data['expiry_date'] === '') {
                $data['expiry_date'] = null;
            }
            if (isset($data['reference_id']) && ($data['reference_id'] === '' || $data['reference_id'] === 'null')) {
                $data['reference_id'] = null;
            }
            if (isset($data['unit_cost']) && ($data['unit_cost'] === '' || $data['unit_cost'] === null)) {
                $data['unit_cost'] = null;
            }
            if (isset($data['total_cost']) && ($data['total_cost'] === '' || $data['total_cost'] === null)) {
                $data['total_cost'] = null;
            }
            
            // Validación
            $validated = validator($data, [
                'product_id'       => 'required|exists:products,id',
                'movement_type'    => 'required|in:purchase,sale,return,adjustment,transfer,damage,expiry',
                'transaction_type' => 'required|in:in,out',
                'quantity'         => 'required|integer|min:1',
                'movement_date'    => 'required|date',
                'unit_cost'        => 'nullable|numeric|min:0',
                'total_cost'       => 'nullable|numeric|min:0',
                'notes'            => 'nullable|string|max:1000',
                'batch_number'     => 'required_if:transaction_type,in|nullable|string|max:100',
                'expiry_date'      => 'nullable|date',
                'supplier'         => 'nullable|string|max:200',
                'reference_type'   => 'nullable|string|max:100',
                'reference_id'     => 'nullable|integer',
                'reference_number' => 'nullable|string|max:100',
            ])->validate();

            // Obtener producto y stock actual
            $product = Product::findOrFail($validated['product_id']);
            $previousStock = $product->stock_quantity ?? 0;

            // Calcular nuevo stock
            $quantityChange = $validated['transaction_type'] === 'in'
                ? $validated['quantity']
                : -$validated['quantity'];

            $newStock = max(0, $previousStock + $quantityChange);

            // Calcular total_cost si no se proporciona pero hay unit_cost y quantity
            if (!isset($validated['total_cost']) || $validated['total_cost'] === null) {
                if (isset($validated['unit_cost']) && $validated['unit_cost'] !== null && $validated['unit_cost'] > 0) {
                    $validated['total_cost'] = $validated['unit_cost'] * $validated['quantity'];
                } else {
                    $validated['total_cost'] = null;
                }
            }

            $batchId = null;

            // Si es entrada de stock (compra/devolución), crear lote
            if ($validated['transaction_type'] === 'in' && !empty($validated['batch_number'])) {
                $batchService = new BatchService();
                $batch = $batchService->createBatch(
                    productId:   $validated['product_id'],
                    quantity:    $validated['quantity'],
                    batchNumber: $validated['batch_number'],
                    expiryDate:  $validated['expiry_date'] ?? null,
                    entryDate:   $validated['movement_date'],
                    costPrice:   $validated['unit_cost'] ?? null,
                    supplier:    $validated['supplier'] ?? null,
                    notes:       $validated['notes'] ?? null,
                );
                $batchId = $batch->id;
            }

            // Si es salida manual, descontar de lotes FEFO
            if ($validated['transaction_type'] === 'out') {
                $batchService = new BatchService();
                $result = $batchService->deductFifo($validated['product_id'], $validated['quantity'], $validated['notes'] ?? '');
                $batchId = $result['batch_id'];
                if ($batchId) {
                    $batchUsed = \App\Models\Batch::find($batchId);
                    $validated['batch_number'] = $batchUsed?->batch_number;
                }
            }

            // Crear movimiento de inventario
            $movement = Inventory::create(array_merge($validated, [
                'previous_stock' => $previousStock,
                'new_stock'      => $newStock,
                'created_by'     => Auth::id(),
                'batch_id'       => $batchId,
            ]));

            // Actualizar stock del producto
            $product->update(['stock_quantity' => $newStock]);

            return redirect('/inventario')->with('success', 'Movimiento de inventario registrado exitosamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Error al registrar movimiento: ' . $e->getMessage()]);
        }
    }

    public function show(Inventory $inventory)
    {
        // Solo cargar product y creator, reference es opcional y puede causar error si la clase no existe
        $inventory->load(['product', 'creator']);

        return Inertia::render('Inventory/Show', [
            'movement' => InertiaHelper::sanitizeData($inventory),
        ]);
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:500',
            'movement_date' => 'required|date',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $previousStock = $product->stock_quantity;
        $newStock = $validated['new_quantity'];
        $quantityChange = $newStock - $previousStock;

        if ($quantityChange != 0) {
            // Crear movimiento de ajuste
            Inventory::create([
                'product_id' => $validated['product_id'],
                'movement_type' => 'adjustment',
                'transaction_type' => $quantityChange > 0 ? 'in' : 'out',
                'quantity' => abs($quantityChange),
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'movement_date' => $validated['movement_date'],
                'notes' => $validated['reason'],
                'created_by' => Auth::id(),
            ]);

            // Actualizar stock del producto
            $product->update(['stock_quantity' => $newStock]);
        }

        return redirect('/inventario')->with('success', 'Ajuste de inventario realizado exitosamente');
    }

    public function lowStock()
    {
        $products = Product::whereColumn('stock_quantity', '<=', 'min_stock')
            ->with([
                'category',
                'batches' => fn($q) => $q->where('status', 'active')->where('remaining_quantity', '>', 0)->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')->limit(1),
            ])
            ->orderBy('stock_quantity', 'asc')
            ->paginate(20);

        return Inertia::render('Inventory/LowStock', [
            'products' => $products,
        ]);
    }

    public function expired()
    {
        $threshold = now()->addDays(90);

        $products = Product::where(function ($q) use ($threshold) {
            $q->whereNotNull('expiry_date')
              ->where('expiry_date', '<=', $threshold);
        })->orWhereHas('inventoryMovements', function ($q) use ($threshold) {
            $q->whereNotNull('expiry_date')
              ->where('expiry_date', '<=', $threshold);
        })
        ->with([
            'category',
            'batches' => fn($q) => $q->where('status', 'active')->where('remaining_quantity', '>', 0)->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')->limit(1),
        ])
        ->paginate(20);

        return Inertia::render('Inventory/Expired', [
            'products' => $products,
        ]);
    }

}
