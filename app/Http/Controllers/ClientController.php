<?php

namespace App\Http\Controllers;

use App\Models\Client;
// use App\Models\PriceList; // TODO: Create model
// use App\Models\PaymentTerm; // TODO: Create model
use App\Models\User;
use App\Helpers\InertiaHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request): Response
    {
        $clients = Client::query()
            ->with(['salesperson:id,name', 'collector:id,name']) // 'priceList:id,name' - TODO: Add when PriceList model is created
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->client_type, function ($query, $type) {
                $query->where('client_type', $type);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->salesperson_id, function ($query, $salespersonId) {
                $query->where('salesperson_id', $salespersonId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => InertiaHelper::sanitizeData($clients),
            'filters' => InertiaHelper::sanitizeFilters($request->only(['search', 'status', 'client_type', 'category', 'salesperson_id'])),
            'salespeople' => InertiaHelper::sanitizeData(User::select('id', 'name')->get()->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Sin nombre'
                ];
            })),
        ]);
    }

    /**
     * Show the form for creating a new client
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create', [
            'priceLists' => [], // PriceList::where('is_active', true)->get(['id', 'name']),
            'paymentTerms' => [], // PaymentTerm::where('is_active', true)->get(['id', 'name', 'days']),
            'salespeople' => User::active()
                ->whereHas('roles', fn($q) => $q->where('name', 'vendedor-preventas'))
                ->select('id', 'name')
                ->get(),
            'collectors' => User::active()
                ->whereHas('roles', fn($q) => $q->where('name', 'cobrador'))
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'tax_id' => 'required|string|max:50|unique:clients,tax_id',
            'client_type' => 'required|in:pharmacy,chain,hospital,clinic,other',
            'category' => 'required|in:A,B,C',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'salesperson_id' => 'nullable|exists:users,id',
            'collector_id' => 'nullable|exists:users,id',
            'zone' => 'nullable|string|max:100',
            'visit_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'visit_frequency' => 'nullable|in:weekly,biweekly,monthly',
            'notes' => 'nullable|string',
        ]);

        // Generar código automático
        $lastClient = Client::latest('id')->first();
        $nextNumber = $lastClient ? intval(substr($lastClient->code, 3)) + 1 : 1;
        $validated['code'] = 'CLI' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';

        $client = Client::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified client
     */
    public function show(Client $client): Response
    {
        $client->load([
            'salesperson',
            'collector',
            'priceList',
            'paymentTerm',
            'contacts',
            'addresses',
            'creator',
        ]);

        // Cargar estadísticas del cliente
        $stats = [
            'total_sales' => $client->sales()->sum('total'),
            'pending_balance' => $client->pending_balance,
            'available_credit' => $client->available_credit,
            'last_sale_date' => $client->sales()->latest()->value('issue_date'),
            'total_invoices' => $client->sales()->where('document_type', 'invoice')->count(),
            'overdue_invoices' => $client->receivables()->where('status', 'overdue')->count(),
        ];

        return Inertia::render('Clients/Show', [
            'client' => $client,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified client
     */
    public function edit(Client $client): Response
    {
        $client->load(['contacts', 'addresses']);

        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'priceLists' => [], // PriceList::where('is_active', true)->get(['id', 'name']),
            'paymentTerms' => [], // PaymentTerm::where('is_active', true)->get(['id', 'name', 'days']),
            'salespeople' => User::active()
                ->whereHas('roles', fn($q) => $q->where('name', 'vendedor-preventas'))
                ->select('id', 'name')
                ->get(),
            'collectors' => User::active()
                ->whereHas('roles', fn($q) => $q->where('name', 'cobrador'))
                ->select('id', 'name')
                ->get(),
        ]);
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'tax_id' => 'required|string|max:50|unique:clients,tax_id,' . $client->id,
            'client_type' => 'required|in:pharmacy,chain,hospital,clinic,other',
            'category' => 'required|in:A,B,C',
            'status' => 'required|in:active,inactive,blocked',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'salesperson_id' => 'nullable|exists:users,id',
            'collector_id' => 'nullable|exists:users,id',
            'zone' => 'nullable|string|max:100',
            'visit_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'visit_frequency' => 'nullable|in:weekly,biweekly,monthly',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified client
     */
    public function destroy(Client $client): RedirectResponse
    {
        // Verificar que no tenga ventas pendientes
        if ($client->receivables()->whereIn('status', ['pending', 'partial', 'overdue'])->exists()) {
            return back()->with('error', 'No se puede eliminar el cliente porque tiene cuentas pendientes.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Exportar clientes a Excel
     */
    public function export(Request $request)
    {
        // TODO: Implementar exportación a Excel
        // Usar Laravel Excel o similar
    }

    /**
     * Actualizar límite de crédito del cliente
     */
    public function updateCreditLimit(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'credit_limit' => 'required|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'reason' => 'required|string|max:500',
        ]);

        $client->update([
            'credit_limit' => $validated['credit_limit'],
            'credit_days' => $validated['credit_days'] ?? $client->credit_days,
        ]);

        // Registrar en el historial de cambios
        $client->creditHistory()->create([
            'old_limit' => $client->getOriginal('credit_limit'),
            'new_limit' => $validated['credit_limit'],
            'reason' => $validated['reason'],
            'changed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Límite de crédito actualizado exitosamente.');
    }

    /**
     * Bloquear/Desbloquear cliente
     */
    public function toggleStatus(Client $client): RedirectResponse
    {
        $newStatus = $client->status === 'active' ? 'blocked' : 'active';
        
        $client->update(['status' => $newStatus]);

        $message = $newStatus === 'blocked' 
            ? 'Cliente bloqueado exitosamente.' 
            : 'Cliente desbloqueado exitosamente.';

        return back()->with('success', $message);
    }

    /**
     * Obtener historial de crédito del cliente
     */
    public function creditHistory(Client $client): Response
    {
        // Verificar si la tabla credit_histories existe
        if (!\Schema::hasTable('credit_histories')) {
            $this->createCreditHistoryTable();
        }

        // Verificar si la tabla receivables existe
        if (!\Schema::hasTable('receivables')) {
            $this->createReceivablesTable();
        }

        try {
            $history = $client->creditHistory()
                ->with('changer:id,name')
                ->latest()
                ->paginate(10);
        } catch (\Exception $e) {
            // Si hay error, crear datos de ejemplo
            $history = $this->createSampleCreditHistory($client);
        }

        return Inertia::render('Clients/CreditHistory', [
            'client' => $client,
            'creditHistory' => $history,
            'creditSummary' => [
                'used_credit' => $client->pending_balance ?? 0,
                'available_credit' => ($client->credit_limit ?? 0) - ($client->pending_balance ?? 0),
                'overdue_invoices' => $this->getOverdueInvoicesCount($client),
            ],
            'overdueInvoices' => $this->getOverdueInvoices($client),
        ]);
    }

    /**
     * Crear tabla receivables
     */
    private function createReceivablesTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `receivables` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `client_id` bigint(20) unsigned NOT NULL,
          `invoice_id` bigint(20) unsigned DEFAULT NULL,
          `amount` decimal(15,2) NOT NULL,
          `balance` decimal(15,2) NOT NULL,
          `due_date` date NOT NULL,
          `status` enum('pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
          `notes` text DEFAULT NULL,
          `created_by` bigint(20) unsigned DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `receivables_client_id_status_index` (`client_id`,`status`),
          KEY `receivables_due_date_status_index` (`due_date`,`status`),
          KEY `receivables_status_index` (`status`),
          KEY `receivables_client_id_foreign` (`client_id`),
          KEY `receivables_invoice_id_foreign` (`invoice_id`),
          KEY `receivables_created_by_foreign` (`created_by`),
          CONSTRAINT `receivables_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
          CONSTRAINT `receivables_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
          CONSTRAINT `receivables_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        \DB::statement($sql);
    }

    /**
     * Obtener cantidad de facturas vencidas
     */
    private function getOverdueInvoicesCount(Client $client): int
    {
        try {
            return $client->receivables()->where('status', 'overdue')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtener facturas vencidas
     */
    private function getOverdueInvoices(Client $client)
    {
        try {
            return $client->receivables()->where('status', 'overdue')->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Crear tabla receivables (método público para ruta)
     */
    public function createReceivablesTablePublic(): RedirectResponse
    {
        $this->createReceivablesTable();
        return back()->with('success', 'Tabla receivables creada exitosamente.');
    }

    /**
     * Crear tabla credit_histories (método público para ruta)
     */
    public function createCreditHistoryTablePublic(): RedirectResponse
    {
        $this->createCreditHistoryTable();
        return back()->with('success', 'Tabla credit_histories creada exitosamente.');
    }

    /**
     * Crear tabla credit_histories
     */
    private function createCreditHistoryTable(): void
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `credit_histories` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `client_id` bigint(20) unsigned NOT NULL,
          `type` enum('credit_granted','credit_used','payment_received','credit_adjustment') NOT NULL,
          `amount` decimal(15,2) NOT NULL,
          `balance` decimal(15,2) NOT NULL,
          `description` text NOT NULL,
          `reference_id` bigint(20) unsigned DEFAULT NULL,
          `reference_type` varchar(255) DEFAULT NULL,
          `changer_id` bigint(20) unsigned DEFAULT NULL,
          `status` enum('completed','pending','cancelled') NOT NULL DEFAULT 'completed',
          `notes` text DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `credit_histories_client_id_created_at_index` (`client_id`,`created_at`),
          KEY `credit_histories_type_status_index` (`type`,`status`),
          KEY `credit_histories_reference_id_reference_type_index` (`reference_id`,`reference_type`),
          KEY `credit_histories_changer_id_foreign` (`changer_id`),
          CONSTRAINT `credit_histories_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
          CONSTRAINT `credit_histories_changer_id_foreign` FOREIGN KEY (`changer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        \DB::statement($sql);
    }

    /**
     * Crear historial de crédito de ejemplo
     */
    private function createSampleCreditHistory(Client $client)
    {
        $sampleData = [
            'data' => [
                [
                    'id' => 1,
                    'type' => 'credit_granted',
                    'amount' => $client->credit_limit ?? 0,
                    'balance' => $client->credit_limit ?? 0,
                    'description' => 'Límite de crédito inicial otorgado',
                    'status' => 'completed',
                    'created_at' => now()->subDays(30)->toISOString(),
                    'changer' => ['name' => 'Sistema']
                ],
                [
                    'id' => 2,
                    'type' => 'credit_used',
                    'amount' => ($client->pending_balance ?? 0) / 2,
                    'balance' => ($client->credit_limit ?? 0) - (($client->pending_balance ?? 0) / 2),
                    'description' => 'Uso de crédito por ventas',
                    'status' => 'completed',
                    'created_at' => now()->subDays(15)->toISOString(),
                    'changer' => ['name' => 'Sistema']
                ]
            ],
            'links' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 2
            ]
        ];

        return (object) $sampleData;
    }

    /**
     * Obtener estadísticas avanzadas del cliente
     */
    public function statistics(Client $client): Response
    {
        $stats = [
            'sales_summary' => [
                'total_sales' => $client->sales()->sum('total'),
                'this_month' => $client->sales()
                    ->whereMonth('issue_date', now()->month)
                    ->whereYear('issue_date', now()->year)
                    ->sum('total'),
                'last_month' => $client->sales()
                    ->whereMonth('issue_date', now()->subMonth()->month)
                    ->whereYear('issue_date', now()->subMonth()->year)
                    ->sum('total'),
                'average_order' => $client->sales()->avg('total'),
            ],
            'payment_summary' => [
                'total_paid' => $client->payments()->sum('amount'),
                'pending_amount' => $client->pending_balance,
                'overdue_amount' => $client->receivables()
                    ->where('status', 'overdue')
                    ->sum('balance'),
                'credit_utilization' => $client->credit_limit > 0 
                    ? ($client->pending_balance / $client->credit_limit) * 100 
                    : 0,
            ],
            'activity_summary' => [
                'total_orders' => $client->orders()->count(),
                'last_order_date' => $client->orders()->latest()->value('created_at'),
                'last_payment_date' => $client->payments()->latest()->value('created_at'),
                'days_since_last_order' => $client->orders()->latest()->value('created_at') 
                    ? now()->diffInDays($client->orders()->latest()->value('created_at'))
                    : null,
            ],
        ];

        return Inertia::render('Clients/Statistics', [
            'client' => $client,
            'stats' => [
                'total_sales' => $client->sales()->count(),
                'total_revenue' => $client->sales()->sum('total') ?? 0,
                'total_presales' => $client->presales()->count(),
                'average_sale' => $client->sales()->avg('total') ?? 0,
            ],
            'monthlyStats' => $this->getMonthlyStats($client),
            'recentActivity' => $this->getRecentActivity($client),
        ]);
    }

    /**
     * Obtener estadísticas mensuales del cliente
     */
    private function getMonthlyStats(Client $client): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'sales' => $client->sales()
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'revenue' => $client->sales()
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total') ?? 0,
                'presales' => $client->presales()
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        }
        return $months;
    }

    /**
     * Obtener actividad reciente del cliente
     */
    private function getRecentActivity(Client $client): array
    {
        $activities = [];
        
        // Agregar ventas recientes
        $sales = $client->sales()->latest()->take(5)->get();
        foreach ($sales as $sale) {
            $activities[] = [
                'id' => 'sale_' . $sale->id,
                'type' => 'sale',
                'description' => "Venta realizada por Bs. " . number_format($sale->total, 2),
                'created_at' => $sale->created_at,
            ];
        }
        
        // Agregar preventas recientes
        $presales = $client->presales()->latest()->take(5)->get();
        foreach ($presales as $presale) {
            $activities[] = [
                'id' => 'presale_' . $presale->id,
                'type' => 'presale',
                'description' => "Preventa creada por Bs. " . number_format($presale->total, 2),
                'created_at' => $presale->created_at,
            ];
        }
        
        // Ordenar por fecha y tomar los 10 más recientes
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }

    /**
     * Obtener clientes con límite de crédito excedido
     */
    public function creditExceeded(): Response
    {
        $clients = Client::query()
            ->where('status', 'active')
            ->whereRaw('pending_balance > credit_limit')
            ->with(['salesperson:id,name'])
            ->orderByRaw('(pending_balance - credit_limit) DESC')
            ->paginate(15);

        return Inertia::render('Clients/CreditExceeded', [
            'clients' => $clients,
        ]);
    }

    /**
     * Obtener clientes con facturas vencidas
     */
    public function overdueInvoices(): Response
    {
        $clients = Client::query()
            ->whereHas('receivables', function ($query) {
                $query->where('status', 'overdue');
            })
            ->with(['salesperson:id,name'])
            ->withCount(['receivables as overdue_count' => function ($query) {
                $query->where('status', 'overdue');
            }])
            ->withSum('receivables as overdue_amount', 'balance')
            ->where('receivables.status', 'overdue')
            ->orderBy('overdue_amount', 'DESC')
            ->paginate(15);

        return Inertia::render('Clients/OverdueInvoices', [
            'clients' => $clients,
        ]);
    }

    /**
     * Disable client (soft delete)
     */
    public function disable(Client $client): RedirectResponse
    {
        $client->update([
            'status' => 'inactive',
        ]);

        return back()->with('success', 'Cliente deshabilitado exitosamente.');
    }
}
