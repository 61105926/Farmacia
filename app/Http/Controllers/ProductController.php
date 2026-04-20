<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Helpers\InertiaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpSpreadsheetDate;
use Carbon\Carbon;

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
                'presentations' => [],
                'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'presentation', 'status', 'stock_status'])),
                'error' => 'La tabla de productos no existe. Por favor, crea las tablas primero.'
            ]);
            }

            $receivedFilters = $request->only(['search', 'presentation', 'status', 'stock_status', 'sort_by', 'sort_order']);
            

            $query = Product::with('category');

            // Filtros
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('code', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Filtro de presentación
            $presentation = $request->input('presentation');
            if ($presentation && $presentation !== '' && $presentation !== null) {
                $query->where('presentation', $presentation);
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

            // Ordenamiento
            $sortBy    = in_array($request->input('sort_by'), ['name', 'description', 'stock_quantity', 'sale_price', 'created_at'])
                ? $request->input('sort_by') : 'created_at';
            $sortOrder = $request->input('sort_order') === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $products = $query->paginate(15)->withQueryString();

            // Agregar acciones disponibles y fecha de vencimiento a cada producto
            $products->getCollection()->transform(function ($product) {
                $product->availableActions = $this->getAvailableActions($product);
                
                // Obtener la fecha de vencimiento más próxima del inventario
                if (Schema::hasTable('inventories')) {
                    $nearestExpiry = Inventory::where('product_id', $product->id)
                        ->whereNotNull('expiry_date')
                        ->where('expiry_date', '>=', now()->toDateString())
                        ->orderBy('expiry_date', 'asc')
                        ->value('expiry_date');
                    
                    $product->nearest_expiry_date = $nearestExpiry;
                } else {
                    $product->nearest_expiry_date = null;
                }
                
                return $product;
            });

            // Cargar presentaciones únicas para filtros
            $presentations = Product::whereNotNull('presentation')
                ->where('presentation', '!=', '')
                ->distinct()
                ->orderBy('presentation', 'asc')
                ->pluck('presentation')
                ->filter()
                ->values();

            // Estadísticas de productos
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::where('is_active', true)->count(),
                'low_stock_products' => Product::whereRaw('stock_quantity <= 10')->where('stock_quantity', '>', 0)->count(),
                'out_of_stock_products' => Product::where('stock_quantity', '<=', 0)->count(),
                'total_value' => Product::sum(DB::raw('stock_quantity * cost_price')),
            ];

            // Ensure filters are always passed, even if empty
            $currentFilters = $request->only(['search', 'presentation', 'status', 'stock_status', 'sort_by', 'sort_order']);
            $sanitizedFilters = InertiaHelper::sanitizeFilters($currentFilters);

            return Inertia::render('Products/Index', [
                'products' => $products, // No sanitizar para mantener estructura de paginación
                'presentations' => InertiaHelper::sanitizeData($presentations),
                'filters' => $sanitizedFilters,
                'stats' => InertiaHelper::sanitizeData($stats),
            ]);

        } catch (\Exception $e) {
            dd($e);
            
            return Inertia::render('Products/Index', [
                'products' => [],
                'presentations' => [],
                'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'presentation', 'status', 'stock_status'])),
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
            'expiry_date' => 'nullable|date',
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
                'expiry_date' => $validated['expiry_date'] ?? null,
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
            
            // Manejar expiry_date - puede ser null o string vacío
            if (isset($data['expiry_date'])) {
                if ($data['expiry_date'] === null || $data['expiry_date'] === '' || $data['expiry_date'] === 'null') {
                    $data['expiry_date'] = null;
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
                'expiry_date' => 'nullable|date',
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
                'expiry_date' => $validated['expiry_date'] ?? null,
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
                'expiry_date' => $validated['expiry_date'] ?? null,
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

            // Cargar últimos movimientos de inventario para cada producto
            $productIds = $products->pluck('id')->toArray();
            $recentMovements = [];
            
            if (Schema::hasTable('inventories') && !empty($productIds)) {
                $movements = \App\Models\Inventory::whereIn('product_id', $productIds)
                    ->with(['creator:id,name'])
                    ->latest('movement_date')
                    ->latest('created_at')
                    ->get();
                
                // Agrupar por producto y tomar los últimos 5
                foreach ($movements as $movement) {
                    if (!isset($recentMovements[$movement->product_id])) {
                        $recentMovements[$movement->product_id] = [];
                    }
                    if (count($recentMovements[$movement->product_id]) < 5) {
                        $recentMovements[$movement->product_id][] = $movement;
                    }
                }
            }

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
                'recentMovements' => InertiaHelper::sanitizeData($recentMovements),
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
     * Import products from Excel file
     */
    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Leer encabezados de la primera fila y construir mapa de columnas
            $headerRow = array_shift($rows);
            $colMap = [];
            // Normalizar texto: minúsculas, sin tildes, sin espacios extra
            $normalizeHeader = function (string $str): string {
                $str = mb_strtolower(trim($str));
                $from = ['á','à','ä','â','ã','é','è','ë','ê','í','ì','ï','î','ó','ò','ö','ô','õ','ú','ù','ü','û','ñ','Á','À','Ä','Â','Ã','É','È','Ë','Ê','Í','Ì','Ï','Î','Ó','Ò','Ö','Ô','Õ','Ú','Ù','Ü','Û','Ñ'];
                $to   = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','n','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','n'];
                $str = str_replace($from, $to, $str);
                return preg_replace('/\s+/', ' ', $str);
            };

            $fieldAliases = [
                'name'          => ['nombre', 'nombregenerico', 'nombre generico', 'name', 'producto', 'product'],
                'code'          => ['codigo', 'code', 'cod', 'id'],
                'description'   => ['descripcion', 'description', 'desc', 'principio activo', 'principio_activo', 'concentracion', 'active_ingredient'],
                'category_name' => ['categoria', 'category', 'cat', 'accion'],
                'brand'         => ['marca', 'brand', 'proveedor', 'supplier', 'laboratorio', 'lab'],
                'cost_price'    => ['precio costo', 'costo', 'cost_price', 'cost', 'precio_costo', 'precio de compra pieza', 'precio de compra', 'precio compra'],
                'sale_price'    => ['precio venta', 'precio', 'sale_price', 'price', 'precio_venta', 'pvp', 'precio1', 'precio 1'],
                'stock_quantity'=> ['stock', 'stock_quantity', 'cantidad', 'quantity', 'existencia', 'existencias'],
                'min_stock'     => ['stock minimo', 'min_stock', 'minimo', 'stock_min', 'cantidad minima', 'cantidad_minima'],
                'unit_type'     => ['unidad', 'unit_type', 'tipo unidad', 'unit', 'concentracion'],
                'is_active'     => ['activo', 'is_active', 'active', 'estado'],
                'barcode'       => ['barcode', 'codigo barras', 'ean', 'upc', 'codigobarras', 'codigo de barras'],
                'lot'           => ['lote', 'lot', 'batch', 'sku'],
                'presentation'  => ['presentacion', 'presentation', 'forma'],
                'expiry_date'   => ['vencimiento', 'expiry_date', 'fecha vencimiento', 'expiry', 'fecha_vencimiento', 'vence', 'fecha de vencimiento'],
            ];

            foreach ($headerRow as $colIndex => $header) {
                if ($header === null) continue;
                $normalized = $normalizeHeader((string)$header);
                foreach ($fieldAliases as $field => $aliases) {
                    if (in_array($normalized, $aliases) && !isset($colMap[$field])) {
                        $colMap[$field] = $colIndex;
                        break;
                    }
                }
            }

            // Si no se detectaron columnas por encabezado, usar mapeo posicional como respaldo
            \Log::info('ProductController import - colMap detectado:', $colMap);
            \Log::info('ProductController import - headerRow:', $headerRow ?? []);

            if (!isset($colMap['name']) && !isset($colMap['code'])) {
                // Fallback a posiciones fijas
                $colMap = [
                    'name'          => 0,
                    'code'          => 1,
                    'description'   => 2,
                    'category_name' => 3,
                    'brand'         => 4,
                    'cost_price'    => 5,
                    'sale_price'    => 6,
                    'stock_quantity'=> 7,
                    'min_stock'     => 8,
                    'unit_type'     => 9,
                    'is_active'     => 10,
                    'barcode'       => 11,
                    'lot'           => 12,
                    'presentation'  => 13,
                    'expiry_date'   => 14,
                ];
                // La fila que se removió como header en realidad era datos — re-insertarla
                array_unshift($rows, $headerRow);
            }

            $getCol = function (array $row, string $field, $default = null) use ($colMap) {
                if (!isset($colMap[$field])) return $default;
                $val = $row[$colMap[$field]] ?? null;
                return ($val === null || $val === '') ? $default : $val;
            };

            $imported = 0;
            $updated = 0;
            $created = 0;
            $errors = [];
            $skipped = 0;
            $importedCodes = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                // Saltar filas vacías
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Parsear número desde Excel (maneja comas, puntos de miles, notación científica)
                    $parseNumber = function ($val, bool $asInt = false) {
                        if ($val === null || $val === '') return 0;
                        if (is_int($val) || is_float($val)) return $asInt ? (int)$val : (float)$val;
                        $str = trim((string)$val);
                        // Notación científica (ej: 1E+3)
                        if (preg_match('/^-?\d+(\.\d+)?[eE][+-]?\d+$/', $str)) {
                            return $asInt ? (int)(float)$str : (float)$str;
                        }
                        // Quitar separadores de miles (coma o punto cuando hay 3 dígitos tras él)
                        $str = preg_replace('/[,.](?=\d{3}(\D|$))/', '', $str);
                        // Reemplazar coma decimal por punto
                        $str = str_replace(',', '.', $str);
                        if (!is_numeric($str)) return 0;
                        return $asInt ? (int)(float)$str : (float)$str;
                    };

                    $rawStock = $getCol($row, 'stock_quantity');
                    $data = [
                        'name'          => $getCol($row, 'name')          !== null ? trim((string)$getCol($row, 'name'))        : null,
                        'code'          => $getCol($row, 'code')          !== null ? trim((string)$getCol($row, 'code'))        : null,
                        'description'   => $getCol($row, 'description')   !== null ? trim((string)$getCol($row, 'description')) : null,
                        'category_name' => $getCol($row, 'category_name') !== null ? trim((string)$getCol($row, 'category_name')) : null,
                        'brand'         => $getCol($row, 'brand')         !== null ? trim((string)$getCol($row, 'brand'))       : null,
                        'cost_price'    => $parseNumber($getCol($row, 'cost_price')),
                        'sale_price'    => $parseNumber($getCol($row, 'sale_price')),
                        'stock_quantity'=> $parseNumber($rawStock, true),
                        'min_stock'     => $parseNumber($getCol($row, 'min_stock'), true),
                        'unit_type'     => $getCol($row, 'unit_type', 'unit'),
                        'is_active'     => $getCol($row, 'is_active')     !== null ? (bool)(is_numeric($getCol($row, 'is_active')) ? (int)$getCol($row, 'is_active') : $getCol($row, 'is_active')) : true,
                        'barcode'       => $getCol($row, 'barcode'),
                        'lot'           => $getCol($row, 'lot'),
                        'presentation'  => $getCol($row, 'presentation'),
                        'expiry_date'   => $getCol($row, 'expiry_date') !== null ? $this->parseDate($getCol($row, 'expiry_date')) : null,
                    ];

                    // Si NombreGenerico está vacío, usar Descripción como nombre
                    if (empty($data['name']) && !empty($data['description'])) {
                        $data['name'] = $data['description'];
                    }
                    // Si Descripción está vacía, usar Nombre como descripción
                    if (empty($data['description']) && !empty($data['name'])) {
                        $data['description'] = $data['name'];
                    }

                    // Validar campos requeridos
                    if (empty($data['name']) || empty($data['code'])) {
                        $missingFields = [];
                        if (empty($data['name'])) $missingFields[] = 'Nombre';
                        if (empty($data['code'])) $missingFields[] = 'Código';
                        $errors[] = "Fila {$rowNumber}: Los siguientes campos son requeridos: " . implode(', ', $missingFields);
                        $skipped++;
                        continue;
                    }

                    // Buscar categoría por nombre
                    $categoryId = null;
                    if (!empty($data['category_name'])) {
                        $category = DB::table('product_categories')
                            ->where('name', 'like', trim($data['category_name']))
                            ->first();
                        if ($category) {
                            $categoryId = $category->id;
                        }
                    }

                    // Registrar código importado
                    $importedCodes[] = trim($data['code']);

                    // Verificar si el código ya existe
                    $existingProduct = DB::table('products')->where('code', $data['code'])->first();
                    
                    // Preparar datos para insertar/actualizar
                    $productData = [
                        'name' => trim($data['name']),
                        'description' => trim($data['description']),
                        'category_id' => $categoryId,
                        'brand' => !empty($data['brand']) ? trim($data['brand']) : null,
                        'presentation' => !empty($data['presentation']) ? trim($data['presentation']) : null,
                        'barcode' => !empty($data['barcode']) ? trim($data['barcode']) : null,
                        'sku' => !empty($data['lot']) ? trim($data['lot']) : null,
                        'cost_price' => $data['cost_price'] ?? 0,
                        'base_price' => $data['cost_price'] ?? 0,
                        'sale_price' => $data['sale_price'] ?? 0,
                        'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
                        'min_stock' => (int)($data['min_stock'] ?? 0),
                        'max_stock' => 0,
                        'unit_type' => $data['unit_type'] ?? 'unit',
                        'is_active' => $data['is_active'] ?? true,
                        'expiry_date' => $data['expiry_date'] ?? null,
                        'updated_at' => now(),
                    ];

                    if ($existingProduct) {
                        // ACTUALIZAR producto existente
                        // Generar slug si cambió el nombre
                        $newSlug = \Str::slug($data['name']);
                        if ($newSlug !== $existingProduct->slug) {
                            // Verificar si el nuevo slug ya existe en otro producto
                            $slugExists = DB::table('products')
                                ->where('slug', $newSlug)
                                ->where('id', '!=', $existingProduct->id)
                                ->exists();
                            
                            if (!$slugExists) {
                                $productData['slug'] = $newSlug;
                            }
                        }

                        // Actualizar producto
                        DB::table('products')
                            ->where('id', $existingProduct->id)
                            ->update($productData);

                        $updated++;
                        $imported++;
                    } else {
                        // CREAR nuevo producto
                        // Generar slug único
                        $slug = \Str::slug($data['name']);
                        $slugCount = DB::table('products')->where('slug', 'like', $slug . '%')->count();
                        if ($slugCount > 0) {
                            $slug = $slug . '-' . ($slugCount + 1);
                        }
                        $productData['slug'] = $slug;
                        $productData['code'] = trim($data['code']);
                        $productData['created_by'] = auth()->id();
                        $productData['created_at'] = now();

                        // Verificar si el código de barras ya existe (solo para nuevos productos)
                        if (!empty($data['barcode'])) {
                            $existingBarcode = DB::table('products')
                                ->where('barcode', $data['barcode'])
                                ->first();
                            if ($existingBarcode) {
                                $errors[] = "Fila {$rowNumber}: El código de barras '{$data['barcode']}' ya existe. Producto: {$existingBarcode->name}";
                                $skipped++;
                                continue;
                            }
                        }

                        // Crear producto
                        DB::table('products')->insert($productData);

                        $created++;
                        $imported++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Fila {$rowNumber}: " . $e->getMessage();
                    $skipped++;
                }
            }

            // Eliminar TODOS los productos que NO están en el Excel importado
            $deleted = 0;
            if (!empty($importedCodes)) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $deleted = DB::table('products')
                    ->whereNotIn('code', $importedCodes)
                    ->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            $message = "Importación completada. ";
            if ($created > 0) {
                $message .= "{$created} productos creados. ";
            }
            if ($updated > 0) {
                $message .= "{$updated} productos actualizados. ";
            }
            if ($deleted > 0) {
                $message .= "{$deleted} productos eliminados. ";
            }
            if ($skipped > 0) {
                $message .= "{$skipped} productos omitidos. ";
            }
            if (!empty($errors)) {
                $message .= " Errores: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " y " . (count($errors) - 5) . " más.";
                }
            }

            return redirect()->route('products.index')
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for product import
     */
    public function downloadTemplate()
    {
        try {
            // Ruta del archivo de plantilla
            $templatePath = public_path('producto/NUEVO.xlsx');
            
            // Log para debugging
            \Log::info('Descargando plantilla', [
                'ruta' => $templatePath,
                'existe' => file_exists($templatePath),
                'es_legible' => is_readable($templatePath),
            ]);
            
            // Verificar que el archivo existe
            if (!file_exists($templatePath)) {
                \Log::error('Plantilla no encontrada', [
                    'ruta' => $templatePath,
                    'public_path' => public_path(),
                    'directorio_existe' => is_dir(dirname($templatePath)),
                ]);
                return redirect()->route('products.index')
                    ->with('error', 'La plantilla no se encuentra disponible. Por favor contacte al administrador.');
            }

            // Verificar que el archivo es legible
            if (!is_readable($templatePath)) {
                \Log::error('Plantilla no es legible', [
                    'ruta' => $templatePath,
                    'permisos' => substr(sprintf('%o', fileperms($templatePath)), -4),
                ]);
                return redirect()->route('products.index')
                    ->with('error', 'La plantilla no es accesible. Por favor contacte al administrador.');
            }

            $filename = 'plantilla_importacion_productos.xlsx';

            // Descargar el archivo
            return response()->download($templatePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al descargar plantilla Excel', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('products.index')
                ->with('error', 'Error al descargar la plantilla: ' . $e->getMessage());
        }
    }

    /**
     * Parse date from Excel cell value
     * Supports multiple formats: YYYY-MM-DD, DD/MM/YYYY, Excel date serial number
     */
    private function parseDate($value)
    {
        if (empty($value) || $value === null) {
            return null;
        }

        // Si es un número (fecha serial de Excel)
        if (is_numeric($value)) {
            try {
                // PhpSpreadsheet puede convertir fechas seriales
                $date = PhpSpreadsheetDate::excelToDateTimeObject($value);
                return Carbon::instance($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $value = trim((string)$value);
        
        // Intentar formato YYYY-MM-DD
        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // Continuar con otros formatos
        }

        // Intentar formato DD/MM/YYYY
        try {
            $date = Carbon::createFromFormat('d/m/Y', $value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // Continuar con otros formatos
        }

        // Intentar formato DD-MM-YYYY
        try {
            $date = Carbon::createFromFormat('d-m-Y', $value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // Continuar con otros formatos
        }

        // Intentar parseo automático
        try {
            $date = Carbon::parse($value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}