<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['product', 'creator']);

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

        $movements = $query->latest('movement_date')->paginate(20)->withQueryString();

        return Inertia::render('Inventory/Index', [
            'movements' => $movements,
            'products' => Product::active()->get(['id', 'name', 'code']),
            'movementTypes' => Inventory::getMovementTypes(),
            'transactionTypes' => Inventory::getTransactionTypes(),
            'filters' => $request->only([
                'search', 'movement_type', 'transaction_type', 
                'product_id', 'date_from', 'date_to'
            ]),
        ]);
    }

    public function movements(Request $request)
    {
        $query = Inventory::with(['product', 'creator']);

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

        $movements = $query->latest('movement_date')->paginate(15)->withQueryString();

        return Inertia::render('Inventory/Movements', [
            'movements' => $movements,
            'products' => Product::active()->get(['id', 'name', 'code']),
            'filters' => $request->only(['product_id', 'date_from', 'date_to']),
        ]);
    }

    public function stock(Request $request)
    {
        // Obtener stock actual por producto
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
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

    public function create()
    {
        return Inertia::render('Inventory/Create', [
            'products' => Product::active()->get(['id', 'name', 'code', 'stock_quantity']),
            'movementTypes' => Inventory::getMovementTypes(),
            'transactionTypes' => Inventory::getTransactionTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:purchase,sale,return,adjustment,transfer,damage,expiry',
            'transaction_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'movement_date' => 'required|date',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date|after:today',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer',
            'reference_number' => 'nullable|string|max:100',
        ]);

        // Obtener producto y stock actual
        $product = Product::findOrFail($validated['product_id']);
        $previousStock = $product->stock_quantity;

        // Calcular nuevo stock
        $quantityChange = $validated['transaction_type'] === 'in' 
            ? $validated['quantity'] 
            : -$validated['quantity'];

        $newStock = max(0, $previousStock + $quantityChange);

        // Crear movimiento de inventario
        $movement = Inventory::create(array_merge($validated, [
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'created_by' => Auth::id(),
        ]));

        // Actualizar stock del producto
        $product->update(['stock_quantity' => $newStock]);

        return redirect('/inventario')->with('success', 'Movimiento de inventario registrado exitosamente');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['product', 'creator', 'reference']);

        return Inertia::render('Inventory/Show', [
            'movement' => $inventory,
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
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->paginate(20);

        return Inertia::render('Inventory/LowStock', [
            'products' => $products,
        ]);
    }

    public function expired()
    {
        // Productos próximos a vencer (próximos 30 días)
        $products = Product::whereHas('inventoryMovements', function ($query) {
            $query->where('expiry_date', '<=', now()->addDays(30))
                  ->where('expiry_date', '>', now());
        })
        ->with('category')
        ->paginate(20);

        return Inertia::render('Inventory/Expired', [
            'products' => $products,
        ]);
    }
}
