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
            'name' => 'Contado',
            'code' => 'CONT',
            'days' => 0,
            'description' => 'Pago al contado',
            'is_default' => true,
        ],
        [
            'name' => '15 días',
            'code' => '15D',
            'days' => 15,
            'description' => 'Pago a 15 días',
            'is_default' => false,
        ],
        [
            'name' => '30 días',
            'code' => '30D',
            'days' => 30,
            'description' => 'Pago a 30 días',
            'is_default' => false,
        ],
        [
            'name' => '45 días',
            'code' => '45D',
            'days' => 45,
            'description' => 'Pago a 45 días',
            'is_default' => false,
        ],
        [
            'name' => '60 días',
            'code' => '60D',
            'days' => 60,
            'description' => 'Pago a 60 días',
            'is_default' => false,
        ],
        [
            'name' => '90 días',
            'code' => '90D',
            'days' => 90,
            'description' => 'Pago a 90 días',
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
