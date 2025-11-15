<?php

use App\Enums\PaymentMethod;

return [
    'default_method' => PaymentMethod::TARJETA,
    'allowed_methods' => [
        PaymentMethod::BIZUM,
        PaymentMethod::TARJETA,
    ],
];
