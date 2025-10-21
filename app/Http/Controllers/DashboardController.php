<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Product;
use App\Models\Order;
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
            'users' => \Schema::hasTable('users') ? User::count() : 0,
            'clients' => \Schema::hasTable('clients') ? Client::count() : 0,
            'products' => \Schema::hasTable('products') ? Product::where('is_active', true)->count() : 0,
            'orders' => \Schema::hasTable('orders') ? Order::count() : 0,
            'invoices' => \Schema::hasTable('invoices') ? Invoice::count() : 0,
            'payments' => \Schema::hasTable('payments') ? Payment::sum('amount') : 0,
            'presales' => \Schema::hasTable('presales') ? DB::table('presales')->count() : 0,
            'sales' => \Schema::hasTable('sales') ? DB::table('sales')->count() : 0,
        ];
    }
    
    private function getMonthlyStats()
    {
        $currentMonth = Carbon::now();
        
        return [
            'orders' => \Schema::hasTable('orders') ? 
                Order::whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->count() : 0,
            'invoices' => \Schema::hasTable('invoices') ? 
                Invoice::whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->count() : 0,
            'revenue' => \Schema::hasTable('payments') ? 
                Payment::whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->sum('amount') : 0,
            'presales' => \Schema::hasTable('presales') ? 
                DB::table('presales')
                    ->whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->count() : 0,
            'sales' => \Schema::hasTable('sales') ? 
                DB::table('sales')
                    ->whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->count() : 0,
        ];
    }
    
    private function getOrderStats()
    {
        if (!\Schema::hasTable('orders')) {
            return ['pending' => 0, 'confirmed' => 0, 'processing' => 0, 'delivered' => 0];
        }
        
        return [
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
        ];
    }
    
    private function getClientStats()
    {
        if (!\Schema::hasTable('clients')) {
            return ['active' => 0, 'inactive' => 0, 'blocked' => 0];
        }
        
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
                'orders' => 0,
                'revenue' => 0,
                'presales' => 0,
                'sales' => 0,
            ];
            
            if (\Schema::hasTable('orders')) {
                $monthData['orders'] = Order::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
            }
            
            if (\Schema::hasTable('payments')) {
                $monthData['revenue'] = Payment::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount');
            }
            
            if (\Schema::hasTable('presales')) {
                $monthData['presales'] = DB::table('presales')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
            }
            
            if (\Schema::hasTable('sales')) {
                $monthData['sales'] = DB::table('sales')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
            }
            
            $salesChart[] = $monthData;
        }
        
        return $salesChart;
    }
    
    private function getRecentOrders()
    {
        if (!\Schema::hasTable('orders')) {
            return [];
        }
        
        return Order::with(['client:id,business_name', 'salesperson:id,name'])
            ->latest()
            ->limit(5)
            ->get();
    }
    
    private function getTopClients()
    {
        if (!\Schema::hasTable('clients')) {
            return [];
        }
        
        return Client::withCount(['orders', 'invoices'])
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();
    }
    
    private function getProductStats()
    {
        if (!\Schema::hasTable('products')) {
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'low_stock' => 0,
                'out_of_stock' => 0,
            ];
        }
        
        return [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'inactive' => Product::where('is_active', false)->count(),
            'low_stock' => Product::where('stock_quantity', '<=', 10)->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
        ];
    }
    
    private function getUserStats()
    {
        if (!\Schema::hasTable('users')) {
            return [
                'total' => 0,
                'active' => 0,
                'by_role' => [],
            ];
        }
        
        $userStats = [
            'total' => User::count(),
            'active' => User::count(), // Sin columna is_active
            'by_role' => [],
        ];
        
        // Estadísticas por rol si existe la tabla de roles
        if (\Schema::hasTable('model_has_roles')) {
            $roleStats = DB::table('users')
                ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('count(*) as count'))
                ->groupBy('roles.name')
                ->get();
                
            $userStats['by_role'] = $roleStats->pluck('count', 'name')->toArray();
        }
        
        return $userStats;
    }
    
    private function getAlerts()
    {
        $alerts = [];
        
        // Productos con stock bajo
        if (\Schema::hasTable('products')) {
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
        }
        
        // Clientes bloqueados
        if (\Schema::hasTable('clients')) {
            $blockedClients = Client::where('status', 'blocked')->count();
            if ($blockedClients > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Clientes Bloqueados',
                    'message' => "{$blockedClients} clientes están bloqueados",
                    'action' => '/clientes?filter=blocked'
                ];
            }
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
        
        // Crecimiento de ingresos
        if (\Schema::hasTable('payments')) {
            $currentRevenue = Payment::whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->sum('amount');
                
            $lastRevenue = Payment::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->sum('amount');
                
            if ($lastRevenue > 0) {
                $metrics['revenue_growth'] = round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 2);
            }
        }
        
        // Crecimiento de órdenes
        if (\Schema::hasTable('orders')) {
            $currentOrders = Order::whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->count();
                
            $lastOrders = Order::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count();
                
            if ($lastOrders > 0) {
                $metrics['order_growth'] = round((($currentOrders - $lastOrders) / $lastOrders) * 100, 2);
            }
        }
        
        // Crecimiento de clientes
        if (\Schema::hasTable('clients')) {
            $currentClients = Client::whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->count();
                
            $lastClients = Client::whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->count();
                
            if ($lastClients > 0) {
                $metrics['client_growth'] = round((($currentClients - $lastClients) / $lastClients) * 100, 2);
            }
        }
        
        return $metrics;
    }
    
    private function getDefaultStats()
    {
        return [
            'users' => 0,
            'clients' => 0,
            'products' => 0,
            'orders' => 0,
            'invoices' => 0,
            'payments' => 0,
            'presales' => 0,
            'sales' => 0,
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