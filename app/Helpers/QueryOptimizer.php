<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueryOptimizer
{
    /**
     * Optimizar consulta con eager loading
     */
    public static function withEagerLoading(Builder $query, array $relations): Builder
    {
        return $query->with($relations);
    }

    /**
     * Cache de consultas frecuentes
     */
    public static function cacheQuery(string $key, callable $callback, int $ttl = 3600)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Optimizar paginación
     */
    public static function optimizePagination(Builder $query, int $perPage = 15): Builder
    {
        return $query->paginate($perPage);
    }

    /**
     * Optimizar consultas con índices
     */
    public static function addIndexHints(Builder $query, string $table, string $index): Builder
    {
        return $query->from(DB::raw("`{$table}` USE INDEX (`{$index}`)"));
    }

    /**
     * Limpiar consultas innecesarias
     */
    public static function cleanQuery(Builder $query): Builder
    {
        // Remover selects innecesarios si no se especifican
        if (empty($query->getQuery()->columns)) {
            $query->select('*');
        }

        return $query;
    }

    /**
     * Optimizar joins
     */
    public static function optimizeJoins(Builder $query, array $joins): Builder
    {
        foreach ($joins as $join) {
            $query->join($join['table'], $join['first'], $join['operator'], $join['second']);
        }

        return $query;
    }

    /**
     * Aplicar filtros de manera eficiente
     */
    public static function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, 'like', "%{$value}%");
                }
            }
        }

        return $query;
    }

    /**
     * Optimizar ordenamiento
     */
    public static function optimizeOrderBy(Builder $query, string $column, string $direction = 'asc'): Builder
    {
        return $query->orderBy($column, $direction);
    }

    /**
     * Limitar resultados para consultas grandes
     */
    public static function limitResults(Builder $query, int $limit = 1000): Builder
    {
        return $query->limit($limit);
    }

    /**
     * Optimizar consultas de estadísticas
     */
    public static function optimizeStats(Builder $query): Builder
    {
        return $query->selectRaw('COUNT(*) as total')
                    ->selectRaw('SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as last_30_days')
                    ->selectRaw('SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as last_7_days');
    }
}
