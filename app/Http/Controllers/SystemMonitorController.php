<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SystemMonitorController extends Controller
{
    /**
     * Mostrar dashboard de monitoreo del sistema
     */
    public function index(): Response
    {
        $stats = $this->getSystemStats();
        $performance = $this->getPerformanceMetrics();
        $errors = $this->getRecentErrors();
        $users = $this->getUserActivity();

        return Inertia::render('System/Monitor', [
            'stats' => $stats,
            'performance' => $performance,
            'errors' => $errors,
            'users' => $users,
        ]);
    }

    /**
     * Obtener estadísticas del sistema
     */
    private function getSystemStats(): array
    {
        return Cache::remember('system_stats', 300, function () {
            try {
                return [
                    'total_users' => DB::table('users')->count(),
                    'active_users' => DB::table('users')->whereNull('blocked_at')->count(),
                    'total_clients' => DB::table('clients')->count(),
                    'active_clients' => DB::table('clients')->where('status', 'active')->count(),
                    'total_products' => DB::table('products')->count(),
                    'active_products' => DB::table('products')->where('is_active', true)->count(),
                    'total_presales' => DB::table('presales')->count(),
                    'total_sales' => DB::table('sales')->count(),
                    'database_size' => $this->getDatabaseSize(),
                    'cache_hits' => $this->getCacheStats(),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting system stats: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Obtener métricas de rendimiento
     */
    private function getPerformanceMetrics(): array
    {
        return [
            'memory_usage' => memory_get_usage(true) / 1024 / 1024, // MB
            'memory_peak' => memory_get_peak_usage(true) / 1024 / 1024, // MB
            'execution_time' => microtime(true) - LARAVEL_START,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Obtener errores recientes
     */
    private function getRecentErrors(): array
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (!file_exists($logFile)) {
                return [];
            }

            $lines = file($logFile);
            $recentErrors = [];

            // Obtener las últimas 50 líneas que contengan ERROR
            $errorLines = array_filter($lines, function($line) {
                return strpos($line, 'ERROR') !== false;
            });

            $recentErrors = array_slice($errorLines, -10);

            return array_map(function($line) {
                return [
                    'message' => trim($line),
                    'timestamp' => $this->extractTimestamp($line),
                ];
            }, $recentErrors);

        } catch (\Exception $e) {
            Log::error('Error reading log file: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener actividad de usuarios
     */
    private function getUserActivity(): array
    {
        try {
            return DB::table('users')
                ->select('id', 'name', 'email', 'last_login_at', 'created_at')
                ->orderBy('last_login_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting user activity: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener tamaño de la base de datos
     */
    private function getDatabaseSize(): string
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");

            return $result[0]->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Obtener estadísticas de caché
     */
    private function getCacheStats(): array
    {
        try {
            return [
                'driver' => config('cache.default'),
                'hits' => Cache::get('cache_hits', 0),
                'misses' => Cache::get('cache_misses', 0),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extraer timestamp de línea de log
     */
    private function extractTimestamp(string $line): string
    {
        preg_match('/\[(.*?)\]/', $line, $matches);
        return $matches[1] ?? 'N/A';
    }

    /**
     * Limpiar caché del sistema
     */
    public function clearCache(): \Illuminate\Http\RedirectResponse
    {
        try {
            Cache::flush();
            Log::info('System cache cleared by user: ' . auth()->user()->name);
            
            return back()->with('success', 'Caché del sistema limpiado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error clearing cache: ' . $e->getMessage());
            return back()->with('error', 'Error al limpiar el caché: ' . $e->getMessage());
        }
    }

    /**
     * Optimizar base de datos
     */
    public function optimizeDatabase(): \Illuminate\Http\RedirectResponse
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $optimized = 0;

            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
                $optimized++;
            }

            Log::info("Database optimized: {$optimized} tables by user: " . auth()->user()->name);
            
            return back()->with('success', "Base de datos optimizada: {$optimized} tablas procesadas.");
        } catch (\Exception $e) {
            Log::error('Error optimizing database: ' . $e->getMessage());
            return back()->with('error', 'Error al optimizar la base de datos: ' . $e->getMessage());
        }
    }
}
