<?php

return [

    /*
    |--------------------------------------------------------------------------
    | País por defecto
    |--------------------------------------------------------------------------
    | Se usa cuando el tenant no tiene un país fiscal configurado.
    */
    'pais_default' => env('FACTURACION_PAIS', 'PE'),

    /*
    |--------------------------------------------------------------------------
    | Emisión asíncrona
    |--------------------------------------------------------------------------
    | true  = se despacha un Job y el POS no espera (recomendado en producción).
    | false = emisión síncrona (útil en desarrollo).
    */
    'async' => env('FACTURACION_ASYNC', false),

    /*
    |--------------------------------------------------------------------------
    | Almacenamiento de XML / PDF
    |--------------------------------------------------------------------------
    */
    'disk' => env('FACTURACION_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Configuración por país
    |--------------------------------------------------------------------------
    | Cada bloque es autocontenido. Agregar un país = agregar una clave aquí
    | y registrar su adaptador en FacturacionServiceProvider. Nada más cambia.
    */
    'paises' => [

        'PE' => [
            'driver'      => \App\Facturacion\Paises\Peru\SunatFacturador::class,
            'ambiente'    => env('SUNAT_AMBIENTE', 'beta'), // beta | produccion
            'endpoint'    => env('SUNAT_ENDPOINT', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'),
            'usuario_sol' => env('SUNAT_SOL_USER'),
            'clave_sol'   => env('SUNAT_SOL_PASS'),
            'certificado' => env('SUNAT_CERT_PATH'),   // ruta al .pem/.pfx
            'cert_clave'  => env('SUNAT_CERT_PASS'),
            'disk'        => env('FACTURACION_DISK', 'local'),
        ],

        // 'CO' => [ 'driver' => \App\Facturacion\Paises\Colombia\DianFacturador::class, ... ],
        // 'CL' => [ 'driver' => \App\Facturacion\Paises\Chile\SiiFacturador::class, ... ],
        // 'AR' => [ 'driver' => \App\Facturacion\Paises\Argentina\ArcaFacturador::class, ... ],
        // 'MX' => [ 'driver' => \App\Facturacion\Paises\Mexico\SatFacturador::class, ... ],
    ],
];
