<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Presale;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles');
        
        try {
            // Estadísticas generales con verificación de tablas
            $stats = $this->getGeneralStats();
            
            // Estadísticas del mes actual
            $monthlyStats = $this->getMonthlyStats();
            
            // Estadísticas de órdenes por estado
            $orderStats = $this->getOrderStats();
            
            // Clientes activos vs inactivos
            $clientStats = $this->getClientStats();
            
            // Ventas de los últimos 6 meses para gráfico
            $salesChart = $this->getSalesChart();
            
            // Órdenes recientes
            $recentOrders = $this->getRecentOrders();
            
            // Clientes con mayor actividad
            $topClients = $this->getTopClients();
            
            // Estadísticas de productos
            $productStats = $this->getProductStats();
            
            // Estadísticas de usuarios
            $userStats = $this->getUserStats();
            
            // Alertas y notificaciones
            $alerts = $this->getAlerts();
            
            // Métricas de rendimiento
            $performanceMetrics = $this->getPerformanceMetrics();
            
            // Productos próximos a vencer
            $expiringProducts = $this->getExpiringProducts();

            return Inertia::render('Dashboard', [
                'user' => $user,
                'stats' => $this->sanitizeData($stats),
                'monthlyStats' => $this->sanitizeData($monthlyStats),
                'orderStats' => $this->sanitizeData($orderStats),
                'clientStats' => $this->sanitizeData($clientStats),
                'salesChart' => $this->sanitizeData($salesChart),
                'recentOrders' => $this->sanitizeData($recentOrders),
                'topClients' => $this->sanitizeData($topClients),
                'productStats' => $this->sanitizeData($productStats),
                'userStats' => $this->sanitizeData($userStats),
                'alerts' => $this->sanitizeData($alerts),
                'performanceMetrics' => $this->sanitizeData($performanceMetrics),
                'expiringProducts' => $this->sanitizeData($expiringProducts),
                // Analytics
                'analyticsKpis'       => $this->sanitizeData($this->getAnalyticsKpis()),
                'monthlyChartData'    => $this->sanitizeData($this->getMonthlyChartData()),
                'periodComparison'    => $this->sanitizeData($this->getPeriodComparisonData()),
                'receivablesBreakdown'=> $this->sanitizeData($this->getReceivablesBreakdown()),
                'salesProjection'     => $this->sanitizeData($this->getSalesProjection()),
                'receivablesProjection'=> $this->sanitizeData($this->getReceivablesProjection()),
                'churnedClients'      => $this->sanitizeData($this->getChurnedClients()),
                'cobroCalendario'     => $this->sanitizeData($this->getCobroCalendario()),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            
            return Inertia::render('Dashboard', [
                'user' => $user,
                'stats' => $this->sanitizeData($this->getDefaultStats()),
                'error' => 'Error al cargar estadísticas: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getGeneralStats()
    {
        return [
            'users' => User::count(),
            'clients' => Client::count(),
            'products' => Product::where('is_active', true)->count(),
            'sales' => Sale::count(),
            'presales' => Presale::count(),
            'invoices' => Invoice::where('status', '!=', 'cancelled')->count(),
            'payments' => Payment::sum('amount') ?? 0,
        ];
    }
    
    private function getMonthlyStats()
    {
        $currentMonth = Carbon::now();
        
        return [
            'presales' => Presale::whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->count(),
            'sales' => Sale::whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->count(),
            'revenue' => Invoice::whereMonth('invoice_date', $currentMonth->month)
                ->whereYear('invoice_date', $currentMonth->year)
                ->where('status', '!=', 'cancelled')
                ->sum('total') ?? 0,
        ];
    }
    
    private function getOrderStats()
    {
        return [
            'pending'   => Presale::where('status', 'draft')->count()
                         + Sale::where('status', 'pending')->count(),
            // Solo entregas físicas confirmadas, sin mezclar con estado de pago
            'delivered' => Sale::where('status', 'completed')
                               ->where('payment_status', 'paid')
                               ->count()
                         + Presale::where('status', 'converted')->count(),
            // Sin pagar: payment_status pending (nunca pagaron)
            'unpaid'    => Sale::where('payment_status', 'pending')
                               ->where('status', '!=', 'cancelled')
                               ->count(),
            // Pagos parciales: entregado pero cobro incompleto
            'partial'   => Sale::where('payment_status', 'partial')
                               ->where('status', '!=', 'cancelled')
                               ->count(),
        ];
    }
    
    private function getClientStats()
    {
        return [
            'active' => Client::where('status', 'active')->count(),
            'inactive' => Client::where('status', 'inactive')->count(),
            'blocked' => Client::where('status', 'blocked')->count(),
        ];
    }
    
    private function getSalesChart()
    {
        $salesChart = [];
        for ($i = 0; $i <= 5; $i++) {
            $date = Carbon::now()->subMonths($i);

            $presales = Presale::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $sales = Sale::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            // Ingresos: ventas si hay, si no suma de lotes ingresados ese mes
            $revenue = Sale::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', '!=', 'cancelled')
                ->sum('total');

            if ($revenue == 0) {
                // Usar valor de lotes ingresados como referencia de movimiento
                $revenue = DB::table('batches')
                    ->whereMonth('entry_date', $date->month)
                    ->whereYear('entry_date', $date->year)
                    ->selectRaw('COALESCE(SUM(initial_quantity * cost_price), 0) as total')
                    ->value('total') ?? 0;
            }

            $purchases = DB::table('batches')
                ->whereMonth('entry_date', $date->month)
                ->whereYear('entry_date', $date->year)
                ->count();

            // Solo incluir meses con alguna actividad
            if ($presales > 0 || $sales > 0 || $purchases > 0) {
                $salesChart[] = [
                    'month'     => $date->format('M Y'),
                    'presales'  => $presales,
                    'sales'     => $sales,
                    'purchases' => $purchases,
                    'revenue'   => round($revenue, 2),
                ];
            }
        }

        return $salesChart;
    }
    
    private function getRecentOrders()
    {
        // Obtener ventas recientes
        return Sale::with(['client:id,business_name', 'salesperson:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'code' => $sale->code,
                    'client' => $sale->client ? [
                        'id' => $sale->client->id,
                        'business_name' => $sale->client->business_name,
                    ] : null,
                    'salesperson' => $sale->salesperson ? [
                        'id' => $sale->salesperson->id,
                        'name' => $sale->salesperson->name,
                    ] : null,
                    'status' => $sale->status,
                    'total' => $sale->total,
                    'created_at' => $sale->created_at?->format('Y-m-d H:i:s'),
                ];
            });
    }
    
    private function getTopClients()
    {
        return Client::withCount(['sales', 'invoices'])
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'business_name' => $client->business_name,
                    'trade_name' => $client->trade_name,
                    'orders_count' => $client->sales_count ?? 0,
                    'invoices_count' => $client->invoices_count ?? 0,
                ];
            });
    }
    
    private function getProductStats()
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'low_stock' => Product::where('stock_quantity', '<=', 10)
                ->where('stock_quantity', '>', 0)
                ->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
        ];
    }
    
    private function getUserStats()
    {
        $userStats = [
            'total' => User::count(),
            'active' => User::count(),
            'by_role' => [],
        ];
        
        // Estadísticas por rol si existe la tabla de roles
        try {
            $roleStats = DB::table('users')
                ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('count(*) as count'))
                ->groupBy('roles.name')
                ->get();
                
            $userStats['by_role'] = $roleStats->pluck('count', 'name')->toArray();
        } catch (\Exception $e) {
            // Si no existe la tabla de roles, simplemente no agregamos estadísticas por rol
            $userStats['by_role'] = [];
        }
        
        return $userStats;
    }
    
    private function getAlerts()
    {
        $alerts = [];
        
        // Productos con stock bajo
        $lowStockProducts = Product::where('stock_quantity', '<=', 10)
            ->where('stock_quantity', '>', 0)
            ->count();
            
        if ($lowStockProducts > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Stock Bajo',
                'message' => "{$lowStockProducts} productos tienen stock bajo",
                'action' => '/productos?filter=low_stock'
            ];
        }
        
        // Productos sin stock
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();
        if ($outOfStockProducts > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Sin Stock',
                'message' => "{$outOfStockProducts} productos están sin stock",
                'action' => '/productos?filter=out_of_stock'
            ];
        }
        
        // Productos próximos a vencer
        $expiringProductsCount = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::today()->addDays(90))
            ->where('is_active', true)
            ->count();
        if ($expiringProductsCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Productos Próximos a Vencer',
                'message' => "{$expiringProductsCount} productos vencen en los próximos 90 días",
                'action' => '/inventario/por-vencer'
            ];
        }
        
        // Facturas vencidas
        $overdueInvoices = Invoice::where('due_date', '<', now())
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->count();
        if ($overdueInvoices > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Facturas Vencidas',
                'message' => "{$overdueInvoices} facturas están vencidas",
                'action' => '/cuentas-por-cobrar?filter=overdue'
            ];
        }
        
        // Clientes bloqueados
        $blockedClients = Client::where('status', 'blocked')->count();
        if ($blockedClients > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Clientes Bloqueados',
                'message' => "{$blockedClients} clientes están bloqueados",
                'action' => '/clientes?filter=blocked'
            ];
        }
        
        return $alerts;
    }
    
    private function getPerformanceMetrics()
    {
        $now         = Carbon::now();
        $currentFrom = $now->copy()->startOfMonth();
        $currentTo   = $now->copy()->endOfMonth();
        $lastFrom    = $now->copy()->subMonth()->startOfMonth();
        $lastTo      = $now->copy()->subMonth()->endOfMonth();

        // Ingresos: suma de ventas del mes (no canceladas)
        $currentRevenue = Sale::whereBetween('created_at', [$currentFrom, $currentTo])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $lastRevenue = Sale::whereBetween('created_at', [$lastFrom, $lastTo])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Órdenes: cantidad de ventas del mes
        $currentSales = Sale::whereBetween('created_at', [$currentFrom, $currentTo])->count();
        $lastSales    = Sale::whereBetween('created_at', [$lastFrom, $lastTo])->count();

        // Clientes: total acumulado hasta fin de cada mes
        $currentClients = Client::where('created_at', '<=', $currentTo)->count();
        $lastClients    = Client::where('created_at', '<=', $lastTo)->count();

        return [
            'revenue_growth' => $this->growthPercent($lastRevenue, $currentRevenue),
            'order_growth'   => $this->growthPercent($lastSales, $currentSales),
            'client_growth'  => $this->growthPercent($lastClients, $currentClients),
            // Valores absolutos para referencia en la vista
            'current_revenue' => round($currentRevenue, 2),
            'last_revenue'    => round($lastRevenue, 2),
            'current_sales'   => $currentSales,
            'last_sales'      => $lastSales,
            'current_clients' => $currentClients,
            'last_clients'    => $lastClients,
        ];
    }

    private function growthPercent($previous, $current): float
    {
        if ($previous == 0 && $current == 0) return 0.0;
        if ($previous == 0) return 100.0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    private function getExpiringProducts()
    {
        $today = Carbon::today();
        $ninetyDaysFromNow = Carbon::today()->addDays(90);
        
        // Productos vencidos o que vencen en los próximos 90 días
        return Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $ninetyDaysFromNow)
            ->where('is_active', true)
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) use ($today) {
                $expiryDate = Carbon::parse($product->expiry_date);
                $daysUntilExpiry = $today->diffInDays($expiryDate, false);
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'description' => $product->description,
                    'expiry_date' => $expiryDate->format('Y-m-d'),
                    'stock_quantity' => $product->stock_quantity,
                    'days_until_expiry' => $daysUntilExpiry,
                    'is_expired' => $expiryDate < $today,
                    'is_expiring_soon' => $daysUntilExpiry > 0 && $daysUntilExpiry <= 30,
                ];
            });
    }
    
    private function getDefaultStats()
    {
        return [
            'users' => 0,
            'clients' => 0,
            'products' => 0,
            'sales' => 0,
            'presales' => 0,
            'invoices' => 0,
            'payments' => 0,
        ];
    }
    
    // ─── ANALYTICS ────────────────────────────────────────────────────────────

    private function getAnalyticsKpis(): array
    {
        $now         = Carbon::now();
        $curFrom     = $now->copy()->startOfMonth();
        $curTo       = $now->copy()->endOfMonth();
        $lastFrom    = $now->copy()->subMonth()->startOfMonth();
        $lastTo      = $now->copy()->subMonth()->endOfMonth();

        $ventasMes   = Sale::whereBetween('created_at', [$curFrom, $curTo])->where('status', '!=', 'cancelled')->sum('total') ?? 0;
        $ventasAnt   = Sale::whereBetween('created_at', [$lastFrom, $lastTo])->where('status', '!=', 'cancelled')->sum('total') ?? 0;
        $cantMes     = Sale::whereBetween('created_at', [$curFrom, $curTo])->where('status', '!=', 'cancelled')->count();
        $cantAnt     = Sale::whereBetween('created_at', [$lastFrom, $lastTo])->where('status', '!=', 'cancelled')->count();

        $cobrosMes   = Payment::whereBetween('payment_date', [$curFrom, $curTo])->where('status', 'completed')->sum('amount') ?? 0;
        $cobrosAnt   = Payment::whereBetween('payment_date', [$lastFrom, $lastTo])->where('status', 'completed')->sum('amount') ?? 0;

        $today = Carbon::today();

        // Usar Invoice igual que el módulo de Cuentas por Cobrar
        $porCobrar    = (float) Invoice::where('status', '!=', 'cancelled')
                            ->where('payment_status', '!=', 'paid')
                            ->sum('balance');

        $vencido      = (float) Invoice::overdue()->sum('balance');

        $clientesVenc = Invoice::overdue()
                            ->distinct('client_id')->count('client_id');

        return [
            'ventas_mes'          => round($ventasMes, 2),
            'ventas_anterior'     => round($ventasAnt, 2),
            'ventas_growth'       => $this->growthPercent($ventasAnt, $ventasMes),
            'cant_ventas_mes'     => $cantMes,
            'cant_growth'         => $this->growthPercent($cantAnt, $cantMes),
            'cobros_mes'          => round($cobrosMes, 2),
            'cobros_anterior'     => round($cobrosAnt, 2),
            'cobros_growth'       => $this->growthPercent($cobrosAnt, $cobrosMes),
            'total_por_cobrar'    => round($porCobrar, 2),
            'total_vencido'       => round($vencido, 2),
            'clientes_vencidos'   => $clientesVenc,
        ];
    }

    private function getMonthlyChartData(): array
    {
        $names = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $data  = [];

        for ($i = 11; $i >= 0; $i--) {
            $d    = Carbon::now()->subMonths($i);
            $from = $d->copy()->startOfMonth();
            $to   = $d->copy()->endOfMonth();

            $ventas  = Sale::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelled')->sum('total') ?? 0;
            $cobros  = Payment::whereBetween('payment_date', [$from, $to])->where('status', 'completed')->sum('amount') ?? 0;
            $presales = Presale::whereBetween('created_at', [$from, $to])->where('status', '!=', 'cancelled')->count();

            $data[] = [
                'label'    => $names[$d->month - 1] . ' ' . $d->year,
                'ventas'   => round($ventas, 2),
                'cobros'   => round($cobros, 2),
                'presales' => $presales,
            ];
        }

        return $data;
    }

    private function getPeriodComparisonData(): array
    {
        $names    = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $curYear  = Carbon::now()->year;
        $prevYear = $curYear - 1;
        $curMonth = Carbon::now()->month;

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $curVal = $m <= $curMonth
                ? round(Sale::whereYear('created_at', $curYear)->whereMonth('created_at', $m)->where('status', '!=', 'cancelled')->sum('total') ?? 0, 2)
                : null;
            $prevVal = round(Sale::whereYear('created_at', $prevYear)->whereMonth('created_at', $m)->where('status', '!=', 'cancelled')->sum('total') ?? 0, 2);

            $months[] = [
                'month'         => $names[$m - 1],
                'current_year'  => $curVal,
                'previous_year' => $prevVal,
            ];
        }

        return [
            'current_year'  => $curYear,
            'previous_year' => $prevYear,
            'months'        => $months,
        ];
    }

    private function getReceivablesBreakdown(): array
    {
        $today = Carbon::today();
        $base  = Invoice::where('status', '!=', 'cancelled')->where('payment_status', '!=', 'paid')->where('balance', '>', 0);

        return [
            'al_dia'        => round((clone $base)->where('due_date', '>=', $today)->sum('balance'), 2),
            'vencido_30'    => round((clone $base)->where('due_date', '<', $today)->where('due_date', '>=', $today->copy()->subDays(30))->sum('balance'), 2),
            'vencido_60'    => round((clone $base)->where('due_date', '<', $today->copy()->subDays(30))->where('due_date', '>=', $today->copy()->subDays(60))->sum('balance'), 2),
            'vencido_mas60' => round((clone $base)->where('due_date', '<', $today->copy()->subDays(60))->sum('balance'), 2),
        ];
    }

    private function getSalesProjection(): array
    {
        $names = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

        // Intentar con hasta 12 meses de historial para tener suficiente base
        $rawHistory = [];
        for ($i = 12; $i >= 1; $i--) {
            $d   = Carbon::now()->subMonths($i);
            $val = (float) (Sale::whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->where('status', '!=', 'cancelled')
                ->sum('total') ?? 0);
            $rawHistory[] = ['month' => $d->format('M Y'), 'value' => $val];
        }

        // Filtrar solo meses con datos para el cálculo
        $withData = array_filter($rawHistory, fn($m) => $m['value'] > 0);
        $hasData  = count($withData) > 0;

        // Usar los últimos 3 meses con datos (o todos si hay menos de 3)
        $recent = array_slice(array_values($withData), -3);
        $vals   = array_column($recent, 'value');

        $avg = $hasData ? array_sum($vals) / count($vals) : 0;

        // Tendencia: variación porcentual promedio entre períodos consecutivos
        $trend = 0;
        if (count($vals) >= 2) {
            $changes = [];
            for ($i = 1; $i < count($vals); $i++) {
                if ($vals[$i - 1] > 0) {
                    $changes[] = ($vals[$i] - $vals[$i - 1]) / $vals[$i - 1];
                }
            }
            $trend = count($changes) > 0 ? array_sum($changes) / count($changes) : 0;
        }
        $trend = max(-0.25, min(0.25, $trend));

        // Mejor y peor mes del historial
        $allVals    = array_column($rawHistory, 'value');
        $bestVal    = $hasData ? max($allVals) : 0;
        $worstVal   = $hasData ? min(array_filter($allVals)) : 0;
        $bestIdx    = $bestVal > 0 ? array_search($bestVal, $allVals) : null;
        $worstIdx   = ($worstVal > 0 && $worstVal !== $bestVal) ? array_search($worstVal, $allVals) : null;

        // Mes actual (en curso)
        $currentMonth = (float) (Sale::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->where('status', '!=', 'cancelled')
            ->sum('total') ?? 0);
        $daysInMonth  = Carbon::now()->daysInMonth;
        $dayOfMonth   = Carbon::now()->day;
        $projectedCurrent = $dayOfMonth > 0 ? round($currentMonth / $dayOfMonth * $daysInMonth, 2) : 0;

        $projection = [];
        for ($i = 1; $i <= 3; $i++) {
            $d   = Carbon::now()->addMonths($i);
            $est = round($avg * pow(1 + $trend, $i), 2);
            $projection[] = [
                'label'        => $names[$d->month - 1] . ' ' . $d->year,
                'estimated'    => max(0, $est),
                'growth'       => round($trend * 100, 1),
                'growth_abs'   => round($trend * $i * 100, 1),
            ];
        }

        return [
            'avg_base'          => round($avg, 2),
            'trend_pct'         => round($trend * 100, 1),
            'projection'        => $projection,
            'has_data'          => $hasData,
            'months_with_data'  => count($withData),
            'best_month'        => $bestIdx !== null ? ['label' => $rawHistory[$bestIdx]['month'], 'value' => $bestVal] : null,
            'worst_month'       => $worstIdx !== null ? ['label' => $rawHistory[$worstIdx]['month'], 'value' => $worstVal] : null,
            'current_month_val' => round($currentMonth, 2),
            'current_projected' => $projectedCurrent,
            'history'           => array_map(fn($m) => ['label' => $m['month'], 'value' => $m['value']], $rawHistory),
        ];
    }

    private function getReceivablesProjection(): array
    {
        $names = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $windows = [
            ['label' => 'Próximos 30 días',  'from' => Carbon::today(),          'to' => Carbon::today()->addDays(30)],
            ['label' => 'Próximos 31-60 días','from' => Carbon::today()->addDays(31), 'to' => Carbon::today()->addDays(60)],
            ['label' => 'Próximos 61-90 días','from' => Carbon::today()->addDays(61), 'to' => Carbon::today()->addDays(90)],
        ];

        $projection = [];
        foreach ($windows as $w) {
            $base   = Invoice::where('status', '!=', 'cancelled')->where('payment_status', '!=', 'paid')->where('balance', '>', 0);
            $amount = (clone $base)->whereBetween('due_date', [$w['from'], $w['to']])->sum('balance');
            $count  = (clone $base)->whereBetween('due_date', [$w['from'], $w['to']])->count();
            $projection[] = [
                'label'  => $w['label'],
                'amount' => round($amount, 2),
                'count'  => $count,
            ];
        }

        // Próximos 6 meses detallado (para gráfico)
        $monthly = [];
        for ($i = 0; $i <= 5; $i++) {
            $d    = Carbon::now()->addMonths($i);
            $from = $d->copy()->startOfMonth();
            $to   = $d->copy()->endOfMonth();
            if ($from->lt(Carbon::today())) $from = Carbon::today();
            if ($from->gt($to)) continue;

            $amount = Invoice::where('status', '!=', 'cancelled')
                ->where('payment_status', '!=', 'paid')
                ->where('balance', '>', 0)
                ->whereBetween('due_date', [$from, $to])
                ->sum('balance');

            $monthly[] = [
                'label'  => $names[$d->month - 1] . ' ' . $d->year,
                'amount' => round($amount, 2),
            ];
        }

        return [
            'windows' => $projection,
            'monthly' => $monthly,
            'total_proyectado' => round(array_sum(array_column($projection, 'amount')), 2),
        ];
    }

    private function getCobroCalendario(): array
    {
        $today = Carbon::today();
        $end   = $today->copy()->addDays(90);

        // Usar Invoice igual que el módulo de Cuentas por Cobrar
        $receivables = Invoice::with('client:id,business_name,phone')
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', '!=', 'paid')
            ->where('balance', '>', 0)
            ->where('due_date', '>=', $today->copy()->subDays(30))
            ->where('due_date', '<=', $end)
            ->orderBy('due_date')
            ->get();

        // Agrupar por semana
        $byWeek = [];
        foreach ($receivables as $r) {
            $dueDate   = Carbon::parse($r->due_date);
            $weekStart = $dueDate->copy()->startOfWeek()->format('Y-m-d');
            $weekLabel = 'Sem. ' . $dueDate->copy()->startOfWeek()->format('d/m')
                       . ' - ' . $dueDate->copy()->endOfWeek()->format('d/m');

            if (!isset($byWeek[$weekStart])) {
                $byWeek[$weekStart] = [
                    'week_label' => $weekLabel,
                    'week_start' => $weekStart,
                    'total'      => 0,
                    'count'      => 0,
                    'clientes'   => [],
                    'overdue'    => $dueDate->lt($today),
                ];
            }

            $byWeek[$weekStart]['total']  += $r->balance;
            $byWeek[$weekStart]['count']  += 1;
            $byWeek[$weekStart]['clientes'][] = [
                'name'     => $r->client?->business_name ?? '—',
                'phone'    => $r->client?->phone,
                'balance'  => round($r->balance, 2),
                'due_date' => $dueDate->format('d/m/Y'),
                'overdue'  => $dueDate->lt($today),
                'days'     => (int) $today->diffInDays($dueDate, false),
            ];
        }

        // Ordenar por fecha y redondear totales
        ksort($byWeek);
        foreach ($byWeek as &$w) {
            $w['total'] = round($w['total'], 2);
            // Ordenar clientes: vencidos primero
            usort($w['clientes'], fn($a, $b) => $a['days'] <=> $b['days']);
        }

        // También agrupar por mes para el gráfico de barras
        $byMonth = [];
        foreach ($receivables as $r) {
            $dueDate    = Carbon::parse($r->due_date);
            $monthKey   = $dueDate->format('Y-m');
            $monthLabel = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][$dueDate->month - 1]
                        . ' ' . $dueDate->year;

            if (!isset($byMonth[$monthKey])) {
                $byMonth[$monthKey] = ['label' => $monthLabel, 'total' => 0, 'count' => 0, 'overdue' => $dueDate->lt($today)];
            }
            $byMonth[$monthKey]['total'] += $r->balance;
            $byMonth[$monthKey]['count'] += 1;
        }
        ksort($byMonth);
        foreach ($byMonth as &$m) {
            $m['total'] = round($m['total'], 2);
        }

        return [
            'by_week'  => array_values($byWeek),
            'by_month' => array_values($byMonth),
        ];
    }

    private function getChurnedClients(): array
    {
        // Clientes que compraron antes del mes pasado pero no tienen ventas en los últimos 60 días
        $cutoff     = Carbon::now()->subDays(60);
        $prevCutoff = Carbon::now()->subDays(180); // solo clientes que compraron en los últimos 6 meses

        $churnedIds = DB::table('sales')
            ->select('client_id')
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $prevCutoff)
            ->groupBy('client_id')
            ->havingRaw('MAX(created_at) < ?', [$cutoff])
            ->pluck('client_id')
            ->toArray();

        if (empty($churnedIds)) return [];

        return Client::whereIn('id', $churnedIds)
            ->limit(10)
            ->get()
            ->map(function ($client) {
                $lastSale = Sale::where('client_id', $client->id)
                    ->where('status', '!=', 'cancelled')
                    ->latest()
                    ->first();
                $daysSince = $lastSale ? (int) Carbon::parse($lastSale->created_at)->diffInDays(now()) : null;

                return [
                    'id'            => $client->id,
                    'name'          => $client->business_name ?? $client->nombre_comercial ?? 'Sin nombre',
                    'last_sale_date'=> $lastSale?->created_at?->format('Y-m-d'),
                    'last_sale_amount'=> $lastSale ? round($lastSale->total, 2) : 0,
                    'days_since'    => $daysSince,
                ];
            })->toArray();
    }

    // ─── END ANALYTICS ────────────────────────────────────────────────────────

    /**
     * Sanitizar datos para evitar valores null que causen problemas con Inertia
     */
    private function sanitizeData($data)
    {
        if ($data === null) {
            return [];
        }
        
        if (is_array($data)) {
            return array_map(function($item) {
                if ($item === null) {
                    return '';
                }
                if (is_array($item)) {
                    return $this->sanitizeData($item);
                }
                if (is_object($item)) {
                    return $this->sanitizeObject($item);
                }
                return $item;
            }, $data);
        }
        
        if (is_object($data)) {
            return $this->sanitizeObject($data);
        }
        
        return $data;
    }
    
    /**
     * Sanitizar objetos para evitar valores null
     */
    private function sanitizeObject($object)
    {
        if ($object === null) {
            return (object) [];
        }
        
        // Si es una Collection de Laravel, convertir a array
        if (method_exists($object, 'toArray')) {
            return $this->sanitizeData($object->toArray());
        }
        
        // Si es un objeto estándar, convertir a array
        $array = (array) $object;
        return $this->sanitizeData($array);
    }
}