<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used
    | when no specific gateway is specified.
    |
    */

    'default' => env('PAYMENT_GATEWAY_DEFAULT', 'redsys'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure each payment gateway. Add or remove gateways
    | as needed for your application.
    |
    */

    'gateways' => [
        'redsys' => [
            'key' => env('REDSYS_KEY'),
            'merchant_code' => env('REDSYS_MERCHANT_CODE'),
            'terminal' => env('REDSYS_TERMINAL', '1'),
            'currency' => env('REDSYS_CURRENCY', '978'), // EUR
            'transaction_type' => env('REDSYS_TRANSACTION_TYPE', '0'),
            'trade_name' => env('REDSYS_TRADE_NAME', env('APP_NAME')),
            'environment' => env('REDSYS_ENVIRONMENT', 'test'), // test or production
            'version' => env('REDSYS_VERSION', 'HMAC_SHA256_V1'),
            'url_notification' => env('REDSYS_URL_NOTIFICATION'),
            'url_ok' => env('REDSYS_URL_OK'),
            'url_ko' => env('REDSYS_URL_KO'),
        ],

        'stripe' => [
            'api_key' => env('STRIPE_API_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'eur'),
        ],

        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
        ],
    ],
];
