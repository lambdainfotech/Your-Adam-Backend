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
    'enabled' => [
        'Core',
        'Auth',
        'User',
        'Address',
        'Catalog',
        'Inventory',
        'Sales',
        'Marketing',
        'Notification',
        'Report',
        'Courier',
        'Audit',
    ],

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
