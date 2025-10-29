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
                'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'category', 'status', 'stock_status'])),
                'error' => 'La tabla de productos no existe. Por favor, crea las tablas primero.'
            ]);
            }

            $receivedFilters = $request->only(['search', 'category', 'status', 'stock_status']);
            

            $query = Product::with('category');

            // Filtros
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('code', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Filtro de categoría
            $category = $request->input('category');
            if ($category && $category !== '' && $category !== null) {
                $categoryId = is_numeric($category) ? (int) $category : null;
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }
            }

            // Filtro de estado
            $status = $request->input('status');
            if ($status && $status !== '' && $status !== null) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Filtro de stock
            $stockStatus = $request->input('stock_status');
            if ($stockStatus && $stockStatus !== '' && $stockStatus !== null) {
                switch ($stockStatus) {
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

            // Cargar categorías para filtros (solo categorías activas, ordenadas)
            $categories = Schema::hasTable('product_categories') ?
                Category::select('id', 'name')
                    ->where('is_active', true)
                    ->whereNull('parent_id') // Solo categorías principales para filtros
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get() :
                collect();

            // Estadísticas de productos
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::where('is_active', true)->count(),
                'low_stock_products' => Product::whereRaw('stock_quantity <= 10')->where('stock_quantity', '>', 0)->count(),
                'out_of_stock_products' => Product::where('stock_quantity', '<=', 0)->count(),
                'total_value' => Product::sum(DB::raw('stock_quantity * cost_price')),
            ];

            // Ensure filters are always passed, even if empty
            $currentFilters = $request->only(['search', 'category', 'status', 'stock_status']);
            $sanitizedFilters = InertiaHelper::sanitizeFilters($currentFilters);

            return Inertia::render('Products/Index', [
                'products' => InertiaHelper::sanitizeData($products),
                'categories' => InertiaHelper::sanitizeData($categories),
                'filters' => $sanitizedFilters,
                'stats' => InertiaHelper::sanitizeData($stats),
            ]);

        } catch (\Exception $e) {
            dd($e);
            
            return Inertia::render('Products/Index', [
                'products' => [],
                'categories' => [],
                'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'category', 'status', 'stock_status'])),
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

            // Cargar categorías (solo categorías activas, ordenadas)
            // Primero verificamos si la tabla existe y tiene datos
            $categories = collect();
            
            if (Schema::hasTable('product_categories')) {
                $categories = Category::select('id', 'name')
                    ->where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get();
                
                // Si no hay categorías activas, intentar cargar todas (por si acaso)
                if ($categories->isEmpty()) {
                    $categories = Category::select('id', 'name')
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();
                }
            }

            return Inertia::render('Products/Create', [
                'categories' => $categories,
            ]);

        } catch (\Exception $e) {
            dd($e);
            
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'brand' => 'nullable|string|max:255',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'unit_type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);


        try {
            // Verificar que la tabla existe
            if (!Schema::hasTable('products')) {
                return back()->with('error', 'La tabla de productos no existe. Por favor, crea las tablas primero.');
            }

            // Generar slug único
            $slug = \Str::slug($validated['name']);
            $slugCount = DB::table('products')->where('slug', 'like', $slug . '%')->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }

            // Crear producto usando consulta directa
            $productId = DB::table('products')->insertGetId([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'cost_price' => $validated['cost_price'],
                'base_price' => $validated['cost_price'],
                'sale_price' => $validated['sale_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'min_stock' => $validated['min_stock'],
                'max_stock' => $validated['max_stock'] ?? 0,
                'unit_type' => $validated['unit_type'] ?? 'unit',
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Producto creado exitosamente.');

        } catch (\Exception $e) {
            dd($e);
            
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
            dd($e);

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
            // Cargar categorías (solo categorías activas, ordenadas)
            $categories = Schema::hasTable('product_categories') ? 
                Category::select('id', 'name')
                    ->where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get() : 
                collect();

            return Inertia::render('Products/Edit', [
                'product' => $product,
                'categories' => $categories,
            ]);

        } catch (\Exception $e) {
            dd($e);
            
            return redirect()->route('products.index')
                ->with('error', 'Error al cargar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
 
        try {
            // Preparar datos antes de validar (convertir nulls a strings vacíos para campos nullable)
            $data = $request->all();
            
            // Convertir nulls a strings vacíos para campos nullable string
            $nullableStringFields = ['brand', 'description', 'active_ingredient', 'dosage', 'presentation', 'barcode'];
            foreach ($nullableStringFields as $field) {
                // Si el campo no existe o es null, establecerlo como string vacío
                if (!isset($data[$field]) || $data[$field] === null || $data[$field] === 'null') {
                    $data[$field] = '';
                } elseif (is_array($data[$field])) {
                    // Si es un array, tomar el primer elemento o convertir a string vacío
                    $data[$field] = !empty($data[$field]) ? (string) reset($data[$field]) : '';
                } else {
                    // Asegurarse de que sea string (convertir cualquier otro tipo)
                    $data[$field] = (string) $data[$field];
                }
            }
            
            // Manejar category_id - puede ser null o string vacío
            if (isset($data['category_id'])) {
                if (is_array($data['category_id'])) {
                    $data['category_id'] = !empty($data['category_id']) ? (int) reset($data['category_id']) : null;
                } elseif ($data['category_id'] === null || $data['category_id'] === '' || $data['category_id'] === 'null') {
                    $data['category_id'] = null;
                } else {
                    $data['category_id'] = (int) $data['category_id'];
                }
            }
            
            // Manejar campos numéricos nullable
            $nullableNumericFields = ['tax_rate', 'max_stock'];
            foreach ($nullableNumericFields as $field) {
                if (isset($data[$field])) {
                    if (is_array($data[$field])) {
                        $data[$field] = !empty($data[$field]) && is_numeric(reset($data[$field])) ? (float) reset($data[$field]) : null;
                    } elseif ($data[$field] === null || $data[$field] === '' || $data[$field] === 'null') {
                        $data[$field] = null;
                    } else {
                        $data[$field] = is_numeric($data[$field]) ? (float) $data[$field] : null;
                    }
                }
            }
            
            // Manejar campos booleanos - asegurar que sean boolean
            $booleanFields = ['requires_prescription', 'is_controlled', 'is_active'];
            foreach ($booleanFields as $field) {
                if (isset($data[$field])) {
                    if (is_array($data[$field])) {
                        $data[$field] = filter_var(reset($data[$field]), FILTER_VALIDATE_BOOLEAN);
                    } else {
                        $data[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
            
            $validated = validator($data, [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:products,code,' . $product->id,
                'description' => 'nullable|string|max:65535',
                'category_id' => 'nullable|exists:product_categories,id',
                'brand' => 'nullable|string|max:255',
                'active_ingredient' => 'nullable|string|max:255',
                'dosage' => 'nullable|string|max:255',
                'presentation' => 'nullable|string|max:255',
                'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
                'base_price' => 'required|numeric|min:0',
                'cost_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'stock_quantity' => 'required|integer|min:0',
                'min_stock' => 'required|integer|min:0',
                'max_stock' => 'nullable|integer|min:0',
                'unit_type' => 'nullable|string|max:50',
                'requires_prescription' => 'sometimes|boolean',
                'is_controlled' => 'sometimes|boolean',
                'is_active' => 'sometimes|boolean',
            ])->validate();
            // Generar slug si cambió el nombre
            $updateData = [
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => !empty($validated['description']) ? $validated['description'] : null,
                'category_id' => $validated['category_id'] ?? null,
                'brand' => !empty($validated['brand']) ? $validated['brand'] : null,
                'active_ingredient' => !empty($validated['active_ingredient']) ? $validated['active_ingredient'] : null,
                'dosage' => !empty($validated['dosage']) ? $validated['dosage'] : null,
                'presentation' => !empty($validated['presentation']) ? $validated['presentation'] : null,
                'barcode' => !empty($validated['barcode']) ? $validated['barcode'] : null,
                'cost_price' => $validated['cost_price'],
                'base_price' => $validated['base_price'] ?? $validated['cost_price'],
                'sale_price' => $validated['sale_price'],
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'stock_quantity' => $validated['stock_quantity'],
                'min_stock' => $validated['min_stock'],
                'max_stock' => $validated['max_stock'] ?? 0,
                'unit_type' => $validated['unit_type'] ?? 'unidad',
                'requires_prescription' => $validated['requires_prescription'] ?? false,
                'is_controlled' => $validated['is_controlled'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            // Si cambió el nombre, actualizar slug
            if ($product->name !== $validated['name']) {
                $slug = \Str::slug($validated['name']);
                $slugCount = DB::table('products')
                    ->where('slug', 'like', $slug . '%')
                    ->where('id', '!=', $product->id)
                    ->count();
                if ($slugCount > 0) {
                    $slug = $slug . '-' . ($slugCount + 1);
                }
                $updateData['slug'] = $slug;
            }

            // Actualizar producto usando consulta directa
            DB::table('products')
                ->where('id', $product->id)
                ->update($updateData);

            return redirect()->route('products.show', $product)
                ->with('success', 'Producto actualizado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e);

            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            dd($e);
            
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
            dd($e);
            
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
            dd($e);
            
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

            // Mapear tipos de movimiento para que coincidan con la migración
            $movementTypes = [
                'add' => 'add',
                'subtract' => 'subtract',
                'set' => 'set'
            ];

            $movementType = $movementTypes[$validated['type']] ?? 'adjustment';

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
                    'type' => $movementType,
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
            dd($e);
            
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
            dd($e);
            
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
            dd($e);
            
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
                'categories' => InertiaHelper::sanitizeData(
                    Category::select('id', 'name')
                        ->where('is_active', true)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get()
                ),
                'stats' => InertiaHelper::sanitizeData($stats),
                'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'category', 'stock_status'])),
            ]);

        } catch (\Exception $e) {
            dd($e);
            
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
            'quantity' => 'required|integer|min:1|max:999999',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $oldStock = $product->stock_quantity;
            $adjustment = $request->quantity;

            // Validar que no se pueda sacar más stock del disponible
            if ($request->type === 'out' && $adjustment > $oldStock) {
                return back()->with('error', 'No se puede sacar más stock del disponible. Stock actual: ' . $oldStock);
            }

            // Mapear tipos de movimiento para que coincidan con la migración
            $movementTypes = [
                'in' => 'add',
                'out' => 'subtract',
                'adjustment' => 'adjustment'
            ];

            $movementType = $movementTypes[$request->type] ?? 'adjustment';

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
                'type' => $movementType,
                'quantity' => $adjustment,
                'previous_stock' => $oldStock,
                'new_stock' => $product->stock_quantity,
                'notes' => $request->reason,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', "Stock ajustado exitosamente. Stock anterior: {$oldStock}, Stock nuevo: {$product->stock_quantity}");

        } catch (\Exception $e) {
            dd($e);
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage());
        }
    }

    /**
     * Obtener categorías
     */
    public function categories(): Response
    {
        try {
            if (!Schema::hasTable('product_categories')) {
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
            dd($e);

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

    /**
     * Obtener historial de movimientos de stock
     */
    public function stockHistory(Product $product): Response
    {
        try {
            // Verificar si la tabla existe
            if (!Schema::hasTable('stock_movements')) {
                return Inertia::render('Products/StockHistory', [
                    'product' => $product,
                    'movements' => ['data' => []],
                    'error' => 'La tabla de movimientos de stock no existe. Por favor, ejecute las migraciones pendientes.'
                ]);
            }

            $movements = DB::table('stock_movements')
                ->leftJoin('users', 'stock_movements.created_by', '=', 'users.id')
                ->where('stock_movements.product_id', $product->id)
                ->select([
                    'stock_movements.*',
                    'users.name as user_name'
                ])
                ->orderBy('stock_movements.created_at', 'desc')
                ->paginate(20);

            // Si no hay movimientos, crear algunos de prueba para demostración
            if ($movements->isEmpty() && Schema::hasTable('stock_movements')) {
                $this->createDemoMovements($product);
                // Recargar los movimientos después de crear los de prueba
                $movements = DB::table('stock_movements')
                    ->leftJoin('users', 'stock_movements.created_by', '=', 'users.id')
                    ->where('stock_movements.product_id', $product->id)
                    ->select([
                        'stock_movements.*',
                        'users.name as user_name'
                    ])
                    ->orderBy('stock_movements.created_at', 'desc')
                    ->paginate(20);
            }

            return Inertia::render('Products/StockHistory', [
                'product' => $product,
                'movements' => $movements
            ]);

        } catch (\Exception $e) {
            dd($e);
            return Inertia::render('Products/StockHistory', [
                'product' => $product,
                'movements' => ['data' => []],
                'error' => 'Error al cargar historial de stock: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Crear movimientos de stock de demostración
     */
    private function createDemoMovements(Product $product)
    {
        $demoMovements = [
            [
                'product_id' => $product->id,
                'type' => 'add',
                'quantity' => 50,
                'previous_stock' => 0,
                'new_stock' => 50,
                'notes' => 'Compra inicial de producto',
                'created_by' => auth()->id() ?: 1,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'product_id' => $product->id,
                'type' => 'subtract',
                'quantity' => 10,
                'previous_stock' => 50,
                'new_stock' => 40,
                'notes' => 'Venta a cliente',
                'created_by' => auth()->id() ?: 1,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'product_id' => $product->id,
                'type' => 'add',
                'quantity' => 20,
                'previous_stock' => 40,
                'new_stock' => 60,
                'notes' => 'Reposición de stock',
                'created_by' => auth()->id() ?: 1,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => 5,
                'previous_stock' => 60,
                'new_stock' => 55,
                'notes' => 'Ajuste por inventario físico',
                'created_by' => auth()->id() ?: 1,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        DB::table('stock_movements')->insert($demoMovements);
    }
}