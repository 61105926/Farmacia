<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Helpers\InertiaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): Response
    {
        try {
            // Verificar si la tabla existe
            if (!Schema::hasTable('products')) {
                return Inertia::render('Products/Index', [
                    'products' => [],
                    'categories' => [],
                    'filters' => $request->only(['search', 'category', 'status', 'stock_status']),
                    'error' => 'La tabla de productos no existe. Por favor, crea las tablas primero.'
                ]);
            }

            $query = Product::with('category');

            // Filtros
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('code', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            if ($request->filled('stock_status')) {
                switch ($request->stock_status) {
                    case 'low_stock':
                        $query->where('stock_quantity', '<=', 10)->where('stock_quantity', '>', 0);
                        break;
                    case 'out_of_stock':
                        $query->where('stock_quantity', '<=', 0);
                        break;
                    case 'in_stock':
                        $query->where('stock_quantity', '>', 10);
                        break;
                }
            }

            $products = $query->latest()->paginate(15)->withQueryString();

            // Agregar acciones disponibles a cada producto
            $products->getCollection()->transform(function ($product) {
                $product->availableActions = $this->getAvailableActions($product);
                return $product;
            });

            // Cargar categorías para filtros
            $categories = Schema::hasTable('categories') ?
                Category::select('id', 'name')->get() :
                collect();

            // Estadísticas de productos
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::where('is_active', true)->count(),
                'low_stock_products' => Product::whereRaw('stock_quantity <= 10')->where('stock_quantity', '>', 0)->count(),
                'out_of_stock_products' => Product::where('stock_quantity', '<=', 0)->count(),
                'total_value' => Product::sum(DB::raw('stock_quantity * cost_price')),
            ];

            return Inertia::render('Products/Index', [
                'products' => InertiaHelper::sanitizeData($products),
                'categories' => InertiaHelper::sanitizeData($categories),
                'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'category', 'status', 'stock_status'])),
                'stats' => InertiaHelper::sanitizeData($stats),
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController index error: ' . $e->getMessage());
            
            return Inertia::render('Products/Index', [
                'products' => [],
                'categories' => [],
                'filters' => $request->only(['search', 'category', 'status', 'stock_status']),
                'error' => 'Error al cargar productos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): Response
    {
        try {
            // Verificar si la tabla existe
            if (!Schema::hasTable('products')) {
                return Inertia::render('Products/Create', [
                    'categories' => [],
                    'error' => 'La tabla de productos no existe. Por favor, crea las tablas primero.'
                ]);
            }

            // Cargar categorías
            $categories = Schema::hasTable('categories') ? 
                Category::select('id', 'name')->get() : 
                collect();

            return Inertia::render('Products/Create', [
                'categories' => $categories,
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController create error: ' . $e->getMessage());
            
            return Inertia::render('Products/Create', [
                'categories' => [],
                'error' => 'Error al cargar formulario: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): RedirectResponse
    {
        \Log::info('ProductController store - Datos recibidos:', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        \Log::info('ProductController store - Datos validados:', $validated);

        try {
            // Verificar que la tabla existe
            if (!Schema::hasTable('products')) {
                return back()->with('error', 'La tabla de productos no existe. Por favor, crea las tablas primero.');
            }

            // Crear producto usando consulta directa
            $productId = DB::table('products')->insertGetId([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'min_stock' => $validated['min_stock'],
                'max_stock' => $validated['max_stock'],
                'unit' => $validated['unit'],
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('ProductController store - Producto creado exitosamente con ID:', $productId);
            
            return redirect()->route('products.index')
                ->with('success', 'Producto creado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('ProductController store - Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): Response
    {
        try {
            $product->load(['category', 'createdBy:id,name']);

            // Estadísticas del producto
            $stats = [
                'total_sales' => 0,
                'total_presales' => 0,
                'total_revenue' => 0,
            ];

            // Calcular estadísticas si las tablas existen
            if (Schema::hasTable('sale_items')) {
                $stats['total_sales'] = DB::table('sale_items')
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                $stats['total_revenue'] = DB::table('sale_items')
                    ->where('product_id', $product->id)
                    ->sum('total');
            }

            if (Schema::hasTable('presale_items')) {
                $stats['total_presales'] = DB::table('presale_items')
                    ->where('product_id', $product->id)
                    ->sum('quantity');
            }

            // Obtener acciones disponibles para el producto
            $availableActions = $this->getAvailableActions($product);

            return Inertia::render('Products/Show', [
                'product' => $product,
                'stats' => $stats,
                'availableActions' => $availableActions,
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController show error: ' . $e->getMessage());

            return redirect()->route('products.index')
                ->with('error', 'Error al cargar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product): Response
    {
        try {
            // Cargar categorías
            $categories = Schema::hasTable('categories') ? 
                Category::select('id', 'name')->get() : 
                collect();

            return Inertia::render('Products/Edit', [
                'product' => $product,
                'categories' => $categories,
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController edit error: ' . $e->getMessage());
            
            return redirect()->route('products.index')
                ->with('error', 'Error al cargar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        \Log::info('ProductController update - Datos recibidos:', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        \Log::info('ProductController update - Datos validados:', $validated);

        try {
            // Actualizar producto usando consulta directa
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'description' => $validated['description'],
                    'category_id' => $validated['category_id'],
                    'purchase_price' => $validated['purchase_price'],
                    'sale_price' => $validated['sale_price'],
                    'stock_quantity' => $validated['stock_quantity'],
                    'min_stock' => $validated['min_stock'],
                    'max_stock' => $validated['max_stock'],
                    'unit' => $validated['unit'],
                    'is_active' => $validated['is_active'] ?? true,
                    'notes' => $validated['notes'],
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            \Log::info('ProductController update - Producto actualizado exitosamente');
            
            return redirect()->route('products.show', $product)
                ->with('success', 'Producto actualizado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('ProductController update - Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            // Verificar si el producto está siendo usado
            $hasSales = Schema::hasTable('sale_items') ? 
                DB::table('sale_items')->where('product_id', $product->id)->exists() : false;
                
            $hasPresales = Schema::hasTable('presale_items') ? 
                DB::table('presale_items')->where('product_id', $product->id)->exists() : false;

            if ($hasSales || $hasPresales) {
                return back()->with('error', 'No se puede eliminar el producto porque está siendo usado en ventas o preventas.');
            }

            DB::table('products')->where('id', $product->id)->delete();

            return redirect()->route('products.index')
                ->with('success', 'Producto eliminado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('ProductController destroy error: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        try {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'is_active' => !$product->is_active,
                    'updated_at' => now(),
                ]);

            $status = !$product->is_active ? 'activado' : 'desactivado';
            
            return back()->with('success', "Producto {$status} exitosamente.");

        } catch (\Exception $e) {
            \Log::error('ProductController toggleStatus error: ' . $e->getMessage());
            
            return back()->with('error', 'Error al cambiar el estado del producto: ' . $e->getMessage());
        }
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'type' => 'required|in:add,subtract,set',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $currentStock = $product->stock_quantity;
            $newStock = $currentStock;

            switch ($validated['type']) {
                case 'add':
                    $newStock = $currentStock + $validated['quantity'];
                    break;
                case 'subtract':
                    $newStock = max(0, $currentStock - $validated['quantity']);
                    break;
                case 'set':
                    $newStock = $validated['quantity'];
                    break;
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'stock_quantity' => $newStock,
                    'updated_at' => now(),
                ]);

            // Registrar movimiento de stock si existe la tabla
            if (Schema::hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id' => $product->id,
                    'type' => $validated['type'],
                    'quantity' => $validated['quantity'],
                    'previous_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'notes' => $validated['notes'],
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return back()->with('success', 'Stock actualizado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('ProductController updateStock error: ' . $e->getMessage());
            
            return back()->with('error', 'Error al actualizar el stock: ' . $e->getMessage());
        }
    }

    /**
     * Get low stock products
     */
    public function lowStock(): Response
    {
        try {
            if (!Schema::hasTable('products')) {
                return Inertia::render('Products/LowStock', [
                    'products' => [],
                    'error' => 'La tabla de productos no existe.'
                ]);
            }

            $products = Product::whereColumn('stock_quantity', '<=', 'min_stock')
                ->where('stock_quantity', '>', 0)
                ->with('category')
                ->orderBy('stock_quantity', 'asc')
                ->get();

            return Inertia::render('Products/LowStock', [
                'products' => $products,
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController lowStock error: ' . $e->getMessage());
            
            return Inertia::render('Products/LowStock', [
                'products' => [],
                'error' => 'Error al cargar productos con stock bajo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get out of stock products
     */
    public function outOfStock(): Response
    {
        try {
            if (!Schema::hasTable('products')) {
                return Inertia::render('Products/OutOfStock', [
                    'products' => [],
                    'error' => 'La tabla de productos no existe.'
                ]);
            }

            $products = Product::where('stock_quantity', '<=', 0)
                ->with('category')
                ->orderBy('name', 'asc')
                ->get();

            return Inertia::render('Products/OutOfStock', [
                'products' => $products,
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController outOfStock error: ' . $e->getMessage());
            
            return Inertia::render('Products/OutOfStock', [
                'products' => [],
                'error' => 'Error al cargar productos sin stock: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Gestión de inventario
     */
    public function inventory(Request $request): Response
    {
        try {
            if (!Schema::hasTable('products')) {
                return Inertia::render('Products/Inventory', [
                    'products' => [],
                    'categories' => [],
                    'stats' => [],
                    'error' => 'La tabla de productos no existe.'
                ]);
            }

            $query = Product::with('category');

            // Filtros
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('code', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }

            if ($request->filled('stock_status')) {
                switch ($request->stock_status) {
                    case 'in_stock':
                        $query->where('stock_quantity', '>', 10);
                        break;
                    case 'low_stock':
                        $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
                        break;
                    case 'out_of_stock':
                        $query->where('stock_quantity', '<=', 0);
                        break;
                }
            }

            $products = $query->latest()->paginate(15)->withQueryString();

            // Estadísticas
            $stats = [
                'total_products' => Product::count(),
                'total_value' => Product::sum(DB::raw('stock_quantity * cost_price')),
                'low_stock' => Product::whereRaw('stock_quantity <= 10')->where('stock_quantity', '>', 0)->count(),
                'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            ];

            return Inertia::render('Products/Inventory', [
                'products' => InertiaHelper::sanitizeData($products),
                'categories' => InertiaHelper::sanitizeData(Category::select('id', 'name')->get()),
                'stats' => InertiaHelper::sanitizeData($stats),
                'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'category', 'stock_status'])),
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController inventory error: ' . $e->getMessage());
            
            return Inertia::render('Products/Inventory', [
                'products' => [],
                'categories' => [],
                'stats' => [],
                'error' => 'Error al cargar inventario: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ajustar stock de producto
     */
    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $oldStock = $product->stock_quantity;
            $adjustment = $request->quantity;

            switch ($request->type) {
                case 'in':
                    $product->stock_quantity += $adjustment;
                    break;
                case 'out':
                    $product->stock_quantity -= $adjustment;
                    break;
                case 'adjustment':
                    $product->stock_quantity = $adjustment;
                    break;
            }

            $product->save();

            // Registrar movimiento de stock
            DB::table('stock_movements')->insert([
                'product_id' => $product->id,
                'type' => $request->type,
                'quantity' => $adjustment,
                'old_stock' => $oldStock,
                'new_stock' => $product->stock_quantity,
                'reason' => $request->reason,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Stock ajustado exitosamente.');

        } catch (\Exception $e) {
            \Log::error('ProductController adjustStock error: ' . $e->getMessage());
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage());
        }
    }

    /**
     * Obtener categorías
     */
    public function categories(): Response
    {
        try {
            if (!Schema::hasTable('categories')) {
                return Inertia::render('Products/Categories', [
                    'categories' => [],
                    'error' => 'La tabla de categorías no existe.'
                ]);
            }

            $categories = Category::withCount('products')->latest()->get();

            return Inertia::render('Products/Categories', [
                'categories' => InertiaHelper::sanitizeData($categories),
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductController categories error: ' . $e->getMessage());

            return Inertia::render('Products/Categories', [
                'categories' => [],
                'error' => 'Error al cargar categorías: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Determinar las acciones disponibles para un producto
     *
     * @param Product $product
     * @return array
     */
    private function getAvailableActions(Product $product): array
    {
        // Verificar si el producto está siendo usado en ventas o preventas
        $hasSales = Schema::hasTable('sale_items') ?
            DB::table('sale_items')->where('product_id', $product->id)->exists() : false;

        $hasPresales = Schema::hasTable('presale_items') ?
            DB::table('presale_items')->where('product_id', $product->id)->exists() : false;

        $isUsedInTransactions = $hasSales || $hasPresales;

        return [
            // Ver siempre está disponible (ya estamos en la vista)
            'canView' => true,

            // Editar: siempre disponible
            'canEdit' => true,

            // Activar/Desactivar: siempre disponible
            // Si está activo, se puede desactivar; si está inactivo, se puede activar
            'canToggleStatus' => true,
            'currentStatus' => $product->is_active,
            'toggleAction' => $product->is_active ? 'deactivate' : 'activate',
            'toggleLabel' => $product->is_active ? 'Desactivar' : 'Activar',

            // Eliminar: NO está disponible si el producto se usa en transacciones
            // (El usuario pidió quitar el botón de eliminar, pero dejamos la lógica por si acaso)
            'canDelete' => false, // Usuario pidió explícitamente que NO haya botón eliminar
            'deleteDisabledReason' => $isUsedInTransactions
                ? 'El producto está siendo usado en ventas o preventas'
                : null,

            // Información adicional
            'isUsedInTransactions' => $isUsedInTransactions,
            'hasSales' => $hasSales,
            'hasPresales' => $hasPresales,
        ];
    }
}