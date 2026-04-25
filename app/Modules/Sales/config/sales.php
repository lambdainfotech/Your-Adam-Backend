<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    */
    'order_prefix' => env('ORDER_PREFIX', 'ORD'),

    'default_delivery_days' => env('DEFAULT_DELIVERY_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Pricing Settings
    |--------------------------------------------------------------------------
    */
    'tax_rate' => env('TAX_RATE', 0),

    'base_shipping' => env('BASE_SHIPPING', 0),

    'free_shipping_threshold' => env('FREE_SHIPPING_THRESHOLD', null),

    /*
    |--------------------------------------------------------------------------
    | Cart Settings
    |--------------------------------------------------------------------------
    */
    'cart_expiry_days' => env('CART_EXPIRY_DAYS', 30),

    'max_cart_items' => env('MAX_CART_ITEMS', 100),
];
