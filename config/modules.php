<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled Modules
    |--------------------------------------------------------------------------
    |
    | Modules have been disabled. Using default Laravel structure instead.
    |
    */
    'enabled' => [],

    /*
    |--------------------------------------------------------------------------
    | Module Paths
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'modules' => base_path('app/Modules'),
        'migrations' => 'Database/Migrations',
        'seeders' => 'Database/Seeders',
        'routes' => 'Routes',
        'config' => 'Config',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    */
    'namespace' => 'App\\Modules',
];
