<?php

namespace App\Services;

use App\Models\ShippingMethod;

/**
 * Servicio para gestionar en sesión el método de envío seleccionado y sus importes.
 */
final class ShippingSession
{
    private const KEY_METHOD_ID = 'cart_shipping_method_id';

    private const KEY_METHOD_NAME = 'cart_shipping_name';

    private const KEY_METHOD_COST = 'cart_shipping_cost';

    public static function set(ShippingMethod $method): void
    {
        session([
            self::KEY_METHOD_ID => $method->id,
            self::KEY_METHOD_NAME => $method->name,
            self::KEY_METHOD_COST => $method->price,
        ]);
    }

    public static function name(): string
    {
        return session(self::KEY_METHOD_NAME, '');
    }

    public static function cost(): float
    {
        return (float) session(self::KEY_METHOD_COST, 0.0);
    }

    public static function clear(): void
    {
        session()->forget([self::KEY_METHOD_ID, self::KEY_METHOD_NAME, self::KEY_METHOD_COST]);
    }

    public static function has(): bool
    {
        return self::id() !== null;
    }

    public static function id(): ?int
    {
        return session(self::KEY_METHOD_ID);
    }
}
