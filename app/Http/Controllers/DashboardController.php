<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Presale;
use App\Models\Invoice;
use App\Models\Payment;
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
        // Estadísticas de preventas por estado
        return [
            'pending' => Presale::where('status', 'draft')->count(),
            'confirmed' => Presale::where('status', 'confirmed')->count(),
            'processing' => Presale::where('status', 'confirmed')->count(), // Preventas confirmadas en proceso
            'delivered' => Presale::where('status', 'converted')->count(), // Preventas convertidas a ventas
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
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            $monthData = [
                'month' => $date->format('M Y'),
                'presales' => Presale::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'sales' => Sale::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'revenue' => Invoice::whereMonth('invoice_date', $date->month)
                    ->whereYear('invoice_date', $date->year)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total') ?? 0,
            ];
            
            $salesChart[] = $monthData;
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
                    'created_at' => $sale->created_at,
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
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        $metrics = [
            'revenue_growth' => 0,
            'order_growth' => 0,
            'client_growth' => 0,
        ];
        
        // Crecimiento de ingresos (basado en facturas)
        $currentRevenue = Invoice::whereMonth('invoice_date', $currentMonth->month)
            ->whereYear('invoice_date', $currentMonth->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total') ?? 0;
            
        $lastRevenue = Invoice::whereMonth('invoice_date', $lastMonth->month)
            ->whereYear('invoice_date', $lastMonth->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total') ?? 0;
            
        if ($lastRevenue > 0) {
            $metrics['revenue_growth'] = round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 2);
        }
        
        // Crecimiento de ventas
        $currentSales = Sale::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();
            
        $lastSales = Sale::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();
            
        if ($lastSales > 0) {
            $metrics['order_growth'] = round((($currentSales - $lastSales) / $lastSales) * 100, 2);
        }
        
        // Crecimiento de clientes
        $currentClients = Client::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();
            
        $lastClients = Client::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();
            
        if ($lastClients > 0) {
            $metrics['client_growth'] = round((($currentClients - $lastClients) / $lastClients) * 100, 2);
        }
        
        return $metrics;
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