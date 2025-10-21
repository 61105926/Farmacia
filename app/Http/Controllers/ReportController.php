<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $stats = [
            'totalSales' => Invoice::where('status', '!=', 'cancelled')->sum('total'),
            'totalOrders' => Order::where('status', '!=', 'cancelled')->count(),
            'totalClients' => Client::where('status', 'active')->count(),
            'totalProducts' => Product::where('is_active', true)->count(),
            'pendingPayments' => Invoice::where('payment_status', '!=', 'paid')->sum('balance'),
            'overdueInvoices' => Invoice::overdue()->count(),
        ];

        // Ventas por mes (últimos 12 meses)
        $monthlySales = Invoice::selectRaw('
                YEAR(invoice_date) as year,
                MONTH(invoice_date) as month,
                SUM(total) as total,
                COUNT(*) as count
            ')
            ->where('status', '!=', 'cancelled')
            ->where('invoice_date', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Top productos más vendidos
        $topProducts = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->selectRaw('
                products.name,
                products.code,
                SUM(invoice_items.quantity) as total_quantity,
                SUM(invoice_items.total) as total_amount
            ')
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.invoice_date', '>=', now()->subMonths(6))
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Top clientes por ventas
        $topClients = Invoice::selectRaw('
                client_name,
                COUNT(*) as invoice_count,
                SUM(total) as total_amount,
                SUM(paid_amount) as paid_amount,
                SUM(balance) as balance_amount
            ')
            ->where('status', '!=', 'cancelled')
            ->where('invoice_date', '>=', now()->subMonths(6))
            ->groupBy('client_name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Reports/Index', [
            'stats' => $stats,
            'monthlySales' => $monthlySales,
            'topProducts' => $topProducts,
            'topClients' => $topClients,
        ]);
    }

    public function sales(Request $request)
    {
        $query = Invoice::with(['client', 'creator'])
            ->where('status', '!=', 'cancelled');

        // Filtros
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        $invoices = $query->latest('invoice_date')->paginate(20)->withQueryString();

        // Estadísticas del período
        $periodStats = [
            'totalInvoices' => $query->count(),
            'totalAmount' => $query->sum('total'),
            'totalPaid' => $query->sum('paid_amount'),
            'totalBalance' => $query->sum('balance'),
            'averageInvoice' => $query->avg('total'),
        ];

        // Ventas por día
        $dailySales = $query->selectRaw('
                DATE(invoice_date) as date,
                COUNT(*) as invoice_count,
                SUM(total) as total_amount
            ')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Ventas por cliente
        $salesByClient = $query->selectRaw('
                client_name,
                COUNT(*) as invoice_count,
                SUM(total) as total_amount
            ')
            ->groupBy('client_name')
            ->orderBy('total_amount', 'desc')
            ->get();

        return Inertia::render('Reports/Sales', [
            'invoices' => $invoices,
            'clients' => Client::where('status', 'active')->get(['id', 'business_name', 'trade_name']),
            'statuses' => Invoice::getStatuses(),
            'paymentStatuses' => Invoice::getPaymentStatuses(),
            'periodStats' => $periodStats,
            'dailySales' => $dailySales,
            'salesByClient' => $salesByClient,
            'filters' => $request->only([
                'date_from', 'date_to', 'client_id', 'status', 'payment_status'
            ]),
        ]);
    }

    public function inventory(Request $request)
    {
        $query = Product::with(['category', 'inventoryMovements']);

        // Filtros
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
            switch ($request->get('stock_status')) {
                case 'low':
                    $query->where('stock_quantity', '<=', DB::raw('reorder_point'));
                    break;
                case 'out':
                    $query->where('stock_quantity', '=', 0);
                    break;
                case 'available':
                    $query->where('stock_quantity', '>', 0);
                    break;
            }
        }

        $products = $query->paginate(20)->withQueryString();

        // Estadísticas de inventario
        $inventoryStats = [
            'totalProducts' => Product::count(),
            'activeProducts' => Product::where('is_active', true)->count(),
            'lowStockProducts' => Product::whereRaw('stock_quantity <= reorder_point')->count(),
            'outOfStockProducts' => Product::where('stock_quantity', 0)->count(),
            'totalStockValue' => Product::sum(DB::raw('stock_quantity * cost_price')),
        ];

        // Movimientos recientes
        $recentMovements = Inventory::with(['product', 'creator'])
            ->latest('movement_date')
            ->limit(20)
            ->get();

        // Productos con bajo stock
        $lowStockProducts = Product::whereRaw('stock_quantity <= reorder_point')
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get();

        // Productos próximos a vencer
        $expiringProducts = Inventory::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->with(['product'])
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get();

        return Inertia::render('Reports/Inventory', [
            'products' => $products,
            'categories' => \App\Models\ProductCategory::all(['id', 'name']),
            'inventoryStats' => $inventoryStats,
            'recentMovements' => $recentMovements,
            'lowStockProducts' => $lowStockProducts,
            'expiringProducts' => $expiringProducts,
            'filters' => $request->only([
                'search', 'category_id', 'stock_status'
            ]),
        ]);
    }

    public function financial(Request $request)
    {
        $query = Invoice::where('status', '!=', 'cancelled');

        // Filtros
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->get('date_to'));
        }

        // Estadísticas financieras
        $financialStats = [
            'totalRevenue' => $query->sum('total'),
            'totalPaid' => $query->sum('paid_amount'),
            'totalOutstanding' => $query->sum('balance'),
            'overdueAmount' => $query->overdue()->sum('balance'),
            'averagePaymentTime' => $this->calculateAveragePaymentTime($query),
        ];

        // Flujo de caja por mes
        $cashFlow = Payment::selectRaw('
                YEAR(payment_date) as year,
                MONTH(payment_date) as month,
                SUM(amount) as total_amount,
                COUNT(*) as payment_count
            ')
            ->where('status', 'completed')
            ->where('payment_date', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Antigüedad de saldos
        $agingReport = [
            'current' => $query->where('due_date', '>=', now())->sum('balance'),
            '1-30' => $query->where('due_date', '<', now())
                ->where('due_date', '>=', now()->subDays(30))
                ->sum('balance'),
            '31-60' => $query->where('due_date', '<', now()->subDays(30))
                ->where('due_date', '>=', now()->subDays(60))
                ->sum('balance'),
            '61-90' => $query->where('due_date', '<', now()->subDays(60))
                ->where('due_date', '>=', now()->subDays(90))
                ->sum('balance'),
            'over_90' => $query->where('due_date', '<', now()->subDays(90))
                ->sum('balance'),
        ];

        // Métodos de pago más utilizados
        $paymentMethods = Payment::selectRaw('
                payment_method,
                COUNT(*) as count,
                SUM(amount) as total_amount
            ')
            ->where('status', 'completed')
            ->where('payment_date', '>=', now()->subMonths(6))
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Facturas vencidas
        $overdueInvoices = Invoice::overdue()
            ->with(['client'])
            ->orderBy('due_date', 'asc')
            ->limit(20)
            ->get();

        return Inertia::render('Reports/Financial', [
            'financialStats' => $financialStats,
            'cashFlow' => $cashFlow,
            'agingReport' => $agingReport,
            'paymentMethods' => $paymentMethods,
            'overdueInvoices' => $overdueInvoices,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    public function clients(Request $request)
    {
        $query = Client::with(['invoices', 'payments']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('trade_name', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('payment_status')) {
            $query->whereHas('invoices', function ($q) use ($request) {
                $q->where('payment_status', $request->get('payment_status'));
            });
        }

        $clients = $query->paginate(20)->withQueryString();

        // Estadísticas de clientes
        $clientStats = [
            'totalClients' => Client::count(),
            'activeClients' => Client::where('status', 'active')->count(),
            'inactiveClients' => Client::where('status', 'inactive')->count(),
            'clientsWithDebt' => Client::whereHas('invoices', function ($q) {
                $q->where('balance', '>', 0);
            })->count(),
        ];

        // Top clientes por ventas
        $topClientsBySales = Client::selectRaw('
                clients.*,
                COUNT(invoices.id) as invoice_count,
                SUM(invoices.total) as total_sales,
                SUM(invoices.paid_amount) as total_paid,
                SUM(invoices.balance) as total_balance
            ')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.invoice_date', '>=', now()->subMonths(12))
            ->groupBy('clients.id')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();

        // Clientes con mayor saldo pendiente
        $clientsWithHighestBalance = Client::selectRaw('
                clients.*,
                SUM(invoices.balance) as total_balance
            ')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.balance', '>', 0)
            ->groupBy('clients.id')
            ->orderBy('total_balance', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Reports/Clients', [
            'clients' => $clients,
            'clientStats' => $clientStats,
            'topClientsBySales' => $topClientsBySales,
            'clientsWithHighestBalance' => $clientsWithHighestBalance,
            'filters' => $request->only([
                'search', 'status', 'payment_status'
            ]),
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'sales');
        $format = $request->get('format', 'excel');

        switch ($type) {
            case 'sales':
                return $this->exportSales($request, $format);
            case 'inventory':
                return $this->exportInventory($request, $format);
            case 'financial':
                return $this->exportFinancial($request, $format);
            case 'clients':
                return $this->exportClients($request, $format);
            default:
                return back()->withErrors(['error' => 'Tipo de reporte no válido']);
        }
    }

    private function exportSales(Request $request, string $format)
    {
        $query = Invoice::with(['client', 'items.product'])
            ->where('status', '!=', 'cancelled');

        // Aplicar filtros
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->get('date_to'));
        }

        $invoices = $query->get();

        // Preparar datos para exportación
        $data = $invoices->map(function ($invoice) {
            return [
                'Número' => $invoice->invoice_number,
                'Cliente' => $invoice->client_name,
                'Fecha' => $invoice->invoice_date->format('d/m/Y'),
                'Vencimiento' => $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A',
                'Total' => $invoice->total,
                'Pagado' => $invoice->paid_amount,
                'Saldo' => $invoice->balance,
                'Estado' => $invoice->status_label,
                'Estado Pago' => $invoice->payment_status_label,
            ];
        });

        return $this->downloadFile($data, 'reporte_ventas', $format);
    }

    private function exportInventory(Request $request, string $format)
    {
        $query = Product::with(['category']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $products = $query->get();

        $data = $products->map(function ($product) {
            return [
                'Código' => $product->code,
                'Nombre' => $product->name,
                'Categoría' => $product->category->name ?? 'N/A',
                'Stock Actual' => $product->stock_quantity,
                'Punto Reorden' => $product->reorder_point,
                'Precio Costo' => $product->cost_price,
                'Precio Venta' => $product->sale_price,
                'Valor Stock' => $product->stock_quantity * $product->cost_price,
                'Estado' => $product->is_active ? 'Activo' : 'Inactivo',
            ];
        });

        return $this->downloadFile($data, 'reporte_inventario', $format);
    }

    private function exportFinancial(Request $request, string $format)
    {
        $query = Invoice::where('status', '!=', 'cancelled');

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->get('date_to'));
        }

        $invoices = $query->get();

        $data = $invoices->map(function ($invoice) {
            return [
                'Número' => $invoice->invoice_number,
                'Cliente' => $invoice->client_name,
                'Fecha' => $invoice->invoice_date->format('d/m/Y'),
                'Vencimiento' => $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A',
                'Total' => $invoice->total,
                'Pagado' => $invoice->paid_amount,
                'Saldo' => $invoice->balance,
                'Días Vencido' => $invoice->due_date ? max(0, now()->diffInDays($invoice->due_date, false)) : 0,
                'Estado Pago' => $invoice->payment_status_label,
            ];
        });

        return $this->downloadFile($data, 'reporte_financiero', $format);
    }

    private function exportClients(Request $request, string $format)
    {
        $query = Client::with(['invoices']);

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $clients = $query->get();

        $data = $clients->map(function ($client) {
            $totalSales = $client->invoices->where('status', '!=', 'cancelled')->sum('total');
            $totalPaid = $client->invoices->sum('paid_amount');
            $totalBalance = $client->invoices->sum('balance');

            return [
                'Razón Social' => $client->business_name,
                'Nombre Comercial' => $client->trade_name,
                'RUT/NIT' => $client->tax_id,
                'Email' => $client->email,
                'Teléfono' => $client->phone,
                'Total Ventas' => $totalSales,
                'Total Pagado' => $totalPaid,
                'Saldo Pendiente' => $totalBalance,
                'Estado' => $client->status === 'active' ? 'Activo' : 'Inactivo',
            ];
        });

        return $this->downloadFile($data, 'reporte_clientes', $format);
    }

    private function downloadFile(array $data, string $filename, string $format)
    {
        if ($format === 'csv') {
            return $this->downloadCsv($data, $filename);
        }

        // Por defecto Excel (implementar con Laravel Excel si está disponible)
        return $this->downloadCsv($data, $filename);
    }

    private function downloadCsv(array $data, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            if (!empty($data)) {
                // Escribir encabezados
                fputcsv($file, array_keys($data[0]));

                // Escribir datos
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function calculateAveragePaymentTime($query)
    {
        $invoices = $query->whereNotNull('paid_at')->get();
        
        if ($invoices->isEmpty()) {
            return 0;
        }

        $totalDays = $invoices->sum(function ($invoice) {
            return $invoice->invoice_date->diffInDays($invoice->paid_at);
        });

        return round($totalDays / $invoices->count());
    }
}
