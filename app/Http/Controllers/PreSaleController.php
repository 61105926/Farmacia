<?php

namespace App\Http\Controllers;

use App\Models\Presale;
use App\Models\PresaleItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PresaleController extends Controller
{
    /**
     * Display a listing of presales
     */
    public function index(Request $request): Response
    {
        Log::info('PreSaleController index - Cargando listado de preventas', [
            'user_id' => auth()->id(),
            'filters' => $request->only(['search', 'status', 'salesperson_id', 'client_id', 'date_from', 'date_to'])
        ]);

        try {
            // Construir query base con relaciones
            $query = Presale::query()
                ->with([
                    'client:id,business_name,trade_name',
                    'salesperson:id,name',
                    'creator:id,name'
                ]);

            // FILTRO: Búsqueda por código, cliente
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($clientQuery) use ($search) {
                          $clientQuery->where('business_name', 'like', "%{$search}%")
                                    ->orWhere('trade_name', 'like', "%{$search}%");
                      });
                });
            }

            // FILTRO: Por estado
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // FILTRO: Por vendedor
            if ($request->filled('salesperson_id')) {
                $query->where('salesperson_id', $request->salesperson_id);
            }

            // FILTRO: Por cliente
            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            // FILTRO: Rango de fechas
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // FILTRO: Por monto mínimo
            if ($request->filled('min_amount')) {
                $query->where('total', '>=', $request->min_amount);
            }

            // FILTRO: Por monto máximo
            if ($request->filled('max_amount')) {
                $query->where('total', '<=', $request->max_amount);
            }

            // ORDENAMIENTO
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $allowedSortFields = ['code', 'created_at', 'total', 'status', 'delivery_date'];
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->latest();
            }

            // PAGINACIÓN
            $perPage = $request->get('per_page', 15);
            $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

            $presales = $query->paginate($perPage)->withQueryString();

            // ESTADÍSTICAS GENERALES
            $stats = $this->getPresalesStats($request);

            // DATOS PARA FILTROS
            $filterData = [
                'salespeople' => User::select('id', 'name')
                    ->whereHas('roles', function($q) {
                        $q->whereIn('name', ['vendedor-preventas', 'admin', 'gerente']);
                    })
                    ->orWhere('id', auth()->id())
                    ->orderBy('name')
                    ->get(),

                'clients' => Client::where('status', 'active')
                    ->select('id', 'business_name', 'trade_name')
                    ->orderBy('business_name')
                    ->get(),

                'statuses' => [
                    ['value' => 'draft', 'label' => 'Borrador'],
                    ['value' => 'confirmed', 'label' => 'Confirmada'],
                    ['value' => 'converted', 'label' => 'Convertida'],
                    ['value' => 'cancelled', 'label' => 'Cancelada'],
                ],
            ];

            return Inertia::render('PreSales/Index', [
                'presales' => $presales,
                'stats' => $stats,
                'filterData' => $filterData,
                'filters' => $request->only([
                    'search',
                    'status',
                    'salesperson_id',
                    'client_id',
                    'date_from',
                    'date_to',
                    'min_amount',
                    'max_amount',
                    'sort_by',
                    'sort_direction',
                    'per_page'
                ]),
            ]);

        } catch (\Exception $e) {
            Log::error('PreSaleController index - Error al cargar preventas', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return Inertia::render('PreSales/Index', [
                'presales' => ['data' => [], 'links' => [], 'meta' => []],
                'stats' => $this->getDefaultStats(),
                'filterData' => ['salespeople' => [], 'clients' => [], 'statuses' => []],
                'filters' => [],
                'error' => 'Error al cargar las preventas. Por favor, intente nuevamente.'
            ]);
        }
    }

    /**
     * Obtener estadísticas de preventas
     */
    private function getPresalesStats(Request $request): array
    {
        try {
            $query = Presale::query();

            // Aplicar mismos filtros de fecha si existen
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Estadísticas base
            $baseQuery = clone $query;

            return [
                'total' => $baseQuery->count(),
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
                'converted' => (clone $query)->where('status', 'converted')->count(),
                'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
                'total_value' => $baseQuery->sum('total'),
                'average_value' => $baseQuery->avg('total'),
                'today' => Presale::whereDate('created_at', today())->count(),
                'this_week' => Presale::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'this_month' => Presale::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::warning('Error al calcular estadísticas de preventas', [
                'message' => $e->getMessage()
            ]);
            return $this->getDefaultStats();
        }
    }

    /**
     * Estadísticas por defecto
     */
    private function getDefaultStats(): array
    {
        return [
            'total' => 0,
            'draft' => 0,
            'confirmed' => 0,
            'converted' => 0,
            'cancelled' => 0,
            'total_value' => 0,
            'average_value' => 0,
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
        ];
    }

    /**
     * Show the form for creating a new presale
     */
    public function create(): Response
    {
        try {
            // Verificar si las tablas existen
            if (!\Schema::hasTable('presales')) {
                return Inertia::render('PreSales/Create', [
                    'clients' => [],
                    'products' => [],
                    'salespeople' => [],
                    'error' => 'Las tablas de preventas no existen. Por favor, crea las tablas primero.'
                ]);
            }

            // Cargar datos de forma simple y segura
            $clients = [];
            $products = [];
            $salespeople = [];

            try {
                if (\Schema::hasTable('clients')) {
                    $clients = \DB::table('clients')
                        ->where('status', 'active')
                        ->select('id', 'business_name', 'trade_name', 'credit_limit')
                        ->get()
                        ->toArray();
                    
                }
            } catch (\Exception $e) {
                // Si falla, usar array vacío
            }

            try {
                if (\Schema::hasTable('products')) {
                    $products = \DB::table('products')
                        ->where('is_active', true)
                        ->select('id', 'name', 'code', 'sale_price', 'stock_quantity')
                        ->get()
                        ->toArray();
                }
            } catch (\Exception $e) {
                // Si falla, usar array vacío
            }

            try {
                if (\Schema::hasTable('users')) {
                    // Cargar todos los usuarios como vendedores (sin filtro de is_active)
                    $salespeople = \DB::table('users')
                        ->select('id', 'name')
                        ->get()
                        ->toArray();
                }
            } catch (\Exception $e) {
                // Si falla, usar array vacío
            }

            return Inertia::render('PreSales/Create', [
                'clients' => $clients,
                'products' => $products,
                'salespeople' => $salespeople,
            ]);

        } catch (\Exception $e) {
            dd($e);
            return Inertia::render('PreSales/Create', [
                'clients' => [],
                'products' => [],
                'salespeople' => [],
                'error' => 'Error al cargar datos: ' . $e->getMessage()
            ]);
            
        }
    }

    /**
     * Store a newly created presale
     */
    public function store(Request $request): RedirectResponse
    {
        // Verificar autenticación
        if (!auth()->check()) {
            Log::error('PresaleController store - Usuario no autenticado');
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para crear preventas.');
        }

        Log::info('PresaleController store - Iniciando creación de preventa', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'client_id' => $request->client_id,
            'items_count' => count($request->items ?? [])
        ]);

        // Validar datos de entrada
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'salesperson_id' => 'nullable|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01|max:999999',
                'items.*.unit_price' => 'required|numeric|min:0|max:999999999',
                'items.*.discount' => 'nullable|numeric|min:0|max:100',
                'notes' => 'nullable|string|max:1000',
                'delivery_date' => 'nullable|date|after_or_equal:today',
            ], [
                'client_id.required' => 'Debe seleccionar un cliente.',
                'client_id.exists' => 'El cliente seleccionado no existe.',
                'items.required' => 'Debe agregar al menos un producto.',
                'items.min' => 'Debe agregar al menos un producto.',
                'items.*.product_id.required' => 'Debe seleccionar un producto.',
                'items.*.product_id.exists' => 'El producto seleccionado no existe.',
                'items.*.quantity.required' => 'La cantidad es obligatoria.',
                'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
                'items.*.unit_price.required' => 'El precio unitario es obligatorio.',
                'items.*.unit_price.min' => 'El precio unitario debe ser mayor o igual a 0.',
                'items.*.discount.max' => 'El descuento no puede ser mayor a 100%.',
                'delivery_date.after_or_equal' => 'La fecha de entrega no puede ser anterior a hoy.',
            ]);
        } catch (ValidationException $e) {
            Log::warning('PresaleController store - Error de validación', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        try {
            // 1. Validar que el cliente esté activo
            $client = Client::find($validated['client_id']);
            if (!$client || $client->status !== 'active') {
                throw ValidationException::withMessages([
                    'client_id' => 'El cliente seleccionado no está activo.'
                ]);
            }

            // 2. Validar que todos los productos estén activos
            $productIds = collect($validated['items'])->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($validated['items'] as $index => $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => "El producto no existe."
                    ]);
                }

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => "El producto '{$product->name}' no está activo."
                    ]);
                }

                // Validar que la cantidad sea razonable según el stock (advertencia)
                if ($item['quantity'] > $product->stock_quantity * 2) {
                    Log::warning('PresaleController store - Cantidad solicitada muy alta', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity_requested' => $item['quantity'],
                        'stock_available' => $product->stock_quantity
                    ]);
                }
            }

            // 3. Validar límite de crédito del cliente (si aplica)
            $totalPresale = 0;
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount'] ?? 0) / 100;
                $totalPresale += ($itemTotal - $itemDiscount);
            }

            if ($client->credit_limit > 0) {
                $pendingBalance = $client->pending_balance ?? 0;
                $availableCredit = $client->credit_limit - $pendingBalance;

                if ($totalPresale > $availableCredit) {
                    Log::warning('PresaleController store - Cliente excede límite de crédito', [
                        'client_id' => $client->id,
                        'credit_limit' => $client->credit_limit,
                        'pending_balance' => $pendingBalance,
                        'available_credit' => $availableCredit,
                        'presale_total' => $totalPresale
                    ]);

                    // No bloqueamos, solo advertimos
                    session()->flash('warning', sprintf(
                        'Advertencia: Esta preventa (%.2f) excede el crédito disponible del cliente (%.2f). Límite: %.2f, Pendiente: %.2f',
                        $totalPresale,
                        $availableCredit,
                        $client->credit_limit,
                        $pendingBalance
                    ));
                }
            }

            // 4. Generar código único de preventa
            $code = $this->generatePresaleCode();

            // 5. Calcular totales
            $subtotal = 0;
            $totalDiscount = 0;
            $total = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount'] ?? 0) / 100;
                $itemSubtotal = $itemTotal - $itemDiscount;

                $subtotal += $itemTotal;
                $totalDiscount += $itemDiscount;
                $total += $itemSubtotal;
            }

            // 6. Crear la preventa
            $presale = Presale::create([
                'code' => $code,
                'client_id' => $validated['client_id'],
                'salesperson_id' => $validated['salesperson_id'] ?? auth()->id(),
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'total' => $total,
                'notes' => $validated['notes'],
                'delivery_date' => $validated['delivery_date'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // 7. Crear items de la preventa
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount'] ?? 0) / 100;

                PresaleItem::create([
                    'presale_id' => $presale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $itemTotal,
                    'discount_amount' => $itemDiscount,
                    'total' => $itemTotal - $itemDiscount,
                ]);
            }

            DB::commit();

            Log::info('PresaleController store - Preventa creada exitosamente', [
                'presale_id' => $presale->id,
                'presale_code' => $presale->code,
                'client_id' => $presale->client_id,
                'total' => $presale->total,
                'items_count' => count($validated['items']),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('presales.show', $presale)
                ->with('success', "Preventa {$code} creada exitosamente.");

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('PresaleController store - Validación de negocio falló', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PresaleController store - Error al crear preventa', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'client_id' => $request->client_id
            ]);

            return back()
                ->with('error', 'Error al crear la preventa. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    /**
     * Generar código único de preventa
     */
    private function generatePresaleCode(): string
    {
        $lastPresale = Presale::latest('id')->lockForUpdate()->first();
        $nextNumber = $lastPresale ? intval(substr($lastPresale->code, 3)) + 1 : 1;
        return 'PRE' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified presale
     */
    public function show(Presale $presale): Response
    {
        $presale->load([
            'client',
            'salesperson',
            'items.product',
            'creator:id,name',
        ]);

        return Inertia::render('PreSales/Show', [
            'presale' => $presale,
        ]);
    }

    /**
     * Show the form for editing the specified presale
     */
    public function edit(Presale $presale): Response
    {
        if ($presale->status !== 'draft') {
            return redirect()->route('presales.show', $presale)
                ->with('error', 'Solo se pueden editar preventas en estado borrador.');
        }

        $presale->load(['items.product']);

        return Inertia::render('PreSales/Edit', [
            'presale' => $presale,
            'clients' => Client::where('status', 'active')
                ->select('id', 'business_name', 'trade_name')
                ->get(),
            'products' => Product::where('is_active', true)
                ->select('id', 'name', 'code', 'sale_price', 'stock_quantity')
                ->get(),
            'salespeople' => User::whereHas('roles', function($q) {
                $q->where('name', 'vendedor-preventas');
            })
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * Update the specified presale
     */
    public function update(Request $request, Presale $presale): RedirectResponse
    {
        // Validar que se pueda editar
        if (!$presale->canBeEdited()) {
            return back()->with('error', 'Solo se pueden editar preventas en estado borrador.');
        }

        // Validar datos de entrada
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'salesperson_id' => 'nullable|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01|max:999999',
                'items.*.unit_price' => 'required|numeric|min:0|max:999999999',
                'items.*.discount' => 'nullable|numeric|min:0|max:100',
                'notes' => 'nullable|string|max:1000',
                'delivery_date' => 'nullable|date|after_or_equal:today',
            ], [
                'client_id.required' => 'Debe seleccionar un cliente.',
                'items.required' => 'Debe agregar al menos un producto.',
                'items.min' => 'Debe agregar al menos un producto.',
                'delivery_date.after_or_equal' => 'La fecha de entrega no puede ser anterior a hoy.',
            ]);
        } catch (ValidationException $e) {
            Log::warning('PresaleController update - Error de validación', [
                'presale_id' => $presale->id,
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        try {
            // Validar cliente activo
            $client = Client::find($validated['client_id']);
            if (!$client || $client->status !== 'active') {
                throw ValidationException::withMessages([
                    'client_id' => 'El cliente seleccionado no está activo.'
                ]);
            }

            // Validar productos activos
            $productIds = collect($validated['items'])->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($validated['items'] as $index => $item) {
                $product = $products->get($item['product_id']);
                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => "El producto no está activo."
                    ]);
                }
            }

            // Calcular totales
            $subtotal = 0;
            $totalDiscount = 0;
            $total = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount'] ?? 0) / 100;
                $itemSubtotal = $itemTotal - $itemDiscount;

                $subtotal += $itemTotal;
                $totalDiscount += $itemDiscount;
                $total += $itemSubtotal;
            }

            // Actualizar preventa
            $presale->update([
                'client_id' => $validated['client_id'],
                'salesperson_id' => $validated['salesperson_id'],
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'total' => $total,
                'notes' => $validated['notes'],
                'delivery_date' => $validated['delivery_date'],
            ]);

            // Eliminar items existentes y crear nuevos
            $presale->items()->delete();

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount'] ?? 0) / 100;

                PresaleItem::create([
                    'presale_id' => $presale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $itemTotal,
                    'discount_amount' => $itemDiscount,
                    'total' => $itemTotal - $itemDiscount,
                ]);
            }

            DB::commit();

            Log::info('PresaleController update - Preventa actualizada', [
                'presale_id' => $presale->id,
                'presale_code' => $presale->code,
                'total' => $presale->total,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('presales.show', $presale)
                ->with('success', "Preventa {$presale->code} actualizada exitosamente.");

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PresaleController update - Error', [
                'presale_id' => $presale->id,
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al actualizar la preventa.')->withInput();
        }
    }

    /**
     * Confirm presale (change status to confirmed)
     */
    public function confirm(Presale $presale): RedirectResponse
    {
        if ($presale->status !== 'draft') {
            return back()->with('error', 'Solo se pueden confirmar preventas en estado borrador.');
        }

        $presale->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Preventa confirmada exitosamente.');
    }

    /**
     * Convert presale to sale
     */
    public function convertToSale(Presale $presale): RedirectResponse
    {
        // Validar que se pueda convertir
        if (!$presale->canBeConverted()) {
            return back()->with('error', 'Solo se pueden convertir preventas confirmadas a ventas.');
        }

        DB::beginTransaction();
        try {
            $presale->load(['items.product', 'client']);

            // 1. Validar que el cliente siga activo
            if ($presale->client->status !== 'active') {
                throw ValidationException::withMessages([
                    'client' => 'El cliente no está activo. No se puede convertir la preventa.'
                ]);
            }

            // 2. Validar que todos los productos sigan activos
            foreach ($presale->items as $item) {
                if (!$item->product->is_active) {
                    throw ValidationException::withMessages([
                        'products' => "El producto '{$item->product->name}' ya no está activo. No se puede convertir la preventa."
                    ]);
                }
            }

            // 3. Validar stock disponible (advertencia, no bloqueante)
            $stockWarnings = [];
            foreach ($presale->items as $item) {
                if ($item->product->stock_quantity < $item->quantity) {
                    $stockWarnings[] = sprintf(
                        '%s: solicitado %.2f, disponible %.2f',
                        $item->product->name,
                        $item->quantity,
                        $item->product->stock_quantity
                    );
                }
            }

            if (!empty($stockWarnings)) {
                Log::warning('PreSaleController convertToSale - Stock insuficiente', [
                    'presale_id' => $presale->id,
                    'warnings' => $stockWarnings
                ]);

                session()->flash('warning', 'Advertencia de stock: ' . implode('; ', $stockWarnings));
            }

            // 4. Validar límite de crédito (advertencia)
            if ($presale->client->credit_limit > 0) {
                $pendingBalance = $presale->client->pending_balance ?? 0;
                $availableCredit = $presale->client->credit_limit - $pendingBalance;

                if ($presale->total > $availableCredit) {
                    Log::warning('PreSaleController convertToSale - Excede crédito', [
                        'presale_id' => $presale->id,
                        'client_id' => $presale->client_id,
                        'credit_limit' => $presale->client->credit_limit,
                        'pending_balance' => $pendingBalance,
                        'sale_total' => $presale->total
                    ]);

                    session()->flash('warning', sprintf(
                        'Esta venta excede el crédito disponible del cliente. Disponible: %.2f, Total venta: %.2f',
                        $availableCredit,
                        $presale->total
                    ));
                }
            }

            // 5. Crear la venta usando el modelo
            $sale = \App\Models\Sale::create([
                'code' => $this->generateSaleCode(),
                'client_id' => $presale->client_id,
                'salesperson_id' => $presale->salesperson_id ?? auth()->id(),
                'presale_id' => $presale->id,
                'subtotal' => $presale->subtotal,
                'total_discount' => $presale->total_discount,
                'total' => $presale->total,
                'payment_method' => 'credit', // Por defecto crédito desde preventa
                'payment_status' => 'pending',
                'notes' => trim(($presale->notes ?? '') . "\n\nConvertida desde preventa #{$presale->code}"),
                'delivery_date' => $presale->delivery_date,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // 6. Copiar items de la preventa a la venta
            foreach ($presale->items as $item) {
                \App\Models\SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'subtotal' => $item->subtotal,
                    'discount_amount' => $item->discount_amount,
                    'total' => $item->total,
                ]);
            }

            // 7. Actualizar estado de la preventa
            $presale->update([
                'status' => 'converted',
                'converted_at' => now(),
                'converted_by' => auth()->id(),
            ]);

            DB::commit();

            Log::info('PreSaleController convertToSale - Preventa convertida exitosamente', [
                'presale_id' => $presale->id,
                'presale_code' => $presale->code,
                'sale_id' => $sale->id,
                'sale_code' => $sale->code,
                'total' => $sale->total,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('sales.show', $sale)
                ->with('success', "Preventa {$presale->code} convertida exitosamente a venta {$sale->code}.");

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('PreSaleController convertToSale - Validación falló', [
                'presale_id' => $presale->id,
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors());

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PreSaleController convertToSale - Error al convertir', [
                'presale_id' => $presale->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al convertir la preventa a venta. Por favor, intente nuevamente.');
        }
    }

    /**
     * Generar código único de venta
     */
    private function generateSaleCode(): string
    {
        $lastSale = \App\Models\Sale::latest('id')->lockForUpdate()->first();
        $nextNumber = $lastSale ? intval(substr($lastSale->code, 3)) + 1 : 1;
        return 'VEN' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Approve presale
     */
    public function approve(Presale $presale): RedirectResponse
    {
        if ($presale->status !== 'draft') {
            return back()->with('error', 'Solo se pueden aprobar preventas en estado borrador.');
        }

        $presale->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
        ]);

        \Log::info('PresaleController approve - Preventa aprobada:', ['presale_id' => $presale->id]);

        return back()->with('success', 'Preventa aprobada exitosamente.');
    }

    /**
     * Reject presale
     */
    public function reject(Presale $presale): RedirectResponse
    {
        if ($presale->status === 'cancelled') {
            return back()->with('error', 'La preventa ya está cancelada.');
        }

        $presale->update([
            'status' => 'cancelled',
        ]);

        \Log::info('PresaleController reject - Preventa cancelada:', ['presale_id' => $presale->id]);

        return back()->with('success', 'Preventa cancelada exitosamente.');
    }

    /**
     * Print presale
     */
    public function print(Presale $presale): Response
    {
        $presale->load(['client', 'salesperson', 'items.product']);
        
        return Inertia::render('PreSales/Print', [
            'presale' => $presale,
        ]);
    }

    /**
     * Export presales to Excel/CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Presale::query()->with(['client', 'salesperson']);

            // Aplicar filtros
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $presales = $query->get();

            // Preparar datos para export
            $data = $presales->map(function ($presale) {
                return [
                    'Código' => $presale->code,
                    'Cliente' => $presale->client->business_name ?? '',
                    'Vendedor' => $presale->salesperson->name ?? '',
                    'Subtotal' => number_format($presale->subtotal, 2),
                    'Descuento' => number_format($presale->total_discount, 2),
                    'Total' => number_format($presale->total, 2),
                    'Estado' => ucfirst($presale->status),
                    'Fecha' => $presale->created_at->format('Y-m-d'),
                    'Entrega' => $presale->delivery_date?->format('Y-m-d') ?? 'N/A',
                ];
            });

            // Crear CSV
            $filename = 'preventas_' . now()->format('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Headers
                if ($data->isNotEmpty()) {
                    fputcsv($file, array_keys($data->first()));
                }

                // Data
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            Log::info('PreSaleController export - Exportando preventas', [
                'count' => $data->count(),
                'user_id' => auth()->id()
            ]);

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('PreSaleController export - Error', [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al exportar preventas.');
        }
    }

    /**
     * Get statistics for dashboard/reports
     */
    public function statistics(Request $request)
    {
        try {
            $dateFrom = $request->get('date_from', now()->subDays(30));
            $dateTo = $request->get('date_to', now());

            $stats = [
                'by_status' => Presale::selectRaw('status, COUNT(*) as count, SUM(total) as total')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy('status')
                    ->get(),

                'by_salesperson' => Presale::selectRaw('salesperson_id, COUNT(*) as count, SUM(total) as total')
                    ->with('salesperson:id,name')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy('salesperson_id')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get(),

                'by_date' => Presale::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as total')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),

                'top_clients' => Presale::selectRaw('client_id, COUNT(*) as count, SUM(total) as total')
                    ->with('client:id,business_name')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy('client_id')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('PreSaleController statistics - Error', [
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }

    /**
     * Bulk actions (approve, cancel, delete multiple)
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:confirm,cancel,delete',
            'presale_ids' => 'required|array|min:1',
            'presale_ids.*' => 'exists:presales,id',
        ]);

        DB::beginTransaction();
        try {
            $presales = Presale::whereIn('id', $validated['presale_ids'])->get();
            $action = $validated['action'];
            $count = 0;

            foreach ($presales as $presale) {
                switch ($action) {
                    case 'confirm':
                        if (in_array($presale->status, ['draft'])) {
                            $presale->update([
                                'status' => 'confirmed',
                                'confirmed_at' => now(),
                                'confirmed_by' => auth()->id(),
                            ]);
                            $count++;
                        }
                        break;

                    case 'cancel':
                        if ($presale->status !== 'converted') {
                            $presale->update([
                                'status' => 'cancelled',
                            ]);
                            $count++;
                        }
                        break;

                    case 'delete':
                        if ($presale->status === 'draft') {
                            $presale->items()->delete();
                            $presale->delete();
                            $count++;
                        }
                        break;
                }
            }

            DB::commit();

            Log::info('PreSaleController bulkAction - Acción masiva ejecutada', [
                'action' => $action,
                'count' => $count,
                'user_id' => auth()->id()
            ]);

            $actionLabels = [
                'confirm' => 'confirmadas',
                'cancel' => 'canceladas',
                'delete' => 'eliminadas',
            ];

            return back()->with('success', "{$count} preventas {$actionLabels[$action]} exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PreSaleController bulkAction - Error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Error al ejecutar la acción masiva.');
        }
    }

    /**
     * Destroy presale
     */
    public function destroy(Presale $presale): RedirectResponse
    {
        if ($presale->status !== 'draft') {
            return back()->with('error', 'Solo se pueden eliminar preventas en estado borrador.');
        }

        DB::beginTransaction();
        try {
            // Eliminar items primero
            $presale->items()->delete();

            // Eliminar preventa
            $presale->delete();

            DB::commit();

            Log::info('PresaleController destroy - Preventa eliminada', [
                'presale_id' => $presale->id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('presales.index')
                ->with('success', 'Preventa eliminada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PresaleController destroy - Error', [
                'presale_id' => $presale->id,
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al eliminar la preventa.');
        }
    }
}