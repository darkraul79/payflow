<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | Default tax rate applied to cart total. Can be overridden per calculation.
    | Example: 0.21 for 21% VAT
    |
    */

    'tax_rate' => env('COMMERCE_TAX_RATE', 0.21),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency for prices
    |
    */

    'currency' => env('COMMERCE_CURRENCY', 'EUR'),

    /*
    |--------------------------------------------------------------------------
    | Currency Symbol
    |--------------------------------------------------------------------------
    |
    | Currency symbol for display
    |
    */

    'currency_symbol' => env('COMMERCE_CURRENCY_SYMBOL', '€'),

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | Session key used to store cart data
    |
    */

    'session_key' => 'cart',

    /*
    |--------------------------------------------------------------------------
    | Order Number Format
    |--------------------------------------------------------------------------
    |
    | Format for generating order numbers
    | Available placeholders: {year}, {month}, {day}, {random}, {increment}
    |
    */

    'order_number_format' => 'ORD-{year}{month}-{random}',

    /*
    |--------------------------------------------------------------------------
    | Order Number Random Length
    |--------------------------------------------------------------------------
    |
    | Length of random string in order number
    |
    */

    'order_number_random_length' => 6,
];
