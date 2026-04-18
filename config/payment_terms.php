<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Condiciones de Pago
    |--------------------------------------------------------------------------
    |
    | Define las condiciones de pago disponibles en el sistema.
    | Puedes modificar, agregar o eliminar términos según las necesidades
    | del negocio.
    |
    */

    'terms' => [
        [
            'name' => 'Efectivo',
            'code' => 'EFE',
            'days' => 0,
            'description' => 'Pago en efectivo',
            'is_default' => true,
        ],
        [
            'name' => 'Crédito',
            'code' => 'CRE',
            'days' => 0,
            'description' => 'Pago a crédito (días definidos en plazo)',
            'is_default' => false,
        ],
        [
            'name' => 'Transferencia',
            'code' => 'TRA',
            'days' => 0,
            'description' => 'Pago por transferencia bancaria',
            'is_default' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Días de Visita
    |--------------------------------------------------------------------------
    |
    | Define los días de la semana disponibles para programar visitas.
    |
    */

    'visit_days' => [
        'monday' => 'Lunes',
        'tuesday' => 'Martes',
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Frecuencia de Visitas
    |--------------------------------------------------------------------------
    |
    | Define las frecuencias disponibles para las visitas de vendedores.
    |
    */

    'visit_frequencies' => [
        'weekly' => 'Semanal',
        'biweekly' => 'Quincenal',
        'monthly' => 'Mensual',
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorías de Cliente
    |--------------------------------------------------------------------------
    |
    | Define las categorías de clientes según su volumen de compra.
    |
    */

    'categories' => [
        'A' => 'A - Alto volumen',
        'B' => 'B - Medio volumen',
        'C' => 'C - Bajo volumen',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de Cliente
    |--------------------------------------------------------------------------
    |
    | Define los tipos de clientes disponibles en el sistema.
    |
    */

    'client_types' => [
        'pharmacy' => 'Farmacia',
        'chain' => 'Cadena de Farmacias',
        'hospital' => 'Hospital',
        'clinic' => 'Clínica',
        'distributor' => 'Distribuidor',
        'other' => 'Otro',
    ],
];
