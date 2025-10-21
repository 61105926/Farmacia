<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sistema de Optimización Farmacia Pando
    |--------------------------------------------------------------------------
    |
    | Configuraciones optimizadas para mejorar el rendimiento del sistema
    |
    */

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hora
        'prefix' => 'farmacia_pando_',
    ],

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    'performance' => [
        'eager_loading' => true,
        'query_optimization' => true,
        'memory_limit' => '256M',
        'max_execution_time' => 30,
    ],

    'security' => [
        'rate_limiting' => true,
        'csrf_protection' => true,
        'xss_protection' => true,
        'sql_injection_protection' => true,
    ],

    'logging' => [
        'enabled' => true,
        'level' => 'info',
        'channels' => ['daily', 'error'],
    ],

    'monitoring' => [
        'performance_monitoring' => true,
        'error_tracking' => true,
        'user_activity_logging' => true,
    ],
];
