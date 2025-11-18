<?php

return [
    'key' => env('REDSYS_KEY'),
    'url_notification' => env('REDSYS_URL_NOTIFICATION', env('APP_URL').'/tienda-solidaria/cesta/pago/response'),
    'url_ok' => (env('APP_URL').'/tienda-solidaria/cesta/pedido/finalizado'),
    'url_ko' => (env('APP_URL').'/tienda-solidaria/cesta/pedido/finalizado'),
    'merchantcode' => env('REDSYS_MERCHANT_CODE'),
    'terminal' => env('REDSYS_TERMINAL', '1'),
    'enviroment' => env('REDSYS_ENVIROMENT', 'test'),
    'tradename' => env('REDSYS_TRADENAME', env('APP_NAME', 'Tienda Solidaria')),
    'currency' => '978', // EURO
    'transactiontype' => '0',
    'version' => 'HMAC_SHA256_V1',
];
