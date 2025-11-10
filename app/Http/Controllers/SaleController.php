<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Models\Presale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request): Response
    {
        try {
            // Verificar si las tablas existen
            if (!\Schema::hasTable('sales')) {
                return Inertia::render('Sales/Index', [
                    'sales' => (object) ['data' => [], 'links' => null],
                    'filters' => $request->only(['search', 'status', 'salesperson_id', 'date_from', 'date_to']),
                    'salespeople' => [],
                    'error' => 'Las tablas de ventas no existen. Por favor, crea las tablas primero.'
                ]);
            }

            $sales = Sale::query()
                ->with(['client:id,business_name', 'salesperson:id,name'])
                ->when($request->search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                          ->orWhereHas('client', function ($clientQuery) use ($search) {
                              $clientQuery->where('business_name', 'like', "%{$search}%");
                          });
                    });
                })
                ->when($request->status, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($request->salesperson_id, function ($query, $salespersonId) {
                    $query->where('salesperson_id', $salespersonId);
                })
                ->when($request->date_from, function ($query, $date) {
                    $query->whereDate('created_at', '>=', $date);
                })
                ->when($request->date_to, function ($query, $date) {
                    $query->whereDate('created_at', '<=', $date);
                })
                ->latest()
                ->paginate(15)
                ->withQueryString();

            // Obtener estadísticas
            $stats = $this->getSalesStats();

            return Inertia::render('Sales/Index', [
                'sales' => $sales,
                'filters' => $request->only(['search', 'status', 'salesperson_id', 'payment_method', 'date_from', 'date_to']),
                'salespeople' => User::whereHas('roles', fn($q) => $q->where('name', 'vendedor-ventas'))
                    ->select('id', 'name')
                    ->get(),
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Sales/Index', [
                'sales' => (object) ['data' => [], 'links' => null],
                'filters' => $request->only(['search', 'status', 'salesperson_id', 'date_from', 'date_to']),
                'salespeople' => [],
                'error' => 'Error al cargar las ventas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new sale
     */
    public function create(): Response
    {
        try {
            // Verificar si las tablas existen
            if (!\Schema::hasTable('sales')) {
                return Inertia::render('Sales/Create', [
                    'clients' => [],
                    'products' => [],
                    'salespeople' => [],
                    'presales' => [],
                    'error' => 'Las tablas de ventas no existen. Por favor, crea las tablas primero.'
                ]);
            }

            // Cargar datos de forma simple y segura
            $clients = [];
            $products = [];
            $salespeople = [];
            $presales = [];

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

            try {
                if (\Schema::hasTable('presales')) {
                    $presales = \DB::table('presales')
                        ->whereIn('status', ['draft', 'confirmed'])
                        ->select('id', 'code', 'client_id', 'total', 'status')
                        ->get()
                        ->toArray();
                }
            } catch (\Exception $e) {
                // Si falla, usar array vacío
            }

            return Inertia::render('Sales/Create', [
                'clients' => $clients,
                'products' => $products,
                'salespeople' => $salespeople,
                'presales' => $presales,
            ]);

        } catch (\Exception $e) {
            return Inertia::render('Sales/Create', [
                'clients' => [],
                'products' => [],
                'salespeople' => [],
                'presales' => [],
                'error' => 'Error al cargar datos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created sale
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('SaleController store - Iniciando creación de venta', [
            'user_id' => auth()->id(),
            'client_id' => $request->client_id,
            'payment_method' => $request->payment_method,
            'items_count' => count($request->items ?? [])
        ]);

        // Validar datos de entrada
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'salesperson_id' => 'nullable|exists:users,id',
                'presale_id' => 'nullable|exists:presales,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01|max:999999',
                'items.*.unit_price' => 'required|numeric|min:0|max:999999999',
                'items.*.discount' => 'nullable|numeric|min:0|max:100',
                'payment_method' => 'required|in:cash,credit,transfer',
                'payment_status' => 'nullable|in:paid,pending,partial',
                'notes' => 'nullable|string|max:1000',
                'delivery_date' => 'nullable|date',
            ], [
                'client_id.required' => 'Debe seleccionar un cliente.',
                'items.required' => 'Debe agregar al menos un producto.',
                'items.min' => 'Debe agregar al menos un producto.',
                'payment_method.required' => 'Debe seleccionar un método de pago.',
                'payment_method.in' => 'El método de pago seleccionado no es válido.',
                'delivery_date.after_or_equal' => 'La fecha de entrega no puede ser anterior a hoy.',
            ]);
        } catch (ValidationException $e) {
            Log::warning('SaleController store - Error de validación', [
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
            }

            // 3. Calcular totales
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

            // 4. Validar límite de crédito (si es venta a crédito)
            if ($validated['payment_method'] === 'credit' && $client->credit_limit > 0) {
                $pendingBalance = $client->pending_balance ?? 0;
                $availableCredit = $client->credit_limit - $pendingBalance;

                if ($total > $availableCredit) {
                    Log::warning('SaleController store - Cliente excede límite de crédito', [
                        'client_id' => $client->id,
                        'credit_limit' => $client->credit_limit,
                        'pending_balance' => $pendingBalance,
                        'available_credit' => $availableCredit,
                        'sale_total' => $total
                    ]);

                    session()->flash('warning', sprintf(
                        'Advertencia: Esta venta (%.2f) excede el crédito disponible del cliente (%.2f).',
                        $total,
                        $availableCredit
                    ));
                }
            }

            // 5. Determinar payment_status según payment_method
            $paymentStatus = $validated['payment_status'] ?? 'pending';
            if ($validated['payment_method'] === 'cash') {
                $paymentStatus = 'paid';
            } elseif ($validated['payment_method'] === 'credit') {
                $paymentStatus = 'pending';
            }

            // 6. Generar código único de venta
            $code = $this->generateSaleCode();

            // 7. Crear la venta
            $sale = Sale::create([
                'code' => $code,
                'client_id' => $validated['client_id'],
                'salesperson_id' => $validated['salesperson_id'] ?? auth()->id(),
                'presale_id' => $validated['presale_id'],
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paymentStatus,
                'notes' => $validated['notes'],
                'delivery_date' => $validated['delivery_date'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // 8. Crear items de la venta
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount'] ?? 0) / 100;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $itemTotal,
                    'discount_amount' => $itemDiscount,
                    'total' => $itemTotal - $itemDiscount,
                ]);
            }

            // 9. Si viene de una preventa, marcarla como convertida
            if ($validated['presale_id']) {
                Presale::where('id', $validated['presale_id'])->update([
                    'status' => 'converted',
                    'converted_at' => now(),
                    'converted_by' => auth()->id(),
                ]);
            }

            // 10. Generar factura automáticamente si:
            // - El método de pago es crédito
            // - O el estado de pago es parcial o pendiente
            $shouldGenerateInvoice = false;
            if ($validated['payment_method'] === 'credit') {
                $shouldGenerateInvoice = true;
            } elseif (in_array($paymentStatus, ['partial', 'pending', 'unpaid'])) {
                $shouldGenerateInvoice = true;
            }

            if ($shouldGenerateInvoice) {
                try {
                    $this->generateInvoiceForSale($sale);
                    Log::info('SaleController store - Factura generada automáticamente', [
                        'sale_id' => $sale->id,
                        'sale_code' => $sale->code,
                        'payment_method' => $sale->payment_method,
                        'payment_status' => $sale->payment_status,
                    ]);
                } catch (\Exception $e) {
                    // Log el error pero no fallar la creación de la venta
                    Log::error('SaleController store - Error al generar factura automática', [
                        'sale_id' => $sale->id,
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            DB::commit();

            Log::info('SaleController store - Venta creada exitosamente', [
                'sale_id' => $sale->id,
                'sale_code' => $sale->code,
                'client_id' => $sale->client_id,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'items_count' => count($validated['items']),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('sales.index')
                ->with('success', "Venta {$code} creada exitosamente.");

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('SaleController store - Validación de negocio falló', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SaleController store - Error al crear venta', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'client_id' => $request->client_id
            ]);

            return back()
                ->with('error', 'Error al crear la venta. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    /**
     * Generar código único de venta
     */
    private function generateSaleCode(): string
    {
        $lastSale = Sale::latest('id')->lockForUpdate()->first();
        $nextNumber = $lastSale ? intval(substr($lastSale->code, 3)) + 1 : 1;
        return 'VEN' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale): Response
    {
        $sale->load([
            'client',
            'salesperson',
            'presale',
            'items.product',
            'creator:id,name',
        ]);

        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Show the form for editing the specified sale
     */
    public function edit(Sale $sale): Response
    {
        if ($sale->status !== 'draft') {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'Solo se pueden editar ventas en estado borrador.');
        }

        $sale->load(['items.product']);

        return Inertia::render('Sales/Edit', [
            'sale' => $sale,
            'clients' => Client::where('status', 'active')
                ->select('id', 'business_name', 'trade_name')
                ->get(),
            'products' => Product::where('is_active', true)
                ->select('id', 'name', 'code', 'sale_price', 'stock_quantity')
                ->get(),
            'salespeople' => User::whereHas('roles', fn($q) => $q->where('name', 'vendedor-ventas'))
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * Update the specified sale
     */
    public function update(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Solo se pueden editar ventas en estado borrador.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'salesperson_id' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'required|in:cash,credit,transfer',
            'payment_status' => 'required|in:paid,draft,partial',
            'notes' => 'nullable|string|max:1000',
            'delivery_date' => 'nullable|date|after_or_equal:today',
        ]);

        DB::beginTransaction();
        try {
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

            // Actualizar venta
            $sale->update([
                'client_id' => $validated['client_id'],
                'salesperson_id' => $validated['salesperson_id'],
                'subtotal' => $subtotal,
                'total_discount' => $totalDiscount,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'],
                'delivery_date' => $validated['delivery_date'],
            ]);

            // Eliminar items existentes
            $sale->items()->delete();

            // Crear nuevos items
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount'] ?? 0) / 100;
                
                SaleItem::create([
                    'sale_id' => $sale->id,
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

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sale
     */
    public function destroy(Sale $sale): RedirectResponse
    {
        // Permitir eliminar ventas en estado draft (borrador)
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Solo se pueden eliminar ventas en estado borrador.');
        }

        try {
            $sale->delete();

            \Log::info('SaleController destroy - Venta eliminada', [
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Venta eliminada exitosamente.');
        } catch (\Exception $e) {
            \Log::error('SaleController destroy - Error:', [
                'sale_id' => $sale->id,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Generate invoice for sale
     */
    public function generateInvoice(Sale $sale): RedirectResponse
    {
        // Permitir generar facturas para ventas a crédito o con pago parcial/pendiente
        // incluso si están en estado draft
        $canGenerateInvoice = false;
        if ($sale->payment_method === 'credit') {
            $canGenerateInvoice = true;
        } elseif (in_array($sale->payment_status, ['partial', 'pending', 'unpaid'])) {
            $canGenerateInvoice = true;
        }

        if (!$canGenerateInvoice && $sale->status !== 'completed') {
            return back()->with('error', 'Solo se pueden generar facturas para ventas completadas o ventas a crédito/pago parcial.');
        }

        // Verificar si ya tiene factura
        if ($sale->invoice) {
            return redirect()->route('account-receivables.show', $sale->invoice)
                ->with('info', 'Esta venta ya tiene una factura generada.');
        }

        DB::beginTransaction();
        try {
            $invoice = $this->generateInvoiceForSale($sale);

            DB::commit();

            return redirect()->route('account-receivables.show', $invoice)
                ->with('success', 'Factura generada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('SaleController generateInvoice - Error:', [
                'sale_id' => $sale->id,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Error al generar la factura: ' . $e->getMessage());
        }
    }

    /**
     * Generar factura a partir de una venta
     */
    protected function generateInvoiceForSale(Sale $sale)
    {
        // Verificar si ya tiene factura
        if ($sale->invoice) {
            return $sale->invoice;
        }

        $sale->load(['client', 'items.product']);

        // Calcular fecha de vencimiento según los días de crédito del cliente
        $dueDate = now()->addDays($sale->client->credit_days ?? 30);

        // Determinar el estado de pago inicial de la factura basado en el payment_status de la venta
        $invoicePaymentStatus = 'unpaid';
        $invoicePaidAmount = 0;
        $invoiceBalance = $sale->total;
        
        // Si la venta tiene pago parcial, reflejarlo en la factura
        if ($sale->payment_status === 'partial') {
            $invoicePaymentStatus = 'partial';
            // El saldo se calculará cuando se registren pagos en la factura
        } elseif ($sale->payment_status === 'paid') {
            $invoicePaymentStatus = 'paid';
            $invoicePaidAmount = $sale->total;
            $invoiceBalance = 0;
        }

        // Crear la factura
        $invoice = \App\Models\Invoice::create([
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => $dueDate,
            'sale_id' => $sale->id,
            'client_id' => $sale->client_id,
            'client_name' => $sale->client->business_name,
            'client_tax_id' => $sale->client->tax_id,
            'client_address' => $sale->client->address,
            'created_by' => auth()->id(),
            'subtotal' => $sale->subtotal,
            'discount_amount' => $sale->total_discount,
            'tax_amount' => 0, // Puedes calcular IVA si lo necesitas
            'total' => $sale->total,
            'paid_amount' => $invoicePaidAmount,
            'balance' => $invoiceBalance,
            'status' => 'pending',
            'payment_status' => $invoicePaymentStatus,
            'payment_method' => $sale->payment_method,
            'notes' => $sale->notes,
        ]);

        // Crear items de la factura
        foreach ($sale->items as $item) {
            \App\Models\InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item->product_id,
                'description' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'discount_amount' => $item->discount_amount,
                'subtotal' => $item->subtotal,
                'tax_amount' => 0,
                'total' => $item->total,
            ]);
        }

        // Crear cuenta por cobrar solo si hay saldo pendiente
        if ($invoiceBalance > 0) {
            \App\Models\Receivable::create([
                'client_id' => $sale->client_id,
                'invoice_id' => $invoice->id,
                'amount' => $sale->total,
                'balance' => $invoiceBalance,
                'due_date' => $dueDate,
                'status' => $invoicePaymentStatus === 'partial' ? 'partial' : 'pending',
                'notes' => "Generada desde venta #{$sale->code}",
                'created_by' => auth()->id(),
            ]);
        }

        return $invoice;
    }

    /**
     * Print sale receipt
     */
    public function printReceipt(Sale $sale): Response
    {
        $sale->load([
            'client',
            'salesperson',
            'items.product',
        ]);

        return Inertia::render('Sales/PrintReceipt', [
            'sale' => $sale,
        ]);
    }

    /**
     * Complete sale (change status to complete)
     */
    public function complete(Sale $sale): RedirectResponse
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'Solo se pueden completar ventas pendientes.');
        }

        DB::beginTransaction();
        try {
            // 1. Verificar stock de productos
            foreach ($sale->items as $item) {
                $product = $item->product;
                if ($product->stock_quantity < $item->quantity) {
                    DB::rollBack();
                    return back()->with('error', "Stock insuficiente para el producto: {$product->name}. Disponible: {$product->stock_quantity}, Requerido: {$item->quantity}");
                }
            }

            // 2. Descontar stock de productos
            foreach ($sale->items as $item) {
                $product = $item->product;
                $product->updateStock($item->quantity, 'subtract', "Venta #{$sale->code}");
            }

            // 3. Actualizar estado de la venta
            $sale->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // 4. Generar factura si:
            // - El método de pago es crédito
            // - O el estado de pago es parcial o pendiente (unpaid)
            $shouldGenerateInvoice = false;
            if ($sale->payment_method === 'credit') {
                $shouldGenerateInvoice = true;
            } elseif (in_array($sale->payment_status, ['partial', 'pending', 'unpaid'])) {
                $shouldGenerateInvoice = true;
            }

            if ($shouldGenerateInvoice) {
                $this->generateInvoiceForSale($sale);
            }

            DB::commit();

            \Log::info('SaleController complete - Venta completada:', [
                'sale_id' => $sale->id,
                'payment_method' => $sale->payment_method,
                'payment_status' => $sale->payment_status,
                'should_generate_invoice' => $shouldGenerateInvoice,
            ]);

            $message = 'Venta completada exitosamente.';
            if ($shouldGenerateInvoice) {
                $message .= ' Se ha generado la factura en cuentas por cobrar.';
            }
            
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('SaleController complete - Error:', [
                'sale_id' => $sale->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al completar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Cancel sale (change status to canceled)
     */
    public function cancel(Sale $sale): RedirectResponse
    {
        if ($sale->status === 'canceled') {
            return back()->with('error', 'La venta ya está cancelada.');
        }

        $sale->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'canceled_by' => auth()->id(),
        ]);

        \Log::info('SaleController cancel - Venta cancelada:', ['sale_id' => $sale->id]);

        return back()->with('success', 'Venta cancelada exitosamente.');
    }

    /**
     * Print sale
     */
    public function print(Sale $sale)
    {
        $sale->load([
            'client:id,business_name,trade_name,email,phone',
            'salesperson:id,name',
            'items.product:id,name,code,description'
        ]);

        // Generar ticket térmico
        $ticket = $this->generateThermalTicket($sale);

        return response($ticket, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'inline'
        ]);
    }

    /**
     * Generate thermal printer ticket for sale
     */
    private function generateThermalTicket($sale): string
    {
        $lineWidth = 32; // Ancho típico para impresoras térmicas

        $ticket = "";

        // Encabezado
        $ticket .= $this->centerText("FARMACIA CENTRAL", $lineWidth) . "\n";
        $ticket .= $this->centerText("TICKET DE VENTA", $lineWidth) . "\n";
        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $ticket .= "\n";

        // Información de la venta
        $ticket .= "Venta #: " . $sale->code . "\n";
        $ticket .= "Fecha: " . $sale->created_at->format('d/m/Y H:i') . "\n";
        $ticket .= "Vendedor: " . ($sale->salesperson->name ?? 'N/A') . "\n";

        if ($sale->client) {
            $ticket .= "Cliente: " . $this->truncateText(($sale->client->business_name ?? $sale->client->trade_name ?? 'N/A'), 30) . "\n";
        }

        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $ticket .= $this->centerText("DETALLE DE PRODUCTOS", $lineWidth) . "\n";
        $ticket .= str_repeat("-", $lineWidth) . "\n";

        // Items
        foreach ($sale->items as $item) {
            $ticket .= $this->formatItem($item, $lineWidth) . "\n";
        }

        $ticket .= str_repeat("-", $lineWidth) . "\n";

        // Totales
        $ticket .= $this->formatTotal("SUBTOTAL", $sale->subtotal, $lineWidth) . "\n";
        if ($sale->total_discount > 0) {
            $ticket .= $this->formatTotal("DESCUENTO", $sale->total_discount, $lineWidth) . "\n";
        }
        $ticket .= $this->formatTotal("TOTAL", $sale->total, $lineWidth) . "\n";

        // Método de pago
        $ticket .= "Metodo de pago: " . ucfirst($sale->payment_method) . "\n";

        if ($sale->payment_method === 'credit') {
            $ticket .= "Estado: PENDIENTE DE PAGO\n";
        } else {
            $ticket .= "Estado: PAGADO\n";
        }

        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $ticket .= $this->centerText("¡Gracias por su compra!", $lineWidth) . "\n";
        $ticket .= "\n";
        $ticket .= "\n";
        $ticket .= "\x1B" . "m"; // Corte de papel (ESC m para algunas impresoras)
        $ticket .= "\n";

        return $ticket;
    }

    private function centerText($text, $width): string
    {
        $padding = max(0, ($width - strlen($text)) / 2);
        return str_repeat(" ", (int)$padding) . $text;
    }

    private function truncateText($text, $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length - 3) . '...';
    }

    private function formatItem($item, $width): string
    {
        $name = $this->truncateText($item->product->name, 20);
        $quantity = $item->quantity;
        $price = number_format($item->unit_price, 2);
        $total = number_format($item->total, 2);

        return sprintf("%-20s %3dx %6s %6s", $name, $quantity, $price, $total);
    }

    private function formatTotal($label, $amount, $width): string
    {
        $formattedAmount = number_format($amount, 2);
        return sprintf("%-20s %10s", $label . ":", $formattedAmount);
    }

    /**
     * Get sales statistics
     */
    private function getSalesStats(): array
    {
        try {
            if (!\Schema::hasTable('sales')) {
                return [
                    'total_sales' => 0,
                    'pending_sales' => 0,
                    'complete_sales' => 0,
                    'canceled_sales' => 0,
                    'total_value' => 0,
                    'pending_value' => 0,
                    'complete_value' => 0,
                ];
            }

            $totalSales = \DB::table('sales')->count();
            // Estados según el enum: 'draft', 'completed', 'cancelled'
            // 'draft' se considera pendiente
            $pendingSales = \DB::table('sales')->where('status', 'draft')->count();
            $completeSales = \DB::table('sales')->where('status', 'completed')->count();
            $canceledSales = \DB::table('sales')->where('status', 'cancelled')->count();
            
            $totalValue = \DB::table('sales')->sum('total') ?? 0;
            $pendingValue = \DB::table('sales')->where('status', 'draft')->sum('total') ?? 0;
            $completeValue = \DB::table('sales')->where('status', 'completed')->sum('total') ?? 0;

            return [
                'total_sales' => $totalSales,
                'pending_sales' => $pendingSales,
                'complete_sales' => $completeSales,
                'canceled_sales' => $canceledSales,
                'total_value' => $totalValue,
                'pending_value' => $pendingValue,
                'complete_value' => $completeValue,
            ];
        } catch (\Exception $e) {
            \Log::error('SaleController getSalesStats - Error:', ['message' => $e->getMessage()]);
            return [
                'total_sales' => 0,
                'pending_sales' => 0,
                'complete_sales' => 0,
                'canceled_sales' => 0,
                'total_value' => 0,
                'pending_value' => 0,
                'complete_value' => 0,
            ];
        }
    }

    /**
     * Marcar una venta como pagada
     */
    public function markAsPaid(Request $request, $saleId)
    {
        // Log inmediato al inicio
        error_log('=== SaleController markAsPaid - MÉTODO LLAMADO ===');
        error_log('Sale ID: ' . $saleId);
        error_log('Request URL: ' . $request->url());
        error_log('Request Method: ' . $request->method());
        error_log('User ID: ' . (auth()->id() ?? 'null'));
        
        \Log::info('SaleController markAsPaid - Método llamado', [
            'sale_id_param' => $saleId,
            'request_all' => $request->all(),
            'user_id' => auth()->id(),
            'url' => $request->url(),
            'method' => $request->method(),
        ]);

        try {
            // Buscar la venta por ID
            $sale = Sale::findOrFail($saleId);
            
            \Log::info('SaleController markAsPaid - Venta encontrada', [
                'sale_id' => $sale->id,
                'current_payment_status' => $sale->payment_status,
            ]);

            if ($sale->payment_status === 'paid') {
                \Log::warning('SaleController markAsPaid - Venta ya está pagada', [
                    'sale_id' => $sale->id,
                ]);
                return back()->with('error', 'La venta ya está marcada como pagada.');
            }

            $sale->update([
                'payment_status' => 'paid',
            ]);

            \Log::info('SaleController markAsPaid - Venta marcada como pagada exitosamente', [
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
            ]);

            // Para Inertia, usar back() directamente
            return back()->with('success', 'La venta ha sido marcada como pagada exitosamente.');
        } catch (\Exception $e) {
            \Log::error('SaleController markAsPaid - Error:', [
                'sale_id' => $sale->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al marcar la venta como pagada: ' . $e->getMessage());
        }
    }

    /**
     * Obtener los items de una preventa para cargar en el formulario de venta
     */
    public function getPresaleItems(Request $request, $presaleId)
    {
        try {
            $presale = Presale::with(['items.product', 'client'])->findOrFail($presaleId);

            // Verificar que la preventa esté en un estado válido (draft o confirmed)
            if (!in_array($presale->status, ['draft', 'confirmed'])) {
                return response()->json([
                    'error' => 'La preventa debe estar en estado borrador o confirmada para convertirla en venta'
                ], 400);
            }

            // Formatear los items para el formulario
            $items = $presale->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount' => (float) $item->discount,
                    'subtotal' => (float) $item->subtotal,
                    'discount_amount' => (float) $item->discount_amount,
                    'total' => (float) $item->total,
                ];
            });

            return response()->json([
                'items' => $items,
                'client_id' => $presale->client_id,
                'subtotal' => (float) $presale->subtotal,
                'total_discount' => (float) $presale->total_discount,
                'total' => (float) $presale->total,
            ]);
        } catch (\Exception $e) {
            \Log::error('SaleController getPresaleItems - Error:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Error al cargar los items de la preventa: ' . $e->getMessage()
            ], 500);
        }
    }
}